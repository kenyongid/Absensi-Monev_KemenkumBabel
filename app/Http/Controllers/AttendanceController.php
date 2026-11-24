<?php

namespace App\Http\Controllers;

use App\Models\Attendance;
use App\Models\Employee;
use App\Models\OfficeSetting;
use App\Models\User;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class AttendanceController extends Controller
{
    public function __construct()
    {
        $this->middleware('auth');
    }

    /**
     * Dashboard Presensi Harian Non-ASN
     */
    public function index()
    {
        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $office = OfficeSetting::getActive();

        $attendance = Attendance::where('user_id', $user->id)
            ->whereDate('date', $today)
            ->first();

        return view('attendance.index', [
            'title' => 'Absensi & Monev Non-ASN',
            'active' => 'attendance',
            'user' => $user,
            'attendance' => $attendance,
            'office' => $office,
            'today' => Carbon::now()->locale('id')->translatedFormat('l, d F Y'),
        ]);
    }

    /**
     * Absen Masuk dengan Foto Wajah & Titik Lokasi Kantor
     */
    public function checkIn(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string', // Base64 data URL
        ], [
            'photo.required' => 'Foto wajah wajib diambil menggunakan kamera.',
            'latitude.required' => 'Titik lokasi GPS diperlukan untuk absensi.',
            'longitude.required' => 'Titik lokasi GPS diperlukan untuk absensi.',
        ]);

        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $office = OfficeSetting::getActive();

        // Cek apakah sudah absen masuk hari ini
        $existing = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();
        if ($existing && $existing->check_in_time) {
            if ($request->wantsJson()) {
                return response()->json([
                    'success' => false,
                    'message' => 'Anda sudah melakukan absen masuk hari ini.'
                ], 422);
            }
            notify()->warning('Anda sudah melakukan absen masuk hari ini.', 'Info');
            return redirect()->back();
        }

        // Hitung jarak ke lokasi kantor (Haversine formula dalam meter)
        $distance = $this->calculateDistance(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $office->latitude,
            (float) $office->longitude
        );

        // Validasi radius kantor (diabaikan jika radius_meters = 0 untuk mode testing/fleksibel)
        if ($office->radius_meters > 0 && $distance > $office->radius_meters) {
            $msg = "Absen Ditolak! Anda berada di luar radius kantor ({$distance} meter dari kantor). Batas toleransi maksimal adalah {$office->radius_meters} meter. Absensi hanya diterima di lokasi kantor.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            notify()->error($msg, 'Di Luar Area Kantor');
            return redirect()->back()->withInput();
        }

        // Simpan foto wajah dari base64
        $photoPath = $this->saveBase64Image($request->photo, 'attendance_checkin');

        // Tentukan status tepat waktu atau terlambat
        $now = Carbon::now();
        $checkInLimit = Carbon::parse($office->check_in_end);
        $checkInStatus = $now->gt($checkInLimit) ? 'terlambat' : 'tepat_waktu';
        $status = $now->gt($checkInLimit) ? 'terlambat' : 'hadir';

        $employeeId = $user->employee ? $user->employee->id : null;

        Attendance::updateOrCreate(
            [
                'user_id' => $user->id,
                'date' => $today,
            ],
            [
                'employee_id' => $employeeId,
                'check_in_time' => $now->toTimeString(),
                'check_in_photo' => $photoPath,
                'check_in_lat' => $request->latitude,
                'check_in_lng' => $request->longitude,
                'check_in_distance' => $distance,
                'check_in_status' => $checkInStatus,
                'status' => $status,
            ]
        );

        $statusMsg = $checkInStatus === 'terlambat' ? ' (Terlambat)' : ' (Tepat Waktu)';
        $successMsg = 'Absen masuk berhasil direkam' . $statusMsg . ' pada jam ' . $now->format('H:i');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        notify()->success($successMsg, 'Berhasil');
        return redirect()->route('attendance.index');
    }

    /**
     * Absen Pulang dengan Monev Aktivitas, Kendala (Min 100 kar), Bukti Foto (Maks 3), dan Foto Wajah
     */
    public function checkOut(Request $request)
    {
        $request->validate([
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'photo' => 'required|string', // Foto wajah pulang (base64)
            'monev_activity' => 'required|string|min:10',
            'monev_obstacle' => 'required|string|min:100', // MINIMAL 100 KARAKTER SESUAI PERMINTAAN USER!
            'activity_photos' => 'nullable|array|max:3', // MAKSIMAL 3 FOTO KEGIATAN!
            'activity_photos.*' => 'image|mimes:jpeg,png,jpg|max:3072',
        ], [
            'photo.required' => 'Foto wajah saat pulang wajib diambil menggunakan kamera.',
            'monev_activity.required' => 'Uraian aktivitas harian wajib diisi.',
            'monev_activity.min' => 'Uraian aktivitas minimal 10 karakter.',
            'monev_obstacle.required' => 'Kendala yang dihadapi wajib diisi minimal 100 karakter.',
            'monev_obstacle.min' => 'Kendala yang dilakukan/dihadapi wajib minimal 100 karakter.',
            'activity_photos.max' => 'Bukti foto kegiatan maksimal 3 foto.',
        ]);

        $user = auth()->user();
        $today = Carbon::today()->toDateString();
        $office = OfficeSetting::getActive();

        $attendance = Attendance::where('user_id', $user->id)->whereDate('date', $today)->first();

        if (!$attendance || !$attendance->check_in_time) {
            $msg = 'Anda belum melakukan absen masuk hari ini.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            notify()->error($msg, 'Gagal');
            return redirect()->back();
        }

        if ($attendance->check_out_time) {
            $msg = 'Anda sudah melakukan absen pulang hari ini.';
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            notify()->warning($msg, 'Info');
            return redirect()->back();
        }

        // Cek Geofencing lokasi kantor saat pulang
        $distance = $this->calculateDistance(
            (float) $request->latitude,
            (float) $request->longitude,
            (float) $office->latitude,
            (float) $office->longitude
        );

        // Cek Geofencing lokasi kantor saat pulang (diabaikan jika radius_meters = 0 untuk mode testing/fleksibel)
        if ($office->radius_meters > 0 && $distance > $office->radius_meters) {
            $msg = "Absen Pulang Ditolak! Anda berada di luar radius kantor ({$distance} meter dari kantor). Absen pulang dan monev hanya diterima di lokasi kantor.";
            if ($request->wantsJson()) {
                return response()->json(['success' => false, 'message' => $msg], 422);
            }
            notify()->error($msg, 'Di Luar Area Kantor');
            return redirect()->back()->withInput();
        }

        // Simpan foto wajah pulang
        $photoPath = $this->saveBase64Image($request->photo, 'attendance_checkout');

        // Simpan bukti foto kegiatan (maksimal 3 foto)
        $activityPhotoPaths = [];
        if ($request->hasFile('activity_photos')) {
            $files = array_slice($request->file('activity_photos'), 0, 3);
            foreach ($files as $file) {
                $path = $file->store('activity_photos', 'public');
                $activityPhotoPaths[] = $path;
            }
        }

        $now = Carbon::now();

        $attendance->update([
            'check_out_time' => $now->toTimeString(),
            'check_out_photo' => $photoPath,
            'check_out_lat' => $request->latitude,
            'check_out_lng' => $request->longitude,
            'check_out_distance' => $distance,
            'monev_activity' => $request->monev_activity,
            'monev_obstacle' => $request->monev_obstacle,
            'activity_photos' => $activityPhotoPaths,
        ]);

        $successMsg = 'Absen pulang dan pelaporan monev kegiatan berhasil disimpan pada jam ' . $now->format('H:i');

        if ($request->wantsJson()) {
            return response()->json(['success' => true, 'message' => $successMsg]);
        }

        notify()->success($successMsg, 'Berhasil Pulang');
        return redirect()->route('attendance.index');
    }

    /**
     * Rekapan Absensi Karyawan Non-ASN
     */
    public function recap(Request $request)
    {
        $user = auth()->user();
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $selectedUserId = $request->input('user_id');
        $statusFilter = $request->input('status');

        $query = Attendance::with(['user.employee.role'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year);

        // Jika bukan admin, hanya bisa melihat rekapan dirinya sendiri
        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
        } else {
            if ($selectedUserId) {
                $query->where('user_id', $selectedUserId);
            }
        }

        if ($statusFilter) {
            $query->where('status', $statusFilter);
        }

        $attendances = $query->orderBy('date', 'desc')->paginate(15);

        // Ringkasan Statistik
        $statsQuery = Attendance::whereMonth('date', $month)->whereYear('date', $year);
        if ($user->role !== 'admin') {
            $statsQuery->where('user_id', $user->id);
        } elseif ($selectedUserId) {
            $statsQuery->where('user_id', $selectedUserId);
        }

        $totalRecords = (clone $statsQuery)->count();
        $totalHadir = (clone $statsQuery)->whereIn('status', ['hadir', 'terlambat'])->count();
        $totalTepatWaktu = (clone $statsQuery)->where('check_in_status', 'tepat_waktu')->count();
        $totalTerlambat = (clone $statsQuery)->where('check_in_status', 'terlambat')->count();
        $totalIzin = (clone $statsQuery)->where('status', 'izin')->count();
        $totalSakit = (clone $statsQuery)->where('status', 'sakit')->count();

        $employees = ($user->role === 'admin') ? User::with('employee.role')->get() : collect([$user]);

        return view('attendance.recap', [
            'title' => 'Rekapan Absensi & Monev Non-ASN',
            'active' => 'attendance_recap',
            'attendances' => $attendances,
            'month' => $month,
            'year' => $year,
            'selectedUserId' => $selectedUserId,
            'statusFilter' => $statusFilter,
            'employees' => $employees,
            'stats' => [
                'total' => $totalRecords,
                'hadir' => $totalHadir,
                'tepat_waktu' => $totalTepatWaktu,
                'terlambat' => $totalTerlambat,
                'izin' => $totalIzin,
                'sakit' => $totalSakit,
            ],
        ]);
    }

    /**
     * Cetak Rekapan Absensi (Print View)
     */
    public function printRecap(Request $request)
    {
        $user = auth()->user();
        $month = $request->input('month', Carbon::now()->month);
        $year = $request->input('year', Carbon::now()->year);
        $selectedUserId = $request->input('user_id');

        $query = Attendance::with(['user.employee.role'])
            ->whereMonth('date', $month)
            ->whereYear('date', $year)
            ->orderBy('date', 'asc');

        if ($user->role !== 'admin') {
            $query->where('user_id', $user->id);
            $targetUser = $user;
        } else {
            if ($selectedUserId) {
                $query->where('user_id', $selectedUserId);
                $targetUser = User::find($selectedUserId);
            } else {
                $targetUser = null;
            }
        }

        $attendances = $query->get();
        $office = OfficeSetting::getActive();

        return view('attendance.print', [
            'title' => 'Cetak Rekap Absensi Non-ASN',
            'attendances' => $attendances,
            'month' => $month,
            'year' => $year,
            'targetUser' => $targetUser,
            'office' => $office,
        ]);
    }

    /**
     * Pengaturan Titik Lokasi Kantor
     */
    public function settings()
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $office = OfficeSetting::getActive();

        return view('attendance.settings', [
            'title' => 'Pengaturan Lokasi Kantor Non-ASN',
            'active' => 'attendance_settings',
            'office' => $office,
        ]);
    }

    /**
     * Simpan Pengaturan Titik Lokasi Kantor
     */
    public function updateSettings(Request $request)
    {
        if (auth()->user()->role !== 'admin') {
            abort(403);
        }

        $validated = $request->validate([
            'office_name' => 'required|string|max:255',
            'latitude' => 'required|numeric',
            'longitude' => 'required|numeric',
            'radius_meters' => 'required|integer|min:0|max:50000000',
            'check_in_start' => 'required',
            'check_in_end' => 'required',
            'check_out_start' => 'required',
        ]);

        $office = OfficeSetting::getActive();
        $office->update($validated);

        notify()->success('Titik lokasi kantor dan ketentuan presensi berhasil diperbarui.', 'Berhasil');
        return redirect()->route('attendance.settings');
    }

    /**
     * Detail Modal Monev
     */
    public function getMonevDetail($id)
    {
        $attendance = Attendance::with(['user.employee.role'])->findOrFail($id);

        if (auth()->user()->role !== 'admin' && $attendance->user_id !== auth()->id()) {
            return response()->json(['error' => 'Unauthorized'], 403);
        }

        return response()->json([
            'id' => $attendance->id,
            'name' => $attendance->user->name,
            'role' => $attendance->user->employee?->role?->name ?? 'Pegawai Non-ASN',
            'date' => Carbon::parse($attendance->date)->translatedFormat('l, d F Y'),
            'check_in_time' => $attendance->check_in_time ? Carbon::parse($attendance->check_in_time)->format('H:i') : '-',
            'check_in_photo' => $attendance->check_in_photo ? asset('storage/' . $attendance->check_in_photo) : null,
            'check_in_distance' => $attendance->check_in_distance,
            'check_out_time' => $attendance->check_out_time ? Carbon::parse($attendance->check_out_time)->format('H:i') : '-',
            'check_out_photo' => $attendance->check_out_photo ? asset('storage/' . $attendance->check_out_photo) : null,
            'check_out_distance' => $attendance->check_out_distance,
            'monev_activity' => $attendance->monev_activity ?? 'Belum ada uraian aktivitas.',
            'monev_obstacle' => $attendance->monev_obstacle ?? 'Tidak ada kendala yang dilaporkan.',
            'activity_photos' => collect($attendance->activity_photos ?? [])->map(fn($p) => asset('storage/' . $p))->values(),
            'status' => $attendance->status,
        ]);
    }

    /**
     * Hitung Jarak dua koordinat GPS menggunakan formula Haversine (hasil dalam Meter)
     */
    private function calculateDistance(float $lat1, float $lon1, float $lat2, float $lon2): int
    {
        $earthRadius = 6371000; // Radius bumi dalam meter

        $latDelta = deg2rad($lat2 - $lat1);
        $lonDelta = deg2rad($lon2 - $lon1);

        $a = sin($latDelta / 2) * sin($latDelta / 2) +
            cos(deg2rad($lat1)) * cos(deg2rad($lat2)) *
            sin($lonDelta / 2) * sin($lonDelta / 2);

        $c = 2 * atan2(sqrt($a), sqrt(1 - $a));

        return (int) round($earthRadius * $c);
    }

    /**
     * Simpan base64 string gambar ke storage disk public
     */
    private function saveBase64Image(string $base64Data, string $folder): string
    {
        if (preg_match('/^data:image\/(\w+);base64,/', $base64Data, $type)) {
            $data = substr($base64Data, strpos($base64Data, ',') + 1);
            $type = strtolower($type[1]);
        } else {
            $data = $base64Data;
            $type = 'jpg';
        }

        $decodedData = base64_decode($data);
        $fileName = $folder . '/' . Str::random(30) . '.' . $type;

        Storage::disk('public')->put($fileName, $decodedData);

        return $fileName;
    }
}
