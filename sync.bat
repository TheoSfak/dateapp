@echo off
REM Sync dateapp to XAMPP htdocs
REM /MIR mirrors source to dest but /XD excludes directories from deletion
REM Uploads are excluded because they're created at runtime on the server side
robocopy "C:\Users\User\Desktop\dateapp" "C:\xampp\htdocs\dateapp" /MIR /XD .git node_modules uploads /NFL /NDL /NJH /NJS /NC /NS
echo Sync complete.
