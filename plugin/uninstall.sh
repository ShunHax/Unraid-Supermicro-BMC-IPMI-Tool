#!/bin/bash

# Supermicro IPMI Plugin Uninstall Script
# This script handles the removal of the plugin

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
LOG_FILE="/var/log/plugins/$PLUGIN_NAME/uninstall.log"

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

log "Starting Supermicro IPMI plugin uninstallation..."

# Stop any running services
log "Stopping IPMI services..."
/etc/rc.d/rc.ipmiseld stop 2>/dev/null || warning "Failed to stop ipmiseld service"

# Remove cron job
log "Removing monitoring cron job..."
rm -f /etc/cron.d/supermicro-ipmi

# Remove plugin files
log "Removing plugin files..."
if [ -d "$PLUGIN_DIR" ]; then
    rm -rf "$PLUGIN_DIR"
    success "Plugin files removed"
else
    warning "Plugin directory not found: $PLUGIN_DIR"
fi

# Remove configuration
log "Removing configuration..."
if [ -d "$CONFIG_DIR" ]; then
    rm -rf "$CONFIG_DIR"
    success "Configuration removed"
else
    warning "Configuration directory not found: $CONFIG_DIR"
fi

# Unload IPMI modules (only if no other services are using them)
log "Unloading IPMI kernel modules..."
modprobe -r ipmi_si 2>/dev/null || warning "Failed to unload ipmi_si (may be in use)"
modprobe -r ipmi_devintf 2>/dev/null || warning "Failed to unload ipmi_devintf (may be in use)"
modprobe -r ipmi_msghandler 2>/dev/null || warning "Failed to unload ipmi_msghandler (may be in use)"

# Note: We don't remove IPMICFG as it might be used by other applications
log "Note: IPMICFG utility was not removed as it may be used by other applications"
log "To remove IPMICFG manually, run: rm -f /usr/local/sbin/ipmicfg"

success "Supermicro IPMI plugin uninstalled successfully!"
log "Uninstallation completed successfully"

echo -e "${GREEN}Plugin has been completely removed from the system${NC}"
echo -e "${YELLOW}Note: IPMICFG utility was not removed automatically${NC}" 