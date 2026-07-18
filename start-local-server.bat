@echo off
REM ==========================================================
REM  Asclepius - local test server (Windows + XAMPP)
REM  Double-click this file to set up the database and run the app.
REM  Requires: XAMPP installed, and MySQL STARTED in the XAMPP Control Panel.
REM ==========================================================
cd /d "%~dp0"
set "PHP=C:\xampp\php\php.exe"
set "MYSQL=C:\xampp\mysql\bin\mysql.exe"

echo ==================================================
echo   Asclepius - Local Test Server
echo ==================================================
echo.
echo [1/3] Creating database (if needed)...
"%MYSQL%" -u root -e "CREATE DATABASE IF NOT EXISTS asclepius_db CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;"
if errorlevel 1 goto nodb

echo [2/3] Loading tables...
"%MYSQL%" -u root asclepius_db < "backend\db\schema.sql"

echo [3/3] Starting web server...
echo.
echo   Open your browser to:  http://localhost:8000/register.php
echo   Keep this window OPEN. Close it (or press Ctrl+C) to stop the server.
echo.
"%PHP%" -S localhost:8000
goto end

:nodb
echo.
echo   ERROR: Could not reach MySQL.
echo   1. Open the XAMPP Control Panel.
echo   2. Click "Start" next to MySQL (the row should turn green).
echo   3. Double-click this file again.
echo.
pause

:end
