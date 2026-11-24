<x-layouts.layout :title="$title" :active="$active">
    <div class="max-w-6xl mx-auto py-6 px-4">
        <!-- Header Banner -->
        <div class="bg-gradient-to-r from-blue-700 to-indigo-800 text-white rounded-2xl p-6 shadow-lg mb-6 flex flex-col md:flex-row justify-between items-start md:items-center gap-4">
            <div>
                <div class="inline-flex items-center gap-2 bg-blue-500/30 text-blue-100 text-xs px-3 py-1 rounded-full font-medium mb-2 backdrop-blur-sm">
                    <span class="size-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Sistem Presensi & Monev Pegawai Non-ASN
                </div>
                <h1 class="text-2xl font-bold">Selamat Datang, {{ $user->name }}</h1>
                <p class="text-blue-100 text-sm mt-1">
                    Jabatan / Role: <span class="font-bold underline">{{ $user->employee?->role?->name ?? 'Tenaga Non-ASN' }}</span> | Hari: {{ $today }}
                </p>
            </div>
            <div class="flex items-center gap-3">
                <a href="{{ route('attendance.recap') }}" class="px-4 py-2 bg-white text-blue-800 rounded-lg text-sm font-semibold hover:bg-blue-50 shadow transition flex items-center gap-1.5">
                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 17v-2m3 2v-4m3 4v-6m2 10H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                    </svg>
                    Lihat Rekap Absensi
                </a>
                @if(auth()->user()->role === 'admin')
                    <a href="{{ route('attendance.settings') }}" class="px-3 py-2 bg-blue-900/60 hover:bg-blue-900 text-white rounded-lg text-sm font-medium transition" title="Pengaturan Lokasi Kantor">
                        <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z" />
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z" />
                        </svg>
                    </a>
                @endif
            </div>
        </div>

        <!-- Lokasi Kantor Info Card -->
        <div class="bg-white border rounded-xl p-4 shadow-sm mb-6 flex flex-col md:flex-row items-center justify-between gap-4">
            <div class="flex items-center gap-3">
                <div class="size-10 rounded-full bg-blue-50 text-blue-600 flex items-center justify-center font-bold">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h4 class="text-sm font-semibold text-gray-800">{{ $office->office_name }}</h4>
                    <p class="text-xs text-gray-500">
                        Radius Toleransi: <span class="font-medium text-gray-700">{{ $office->radius_meters }} meter</span> | 
                        Jam Masuk: <span class="font-medium text-gray-700">{{ substr($office->check_in_start, 0, 5) }} - {{ substr($office->check_in_end, 0, 5) }}</span> | 
                        Jam Pulang: <span class="font-medium text-gray-700">&ge; {{ substr($office->check_out_start, 0, 5) }}</span>
                    </p>
                </div>
            </div>
            <div id="locationStatusBadge" class="flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-gray-100 text-gray-600">
                <span class="size-2 rounded-full bg-gray-400"></span>
                <span>Mendeteksi Lokasi GPS Anda...</span>
            </div>
        </div>

        <!-- Status Presensi Hari Ini Grid -->
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-8">
            <!-- Box Absen Masuk -->
            <div class="bg-white border rounded-xl p-5 shadow-sm relative overflow-hidden">
                <div class="flex items-center justify-between mb-4 border-b pb-3">
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-lg bg-green-50 text-green-600">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </span>
                        <h3 class="font-bold text-gray-800">1. Absen Masuk (Datang)</h3>
                    </div>
                    @if($attendance && $attendance->check_in_time)
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold {{ $attendance->check_in_status === 'tepat_waktu' ? 'bg-green-100 text-green-800' : 'bg-yellow-100 text-yellow-800' }}">
                            {{ $attendance->check_in_status === 'tepat_waktu' ? 'Tepat Waktu' : 'Terlambat' }}
                        </span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Belum Masuk</span>
                    @endif
                </div>

                @if($attendance && $attendance->check_in_time)
                    <div class="flex items-center gap-4">
                        @if($attendance->check_in_photo)
                            <img src="{{ asset('storage/' . $attendance->check_in_photo) }}" alt="Foto Masuk" 
                                class="size-24 rounded-lg object-cover border-2 border-green-500 shadow-sm cursor-pointer hover:opacity-90"
                                onclick="openPhotoModal(this.src, 'Foto Wajah Absen Masuk')">
                        @endif
                        <div class="space-y-1 text-sm">
                            <div class="text-xs text-gray-500">Waktu Masuk:</div>
                            <div class="text-xl font-bold text-gray-800">{{ Carbon\Carbon::parse($attendance->check_in_time)->format('H:i:s') }} WIB</div>
                            <div class="text-xs text-gray-500">
                                Jarak ke Kantor: <span class="font-medium text-gray-700">{{ $attendance->check_in_distance ?? 0 }} meter</span>
                            </div>
                            <div class="text-xs text-green-600 font-medium flex items-center gap-1">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Absen masuk berhasil diverifikasi
                            </div>
                        </div>
                    </div>
                @else
                    <!-- Form Absen Masuk Kamera -->
                    <div class="space-y-4">
                        <p class="text-xs text-gray-600">Posisikan wajah Anda tegak di depan kamera. Ambil foto menggunakan kamera langsung atau live streaming.</p>
                        
                        <!-- Camera Area -->
                        <div class="relative bg-gray-900 rounded-xl overflow-hidden aspect-video flex items-center justify-center border-2 border-gray-300">
                            <video id="videoCheckIn" autoplay playsinline muted class="w-full h-full object-cover hidden"></video>
                            <canvas id="canvasCheckIn" class="hidden"></canvas>
                            <img id="previewCheckIn" class="hidden w-full h-full object-cover" />
                            
                            <!-- Initial Placeholder -->
                            <div id="placeholderCheckIn" class="text-center p-4">
                                <svg class="size-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span class="text-xs text-gray-300 block">Kamera belum aktif. Klik tombol di bawah untuk mengambil foto.</span>
                            </div>

                            <!-- Camera Frame Guide -->
                            <div id="guideCheckIn" class="hidden absolute inset-0 border-2 border-dashed border-white/60 m-6 rounded-2xl pointer-events-none flex items-center justify-center">
                                <span class="text-[11px] text-white/90 bg-black/50 px-2 py-0.5 rounded">Posisikan Wajah di Sini</span>
                            </div>
                        </div>

                        <!-- Camera Action Buttons -->
                        <div class="flex flex-wrap items-center justify-between gap-2">
                            <!-- Direct Hardware/Mobile Camera Trigger (WORKS EVERYWHERE!) -->
                            <input type="file" id="directCaptureIn" accept="image/*" capture="user" class="hidden" 
                                onchange="handleDirectCapture(this, 'previewCheckIn', 'placeholderCheckIn', 'photoInputIn', 'btnSubmitIn', 'videoCheckIn', 'guideCheckIn')">
                            
                            <button type="button" onclick="document.getElementById('directCaptureIn').click()" 
                                class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow flex items-center gap-1.5 transition">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                Buka Kamera HP / Perangkat
                            </button>

                            <div class="flex items-center gap-1">
                                <button type="button" id="btnStartCameraIn" onclick="initCamera('videoCheckIn', 'placeholderCheckIn', 'guideCheckIn', 'previewCheckIn', 'directCaptureIn')" class="px-2.5 py-2 bg-gray-100 hover:bg-gray-200 border rounded-lg text-xs font-medium text-gray-700 flex items-center gap-1">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                    Live Stream
                                </button>
                                <button type="button" id="btnCaptureIn" onclick="takeSnapshot('videoCheckIn', 'canvasCheckIn', 'previewCheckIn', 'photoInputIn', 'btnSubmitIn', 'guideCheckIn')" class="px-3 py-2 bg-emerald-600 hover:bg-emerald-700 text-white text-xs font-bold rounded-lg shadow flex items-center gap-1">
                                    Ambil Foto
                                </button>
                            </div>
                        </div>

                        <form action="{{ route('attendance.checkin') }}" method="POST" id="formCheckIn">
                            @csrf
                            <input type="hidden" name="latitude" id="latIn">
                            <input type="hidden" name="longitude" id="lngIn">
                            <input type="hidden" name="photo" id="photoInputIn">

                            <div id="photoNoticeIn" class="hidden mb-3 text-xs bg-emerald-50 text-emerald-800 border border-emerald-200 rounded-lg p-2.5 flex items-center gap-2">
                                <svg class="size-4 shrink-0 text-emerald-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                <span>Foto wajah berhasil diambil! Klik tombol <strong>Kirim Absen Masuk</strong> di bawah ini untuk menyelesaikan absensi.</span>
                            </div>

                            <button type="submit" id="btnSubmitIn" disabled class="w-full py-2.5 bg-emerald-600 hover:bg-emerald-700 disabled:bg-gray-300 disabled:cursor-not-allowed text-white text-sm font-bold rounded-lg shadow transition flex items-center justify-center gap-2">
                                <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7" /></svg>
                                Kirim Absen Masuk
                            </button>
                        </form>
                    </div>
                @endif
            </div>

            <!-- Box Status Absen Pulang & Monev Summary -->
            <div class="bg-white border rounded-xl p-5 shadow-sm">
                <div class="flex items-center justify-between mb-4 border-b pb-3">
                    <div class="flex items-center gap-2">
                        <span class="p-2 rounded-lg bg-indigo-50 text-indigo-600">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                        </span>
                        <h3 class="font-bold text-gray-800">2. Absen Pulang & Monev</h3>
                    </div>
                    @if($attendance && $attendance->check_out_time)
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-emerald-100 text-emerald-800">Sudah Pulang</span>
                    @elseif($attendance && $attendance->check_in_time)
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-blue-100 text-blue-800">Sedang Bekerja</span>
                    @else
                        <span class="px-2.5 py-1 rounded-full text-xs font-semibold bg-gray-100 text-gray-600">Menunggu Masuk</span>
                    @endif
                </div>

                @if($attendance && $attendance->check_out_time)
                    <div class="space-y-4">
                        <div class="flex items-center gap-4">
                            @if($attendance->check_out_photo)
                                <img src="{{ asset('storage/' . $attendance->check_out_photo) }}" alt="Foto Pulang" 
                                    class="size-20 rounded-lg object-cover border-2 border-indigo-500 shadow-sm cursor-pointer hover:opacity-90"
                                    onclick="openPhotoModal(this.src, 'Foto Wajah Absen Pulang')">
                            @endif
                            <div class="space-y-0.5 text-sm">
                                <div class="text-xs text-gray-500">Waktu Pulang:</div>
                                <div class="text-xl font-bold text-gray-800">{{ Carbon\Carbon::parse($attendance->check_out_time)->format('H:i:s') }} WIB</div>
                                <div class="text-xs text-gray-500">Jarak saat Pulang: {{ $attendance->check_out_distance ?? 0 }} meter</div>
                            </div>
                        </div>

                        <!-- Monev Summary -->
                        <div class="bg-gray-50 p-3 rounded-lg border text-xs space-y-2">
                            <div>
                                <span class="font-semibold text-gray-700">Uraian Aktivitas:</span>
                                <p class="text-gray-600 mt-0.5">{{ $attendance->monev_activity }}</p>
                            </div>
                            <div>
                                <span class="font-semibold text-gray-700">Kendala Dilakukan (Min 100 kar):</span>
                                <p class="text-gray-600 mt-0.5 italic">{{ $attendance->monev_obstacle }}</p>
                            </div>
                            @if(!empty($attendance->activity_photos))
                                <div>
                                    <span class="font-semibold text-gray-700 block mb-1">Bukti Foto Kegiatan ({{ count($attendance->activity_photos) }} Foto):</span>
                                    <div class="flex gap-2">
                                        @foreach($attendance->activity_photos as $p)
                                            <img src="{{ asset('storage/' . $p) }}" 
                                                class="size-16 rounded object-cover border cursor-pointer hover:scale-105 transition"
                                                onclick="openPhotoModal(this.src, 'Bukti Foto Kegiatan')">
                                        @endforeach
                                    </div>
                                </div>
                            @endif
                        </div>
                    </div>
                @else
                    <div class="text-center py-8 text-gray-500 text-sm">
                        <svg class="size-12 mx-auto text-gray-300 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                        </svg>
                        <p class="font-medium text-gray-700">Form Absen Pulang & Monev</p>
                        <p class="text-xs text-gray-400 mt-1">
                            @if(!$attendance || !$attendance->check_in_time)
                                Anda harus melakukan absen masuk terlebih dahulu sebelum dapat absen pulang.
                            @else
                                Form pengisian monev dan absen pulang tersedia di bawah ini.
                            @endif
                        </p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Section Form Monev & Absen Pulang (Hanya jika sudah absen masuk & belum absen pulang) -->
        @if($attendance && $attendance->check_in_time && !$attendance->check_out_time)
            <div class="bg-white border rounded-2xl p-6 shadow-md mb-8">
                <div class="flex items-center gap-3 mb-6 pb-4 border-b">
                    <div class="size-10 rounded-xl bg-indigo-100 text-indigo-700 flex items-center justify-center font-bold">
                        <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4" />
                        </svg>
                    </div>
                    <div>
                        <h2 class="text-xl font-bold text-gray-800">Form Monev Aktivitas Harian & Absen Pulang</h2>
                        <p class="text-xs text-gray-500">Sebelum absen pulang, pegawai Non-ASN diwajibkan mengisi uraian aktivitas, kendala minimal 100 karakter, bukti kegiatan maks 3 foto, serta foto wajah di titik lokasi kantor.</p>
                    </div>
                </div>

                <form action="{{ route('attendance.checkout') }}" method="POST" enctype="multipart/form-data" id="formCheckOut" class="space-y-6">
                    @csrf
                    <input type="hidden" name="latitude" id="latOut">
                    <input type="hidden" name="longitude" id="lngOut">
                    <input type="hidden" name="photo" id="photoInputOut">

                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                        <!-- Left Side: Camera Face Capture for Checkout -->
                        <div class="space-y-3">
                            <label class="block text-sm font-semibold text-gray-700">1. Foto Wajah Saat Pulang <span class="text-red-500">*</span></label>
                            
                            <div class="relative bg-gray-900 rounded-xl overflow-hidden aspect-video flex items-center justify-center border-2 border-gray-300">
                                <video id="videoCheckOut" autoplay playsinline muted class="w-full h-full object-cover hidden"></video>
                                <canvas id="canvasCheckOut" class="hidden"></canvas>
                                <img id="previewCheckOut" class="hidden w-full h-full object-cover" />
                                
                                <div id="placeholderCheckOut" class="text-center p-4">
                                    <svg class="size-12 mx-auto text-gray-400 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" />
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" />
                                    </svg>
                                    <span class="text-xs text-gray-300 block">Kamera belum aktif. Klik tombol di bawah untuk mengambil foto.</span>
                                </div>

                                <div id="guideCheckOut" class="hidden absolute inset-0 border-2 border-dashed border-white/60 m-6 rounded-2xl pointer-events-none flex items-center justify-center">
                                    <span class="text-[11px] text-white/90 bg-black/50 px-2 py-0.5 rounded">Posisikan Wajah di Sini</span>
                                </div>
                            </div>

                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <input type="file" id="directCaptureOut" accept="image/*" capture="user" class="hidden" 
                                    onchange="handleDirectCapture(this, 'previewCheckOut', 'placeholderCheckOut', 'photoInputOut', null, 'videoCheckOut', 'guideCheckOut')">
                                
                                <button type="button" onclick="document.getElementById('directCaptureOut').click()" 
                                    class="px-3.5 py-2 bg-indigo-600 hover:bg-indigo-700 text-white rounded-lg text-xs font-bold shadow flex items-center gap-1.5 transition">
                                    <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 9a2 2 0 012-2h.93a2 2 0 001.664-.89l.812-1.22A2 2 0 0110.07 4h3.86a2 2 0 011.664.89l.812 1.22A2 2 0 0018.07 7H19a2 2 0 012 2v9a2 2 0 01-2 2H5a2 2 0 01-2-2V9z" /><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 13a3 3 0 11-6 0 3 3 0 016 0z" /></svg>
                                    Buka Kamera HP / Perangkat
                                </button>

                                <div class="flex items-center gap-1">
                                    <button type="button" onclick="initCamera('videoCheckOut', 'placeholderCheckOut', 'guideCheckOut', 'previewCheckOut', 'directCaptureOut')" class="px-2.5 py-2 bg-gray-100 hover:bg-gray-200 border rounded-lg text-xs font-medium text-gray-700 flex items-center gap-1">
                                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z" /></svg>
                                        Live Stream
                                    </button>
                                    <button type="button" onclick="takeSnapshot('videoCheckOut', 'canvasCheckOut', 'previewCheckOut', 'photoInputOut', null, 'guideCheckOut')" class="px-3 py-2 bg-indigo-600 hover:bg-indigo-700 text-white text-xs font-bold rounded-lg shadow flex items-center gap-1">
                                        Ambil Foto
                                    </button>
                                </div>
                            </div>
                        </div>

                        <!-- Right Side: Monev Inputs -->
                        <div class="space-y-4">
                            <!-- Uraian Aktivitas -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    2. Uraian Aktivitas Pekerjaan Hari Ini <span class="text-red-500">*</span>
                                </label>
                                <textarea name="monev_activity" id="monev_activity" rows="3" required
                                    class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                    placeholder="Uraikan tugas, agenda, atau aktivitas pekerjaan yang telah diselesaikan hari ini...">{{ old('monev_activity') }}</textarea>
                                @error('monev_activity')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Kendala yang dihadapi (MINIMAL 100 KARAKTER SESUAI INSTRUKSI USER) -->
                            <div>
                                <div class="flex items-center justify-between mb-1">
                                    <label class="block text-sm font-semibold text-gray-700">
                                        3. Kendala yang Dihadapi <span class="text-red-500">*</span>
                                    </label>
                                    <span id="charCount" class="text-xs font-semibold text-red-500">
                                        0 / 100 karakter minimum
                                    </span>
                                </div>
                                <textarea name="monev_obstacle" id="monev_obstacle" rows="4" required minlength="100"
                                    oninput="checkObstacleLength(this)"
                                    class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-indigo-500 focus:outline-none"
                                    placeholder="Tuliskan kendala / hambatan pelaksanaan tugas yang dihadapi secara detail (Wajib minimal 100 karakter)...">{{ old('monev_obstacle') }}</textarea>
                                <p class="text-[11px] text-gray-500 mt-0.5">
                                    * Aturan Non-ASN: Kendala wajib diuraikan minimal 100 karakter sebagai bahan evaluasi & monitoring harian pimpinan.
                                </p>
                                @error('monev_obstacle')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </div>

                            <!-- Bukti Foto Kegiatan Maksimal 3 Foto -->
                            <div>
                                <label class="block text-sm font-semibold text-gray-700 mb-1">
                                    4. Bukti Foto Kegiatan (Maksimal 3 Foto)
                                </label>
                                <input type="file" name="activity_photos[]" id="activity_photos" multiple accept="image/*"
                                    onchange="previewActivityPhotos(this)"
                                    class="w-full border border-gray-300 rounded-lg p-2 text-xs bg-gray-50 focus:outline-none file:mr-3 file:py-1 file:px-3 file:rounded-md file:border-0 file:text-xs file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                <p class="text-[11px] text-gray-500 mt-1">Dapat memilih 1 hingga 3 foto hasil kerja/dokumentasi lapangan (format JPG, PNG, max 3MB per foto).</p>
                                
                                <!-- Preview Container -->
                                <div id="activityPhotosPreview" class="flex gap-2 mt-2"></div>
                                @error('activity_photos')
                                    <span class="text-xs text-red-600">{{ $message }}</span>
                                @enderror
                            </div>
                        </div>
                    </div>

                    <!-- Submit Button -->
                    <div class="pt-4 border-t flex justify-end">
                        <button type="submit" id="btnSubmitOut"
                            class="px-8 py-3 bg-indigo-600 hover:bg-indigo-700 text-white font-bold text-sm rounded-xl shadow-lg transition flex items-center gap-2">
                            <svg class="size-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1" />
                            </svg>
                            Kirim Monev & Selesaikan Absen Pulang
                        </button>
                    </div>
                </form>
            </div>
        @endif
    </div>

    <!-- Image Preview Modal (Fixed Closing) -->
    <div id="photoModal" style="display: none;" class="fixed inset-0 bg-black/80 z-[100] items-center justify-center p-4">
        <div class="bg-white rounded-2xl overflow-hidden max-w-lg w-full shadow-2xl relative" onclick="event.stopPropagation()">
            <div class="p-3.5 border-b flex justify-between items-center bg-gray-50">
                <h4 id="photoModalTitle" class="text-sm font-bold text-gray-800">Preview Foto</h4>
                <button type="button" onclick="closePhotoModal()" class="text-gray-400 hover:text-gray-700 text-2xl font-bold w-8 h-8 flex items-center justify-center rounded-full hover:bg-gray-200 transition">&times;</button>
            </div>
            <div class="p-4 flex justify-center bg-black">
                <img id="photoModalImg" src="" class="max-h-[75vh] object-contain rounded">
            </div>
            <div class="p-3 bg-gray-50 border-t flex justify-end">
                <button type="button" onclick="closePhotoModal()" class="px-4 py-1.5 bg-gray-200 hover:bg-gray-300 text-gray-700 text-xs font-bold rounded-lg transition">
                    Tutup
                </button>
            </div>
        </div>
    </div>

    <!-- SweetAlert2 CDN Fallback -->
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>

    <script>
        let currentStream = null;
        let userLat = null;
        let userLng = null;
        const officeLat = {{ $office->latitude }};
        const officeLng = {{ $office->longitude }};
        const officeRadius = {{ $office->radius_meters }};

        // Geolocation tracking
        document.addEventListener('DOMContentLoaded', function() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(
                    function(position) {
                        userLat = position.coords.latitude;
                        userLng = position.coords.longitude;

                        // Fill hidden inputs
                        const latIn = document.getElementById('latIn');
                        const lngIn = document.getElementById('lngIn');
                        const latOut = document.getElementById('latOut');
                        const lngOut = document.getElementById('lngOut');
                        if (latIn) latIn.value = userLat;
                        if (lngIn) lngIn.value = userLng;
                        if (latOut) latOut.value = userLat;
                        if (lngOut) lngOut.value = userLng;

                        // Calculate distance to office
                        const distance = calculateDistance(userLat, userLng, officeLat, officeLng);
                        const badge = document.getElementById('locationStatusBadge');

                        if (officeRadius === 0) {
                            badge.className = "flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200";
                            badge.innerHTML = `<span class="size-2 rounded-full bg-emerald-500"></span> Mode Testing / Bebas Radius (${distance}m dari pusat kantor)`;
                        } else if (distance <= officeRadius) {
                            badge.className = "flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-emerald-50 text-emerald-700 border border-emerald-200";
                            badge.innerHTML = `<span class="size-2 rounded-full bg-emerald-500"></span> Dalam Area Kantor (${distance}m dari pusat kantor)`;
                        } else {
                            badge.className = "flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-rose-50 text-rose-700 border border-rose-200";
                            badge.innerHTML = `<span class="size-2 rounded-full bg-rose-500"></span> Di Luar Area Kantor (${distance}m, maks ${officeRadius}m)`;
                        }
                    },
                    function(error) {
                        const badge = document.getElementById('locationStatusBadge');
                        badge.className = "flex items-center gap-2 px-3 py-1.5 rounded-lg text-xs font-semibold bg-amber-50 text-amber-700 border border-amber-200";
                        badge.innerHTML = `<span class="size-2 rounded-full bg-amber-500"></span> GPS: Klik 'Izinkan Lokasi' pada browser Anda`;
                    },
                    { enableHighAccuracy: true, timeout: 10000 }
                );
            }
        });

        // Camera WebRTC helper with automatic fallback
        async function initCamera(videoId, placeholderId, guideId, previewId, directInputId) {
            const video = document.getElementById(videoId);
            const placeholder = document.getElementById(placeholderId);
            const guide = document.getElementById(guideId);
            const preview = document.getElementById(previewId);

            // Periksa ketersediaan MediaDevices (HTTPS / Localhost constraint)
            if (!navigator.mediaDevices || !navigator.mediaDevices.getUserMedia) {
                alert("Browser membatasi streaming WebRTC pada koneksi non-HTTPS. Sistem otomatis membuka kamera perangkat Anda langsung.");
                document.getElementById(directInputId).click();
                return;
            }

            try {
                if (currentStream) {
                    currentStream.getTracks().forEach(track => track.stop());
                }

                // Coba resolusi ideal, jika gagal coba constraint dasar
                let stream;
                try {
                    stream = await navigator.mediaDevices.getUserMedia({
                        video: { facingMode: "user", width: { ideal: 640 }, height: { ideal: 480 } },
                        audio: false
                    });
                } catch(e) {
                    stream = await navigator.mediaDevices.getUserMedia({ video: true, audio: false });
                }

                currentStream = stream;
                video.srcObject = stream;
                video.classList.remove('hidden');
                if (placeholder) placeholder.classList.add('hidden');
                if (guide) guide.classList.remove('hidden');
                if (preview) preview.classList.add('hidden');
            } catch (err) {
                console.error("Camera error:", err);
                alert("Tidak dapat mengakses streaming webcam (" + err.message + "). Membuka kamera langsung dari perangkat Anda...");
                document.getElementById(directInputId).click();
            }
        }

        // Direct Native Camera / File Input Capture
        function handleDirectCapture(fileInput, previewId, placeholderId, hiddenInputId, submitBtnId, videoId, guideId) {
            if (!fileInput.files || !fileInput.files[0]) return;

            const file = fileInput.files[0];
            const reader = new FileReader();

            reader.onload = function(e) {
                const img = new Image();
                img.onload = function() {
                    // Kompresi ke canvas agar ukuran tidak melebihi memory
                    const canvas = document.createElement('canvas');
                    let width = img.width;
                    let height = img.height;
                    const maxDim = 800;

                    if (width > height && width > maxDim) {
                        height = Math.round(height * (maxDim / width));
                        width = maxDim;
                    } else if (height > maxDim) {
                        width = Math.round(width * (maxDim / height));
                        height = maxDim;
                    }

                    canvas.width = width;
                    canvas.height = height;
                    const ctx = canvas.getContext('2d');
                    ctx.drawImage(img, 0, 0, width, height);

                    const dataUrl = canvas.toDataURL('image/jpeg', 0.85);

                    // Set ke input hidden dan preview
                    document.getElementById(hiddenInputId).value = dataUrl;
                    const preview = document.getElementById(previewId);
                    preview.src = dataUrl;
                    preview.classList.remove('hidden');

                    const video = document.getElementById(videoId);
                    if (video) video.classList.add('hidden');
                    const placeholder = document.getElementById(placeholderId);
                    if (placeholder) placeholder.classList.add('hidden');
                    const guide = document.getElementById(guideId);
                    if (guide) guide.classList.add('hidden');

                    if (submitBtnId) {
                        const submitBtn = document.getElementById(submitBtnId);
                        if (submitBtn) submitBtn.disabled = false;
                    }
                    const noticeIn = document.getElementById('photoNoticeIn');
                    if (noticeIn && hiddenInputId === 'photoInputIn') noticeIn.classList.remove('hidden');

                    // Hentikan stream kamera jika sedang berjalan
                    if (currentStream) {
                        currentStream.getTracks().forEach(track => track.stop());
                    }
                };
                img.src = e.target.result;
            };

            reader.readAsDataURL(file);
        }

        // Take Snapshot Face Photo from WebRTC Stream
        function takeSnapshot(videoId, canvasId, previewId, inputId, submitBtnId, guideId) {
            const video = document.getElementById(videoId);
            const canvas = document.getElementById(canvasId);
            const preview = document.getElementById(previewId);
            const input = document.getElementById(inputId);
            const guide = document.getElementById(guideId);

            if (!video.srcObject) {
                if (window.Swal) {
                    Swal.fire({
                        icon: 'warning',
                        title: 'Kamera Belum Aktif',
                        text: "Silakan nyalakan live stream kamera terlebih dahulu atau gunakan tombol 'Buka Kamera HP / Perangkat'!"
                    });
                } else {
                    alert("Silakan nyalakan live stream kamera terlebih dahulu atau gunakan tombol 'Buka Kamera HP / Perangkat'!");
                }
                return;
            }

            canvas.width = video.videoWidth || 640;
            canvas.height = video.videoHeight || 480;
            const ctx = canvas.getContext('2d');
            ctx.drawImage(video, 0, 0, canvas.width, canvas.height);

            const dataUrl = canvas.toDataURL('image/jpeg', 0.85);
            input.value = dataUrl;
            preview.src = dataUrl;
            preview.classList.remove('hidden');
            video.classList.add('hidden');
            if (guide) guide.classList.add('hidden');

            if (submitBtnId) {
                const btnSubmit = document.getElementById(submitBtnId);
                if (btnSubmit) btnSubmit.disabled = false;
            }
            const noticeIn = document.getElementById('photoNoticeIn');
            if (noticeIn && inputId === 'photoInputIn') noticeIn.classList.remove('hidden');
        }

        // Haversine distance in meters
        function calculateDistance(lat1, lon1, lat2, lon2) {
            const R = 6371000;
            const dLat = (lat2 - lat1) * Math.PI / 180;
            const dLon = (lon2 - lon1) * Math.PI / 180;
            const a = Math.sin(dLat/2) * Math.sin(dLat/2) +
                      Math.cos(lat1 * Math.PI / 180) * Math.cos(lat2 * Math.PI / 180) *
                      Math.sin(dLon/2) * Math.sin(dLon/2);
            const c = 2 * Math.atan2(Math.sqrt(a), Math.sqrt(1-a));
            return Math.round(R * c);
        }

        // Kendala Character Counter Validation (MIN 100 CHARS)
        function checkObstacleLength(textarea) {
            const length = textarea.value.trim().length;
            const counter = document.getElementById('charCount');
            if (length >= 100) {
                counter.className = "text-xs font-semibold text-emerald-600";
                counter.innerText = `${length} karakter (Memenuhi syarat minimal)`;
            } else {
                counter.className = "text-xs font-semibold text-red-500";
                counter.innerText = `${length} / 100 karakter minimum`;
            }
        }

        // Preview Activity Photos (max 3)
        function previewActivityPhotos(input) {
            const container = document.getElementById('activityPhotosPreview');
            container.innerHTML = '';
            if (input.files.length > 3) {
                alert("Maksimal hanya dapat mengunggah 3 foto kegiatan.");
                input.value = '';
                return;
            }
            Array.from(input.files).forEach(file => {
                const reader = new FileReader();
                reader.onload = function(e) {
                    const img = document.createElement('img');
                    img.src = e.target.result;
                    img.className = "size-16 rounded object-cover border-2 border-indigo-300";
                    container.appendChild(img);
                };
                reader.readAsDataURL(file);
            });
        }

        // Modal Photo Preview (Fixed Closing, Escape & Backdrop Support)
        function openPhotoModal(src, title) {
            const modal = document.getElementById('photoModal');
            document.getElementById('photoModalImg').src = src;
            document.getElementById('photoModalTitle').innerText = title;
            modal.style.display = 'flex';
        }

        function closePhotoModal() {
            const modal = document.getElementById('photoModal');
            if (modal) modal.style.display = 'none';
        }

        // Close modal on Escape key
        document.addEventListener('keydown', function(e) {
            if (e.key === 'Escape') closePhotoModal();
        });

        // Close modal on backdrop click
        document.addEventListener('DOMContentLoaded', function() {
            const photoModalEl = document.getElementById('photoModal');
            if (photoModalEl) {
                photoModalEl.addEventListener('click', function(e) {
                    if (e.target === this) closePhotoModal();
                });
            }
        });

        // CheckIn Form Submission via AJAX with SweetAlert2
        const formCheckIn = document.getElementById('formCheckIn');
        if (formCheckIn) {
            formCheckIn.addEventListener('submit', async function(e) {
                e.preventDefault();

                const photoInput = document.getElementById('photoInputIn').value;
                const lat = document.getElementById('latIn').value;
                const lng = document.getElementById('lngIn').value;

                if (!photoInput) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Foto Belum Diambil',
                            text: 'Silakan ambil foto wajah Anda menggunakan kamera atau unggah foto terlebih dahulu!'
                        });
                    } else {
                        alert('Silakan ambil foto wajah terlebih dahulu!');
                    }
                    return false;
                }

                if (!lat || !lng) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Lokasi GPS Belum Terdeteksi',
                            text: "Browser belum mendeteksi titik GPS Anda. Pastikan Anda mengklik 'Izinkan / Allow' pada notifikasi izin lokasi di browser."
                        });
                    } else {
                        alert("Lokasi GPS belum terdeteksi. Izinkan akses lokasi pada browser Anda.");
                    }
                    return false;
                }

                const submitBtn = document.getElementById('btnSubmitIn');
                const origText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5 text-white inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Memverifikasi & Menyimpan Absen...
                `;

                try {
                    const formData = new FormData(formCheckIn);
                    const res = await fetch("{{ route('attendance.checkin') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        if (window.Swal) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Absen Masuk Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#059669'
                            });
                        } else {
                            alert(data.message);
                        }
                        window.location.reload();
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origText;
                        const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Terjadi kesalahan saat memproses absensi.');
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Absen Masuk Ditolak',
                                html: `<div class="text-left text-sm"><p class="font-medium text-gray-800 mb-2">${errMsg}</p><p class="text-xs text-gray-500 bg-gray-100 p-2.5 rounded mt-2">💡 <b>Info Pengujian:</b> Jika berada di luar radius kantor default (Jakarta), Anda dapat menyetel radius menjadi <b>0 (Mode Bebas Testing)</b> atau menyesuaikan titik koordinat di menu Pengaturan Lokasi Kantor.</p></div>`,
                                confirmButtonColor: '#dc2626'
                            });
                        } else {
                            alert('Absen Ditolak: ' + errMsg);
                        }
                    }
                } catch(err) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origText;
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Terputus',
                            text: 'Gagal mengirim data ke server lokal. Pastikan server masih berjalan.'
                        });
                    } else {
                        alert('Terjadi kesalahan koneksi.');
                    }
                }
            });
        }

        // CheckOut Form Submission via AJAX with SweetAlert2
        const formCheckOut = document.getElementById('formCheckOut');
        if (formCheckOut) {
            formCheckOut.addEventListener('submit', async function(e) {
                e.preventDefault();

                const photoInput = document.getElementById('photoInputOut').value;
                const obstacleInput = document.getElementById('monev_obstacle').value.trim();
                const activityInput = document.getElementById('monev_activity').value.trim();
                const lat = document.getElementById('latOut').value;
                const lng = document.getElementById('lngOut').value;

                if (!photoInput) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Foto Wajah Pulang Wajib',
                            text: 'Foto wajah saat pulang wajib diambil terlebih dahulu menggunakan kamera!'
                        });
                    } else {
                        alert("Foto wajah pulang wajib diambil terlebih dahulu menggunakan kamera!");
                    }
                    return false;
                }

                if (!activityInput) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Uraian Aktivitas Wajib Diisi',
                            text: 'Silakan isi uraian tugas / aktivitas pekerjaan harian yang telah diselesaikan.'
                        });
                    } else {
                        alert('Uraian aktivitas pekerjaan harian wajib diisi!');
                    }
                    document.getElementById('monev_activity').focus();
                    return false;
                }

                if (obstacleInput.length < 100) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Kendala Kurang dari 100 Karakter',
                            text: "Uraian kendala baru " + obstacleInput.length + " karakter. Ketentuan Non-ASN mewajibkan uraian kendala minimal 100 karakter sebagai bahan evaluasi pimpinan!"
                        });
                    } else {
                        alert("Kendala yang dilaporkan masih kurang dari 100 karakter (saat ini: " + obstacleInput.length + " karakter). Minimal 100 karakter!");
                    }
                    document.getElementById('monev_obstacle').focus();
                    return false;
                }

                if (!lat || !lng) {
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'warning',
                            title: 'Lokasi GPS Belum Terdeteksi',
                            text: 'Mohon izinkan akses lokasi (GPS) pada browser Anda agar absen pulang dapat diverifikasi.'
                        });
                    } else {
                        alert('Lokasi GPS belum terdeteksi.');
                    }
                    return false;
                }

                const submitBtn = document.getElementById('btnSubmitOut');
                const origText = submitBtn.innerHTML;
                submitBtn.disabled = true;
                submitBtn.innerHTML = `
                    <svg class="animate-spin h-5 w-5 text-white inline mr-2" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24">
                        <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                        <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                    </svg>
                    Menyimpan Monev & Absen Pulang...
                `;

                try {
                    const formData = new FormData(formCheckOut);
                    const res = await fetch("{{ route('attendance.checkout') }}", {
                        method: 'POST',
                        headers: {
                            'X-Requested-With': 'XMLHttpRequest',
                            'Accept': 'application/json'
                        },
                        body: formData
                    });

                    const data = await res.json();

                    if (res.ok && data.success) {
                        if (window.Swal) {
                            await Swal.fire({
                                icon: 'success',
                                title: 'Absen Pulang Berhasil!',
                                text: data.message,
                                confirmButtonColor: '#4f46e5'
                            });
                        } else {
                            alert(data.message);
                        }
                        window.location.reload();
                    } else {
                        submitBtn.disabled = false;
                        submitBtn.innerHTML = origText;
                        const errMsg = data.message || (data.errors ? Object.values(data.errors).flat().join('\n') : 'Terjadi kesalahan saat memproses absensi pulang.');
                        if (window.Swal) {
                            Swal.fire({
                                icon: 'error',
                                title: 'Absen Pulang Ditolak',
                                text: errMsg,
                                confirmButtonColor: '#dc2626'
                            });
                        } else {
                            alert('Gagal: ' + errMsg);
                        }
                    }
                } catch(err) {
                    submitBtn.disabled = false;
                    submitBtn.innerHTML = origText;
                    if (window.Swal) {
                        Swal.fire({
                            icon: 'error',
                            title: 'Koneksi Terputus',
                            text: 'Gagal mengirim data ke server. Pastikan server masih berjalan.'
                        });
                    } else {
                        alert('Terjadi kesalahan koneksi.');
                    }
                }
            });
        }
    </script>
</x-layouts.layout>
