Set WshShell = CreateObject("WScript.Shell")
WshShell.Run """C:\Users\kedar\Downloads\pgsql\bin\pg_ctl.exe"" -D ""C:\Users\kedar\Downloads\pgsql\data"" -l ""C:\Users\kedar\Downloads\pgsql\logfile.log"" start", 0, False
