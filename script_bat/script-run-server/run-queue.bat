@echo off

cd /d C:\laragon\www\pastech-queue-application

start /b php artisan serve --host=0.0.0.0 --port=8000
timeout /t 1 >nul
start /b php artisan reverb:start

exit

