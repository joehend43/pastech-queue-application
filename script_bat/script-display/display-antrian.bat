@echo off
title Display Antrian

set "URL=http://localhost:8000/display?left=1&right=2"
set CHROME_PATH=C:\Program Files\Google\Chrome\Application\chrome.exe

start "" "%CHROME_PATH%" --kiosk "%URL%" --autoplay-policy=no-user-gesture-required

timeout /t 5 /nobreak >nul

powershell -ExecutionPolicy Bypass -File move_chrome.ps1

exit