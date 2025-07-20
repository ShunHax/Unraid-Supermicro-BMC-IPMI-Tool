#!/bin/bash

# IPMICFG Installation Script for Unraid
# This script installs the IPMICFG utility from the included binary

set -e

# Configuration
IPMICFG_PATH="/usr/local/sbin/ipmicfg"
PLUGIN_DIR="/usr/local/emhttp/plugins/supermicro-ipmi"
INCLUDED_IPMICFG="$PLUGIN_DIR/ipmicfg/IPMICFG-Linux.x86_64"
LOG_FILE="/var/log/plugins/supermicro-ipmi/ipmicfg_install.log"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

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

log "Starting IPMICFG installation..."

# Check if included IPMICFG binary exists
if [[ ! -f "$INCLUDED_IPMICFG" ]]; then
    error_exit "Included IPMICFG binary not found at $INCLUDED_IPMICFG"
fi

log "Found included IPMICFG binary: $INCLUDED_IPMICFG"

# Check if IPMICFG already exists
if [[ -f "$IPMICFG_PATH" ]]; then
    warning "IPMICFG already exists at $IPMICFG_PATH"
    read -p "Do you want to reinstall? (y/N): " -n 1 -r
    echo
    if [[ ! $REPLY =~ ^[Yy]$ ]]; then
        log "Installation cancelled by user"
        exit 0
    fi
    rm -f "$IPMICFG_PATH"
fi

# Create sbin directory if it doesn't exist
mkdir -p "$(dirname "$IPMICFG_PATH")"

# Copy to system path
log "Installing IPMICFG to system path..."
if ! cp "$INCLUDED_IPMICFG" "$IPMICFG_PATH"; then
    error_exit "Failed to copy IPMICFG to $IPMICFG_PATH"
fi

# Set proper permissions
log "Setting permissions..."
chmod 755 "$IPMICFG_PATH"
chown root:root "$IPMICFG_PATH"

# Test the installation
log "Testing IPMICFG installation..."
if "$IPMICFG_PATH" -s >/dev/null 2>&1; then
    success "IPMICFG installed and tested successfully"
else
    warning "IPMICFG installed but test failed (this may be normal if no BMC is present)"
    log "You can test manually by running: $IPMICFG_PATH -s"
fi

success "IPMICFG installation completed successfully"
log "Installation completed successfully"

echo -e "${GREEN}IPMICFG has been installed to: $IPMICFG_PATH${NC}"
echo -e "${GREEN}You can test it by running: $IPMICFG_PATH -s${NC}" 