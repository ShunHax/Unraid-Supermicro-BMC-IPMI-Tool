<?php
/*
 * Supermicro IPMI Plugin for Unraid
 * 
 * This plugin provides a web-based interface to manage Supermicro motherboards
 * with IPMI support using the IPMICFG utility.
 * 
 * Features:
 * - Local BMC management (no network required)
 * - Remote BMC management with authentication
 * - Power control (on/off/reset)
 * - Sensor monitoring
 * - System information
 * - User management
 * - Event log viewing
 */

$plugin = "supermicro-ipmi";
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// Add the plugin to the plugins page
$plugin_name = "Supermicro BMC/IPMI Tool";
$plugin_description = "Manage IPMI compatible Supermicro motherboards with the IPMICFG utility. ";

// Plugin version
$plugin_version = "1.0.0";

// Plugin author
$plugin_author = "ShunHax";

// Plugin support URL
$plugin_support = "https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool";

// Plugin icon
$plugin_icon = "/plugins/$plugin/images/icon.png";

// Plugin settings
$plugin_settings = "/plugins/$plugin/settings.php";

// Plugin page
$plugin_page = "/plugins/$plugin/supermicro-ipmi.php";

// Plugin configuration
$plugin_config = "/plugins/$plugin/config.php";

// Plugin log
$plugin_log = "/var/log/plugins/$plugin.log";

// Plugin temp directory
$plugin_temp = "/tmp/plugins/$plugin";

// Plugin cache directory
$plugin_cache = "/var/cache/plugins/$plugin";

// Plugin data directory
$plugin_data = "/var/local/plugins/$plugin";

// Plugin backup directory
$plugin_backup = "/mnt/user/appdata/plugins/$plugin/backup";

// Plugin configuration file
$plugin_config_file = "$plugin_data/config.json";

// Plugin default configuration
$plugin_default_config = [
    'local_bmc' => [
        'enabled' => true,
        'device' => '/dev/ipmi0',
        'ipmicfg_path' => '/usr/local/sbin/ipmicfg'
    ],
    'remote_bmc' => [
        'enabled' => false,
        'host' => '',
        'port' => 623,
        'username' => '',
        'password' => '',
        'privilege_level' => 'ADMINISTRATOR'
    ],
    'gui_settings' => [
        'refresh_interval' => 30,
        'auto_refresh' => true,
        'show_sensors' => true,
        'show_events' => true,
        'show_users' => true
    ],
    'security' => [
        'require_authentication' => true,
        'allowed_users' => ['root'],
        'session_timeout' => 3600
    ]
];

// Plugin initialization
function plugin_init() {
    global $plugin, $plugin_data, $plugin_default_config, $plugin_config_file;
    
    // Create plugin directories if they don't exist
    $dirs = [$plugin_data, dirname($plugin_config_file)];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    // Create default configuration if it doesn't exist
    if (!file_exists($plugin_config_file)) {
        file_put_contents($plugin_config_file, json_encode($plugin_default_config, JSON_PRETTY_PRINT));
    }
}

// Plugin installation
function plugin_install() {
    global $plugin, $plugin_data, $plugin_temp, $plugin_cache, $plugin_backup;
    
    // Create required directories
    $dirs = [$plugin_data, $plugin_temp, $plugin_cache, $plugin_backup];
    foreach ($dirs as $dir) {
        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }
    }
    
    // Install IPMICFG if not present
    install_ipmicfg();
    
    // Set up cron job for monitoring
    setup_cron_job();
    
    return true;
}

// Plugin uninstallation
function plugin_uninstall() {
    global $plugin, $plugin_data, $plugin_temp, $plugin_cache, $plugin_backup;
    
    // Remove cron job
    remove_cron_job();
    
    // Clean up directories (optional - keep data for reinstall)
    // rmdir_recursive($plugin_temp);
    // rmdir_recursive($plugin_cache);
    
    return true;
}

