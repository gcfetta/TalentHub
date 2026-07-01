@echo off
set DB_NAME=talenthub_db
set DB_USER=root
set DB_PASS=tu_password
set BACKUP_DIR=C:\backups\talenthub
for /f "tokens=1-3 delims=/ " %%a in ('date /t') do set FECHA=%%c%%a%%b
for /f "tokens=1-2 delims=: " %%a in ('time /t') do set HORA=%%a%%b

if not exist "%BACKUP_DIR%" mkdir "%BACKUP_DIR%"

mysqldump -u %DB_USER% -p%DB_PASS% --routines --triggers --single-transaction %DB_NAME% > "%BACKUP_DIR%\talenthub_%FECHA%_%HORA%.sql"