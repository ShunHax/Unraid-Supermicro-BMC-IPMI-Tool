# Manual Installation Guide

If the `.plg` files are not working, you can install the plugin manually.

## Method 1: Direct File Copy

### Step 1: SSH into your Unraid server
```bash
ssh root@your-unraid-server-ip
```

### Step 2: Create plugin directory
```bash
mkdir -p /usr/local/emhttp/plugins/supermicro-ipmi
```

### Step 3: Download plugin files
```bash
cd /tmp
wget https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/archive/main.zip
unzip main.zip
```

### Step 4: Copy files to plugin directory
```bash
cp -r Unraid-Supermicro-BMC-IPMI-Tool-main/* /usr/local/emhttp/plugins/supermicro-ipmi/
```

### Step 5: Set permissions
```bash
chmod +x /usr/local/emhttp/plugins/supermicro-ipmi/scripts/*.sh
chown -R root:root /usr/local/emhttp/plugins/supermicro-ipmi
```

### Step 6: Restart web interface
```bash
/etc/rc.d/rc.nginx restart
```

## Method 2: Git Clone

### Step 1: SSH into your Unraid server
```bash
ssh root@your-unraid-server-ip
```

### Step 2: Clone the repository
```bash
cd /usr/local/emhttp/plugins
git clone https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool.git supermicro-ipmi
```

### Step 3: Set permissions
```bash
chmod +x /usr/local/emhttp/plugins/supermicro-ipmi/scripts/*.sh
chown -R root:root /usr/local/emhttp/plugins/supermicro-ipmi
```

### Step 4: Restart web interface
```bash
/etc/rc.d/rc.nginx restart
```

## Method 3: Individual File Download

### Step 1: SSH into your Unraid server
```bash
ssh root@your-unraid-server-ip
```

### Step 2: Create directory structure
```bash
mkdir -p /usr/local/emhttp/plugins/supermicro-ipmi/{includes,scripts,css,js,images}
```

### Step 3: Download individual files
```bash
cd /usr/local/emhttp/plugins/supermicro-ipmi

# Main files
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/plugin.php
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/supermicro-ipmi.php
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/settings.php

# Includes
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/includes/functions.php -O includes/functions.php
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/includes/ipmi.php -O includes/ipmi.php
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/includes/gui.php -O includes/gui.php

# Scripts
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/scripts/monitor.sh -O scripts/monitor.sh
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/scripts/install_ipmicfg.sh -O scripts/install_ipmicfg.sh

# Assets
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/css/style.css -O css/style.css
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/js/script.js -O js/script.js
wget https://raw.githubusercontent.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool/main/images/icon.png -O images/icon.png
```

### Step 4: Set permissions
```bash
chmod +x scripts/*.sh
chown -R root:root /usr/local/emhttp/plugins/supermicro-ipmi
```

### Step 5: Restart web interface
```bash
/etc/rc.d/rc.nginx restart
```

## Verification

After installation, you should see:

1. **Plugin appears** in the Unraid web interface
2. **"Supermicro IPMI" tab** in the main navigation
3. **Plugin settings** accessible from the Settings page

## Troubleshooting

### Plugin doesn't appear
```bash
# Check if files exist
ls -la /usr/local/emhttp/plugins/supermicro-ipmi/

# Check permissions
ls -la /usr/local/emhttp/plugins/supermicro-ipmi/scripts/

# Check logs
tail -f /var/log/plugins/supermicro-ipmi.log
```

### IPMICFG not found
```bash
# Run the manual installer
/usr/local/emhttp/plugins/supermicro-ipmi/scripts/install_ipmicfg.sh
```

### Permission issues
```bash
# Fix permissions
chmod +x /usr/local/emhttp/plugins/supermicro-ipmi/scripts/*.sh
chown -R root:root /usr/local/emhttp/plugins/supermicro-ipmi
```

## Next Steps

Once the plugin is installed:

1. **Access the plugin** from the Unraid web interface
2. **Configure settings** in the plugin's settings page
3. **Test IPMICFG** by running: `/usr/local/sbin/ipmicfg -s`
4. **Check BMC connectivity** through the plugin interface

The manual installation bypasses any `.plg` file issues and gives you direct control over the installation process. 