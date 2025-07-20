# Supermicro BMC/IPMI Tool for Unraid

A web-based IPMI management plugin for Supermicro motherboards with BMC support.

## Features

- **Power Control**: On/Off/Reset/Power Cycle
- **Sensor Monitoring**: Real-time temperature, voltage, and fan monitoring
- **User Management**: BMC account administration
- **Event Logs**: View and filter system events
- **Local & Remote**: Works with local BMC or remote IPMI connections

## Installation

### Method 1: Plugin Manager (Recommended)
1. Go to **Apps** → **Community Applications**
2. Search for "Supermicro IPMI"
3. Click **Install**

### Method 2: Manual Installation
1. Download the plugin files
2. Copy to `/tmp/supermicro-ipmi-plugin/` on your Unraid server
3. Run: `installplg /tmp/supermicro-ipmi-plugin/supermicro-ipmi.plg`

## Usage

After installation, access the plugin at:
**Settings** → **Supermicro-IPMI**

The plugin automatically downloads and installs the IPMICFG utility from Supermicro.

## Support

- **GitHub Issues**: [Report bugs or request features](https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/issues)
- **Documentation**: See the plugin's web interface for detailed instructions

## Requirements

- Unraid 6.8+
- Supermicro motherboard with IPMI/BMC support
- Internet connection (for initial IPMICFG download)

## License

MIT License - see LICENSE file for details.
