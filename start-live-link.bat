@echo off
title El-3awatleya Live Link
chcp 65001 >nul
color 0A
echo =======================================================
echo    تشغيل موقع العواطلية وسيرفر النفق المباشر
echo =======================================================
echo.
echo [1/2] جاري تشغيل سيرفر PHP المحلي...
start "PHP Server (El-3awatleya)" /min cmd /c "php -S 127.0.0.1:8000 index.php"

timeout /t 2 /nobreak >nul

echo [2/2] جاري إنشاء الرابط المباشر (Public Link)...
echo.
echo عند فتح الرابط لأول مرة، إذا طُلب منك Password أدخل IP جهازك
echo لمعرفة الـ IP اضغط على الرابط اللي هيظهرلك أو افتح https://loca.lt/mytunnelpassword
echo =======================================================
echo.

npx.cmd --yes localtunnel --port 8000
pause

