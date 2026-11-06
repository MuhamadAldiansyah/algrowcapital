@echo off
title WhatsApp Blast Service
cd /d %~dp0
echo ==============================================
echo  Menjalankan Layanan WhatsApp Blast (Baileys)
echo  Port: 5050 - Drive D:
echo ==============================================
node server.js
pause

