#!/bin/bash

# Supermicro IPMI Plugin Installation Script
# This script handles the installation and setup of the plugin

set -e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Plugin configuration
PLUGIN_NAME="supermicro-ipmi"
PLUGIN_DIR="/usr/local/emhttp/plugins/$PLUGIN_NAME"
CONFIG_DIR="/boot/config/plugins/$PLUGIN_NAME"
LOG_FILE="/var/log/plugins/$PLUGIN_NAME/install.log"

# Logging function
log() {
    echo "$(date '+%Y-%m-%d %H:%M:%S') - $1" | tee -a "$LOG_FILE"
}

# Error handling
error_exit() {
    log "ERROR: $1"
    echo -e "${RED}ERROR: $1${NC}"
    exit 1
}

# Success message
success() {
    log "SUCCESS: $1"
    echo -e "${GREEN}SUCCESS: $1${NC}"
}

# Warning message
warning() {
    log "WARNING: $1"
    echo -e "${YELLOW}WARNING: $1${NC}"
}

# Check if running as root
if [[ $EUID -ne 0 ]]; then
    error_exit "This script must be run as root"
fi

# Create log directory
mkdir -p "$(dirname "$LOG_FILE")"

log "Starting Supermicro IPMI plugin installation..."

# Create plugin directories
log "Creating plugin directories..."
mkdir -p "$PLUGIN_DIR"
mkdir -p "$CONFIG_DIR"

# Set proper permissions
log "Setting permissions..."
chmod 755 "$PLUGIN_DIR"
chmod 755 "$CONFIG_DIR"

# Make scripts executable
if [ -d "$PLUGIN_DIR/scripts" ]; then
    chmod +x "$PLUGIN_DIR/scripts"/*.sh 2>/dev/null || true
fi

# Copy icon to config directory if it exists
if [ -f "$PLUGIN_DIR/images/icon.png" ]; then
    cp "$PLUGIN_DIR/images/icon.png" "$CONFIG_DIR/icon.png"
    log "Icon copied to config directory"
fi

# Load IPMI modules
log "Loading IPMI kernel modules..."
modprobe ipmi_msghandler 2>/dev/null || warning "Failed to load ipmi_msghandler"
modprobe ipmi_devintf 2>/dev/null || warning "Failed to load ipmi_devintf"
modprobe ipmi_si 2>/dev/null || warning "Failed to load ipmi_si"

# Install IPMICFG if the script exists
if [ -f "$PLUGIN_DIR/scripts/install_ipmicfg.sh" ]; then
    log "Installing IPMICFG utility..."
    if "$PLUGIN_DIR/scripts/install_ipmicfg.sh"; then
        success "IPMICFG utility installed successfully"
    else
        warning "IPMICFG utility installation failed"
    fi
fi

# Setup monitoring cron job if monitor script exists
if [ -f "$PLUGIN_DIR/scripts/monitor.sh" ]; then
    log "Setting up monitoring cron job..."
    echo "*/5 * * * * $PLUGIN_DIR/scripts/monitor.sh" > /etc/cron.d/supermicro-ipmi
    chmod 644 /etc/cron.d/supermicro-ipmi
    success "Monitoring cron job configured"
fi

# Create default configuration if it doesn't exist
if [ ! -f "$CONFIG_DIR/config.json" ]; then
    log "Creating default configuration..."
    cat > "$CONFIG_DIR/config.json" << 'EOF'
{
    "local_bmc": {
        "enabled": true,
        "device": "/dev/ipmi0",
        "ipmicfg_path": "/usr/local/sbin/ipmicfg"
    },
    "remote_bmc": {
        "enabled": false,
        "host": "",
        "port": 623,
        "username": "",
        "password": "",
        "privilege_level": "ADMINISTRATOR"
    },
    "gui_settings": {
        "refresh_interval": 30,
        "auto_refresh": true,
        "show_sensors": true,
        "show_events": true,
        "show_users": true
    }
}
EOF
    chmod 644 "$CONFIG_DIR/config.json"
    success "Default configuration created"
fi

success "Supermicro IPMI plugin installed successfully!"
log "Installation completed successfully"

echo -e "${GREEN}Plugin installed to: $PLUGIN_DIR${NC}"
echo -e "${GREEN}Configuration directory: $CONFIG_DIR${NC}"
echo -e "${GREEN}You can access the plugin from the Unraid web interface${NC}" 