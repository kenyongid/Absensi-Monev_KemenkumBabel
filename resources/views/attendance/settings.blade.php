<x-layouts.layout :title="$title" :active="$active">
    <div class="max-w-3xl mx-auto py-8 px-4">
        <div class="bg-white border rounded-2xl p-6 shadow-sm">
            <div class="flex items-center gap-3 pb-4 mb-6 border-b">
                <div class="size-10 rounded-xl bg-blue-100 text-blue-700 flex items-center justify-center font-bold">
                    <svg class="size-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                    </svg>
                </div>
                <div>
                    <h1 class="text-xl font-bold text-gray-800">Pengaturan Titik Lokasi Kantor & Ketentuan Presensi</h1>
                    <p class="text-xs text-gray-500">Tentukan koordinat titik kantor dan radius maksimal agar absensi pegawai Non-ASN hanya diterima di lokasi kantor.</p>
                </div>
            </div>

            <form action="{{ route('attendance.settings.update') }}" method="POST" class="space-y-5">
                @csrf
                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Nama Kantor / Instansi <span class="text-red-500">*</span></label>
                    <input type="text" name="office_name" value="{{ old('office_name', $office->office_name) }}" required
                        class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    @error('office_name') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Latitude Kantor <span class="text-red-500">*</span></label>
                        <input type="text" name="latitude" id="officeLat" value="{{ old('latitude', $office->latitude) }}" required
                            class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('latitude') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 mb-1">Longitude Kantor <span class="text-red-500">*</span></label>
                        <input type="text" name="longitude" id="officeLng" value="{{ old('longitude', $office->longitude) }}" required
                            class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                        @error('longitude') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                    </div>
                </div>

                <div class="flex justify-end">
                    <button type="button" onclick="getCurrentLocation()" class="text-xs font-semibold text-blue-600 hover:text-blue-800 flex items-center gap-1 bg-blue-50 px-3 py-1.5 rounded-lg border border-blue-200">
                        <svg class="size-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        Ambil Titik Koordinat GPS Saat Ini
                    </button>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 mb-1">Radius Toleransi Lokasi (Meter) <span class="text-red-500">*</span></label>
                    <input type="number" name="radius_meters" value="{{ old('radius_meters', $office->radius_meters) }}" required min="0" max="50000000"
                        class="w-full border border-gray-300 rounded-lg p-2.5 text-sm focus:ring-2 focus:ring-blue-500 focus:outline-none">
                    <p class="text-xs text-gray-500 mt-1">Pegawai Non-ASN hanya dapat absen jika berada dalam jarak radius ini dari titik kantor. <span class="text-indigo-600 font-semibold">(Isi 0 untuk Mode Bebas / Testing tanpa batasan radius).</span></p>
                    @error('radius_meters') <span class="text-xs text-red-600">{{ $message }}</span> @enderror
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 pt-2 border-t">
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Mulai Absen Masuk:</label>
                        <input type="time" name="check_in_start" value="{{ old('check_in_start', $office->check_in_start) }}" required
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Batas Tepat Waktu (Jam Masuk):</label>
                        <input type="time" name="check_in_end" value="{{ old('check_in_end', $office->check_in_end) }}" required
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none">
                    </div>
                    <div>
                        <label class="block text-xs font-semibold text-gray-700 mb-1">Jam Mulai Absen Pulang:</label>
                        <input type="time" name="check_out_start" value="{{ old('check_out_start', $office->check_out_start) }}" required
                            class="w-full border border-gray-300 rounded-lg p-2 text-sm focus:outline-none">
                    </div>
                </div>

                <div class="pt-6 border-t flex justify-end gap-3">
                    <a href="{{ route('attendance.index') }}" class="px-5 py-2 border rounded-lg text-sm text-gray-600 hover:bg-gray-100 font-medium">
                        Batal
                    </a>
                    <button type="submit" class="px-6 py-2 bg-blue-600 hover:bg-blue-700 text-white rounded-lg text-sm font-semibold shadow">
                        Simpan Pengaturan
                    </button>
                </div>
            </form>
        </div>
    </div>

    <script>
        function getCurrentLocation() {
            if ("geolocation" in navigator) {
                navigator.geolocation.getCurrentPosition(function(pos) {
                    document.getElementById('officeLat').value = pos.coords.latitude;
                    document.getElementById('officeLng').value = pos.coords.longitude;
                    alert("Koordinat GPS berhasil diperbarui sesuai lokasi perangkat Anda saat ini: " + pos.coords.latitude + ", " + pos.coords.longitude);
                }, function(err) {
                    alert("Gagal membaca GPS: " + err.message);
                }, { enableHighAccuracy: true });
            } else {
                alert("Browser tidak mendukung geolokasi.");
            }
        }
    </script>
</x-layouts.layout>
