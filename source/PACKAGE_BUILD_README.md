# Package Building Guide

This guide explains how to create the TXZ package for the Supermicro BMC/IPMI Tool Unraid plugin.

## Quick Start

### Windows (Recommended for Development)

1. **Build Package Structure:**
   ```cmd
   build-package.bat
   ```

2. **Create TXZ Package:**
   ```powershell
   .\create-txz.ps1
   ```

### Linux/Unraid

1. **Build Package Structure:**
   ```bash
   chmod +x build-package.sh
   ./build-package.sh
   ```

## Package Structure

The package follows the standard Unraid plugin structure:

```
supermicro-ipmi-1.0.0.txz
├── usr/local/emhttp/plugins/supermicro-ipmi/
│   ├── plugin.php                 # Plugin metadata
│   ├── supermicro-ipmi.php        # Main plugin page
│   ├── settings.php               # Settings page
│   ├── install.sh                 # Installation script
│   ├── uninstall.sh               # Uninstallation script
│   ├── includes/                  # PHP includes
│   │   ├── functions.php          # Core functions
│   │   ├── gui.php                # GUI components
│   │   └── ipmi.php               # IPMI interface
│   ├── css/                       # Stylesheets
│   │   └── style.css              # Plugin styles
│   ├── js/                        # JavaScript files
│   │   └── script.js              # Plugin scripts
│   ├── images/                    # Plugin icons
│   │   └── icon.png               # Plugin icon
│   └── scripts/                   # Utility scripts
│       ├── install_ipmicfg.sh     # IPMICFG installer
│       └── monitor.sh             # Monitoring script
├── ipmicfg/                       # IPMICFG binary
│   └── IPMICFG-Linux.x86_64       # Linux x64 IPMICFG binary
├── README.md                      # Package documentation
├── LICENSE                        # License information
├── package.manifest               # Package metadata
└── INSTALL.md                     # Installation instructions
```

## Installation Methods

### 1. Community Applications (Recommended)

Users can install directly from Community Applications by adding the repository:

```
https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool
```

### 2. Manual Installation

Users can download the TXZ file and install manually:

```bash
# Upload TXZ file to Unraid server
# SSH into Unraid
installpkg supermicro-ipmi-1.0.0.txz
```

### 3. Direct Download

Users can download the TXZ file directly from GitHub releases and install.

## Package Features

- **Included IPMICFG Binary**: Package includes the latest IPMICFG utility for Linux x64 (no internet required)
- **Plugin Integration**: Seamlessly integrates with Unraid's web interface
- **Configuration Persistence**: Settings stored in `/boot/config/plugins/supermicro-ipmi/`
- **Monitoring**: Automatic sensor monitoring with cron jobs
- **Clean Uninstallation**: Proper cleanup of all files and services

## Building Requirements

### Windows
- PowerShell 5.0 or higher
- 7-Zip (optional, for TXZ creation)
- Windows 10 or higher

### Linux/Unraid
- Bash shell
- tar and xz-utils packages
- Standard Linux utilities

## Release Process

1. **Update Version:**
   - Update version in `build-package.bat`, `build-package.sh`, and `package.json`
   - Update version in `supermicro-ipmi-ca.xml`

2. **Build Package:**
   ```bash
   # Windows
   build-package.bat
   .\create-txz.ps1
   
   # Linux
   ./build-package.sh
   ```

3. **Test Package:**
   - Upload to test Unraid system
   - Verify installation and functionality
   - Test uninstallation

4. **Create Release:**
   - Create GitHub release with TXZ file
   - Update Community Applications XML
   - Tag repository with version

## Community Applications Integration

The `supermicro-ipmi-ca.xml` file enables installation through Community Applications:

```xml
<?xml version="1.0"?>
<Container xmlns:xsi="http://www.w3.org/2001/XMLSchema-instance" version="1.0">
  <Name>Supermicro BMC/IPMI Tool</Name>
  <Repository>shunhax/supermicro-ipmi</Repository>
  <Registry>https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool</Registry>
  <!-- ... other metadata ... -->
</Container>
```

## Troubleshooting

### Package Creation Issues

1. **Missing 7-Zip on Windows:**
   - Install 7-Zip from https://7-zip.org/
   - Or use manual Linux method

2. **Permission Issues:**
   - Ensure scripts are executable: `chmod +x *.sh`
   - Run as administrator on Windows

3. **Missing Files:**
   - Verify all required files exist in `package/` directory
   - Check file paths in build scripts

### Installation Issues

1. **Plugin Not Appearing:**
   - Check plugin.php file exists and has correct metadata
   - Verify file permissions on Unraid

2. **IPMICFG Installation Fails:**
   - Check if IPMICFG binary exists in package
   - Verify file permissions on included binary
   - Check log files in `/var/log/plugins/supermicro-ipmi/`

## Development Notes

- The package includes the IPMICFG binary for Linux x64 (no internet required)
- Configuration is stored in JSON format for easy modification
- All scripts include proper error handling and logging
- The plugin follows Unraid's plugin development guidelines

## Support

For issues with package building or installation:

- **GitHub Issues**: https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/issues
- **Documentation**: https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool
- **Releases**: https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/releases 