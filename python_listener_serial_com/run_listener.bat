@echo off

:loop
cd C:\Queue_Listener\dist\queue_listener

queue_listener.exe

echo Program Crash. Restart in 5 sec...
timeout /t 5 /nobreak >nul

goto loop