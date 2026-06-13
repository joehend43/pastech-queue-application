@echo off
title Script Anti-Sleep Printer Antrian
set IP_PRINTER=192.168.100.200

echo Start ping %IP_PRINTER%...

:loop
ping -n 1 %IP_PRINTER% >nul

if %errorlevel%==0 (
    echo [%date% %time%] Printer Standby
) else (
    echo [%date% %time%] WARNING: Printer tidak merespon! Cek kabel/daya.
)

:: Menunggu selama 180 detik (3 menit) sebelum ping ulang
timeout /t 180 /nobreak >nul
goto loop