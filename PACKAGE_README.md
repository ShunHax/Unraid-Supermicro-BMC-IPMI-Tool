# Supermicro BMC/IPMI Tool - Unraid Plugin Package

This is the package distribution for the Supermicro BMC/IPMI Tool Unraid plugin.

## Quick Installation

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

1. Download the `supermicro-ipmi-1.0.0.txz` file
2. Upload it to your Unraid server (via SMB, SSH, or USB)
3. SSH into your Unraid server
4. Navigate to the directory containing the package
5. Run: `installpkg supermicro-ipmi-1.0.0.txz`
6. The plugin will appear in your Unraid web interface

## What's Included

- **Plugin Interface**: Web-based GUI for managing IPMI settings
- **IPMICFG Utility**: Includes the latest IPMICFG binary for Linux x64 (no internet required)
- **Monitoring**: Automatic sensor monitoring and event logging
- **Configuration**: Persistent settings stored in `/boot/config/plugins/supermicro-ipmi/`

## Features

- **Local BMC Management**: Direct access to local IPMI interface
- **Remote BMC Support**: Manage remote BMCs over network
- **Sensor Monitoring**: Real-time temperature, voltage, and fan monitoring
- **Event Logging**: IPMI event log viewing and management
- **User Management**: BMC user account management
- **Power Management**: Remote power control (on/off/reset)
- **Network Configuration**: BMC network settings management

## Requirements

- Unraid 6.8.0 or higher
- Supermicro motherboard with IPMI support
- Linux x64 architecture (IPMICFG binary included)

## Post-Installation

1. The plugin will automatically install the included IPMICFG utility
2. Access the plugin from the Unraid web interface
3. Configure your BMC settings in the plugin interface
4. Monitor your system sensors and events

## Support

- **GitHub Issues**: https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/issues
- **Documentation**: https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool
- **Releases**: https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/releases

## Uninstallation

To remove the plugin:

1. Go to **Settings** > **Plugins** in Unraid
2. Find "Supermicro BMC/IPMI Tool"
3. Click **Remove**

Or via SSH:
```bash
removepkg supermicro-ipmi
```

## Package Contents

```
supermicro-ipmi-1.0.0.txz
├── usr/local/emhttp/plugins/supermicro-ipmi/
│   ├── plugin.php                 # Plugin metadata
│   ├── supermicro-ipmi.php        # Main plugin page
│   ├── settings.php               # Settings page
│   ├── install.sh                 # Installation script
│   ├── uninstall.sh               # Uninstallation script
│   ├── includes/                  # PHP includes
│   ├── css/                       # Stylesheets
│   ├── js/                        # JavaScript files
│   ├── images/                    # Plugin icons
│   └── scripts/                   # Utility scripts
│       ├── install_ipmicfg.sh     # IPMICFG installer
│       └── monitor.sh             # Monitoring script
├── ipmicfg/                       # IPMICFG binary
│   └── IPMICFG-Linux.x86_64       # Linux x64 IPMICFG binary
├── README.md                      # This file
├── LICENSE                        # License information
├── package.manifest               # Package metadata
└── INSTALL.md                     # Installation instructions
```

## Version History

- **v1.0.0**: Initial release with basic IPMI management features

## License

This plugin is licensed under the MIT License. See the LICENSE file for details. 