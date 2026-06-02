@echo off

set "URL=http://localhost:8000/display?left=1^&right=2"
set "CHROME_PATH=C:\Program Files\Google\Chrome\Application\chrome.exe"

start "" "%CHROME_PATH%" ^
--new-window ^
--disable-gpu ^
"%URL%"

timeout /t 3 /nobreak >nul

powershell -ExecutionPolicy Bypass -File move_chrome.ps1



@REM start "" "%CHROME_PATH%" ^
@REM --new-window ^
@REM --disable-gpu ^
@REM --disable-gpu-compositing ^
@REM --disable-features=CalculateNativeWinOcclusion ^

@REM --disable-features=UseSkiaRenderer ^
@REM --disable-backgrounding-occluded-windows ^
@REM --disable-renderer-backgrounding ^