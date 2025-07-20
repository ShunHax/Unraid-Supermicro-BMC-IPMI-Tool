@echo off
setlocal enabledelayedexpansion

REM Build script for Supermicro IPMI Plugin Package (Windows)
REM This script creates a proper TXZ package for Unraid installation

REM Configuration
set PLUGIN_NAME=supermicro-ipmi
set VERSION=1.0.0
set PACKAGE_NAME=%PLUGIN_NAME%-%VERSION%
set BUILD_DIR=build
set PACKAGE_DIR=%BUILD_DIR%\%PACKAGE_NAME%
set FINAL_PACKAGE=%PACKAGE_NAME%.txz

echo Building Supermicro IPMI Plugin Package v%VERSION%...

REM Clean previous build
if exist "%BUILD_DIR%" (
    echo Cleaning previous build...
    rmdir /s /q "%BUILD_DIR%"
)

REM Create build directory structure
echo Creating package structure...
mkdir "%PACKAGE_DIR%"

REM Copy plugin files to package directory
echo Copying plugin files...

REM Main plugin files
xcopy "package\usr" "%PACKAGE_DIR%\usr" /e /i /y

REM Copy additional files from root if they exist
if exist "README.md" (
    copy "README.md" "%PACKAGE_DIR%\"
)

if exist "LICENSE" (
    copy "LICENSE" "%PACKAGE_DIR%\"
)

REM Copy IPMICFG binary if it exists
if exist "ipmicfg" (
    xcopy "ipmicfg" "%PACKAGE_DIR%\ipmicfg" /e /i /y
)

REM Create package manifest
echo Creating package manifest...
(
echo Package: %PLUGIN_NAME%
echo Version: %VERSION%
echo Description: Manage IPMI compatible Supermicro motherboards with the IPMICFG utility
echo Author: ShunHax
echo Maintainer: ShunHax ^<shunhax@shunhax.com^>
echo Homepage: https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool
echo License: MIT
echo Architecture: x86_64
echo Section: unraid-plugins
echo Priority: optional
echo Depends: unraid ^(^>=6.8.0^)
echo Conflicts: 
echo Replaces: 
echo Provides: %PLUGIN_NAME%
) > "%PACKAGE_DIR%\package.manifest"

REM Create installation instructions
echo Creating installation instructions...
(
echo # Supermicro IPMI Plugin Installation
echo.
echo ## Automatic Installation ^(Recommended^)
echo.
echo 1. Download the `.txz` package file
echo 2. In Unraid, go to **Settings** ^> **Community Applications**
echo 3. Click on the **Settings** tab
echo 4. Under **Custom Repositories**, add:
echo    ```
echo    https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool
echo    ```
echo 5. Search for "Supermicro IPMI" in Community Applications
echo 6. Click **Install**
echo.
echo ## Manual Installation
echo.
echo 1. Download the `.txz` package file
echo 2. Upload it to your Unraid server ^(via SMB, SSH, or USB^)
echo 3. SSH into your Unraid server
echo 4. Navigate to the directory containing the package
echo 5. Run: `installpkg supermicro-ipmi-1.0.0.txz`
echo 6. The plugin will appear in your Unraid web interface
echo.
echo ## Post-Installation
echo.
echo 1. The plugin will automatically install the IPMICFG utility
echo 2. Configure your BMC settings in the plugin interface
echo 3. Access the plugin from the Unraid web interface
echo.
echo ## Requirements
echo.
echo - Unraid 6.8.0 or higher
echo - Supermicro motherboard with IPMI support
echo - Network connectivity for IPMICFG download
echo.
echo ## Support
echo.
echo For support and issues, visit:
echo https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/issues
) > "%PACKAGE_DIR%\INSTALL.md"

echo Package structure created successfully!
echo.
echo Package directory: %PACKAGE_DIR%
echo.
echo To create the TXZ package on Linux/Unraid:
echo 1. Copy the %PACKAGE_DIR% folder to a Linux system
echo 2. Run: tar -cJf %FINAL_PACKAGE% %PACKAGE_NAME%
echo.
echo Or use the build-package.sh script on Linux/Unraid
echo.
echo Package ready for distribution! 