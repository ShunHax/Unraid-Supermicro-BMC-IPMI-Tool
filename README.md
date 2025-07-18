# Supermicro BMC/IPMI Tool for Unraid

A comprehensive Unraid plugin for managing Supermicro motherboards with IPMI support using the IPMICFG utility. This plugin provides a modern web-based interface for local and remote BMC management without requiring network connectivity for local operations.

## Features

### 🔌 Power Management
- **Power On/Off**: Complete system power control
- **Reset**: Hard reset functionality
- **Power Cycle**: Graceful shutdown and restart
- **Status Monitoring**: Real-time power state tracking

### 📊 System Monitoring
- **Sensor Monitoring**: Temperature, voltage, fan speed, and more
- **Event Log**: System event tracking with filtering
- **Health Status**: Overall system health assessment
- **Real-time Updates**: Auto-refresh capabilities

### 👥 User Management
- **User Administration**: Add, edit, and delete BMC users
- **Privilege Levels**: User, Operator, and Administrator roles
- **Password Management**: Secure credential handling

### 🌐 Network Configuration
- **Local BMC**: Direct communication without network
- **Remote BMC**: Network-based management with authentication
- **IP Configuration**: BMC network settings management

### 🛡️ Security Features
- **Authentication**: Secure access control
- **Session Management**: Configurable timeouts
- **Audit Logging**: Comprehensive activity tracking

## Prerequisites

### Hardware Requirements
- Supermicro motherboard with IPMI support
- BMC (Baseboard Management Controller) enabled
- IPMI interface properly configured

### Software Requirements
- Unraid 6.8 or later
- IPMICFG utility (automatically downloaded during installation)
- PHP 7.4 or later
- Web server (Apache/Nginx)

## Installation

### Method 1: Plugin Manager (Recommended)

1. **Download the .plg file** to your local computer
2. **Open your Unraid Web Interface**
3. **Go to the Apps tab**
4. **Click "Install Plugin"** (usually a button or option in the Apps section)
5. **Upload the `supermicro-ipmi.plg` file**
6. **Click Install**

The plugin manager will automatically:
- Download all the plugin files from the repository
- Install them to the correct location
- Set proper permissions
- Restart the web interface
- **Automatically download and install IPMICFG from Supermicro**

### Method 2: Manual Installation

If you prefer manual installation:

```bash
# SSH into your Unraid server
ssh root@your-unraid-server-ip

# Download the .plg file
cd /tmp
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/supermicro-ipmi.plg

# Install the plugin
installplg supermicro-ipmi.plg
```

### Method 3: Direct File Installation

```bash
# SSH into your Unraid server
ssh root@your-unraid-server-ip

# Download and install directly
cd /tmp
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/supermicro-ipmi.plg
installplg supermicro-ipmi.plg
```

## What Happens During Installation

1. **Plugin Installation**: The plugin files are copied to `/usr/local/emhttp/plugins/supermicro-ipmi/`
2. **Automatic IPMICFG Download**: The plugin will automatically download IPMICFG from Supermicro
3. **IPMICFG Installation**: The tool is extracted and installed to `/usr/local/sbin/ipmicfg`
4. **Permission Setup**: Proper permissions are set for security
5. **Testing**: The installation is tested to ensure it works
6. **Service Setup**: Cron jobs and monitoring services are configured

## Configuration

### Local BMC Settings

```json
{
  "local_bmc": {
    "enabled": true,
    "device": "/dev/ipmi0",
    "ipmicfg_path": "/usr/local/sbin/ipmicfg"
  }
}
```

### Remote BMC Settings

```json
{
  "remote_bmc": {
    "enabled": false,
    "host": "192.168.1.100",
    "port": 623,
    "username": "admin",
    "password": "password",
    "privilege_level": "ADMINISTRATOR"
  }
}
```

### GUI Settings

```json
{
  "gui_settings": {
    "refresh_interval": 30,
    "auto_refresh": true,
    "show_sensors": true,
    "show_events": true,
    "show_users": true
  }
}
```

