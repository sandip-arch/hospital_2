@echo off
title Hospital Management System - Mobile Phone Tethering Host
color 0B
echo =========================================================================
echo    APEX HORIZON MEDICAL CENTER - MOBILE USB TETHERING HOST
echo =========================================================================
echo.
echo Detecting Active Network Adapters and IP Addresses:
echo -------------------------------------------------------------------------
for /f "tokens=4" %%a in ('route print 0.0.0.0 ^| findstr 0.0.0.0 ^| findstr /v "0.0.0.0.*0.0.0.0"') do (
    set DEFAULT_IP=%%a
)
ipconfig | findstr /i "IPv4"
echo -------------------------------------------------------------------------
echo.
echo NOTE FOR ANDROID / IPHONE USB TETHERING:
echo   1. Connect phone via USB cable and enable "USB Tethering" in Phone Settings.
echo   2. Look for the IPv4 address above (usually starting with 192.168.42.xxx).
echo   3. On your phone's browser (Chrome / Safari), navigate to:
echo.
echo      http://YOUR_LAPTOP_IP:8000
echo.
echo      (Or if using XAMPP Apache: http://YOUR_LAPTOP_IP/hospital/public)
echo.
echo =========================================================================
echo Starting Laravel Server on 0.0.0.0:8000 (accessible across tethering)...
echo Press Ctrl + C to stop the server at any time.
echo =========================================================================
echo.
php artisan serve --host=0.0.0.0 --port=8000
pause

