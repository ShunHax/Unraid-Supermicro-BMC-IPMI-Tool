#!/bin/bash

# IPMICFG Installation Script for Unraid
# This script downloads and installs the IPMICFG utility from Supermicro

set -e

# Configuration
IPMICFG_URL="https://www.supermicro.com/Bios/sw_download/897/IPMICFG_1.36.0_build.250225.zip"
IPMICFG_PATH="/usr/local/sbin/ipmicfg"
TEMP_DIR="/tmp/ipmicfg_install"
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

# Create temp directory
log "Creating temporary directory..."
rm -rf "$TEMP_DIR"
mkdir -p "$TEMP_DIR"

# Download IPMICFG
log "Downloading IPMICFG from Supermicro..."
if ! wget -q -O "$TEMP_DIR/ipmicfg.zip" "$IPMICFG_URL"; then
    error_exit "Failed to download IPMICFG from $IPMICFG_URL"
fi

# Extract the zip file
log "Extracting IPMICFG archive..."
if ! cd "$TEMP_DIR" && unzip -q ipmicfg.zip; then
    error_exit "Failed to extract IPMICFG archive"
fi

# Find the ipmicfg binary
log "Locating IPMICFG binary..."
IPMICFG_BINARY=$(find "$TEMP_DIR" -name "ipmicfg" -type f 2>/dev/null | head -n 1)

if [[ -z "$IPMICFG_BINARY" ]] || [[ ! -f "$IPMICFG_BINARY" ]]; then
    error_exit "IPMICFG binary not found in downloaded archive"
fi

log "Found IPMICFG binary at: $IPMICFG_BINARY"

# Create sbin directory if it doesn't exist
mkdir -p "$(dirname "$IPMICFG_PATH")"

# Copy to system path
log "Installing IPMICFG to system path..."
if ! cp "$IPMICFG_BINARY" "$IPMICFG_PATH"; then
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

# Clean up
log "Cleaning up temporary files..."
rm -rf "$TEMP_DIR"

success "IPMICFG installation completed successfully"
log "Installation completed successfully"

echo -e "${GREEN}IPMICFG has been installed to: $IPMICFG_PATH${NC}"
echo -e "${GREEN}You can test it by running: $IPMICFG_PATH -s${NC}" 