@echo off
cd /d "%~dp0"
title KPI Anak - Server
echo  ==============================================
echo    KPI ANAK - server lokal
echo  ==============================================
echo.
echo    Dari laptop ini : http://localhost:8000
for /f %%i in ('powershell -NoProfile -Command "(Get-NetIPAddress -AddressFamily IPv4 | Where-Object { $_.IPAddress -notlike '169.254*' -and $_.IPAddress -ne '127.0.0.1' } | Select-Object -First 1 -ExpandProperty IPAddress)"') do echo    Dari HP (WiFi sama) : http://%%i:8000
echo.
echo    Biarkan jendela ini terbuka selama aplikasi dipakai.
echo    Tekan Ctrl+C untuk berhenti.
echo  ==============================================
echo.
php artisan serve --host=0.0.0.0 --port=8000
