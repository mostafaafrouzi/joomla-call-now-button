@echo off
REM Build script for Windows
REM Run this to create a release ZIP file

echo.
echo ============================================
echo   Call Now Button Module - Build Script
echo ============================================
echo.

REM Run PowerShell build script
PowerShell -ExecutionPolicy Bypass -File "%~dp0build.ps1"

echo.
pause

