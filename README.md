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
- IPMICFG utility from Supermicro
- PHP 7.4 or later
- Web server (Apache/Nginx)

## Installation

### Step 1: Download IPMICFG

1. Visit the [Supermicro IPMICFG download page](https://www.supermicro.com/support/faqs/faq.cfm?faq=16428)
2. Download the appropriate version for your system architecture (x86_64 recommended)
3. Extract the archive and locate the `ipmicfg` binary

### Step 2: Install IPMICFG

```bash
# Copy IPMICFG to the system path
sudo cp ipmicfg /usr/local/sbin/
sudo chmod +x /usr/local/sbin/ipmicfg

# Test the installation
ipmicfg -s
```

### Step 3: Install the Plugin

1. **Manual Installation**:
   ```bash
   # Download the plugin
   cd /tmp
   wget https://github.com/ShunHax/supermicro-ipmi/archive/main.zip
   unzip main.zip
   
   # Install to Unraid plugins directory
   sudo cp -r supermicro-ipmi-main /usr/local/emhttp/plugins/supermicro-ipmi
   sudo chmod +x /usr/local/emhttp/plugins/supermicro-ipmi/scripts/monitor.sh
   ```

2. **Plugin Manager Installation** (if available):
   - Go to Unraid Web Interface → Apps
   - Search for "Supermicro IPMI"
   - Click Install

### Step 4: Configure the Plugin

1. Access the plugin from the Unraid Web Interface
2. Go to Settings and configure:
   - **Local BMC**: Enable and set IPMICFG path
   - **Remote BMC**: Configure if needed
   - **GUI Settings**: Customize refresh intervals
   - **Security**: Set authentication requirements

### Step 5: Verify Installation

1. Check the plugin appears in the Unraid interface
2. Test BMC connectivity
3. Verify sensor readings are displayed
4. Test power control functions

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
**Solution**: Download and install IPMICFG from Supermicro website

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
- Regular backup of configuration files
- Secure storage of credentials
- Audit trail maintenance

## API Reference

### REST Endpoints

#### Power Management
```
POST /plugins/supermicro-ipmi/api/power
{
  "action": "on|off|reset|cycle"
}
```

#### Sensor Data
```
GET /plugins/supermicro-ipmi/api/sensors
```

#### Event Log
```
GET /plugins/supermicro-ipmi/api/events
POST /plugins/supermicro-ipmi/api/events/clear
```

#### User Management
```
GET /plugins/supermicro-ipmi/api/users
POST /plugins/supermicro-ipmi/api/users
PUT /plugins/supermicro-ipmi/api/users/{id}
DELETE /plugins/supermicro-ipmi/api/users/{id}
```

### Response Format

```json
{
  "success": true,
  "message": "Operation completed successfully",
  "data": {
    // Response data
  }
}
```

## Development

### Building from Source

```bash
# Clone the repository
git clone https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool.git
cd supermicro-ipmi

# Install dependencies
npm install

# Build the plugin
npm run build
```

### Contributing

1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Add tests if applicable
5. Submit a pull request

### Testing

```bash
# Run unit tests
npm test

# Run integration tests
npm run test:integration

# Run linting
npm run lint
```

## Support

### Documentation
- [User Guide](docs/user-guide.md)
- [API Documentation](docs/api.md)
- [Troubleshooting Guide](docs/troubleshooting.md)

### Community
- [GitHub Issues](https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/issues)
- [Discussions](https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/discussions)
- [Wiki](https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/wiki)

## License

This project is licensed under the MIT License - see the [LICENSE](LICENSE) file for details.

## Acknowledgments

- Supermicro for providing the IPMICFG utility
- Unraid community for feedback and testing
- Contributors and maintainers

## Changelog

### Version 1.0.0
- Initial release
- Basic power management
- Sensor monitoring
- User management
- Event logging
- Local and remote BMC support

### Version 1.1.0 (Planned)
- Advanced sensor graphing
- Email notifications
- Mobile-responsive design
- API improvements
- Performance optimizations

---

**Note**: This plugin requires the IPMICFG utility from Supermicro. Please ensure you comply with Supermicro's licensing terms when using their software. # Supermicro-IPMI
# Supermicro-IPMI