## Usage

### Accessing the Interface

1. Open your Unraid Web Interface
2. Navigate to the "Supermicro IPMI" tab
3. The main dashboard will display system status

### Power Management

1. **Power On**: Click the green "Power On" button
2. **Power Off**: Click the yellow "Power Off" button
3. **Reset**: Click the red "Reset" button
4. **Power Cycle**: Click the blue "Power Cycle" button

### Monitoring Sensors

- View real-time sensor data in the Sensors section
- Color-coded status indicators (Green=OK, Yellow=Warning, Red=Critical)
- Hover over sensors for detailed information
- Click refresh to update readings

### Managing Users

1. **Add User**: Click "Add User" in the User Management section
2. **Edit User**: Click the edit icon next to a user
3. **Delete User**: Click the delete icon (use with caution)

### Viewing Events

- Browse system events in chronological order
- Filter by severity level (Info, Warning, Critical)
- Clear event log when needed
- Export events for analysis

## Troubleshooting

### Common Issues

#### IPMICFG Not Found
```
Error: IPMICFG not found at /usr/local/sbin/ipmicfg
```
**Solution**: The plugin should automatically download IPMICFG. If it fails, run:
```bash
/usr/local/emhttp/plugins/supermicro-ipmi/scripts/install_ipmicfg.sh
```

#### BMC Connection Failed
```
Error: BMC connection failed
```
**Solutions**:
- Verify BMC is enabled in BIOS
- Check IPMI interface configuration
- Ensure proper permissions on IPMICFG

#### Permission Denied
```
Error: Permission denied when executing IPMICFG
```
**Solution**: 
```bash
sudo chmod +x /usr/local/sbin/ipmicfg
sudo chown root:root /usr/local/sbin/ipmicfg
```

#### Sensor Data Not Displaying
```
Error: No sensor data available
```
**Solutions**:
- Verify BMC firmware is up to date
- Check sensor configuration in BMC
- Ensure proper IPMI driver loading

### Log Files

- **Plugin Log**: `/var/log/plugins/supermicro-ipmi.log`
- **System Log**: `/var/log/syslog`
- **BMC Log**: Check BMC web interface

### Debug Mode

Enable debug logging by editing the configuration:

```json
{
  "debug": {
    "enabled": true,
    "level": "DEBUG"
  }
}
```

## Security Considerations

### Access Control
- Use strong passwords for BMC users
- Limit administrative access
- Regularly audit user accounts
- Enable session timeouts

### Network Security
- Use HTTPS for remote access
- Configure firewall rules appropriately
- Monitor access logs
- Keep BMC firmware updated

### Data Protection
- Encrypt sensitive configuration data
- Regular backup of plugin settings
- Monitor for unauthorized access attempts

## Development

### Building the Plugin Package

To create the plugin package for distribution:

```bash
# Create the package directory
mkdir -p supermicro-ipmi-1.0.0-x86_64-1/usr/local/emhttp/plugins/supermicro-ipmi

# Copy plugin files
cp -r * supermicro-ipmi-1.0.0-x86_64-1/usr/local/emhttp/plugins/supermicro-ipmi/

# Create the package
makepkg supermicro-ipmi-1.0.0-x86_64-1.txz

# Generate MD5 hash
md5sum supermicro-ipmi-1.0.0-x86_64-1.txz > supermicro-ipmi-1.0.0-x86_64-1.md5
```

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Support

- **GitHub Issues**: [Report bugs or request features](https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/issues)
- **Documentation**: [Plugin documentation](https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/wiki)
- **Community**: [Unraid Community Forums](https://forums.unraid.net/)

## Changelog

### Version 1.0.0
- Initial release
- Automatic IPMICFG download and installation from Supermicro
- Web-based BMC management interface
- Power control (on/off/reset/power cycle)
- Sensor monitoring with real-time updates
- User management for BMC accounts
- Event log viewing and filtering
- Local and remote BMC support
- Security features with authentication
- Modern responsive UI design
