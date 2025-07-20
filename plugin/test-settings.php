<?php
/*
 * Test file to verify settings page accessibility
 */

echo "<h1>Supermicro IPMI Plugin - Settings Test</h1>";
echo "<p>If you can see this, the plugin is working!</p>";

// Test if we can load the configuration
$plugin = "supermicro-ipmi";
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';
$plugin_data = "/var/local/plugins/$plugin";
$plugin_config_file = "$plugin_data/config.json";

echo "<h2>Configuration Test</h2>";
echo "<p>Plugin data directory: $plugin_data</p>";
echo "<p>Config file: $plugin_config_file</p>";

if (is_dir($plugin_data)) {
    echo "<p style='color: green;'>✓ Plugin data directory exists</p>";
} else {
    echo "<p style='color: red;'>✗ Plugin data directory missing</p>";
}

if (file_exists($plugin_config_file)) {
    echo "<p style='color: green;'>✓ Configuration file exists</p>";
    $config = json_decode(file_get_contents($plugin_config_file), true);
    if ($config) {
        echo "<p style='color: green;'>✓ Configuration is valid JSON</p>";
        echo "<pre>" . print_r($config, true) . "</pre>";
    } else {
        echo "<p style='color: red;'>✗ Configuration is not valid JSON</p>";
    }
} else {
    echo "<p style='color: red;'>✗ Configuration file missing</p>";
}

echo "<h2>Plugin Files Test</h2>";
$plugin_dir = "$docroot/plugins/$plugin";
echo "<p>Plugin directory: $plugin_dir</p>";

if (is_dir($plugin_dir)) {
    echo "<p style='color: green;'>✓ Plugin directory exists</p>";
    $files = scandir($plugin_dir);
    echo "<p>Files in plugin directory:</p>";
    echo "<ul>";
    foreach ($files as $file) {
        if ($file != '.' && $file != '..') {
            echo "<li>$file</li>";
        }
    }
    echo "</ul>";
} else {
    echo "<p style='color: red;'>✗ Plugin directory missing</p>";
}

echo "<h2>Links</h2>";
echo "<p><a href='/plugins/$plugin/page.php'>Main Plugin Page</a></p>";
echo "<p><a href='/plugins/$plugin/settings.php'>Settings Page</a></p>";
echo "<p><a href='/plugins/$plugin/test.php'>Test Page</a></p>";
?> 