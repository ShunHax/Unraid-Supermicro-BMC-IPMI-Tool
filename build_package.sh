#!/bin/bash

# Build script for Supermicro IPMI Plugin Package
# This script creates the .txz package and MD5 hash for Unraid plugin distribution

set -e

# Configuration
PLUGIN_NAME="supermicro-ipmi"
VERSION="1.0.0"
ARCH="x86_64"
PACKAGE_NAME="${PLUGIN_NAME}-${VERSION}-${ARCH}-1"
BUILD_DIR="/tmp/${PACKAGE_NAME}"
INSTALL_DIR="${BUILD_DIR}/usr/local/emhttp/plugins/${PLUGIN_NAME}"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

echo -e "${GREEN}Building Supermicro IPMI Plugin Package${NC}"
echo "Package: ${PACKAGE_NAME}"
echo "Build directory: ${BUILD_DIR}"
echo ""

# Clean previous build
if [ -d "${BUILD_DIR}" ]; then
    echo "Cleaning previous build..."
    rm -rf "${BUILD_DIR}"
fi

# Create build directory structure
echo "Creating build directory structure..."
mkdir -p "${INSTALL_DIR}"

# Copy plugin files
echo "Copying plugin files..."
cp -r plugin.php "${INSTALL_DIR}/"
cp -r supermicro-ipmi.php "${INSTALL_DIR}/"
cp -r settings.php "${INSTALL_DIR}/"
cp -r includes/ "${INSTALL_DIR}/"
cp -r scripts/ "${INSTALL_DIR}/"
cp -r css/ "${INSTALL_DIR}/"
cp -r js/ "${INSTALL_DIR}/"
cp -r images/ "${INSTALL_DIR}/"

# Set proper permissions
echo "Setting permissions..."
chmod +x "${INSTALL_DIR}/scripts/monitor.sh"
chmod +x "${INSTALL_DIR}/scripts/install_ipmicfg.sh"
chown -R root:root "${BUILD_DIR}"

# Create package
echo "Creating package..."
cd /tmp
makepkg "${PACKAGE_NAME}.txz"

# Generate MD5 hash
echo "Generating MD5 hash..."
md5sum "${PACKAGE_NAME}.txz" > "${PACKAGE_NAME}.md5"

# Move files to current directory
echo "Moving package files..."
mv "${PACKAGE_NAME}.txz" ./
mv "${PACKAGE_NAME}.md5" ./

# Clean up build directory
echo "Cleaning up..."
rm -rf "${BUILD_DIR}"

echo ""
echo -e "${GREEN}Package build completed successfully!${NC}"
echo "Files created:"
echo "  - ${PACKAGE_NAME}.txz"
echo "  - ${PACKAGE_NAME}.md5"
echo ""
echo "You can now distribute these files with your plugin."
echo "Make sure to update the MD5 hash in your .plg file." 