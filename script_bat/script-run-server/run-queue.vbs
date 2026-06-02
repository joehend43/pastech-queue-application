Set WshShell = CreateObject("WScript.Shell")
WshShell.Run "cmd /c cd /d C:\laragon\www\pastech-queue-application && php artisan serve --host=0.0.0.0 --port=8000", 0, False
WshShell.Run "cmd /c cd /d C:\laragon\www\pastech-queue-application && php artisan reverb:start", 0, False