# Community Applications Submission Guide

This guide explains how to submit your Supermicro BMC/IPMI Tool plugin to the official Community Applications repository.

## 🎯 Submission Options

### Option 1: Submit to Official CA (Recommended)

#### Step 1: Prepare Your Release
1. **Build the package**:
   ```cmd
   build-package.bat
   .\create-txz.ps1
   ```

2. **Create a GitHub Release**:
   - Go to your GitHub repository
   - Click "Releases" → "Create a new release"
   - Tag: `v1.0.0`
   - Title: `Supermicro BMC/IPMI Tool v1.0.0`
   - Upload the `supermicro-ipmi-1.0.0.txz` file
   - Publish the release

#### Step 2: Submit to CA Repository
1. **Go to CA Templates Repository**:
   - Visit: https://github.com/CommunityApplications/unraid-ca-templates

2. **Create a New Issue**:
   - Click "Issues" → "New Issue"
   - Select "Application Request" template
   - Fill out the required information:

   ```
   **Application Name:** Supermicro BMC/IPMI Tool
   
   **Application URL:** https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool
   
   **Application Category:** Tools:Utilities
   
   **Application Description:** 
   Manage IPMI compatible Supermicro motherboards with the IPMICFG utility. 
   This plugin provides a web-based interface to monitor and configure your 
   Supermicro BMC/IPMI settings directly from the Unraid web interface.
   
   **Application Features:**
   - Local and remote BMC management
   - Sensor monitoring (temperature, voltage, fans)
   - Power management (on/off/reset)
   - User administration
   - Event logging
   - Network configuration
   - Included IPMICFG binary (no internet required)
   
   **Requirements:** Unraid 6.8.0+, Supermicro motherboard with IPMI support
   
   **Download URL:** https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/releases/download/v1.0.0/supermicro-ipmi-1.0.0.txz
   
   **Icon URL:** https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/package/usr/local/emhttp/plugins/supermicro-ipmi/images/icon.png
   ```

3. **Submit the Issue** and wait for CA maintainer response

### Option 2: Use Your Own Repository (Current Setup)

Your current setup already works with CA! Users can install directly:

1. **In Unraid, go to Settings → Community Applications**
2. **Click the Settings tab**
3. **Under "Custom Repositories", add:**
   ```
   https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool
   ```
4. **Search for "Supermicro IPMI" and install**

### Option 3: Create Your Own CA Template Repository

You can create your own CA template repository for more control:

1. **Create a new repository** called `unraid-ca-templates`
2. **Add the template file** (`ca-template.xml`) to the repository
3. **Users can add your template repository** to CA

## 📋 CA Template Requirements

### Required Fields:
- `<Name>` - Plugin name
- `<Repository>` - Repository name
- `<Registry>` - GitHub repository URL
- `<Project>` - Project URL
- `<Overview>` - Description (max 500 characters)
- `<Category>` - Must be from CA category list
- `<Icon>` - Icon URL (must be accessible)
- `<MinOSVersion>` - Minimum Unraid version
- `<Requires>` - Package file name
- `<Support>` - Support URL
- `<Maintainer>` - Your name
- `<Email>` - Your email

### Optional Fields:
- `<WebUI>` - Web interface URL
- `<Changelog>` - Changelog URL
- `<Beta>` - Beta status
- `<Hidden>` - Hidden from search

## 🎨 Icon Requirements

Your icon must be:
- **Format**: PNG
- **Size**: 64x64 pixels
- **Accessible**: Must be publicly accessible via HTTPS
- **Location**: Should be in your repository

## 📝 Category Options

Choose from these CA categories:
- `Tools:Utilities` (recommended for your plugin)
- `Tools:System`
- `Tools:Monitoring`
- `Tools:Hardware`

## ⏱️ Timeline

- **CA Review**: Usually 1-3 days
- **Approval**: Depends on completeness and quality
- **Publication**: Within 24 hours after approval

## 🔧 Troubleshooting

### Common Issues:
1. **Icon not accessible**: Make sure icon URL is public
2. **Invalid category**: Use exact category name from CA list
3. **Missing requirements**: Include all required fields
4. **Broken download link**: Ensure TXZ file is uploaded to releases

### Tips for Success:
1. **Test your package** thoroughly before submission
2. **Provide clear documentation** in your GitHub repository
3. **Include screenshots** of the plugin interface
4. **Respond quickly** to any CA maintainer questions

## 🎉 After Approval

Once approved:
1. **Your plugin** will appear in Community Applications
2. **Users can install** directly from CA
3. **Updates** can be submitted via the same process
4. **Support** users through GitHub issues

## 📞 Support

If you need help with CA submission:
- **CA Discord**: https://discord.gg/unraid
- **CA Issues**: https://github.com/CommunityApplications/unraid-ca-templates/issues
- **CA Documentation**: https://github.com/CommunityApplications/unraid-ca-templates/wiki 