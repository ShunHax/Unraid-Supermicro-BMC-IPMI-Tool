# Supermicro IPMI Plugin Installation

## Automatic Installation (Recommended)

1. Download the `.txz` package file
2. In Unraid, go to **Settings** > **Community Applications**
3. Click on the **Settings** tab
4. Under **Custom Repositories**, add:
   ```
   https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool
   ```
5. Search for "Supermicro IPMI" in Community Applications
6. Click **Install**

## Manual Installation

1. Download the `.txz` package file
2. Upload it to your Unraid server (via SMB, SSH, or USB)
3. SSH into your Unraid server
4. Navigate to the directory containing the package
5. Run: `installpkg supermicro-ipmi-1.0.0.txz`
6. The plugin will appear in your Unraid web interface

## Post-Installation

1. The plugin will automatically install the IPMICFG utility
2. Configure your BMC settings in the plugin interface
3. Access the plugin from the Unraid web interface

## Requirements

- Unraid 6.8.0 or higher
- Supermicro motherboard with IPMI support
- Network connectivity for IPMICFG download

## Support

For support and issues, visit:
https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/issues
