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
    
    if (!file_exists($ipmicfg_path)) {
        // Download IPMICFG from Supermicro (this would need to be done manually)
        // For now, we'll just create a placeholder
        if (!is_dir(dirname($ipmicfg_path))) {
            mkdir(dirname($ipmicfg_path), 0755, true);
        }
        
        // Create a script that checks for IPMICFG and provides instructions
        $script = "#!/bin/bash\n";
        $script .= "# IPMICFG utility placeholder\n";
        $script .= "# Please download IPMICFG from Supermicro and place it here\n";
        $script .= "# Download from: https://www.supermicro.com/support/faqs/faq.cfm?faq=16428\n";
        $script .= "echo 'IPMICFG not found. Please download from Supermicro website.'\n";
        $script .= "exit 1\n";
        
        file_put_contents($ipmicfg_path, $script);
        chmod($ipmicfg_path, 0755);
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