@echo off
cd /d "%~dp0"
powershell -NoProfile -ExecutionPolicy Bypass -File "%~dp0deploy\buat-paket-hosting.ps1" %*
echo.
pause