// Install IPMICFG utility
function install_ipmicfg() {
    $ipmicfg_path = '/usr/local/sbin/ipmicfg';
    $ipmicfg_url = 'https://www.supermicro.com/Bios/sw_download/897/IPMICFG_1.36.0_build.250225.zip';
    $temp_dir = '/tmp/ipmicfg_install';
    
    if (!file_exists($ipmicfg_path)) {
        // Create temp directory
        if (!is_dir($temp_dir)) {
            mkdir($temp_dir, 0755, true);
        }
        
        // Create sbin directory if it doesn't exist
        if (!is_dir(dirname($ipmicfg_path))) {
            mkdir(dirname($ipmicfg_path), 0755, true);
        }
        
        try {
            // Download IPMICFG
            $zip_file = "$temp_dir/ipmicfg.zip";
            $download_cmd = "wget -q -O '$zip_file' '$ipmicfg_url'";
            exec($download_cmd, $output, $return_code);
            
            if ($return_code !== 0) {
                throw new Exception("Failed to download IPMICFG from Supermicro");
            }
            
            // Extract the zip file
            $extract_cmd = "cd '$temp_dir' && unzip -q '$zip_file'";
            exec($extract_cmd, $output, $return_code);
            
            if ($return_code !== 0) {
                throw new Exception("Failed to extract IPMICFG archive");
            }
            
            // Find the ipmicfg binary in the extracted files
            $find_cmd = "find '$temp_dir' -name 'ipmicfg' -type f 2>/dev/null";
            $ipmicfg_binary = trim(shell_exec($find_cmd));
            
            if (empty($ipmicfg_binary) || !file_exists($ipmicfg_binary)) {
                throw new Exception("IPMICFG binary not found in downloaded archive");
            }
            
            // Copy to system path
            $copy_cmd = "cp '$ipmicfg_binary' '$ipmicfg_path'";
            exec($copy_cmd, $output, $return_code);
            
            if ($return_code !== 0) {
                throw new Exception("Failed to copy IPMICFG to system path");
            }
            
            // Set proper permissions
            chmod($ipmicfg_path, 0755);
            chown($ipmicfg_path, 'root');
            chgrp($ipmicfg_path, 'root');
            
            // Test the installation
            $test_cmd = "$ipmicfg_path -s 2>/dev/null";
            exec($test_cmd, $output, $return_code);
            
            if ($return_code !== 0) {
                // Installation succeeded but test failed (might be normal if no BMC)
                error_log("IPMICFG installed successfully but test failed (this may be normal if no BMC is present)");
            }
            
            // Clean up temp directory
            exec("rm -rf '$temp_dir'");
            
            error_log("IPMICFG successfully installed from Supermicro");
            
        } catch (Exception $e) {
            error_log("IPMICFG installation failed: " . $e->getMessage());
            
            // Create a fallback script with download instructions
            $script = "#!/bin/bash\n";
            $script .= "# IPMICFG utility not found\n";
            $script .= "# Automatic download failed. Please download manually:\n";
            $script .= "# URL: $ipmicfg_url\n";
            $script .= "# Then extract and copy the ipmicfg binary to this location\n";
            $script .= "echo 'IPMICFG not found. Please download from Supermicro website.'\n";
            $script .= "echo 'URL: $ipmicfg_url'\n";
            $script .= "exit 1\n";
            
            file_put_contents($ipmicfg_path, $script);
            chmod($ipmicfg_path, 0755);
        }
    }
}

// Set up cron job for monitoring
function setup_cron_job() {
    global $plugin;
    
    $cron_file = "/etc/cron.d/$plugin";
    $cron_job = "*/5 * * * * root /usr/local/emhttp/plugins/$plugin/scripts/monitor.sh >/dev/null 2>&1\n";
    
    file_put_contents($cron_file, $cron_job);
}

// Remove cron job
function remove_cron_job() {
    global $plugin;
    
    $cron_file = "/etc/cron.d/$plugin";
    if (file_exists($cron_file)) {
        unlink($cron_file);
    }
}

// Initialize plugin
plugin_init();

// Include required files
require_once "$docroot/plugins/$plugin/includes/functions.php";
require_once "$docroot/plugins/$plugin/includes/ipmi.php";
require_once "$docroot/plugins/$plugin/includes/gui.php";
?> 