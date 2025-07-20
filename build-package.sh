#!/bin/bash

# Build script for Supermicro IPMI Plugin Package
# This script creates a proper TXZ package for Unraid installation

set -e

# Configuration
PLUGIN_NAME="supermicro-ipmi"
VERSION="1.0.0"
PACKAGE_NAME="${PLUGIN_NAME}-${VERSION}"
BUILD_DIR="build"
PACKAGE_DIR="$BUILD_DIR/$PACKAGE_NAME"
FINAL_PACKAGE="${PACKAGE_NAME}.txz"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Error handling
error_exit() {
    echo -e "${RED}ERROR: $1${NC}"
    exit 1
}

# Success message
success() {
    echo -e "${GREEN}SUCCESS: $1${NC}"
}

# Warning message
warning() {
    echo -e "${YELLOW}WARNING: $1${NC}"
}

echo "Building Supermicro IPMI Plugin Package v$VERSION..."

# Clean previous build
if [ -d "$BUILD_DIR" ]; then
    echo "Cleaning previous build..."
    rm -rf "$BUILD_DIR"
fi

# Create build directory structure
echo "Creating package structure..."
mkdir -p "$PACKAGE_DIR"

# Copy plugin files to package directory
echo "Copying plugin files..."

# Main plugin files
cp -r package/usr "$PACKAGE_DIR/"

# Copy additional files from root if they exist
if [ -f "README.md" ]; then
    cp README.md "$PACKAGE_DIR/"
fi

if [ -f "LICENSE" ]; then
    cp LICENSE "$PACKAGE_DIR/"
fi

# Copy IPMICFG binary if it exists
if [ -d "ipmicfg" ]; then
    cp -r ipmicfg "$PACKAGE_DIR/"
    log "IPMICFG binary included in package"
fi

# Make scripts executable
find "$PACKAGE_DIR" -name "*.sh" -type f -exec chmod +x {} \;

# Create package manifest
echo "Creating package manifest..."
cat > "$PACKAGE_DIR/package.manifest" << EOF
Package: $PLUGIN_NAME
Version: $VERSION
Description: Manage IPMI compatible Supermicro motherboards with the IPMICFG utility
Author: ShunHax
Maintainer: ShunHax <shunhax@shunhax.com>
Homepage: https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool
License: MIT
Architecture: x86_64
Section: unraid-plugins
Priority: optional
Depends: unraid (>=6.8.0)
Conflicts: 
Replaces: 
Provides: $PLUGIN_NAME
EOF

# Create installation instructions
echo "Creating installation instructions..."
cat > "$PACKAGE_DIR/INSTALL.md" << 'EOF'
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
EOF

# Create the TXZ package
echo "Creating TXZ package..."
cd "$BUILD_DIR"
tar -cJf "../$FINAL_PACKAGE" "$PACKAGE_NAME"
cd ..

# Verify package
if [ -f "$FINAL_PACKAGE" ]; then
    success "Package created successfully: $FINAL_PACKAGE"
    echo "Package size: $(du -h "$FINAL_PACKAGE" | cut -f1)"
    echo "Package contents:"
    tar -tJf "$FINAL_PACKAGE" | head -20
    echo "..."
else
    error_exit "Failed to create package"
fi

# Clean up build directory
echo "Cleaning up build files..."
rm -rf "$BUILD_DIR"

success "Build completed successfully!"
echo "Package ready: $FINAL_PACKAGE"
echo ""
echo "To install on Unraid:"
echo "1. Upload $FINAL_PACKAGE to your Unraid server"
echo "2. SSH into Unraid and run: installpkg $FINAL_PACKAGE"
echo "3. Or install via Community Applications" 