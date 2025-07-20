# Supermicro BMC/IPMI Tool for Unraid

A comprehensive Unraid plugin for managing Supermicro motherboards with IPMI support using the IPMICFG utility.

## 🚀 Quick Installation

### Option 1: Community Applications (Recommended)
1. In Unraid, go to **Settings** > **Community Applications**
2. Click on the **Settings** tab
3. Under **Custom Repositories**, add:
   ```
   https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool
   ```
4. Search for "Supermicro IPMI" in Community Applications
5. Click **Install**

### Option 2: Manual Installation
1. Download the `supermicro-ipmi-1.0.0.txz` file from [Releases](https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/releases)
2. Upload it to your Unraid server
3. SSH into Unraid and run: `installpkg supermicro-ipmi-1.0.0.txz`

## ✨ Features

- **Local BMC Management**: Direct access to local IPMI interface
- **Remote BMC Support**: Manage remote BMCs over network
- **Sensor Monitoring**: Real-time temperature, voltage, and fan monitoring
- **Event Logging**: IPMI event log viewing and management
- **User Management**: BMC user account management
- **Power Management**: Remote power control (on/off/reset)
- **Network Configuration**: BMC network settings management
- **Included IPMICFG**: No internet required - binary included in package

## 📦 What's Included

- **Plugin Interface**: Web-based GUI for managing IPMI settings
- **IPMICFG Utility**: Includes the latest IPMICFG binary for Linux x64 (no internet required)
- **Monitoring**: Automatic sensor monitoring and event logging
- **Configuration**: Persistent settings stored in `/boot/config/plugins/supermicro-ipmi/`

## 🔧 Requirements

- Unraid 6.8.0 or higher
- Supermicro motherboard with IPMI support
- Linux x64 architecture (IPMICFG binary included)

## 📁 Project Structure

Following SimonFair's clean organization pattern:

```
Unraid-Supermicro-BMC-IPMI-Tool/
├── archive/                    # Built packages and releases
│   └── supermicro-ipmi-1.0.0.txz
├── packages/                   # Package definitions and CA templates
│   ├── ca-template.xml         # Community Applications template
│   └── supermicro-ipmi-ca.xml  # CA integration file
├── plugin/                     # Plugin source files
│   ├── plugin.php              # Plugin metadata
│   ├── supermicro-ipmi.php     # Main plugin page
│   ├── settings.php            # Settings page
│   ├── install.sh              # Installation script
│   ├── uninstall.sh            # Uninstallation script
│   ├── includes/               # PHP includes
│   ├── css/                    # Stylesheets
│   ├── js/                     # JavaScript files
│   ├── images/                 # Plugin icons
│   ├── scripts/                # Utility scripts
│   └── ipmicfg/                # IPMICFG binary
│       └── IPMICFG-Linux.x86_64
├── source/                     # Source code and documentation
│   ├── PACKAGE_README.md       # User installation guide
│   ├── PACKAGE_BUILD_README.md # Developer build guide
│   ├── PACKAGE_SUMMARY.md      # Project summary
│   ├── CA_SUBMISSION_GUIDE.md  # CA submission guide
│   └── package.json            # Project metadata
├── .github/workflows/          # Automated build and release
├── build-package.bat           # Windows build script
├── build-package.sh            # Linux build script
└── README.md                   # This file
```

## 🛠️ Development

For developers who want to build the package:

### Windows
```cmd
build-package.bat
```

### Linux/Unraid
```bash
chmod +x build-package.sh
./build-package.sh
```

See [source/PACKAGE_BUILD_README.md](source/PACKAGE_BUILD_README.md) for detailed development instructions.

## 📖 Documentation

- [Package Installation Guide](source/PACKAGE_README.md) - Complete user installation instructions
- [Package Building Guide](source/PACKAGE_BUILD_README.md) - Developer build instructions
- [Project Summary](source/PACKAGE_SUMMARY.md) - Overview of what's been created
- [CA Submission Guide](source/CA_SUBMISSION_GUIDE.md) - How to submit to Community Applications

## 🤝 Support

- **GitHub Issues**: [Report bugs or request features](https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/issues)
- **Documentation**: [Complete project documentation](https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool)
- **Releases**: [Download latest version](https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/releases)

## 📄 License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## 🙏 Acknowledgments

- Supermicro for providing the IPMICFG utility
- Unraid community for plugin development guidelines
- SimonFair for the clean project structure inspiration
- Contributors and testers
