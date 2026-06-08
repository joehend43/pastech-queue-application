Set WshShell = CreateObject("WScript.Shell")
WshShell.Run chr(34) & "C:\queue_listener\run_listener.bat" & chr(34), 0
Set WshShell = Nothing