<?php
/*
 * Supermicro IPMI Plugin for Unraid
 * 
 * This plugin provides a web-based interface to manage Supermicro motherboards
 * with IPMI support using the IPMICFG utility.
 */

// Plugin information
$plugin_name = "Supermicro BMC/IPMI Tool";
$plugin_description = "Manage IPMI compatible Supermicro motherboards with the IPMICFG utility.";
$plugin_version = "1.0.0";
$plugin_author = "ShunHax";
$plugin_support = "https://github.com/ShunHax/Unraid-Supermicro-BMC-IPMI-Tool";

// Plugin paths
$plugin = "supermicro-ipmi";
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// Plugin directories
$plugin_data = "/var/local/plugins/$plugin";
$plugin_config_file = "$plugin_data/config.json";

// Default configuration
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
    ]
];

// Initialize plugin
function plugin_init() {
    global $plugin, $plugin_data, $plugin_default_config, $plugin_config_file;
    
    // Create plugin directories
    if (!is_dir($plugin_data)) {
        mkdir($plugin_data, 0755, true);
    }
    
    // Create default configuration if it doesn't exist
    if (!file_exists($plugin_config_file)) {
        file_put_contents($plugin_config_file, json_encode($plugin_default_config, JSON_PRETTY_PRINT));
    }
}

// Load configuration
function load_config() {
    global $plugin_default_config, $plugin_config_file;
    
    if (file_exists($plugin_config_file)) {
        $config = json_decode(file_get_contents($plugin_config_file), true);
        if ($config === null) {
            $config = $plugin_default_config;
            save_config($config);
        }
    } else {
        $config = $plugin_default_config;
        save_config($config);
    }
    
    return $config;
}

// Save configuration
function save_config($config) {
    global $plugin_config_file;
    return file_put_contents($plugin_config_file, json_encode($config, JSON_PRETTY_PRINT));
}

// Initialize the plugin
plugin_init();

// For Unraid 6.8+ plugins, we need to register the settings page
// This is done by creating a symlink or by the plugin system automatically
// The settings page should be accessible at /plugins/supermicro-ipmi/settings.php 