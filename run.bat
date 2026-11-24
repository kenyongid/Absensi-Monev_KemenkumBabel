@echo off
set "PATH=D:\laragon\bin\php\php-8.3.37-Win32-vs16-x64;D:\laragon\bin\mysql\mysql-8.4.3-winx64\bin;D:\laragon\bin\composer;%PATH%"
echo ===================================================
echo  Menjalankan Absensi-Monev Kemenkum Babel
echo  URL: http://127.0.0.1:8000
echo ===================================================
php artisan serve --port=8000
pause
