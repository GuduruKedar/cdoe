@echo off
echo Starting PostgreSQL...
"C:\Users\kedar\Downloads\pgsql\bin\pg_ctl.exe" -D "C:\Users\kedar\Downloads\pgsql\data" -l "C:\Users\kedar\Downloads\pgsql\logfile.log" start
echo PostgreSQL started successfully.
timeout /t 3
