#!/bin/bash

# Manual Installation Script for Supermicro IPMI Tool
# This script installs the plugin manually without using the plugin system

set -e

echo "=== Supermicro IPMI Tool Manual Installation ==="
echo ""

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    echo "ERROR: This script must be run as root"
    exit 1
fi

# Check if plugin source exists
PLUGIN_SOURCE="/tmp/supermicro-ipmi-plugin"
if [ ! -d "$PLUGIN_SOURCE" ]; then
    echo "ERROR: Plugin source directory not found at $PLUGIN_SOURCE"
    echo "Please ensure the plugin files are copied to /tmp/supermicro-ipmi-plugin/"
    exit 1
fi

echo "Creating plugin directories..."
mkdir -p /boot/config/plugins/supermicro-ipmi
mkdir -p /usr/local/emhttp/plugins/supermicro-ipmi
mkdir -p /var/local/plugins/supermicro-ipmi
mkdir -p /tmp/plugins/supermicro-ipmi
mkdir -p /var/cache/plugins/supermicro-ipmi
mkdir -p /mnt/user/appdata/plugins/supermicro-ipmi/backup

echo "Copying plugin files..."
cp -r "$PLUGIN_SOURCE"/* /usr/local/emhttp/plugins/supermicro-ipmi/

echo "Setting permissions..."
chown -R root:root /usr/local/emhttp/plugins/supermicro-ipmi
chmod -R 755 /usr/local/emhttp/plugins/supermicro-ipmi

if [ -d "/usr/local/emhttp/plugins/supermicro-ipmi/scripts" ]; then
    echo "Making scripts executable..."
    chmod +x /usr/local/emhttp/plugins/supermicro-ipmi/scripts/*.sh
fi

echo "Loading IPMI drivers..."
for module in ipmi_msghandler ipmi_devintf ipmi_si; do
    modprobe -q $module
done

echo "Installing IPMICFG..."
if [ -f "/usr/local/emhttp/plugins/supermicro-ipmi/scripts/install_ipmicfg.sh" ]; then
    /usr/local/emhttp/plugins/supermicro-ipmi/scripts/install_ipmicfg.sh
else
    echo "WARNING: IPMICFG installation script not found"
fi

echo "Setting up monitoring cron job..."
if [ ! -f "/etc/cron.d/supermicro-ipmi" ]; then
    echo "*/5 * * * * root /usr/local/emhttp/plugins/supermicro-ipmi/scripts/monitor.sh >/dev/null 2>&1" > /etc/cron.d/supermicro-ipmi
fi

echo "Creating configuration file..."
cat > /boot/config/plugins/supermicro-ipmi/supermicro-ipmi.cfg << 'EOF'
LOCAL_BMC="enable"
LOCAL_DEVICE="/dev/ipmi0"
IPMICFG_PATH="/usr/local/sbin/ipmicfg"
REMOTE_BMC="disable"
REMOTE_HOST=""
REMOTE_PORT="623"
REMOTE_USER=""
REMOTE_PASSWORD=""
REMOTE_PRIVILEGE="ADMINISTRATOR"
GUI_REFRESH="30"
GUI_AUTO_REFRESH="enable"
GUI_SHOW_SENSORS="enable"
GUI_SHOW_EVENTS="enable"
GUI_SHOW_USERS="enable"
SECURITY_AUTH="enable"
SECURITY_USERS="root"
SECURITY_TIMEOUT="3600"
EOF

echo ""
echo "=== Installation Complete! ==="
echo ""
echo "The plugin has been installed manually."
echo "You may need to restart the web interface to see the plugin:"
echo "  /etc/rc.d/rc.nginx restart"
echo ""
echo "Or reboot your server to ensure all services are properly loaded."
echo ""
echo "The plugin should appear in Settings -> Supermicro-IPMI"
echo "" 