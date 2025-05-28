@echo off
title Serveur PHP local - Télé-enseignement

:: Récupérer l'IP locale
for /f "tokens=2 delims=:" %%a in ('ipconfig ^| findstr /R /C:"IPv4"') do set IP=%%a
set IP=%IP:~1%

:: Port utilisé
set PORT=8000

:: Lancer le serveur PHP
echo ------------------------------------------------------
echo Lancement du serveur PHP...
echo Adresse locale : http://localhost:%PORT%
echo Adresse réseau : http://%IP%:%PORT%
echo (Ouvre ces liens dans ton navigateur)
echo ------------------------------------------------------
php -S 0.0.0.0:%PORT%
pause