<?php
/*
 * Simple test file for Supermicro IPMI Plugin
 */

$plugin = "supermicro-ipmi";
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

echo "<h1>Supermicro IPMI Plugin Test</h1>";

// Test if plugin directory exists
$plugin_dir = "$docroot/plugins/$plugin";
if (is_dir($plugin_dir)) {
    echo "<p>✓ Plugin directory exists: $plugin_dir</p>";
} else {
    echo "<p>✗ Plugin directory missing: $plugin_dir</p>";
}

// Test if plugin.php exists
$plugin_file = "$plugin_dir/plugin.php";
if (file_exists($plugin_file)) {
    echo "<p>✓ Plugin file exists: $plugin_file</p>";
} else {
    echo "<p>✗ Plugin file missing: $plugin_file</p>";
}

// Test if page.php exists
$page_file = "$plugin_dir/page.php";
if (file_exists($page_file)) {
    echo "<p>✓ Page file exists: $page_file</p>";
} else {
    echo "<p>✗ Page file missing: $page_file</p>";
}

// Test if includes directory exists
$includes_dir = "$plugin_dir/includes";
if (is_dir($includes_dir)) {
    echo "<p>✓ Includes directory exists: $includes_dir</p>";
} else {
    echo "<p>✗ Includes directory missing: $includes_dir</p>";
}

// Test if functions.php exists
$functions_file = "$includes_dir/functions.php";
if (file_exists($functions_file)) {
    echo "<p>✓ Functions file exists: $functions_file</p>";
} else {
    echo "<p>✗ Functions file missing: $functions_file</p>";
}

// Test if ipmi.php exists
$ipmi_file = "$includes_dir/ipmi.php";
if (file_exists($ipmi_file)) {
    echo "<p>✓ IPMI file exists: $ipmi_file</p>";
} else {
    echo "<p>✗ IPMI file missing: $ipmi_file</p>";
}

// Test if gui.php exists
$gui_file = "$includes_dir/gui.php";
if (file_exists($gui_file)) {
    echo "<p>✓ GUI file exists: $gui_file</p>";
} else {
    echo "<p>✗ GUI file missing: $gui_file</p>";
}

// Test if CSS directory exists
$css_dir = "$plugin_dir/css";
if (is_dir($css_dir)) {
    echo "<p>✓ CSS directory exists: $css_dir</p>";
} else {
    echo "<p>✗ CSS directory missing: $css_dir</p>";
}

// Test if JS directory exists
$js_dir = "$plugin_dir/js";
if (is_dir($js_dir)) {
    echo "<p>✓ JS directory exists: $js_dir</p>";
} else {
    echo "<p>✗ JS directory missing: $js_dir</p>";
}

// Test if images directory exists
$images_dir = "$plugin_dir/images";
if (is_dir($images_dir)) {
    echo "<p>✓ Images directory exists: $images_dir</p>";
} else {
    echo "<p>✗ Images directory missing: $images_dir</p>";
}

// Test if scripts directory exists
$scripts_dir = "$plugin_dir/scripts";
if (is_dir($scripts_dir)) {
    echo "<p>✓ Scripts directory exists: $scripts_dir</p>";
} else {
    echo "<p>✗ Scripts directory missing: $scripts_dir</p>";
}

echo "<h2>Plugin Status</h2>";
echo "<p>If all tests show ✓, the plugin should be working correctly.</p>";
echo "<p>You can access the plugin at: <a href='/plugins/$plugin/page.php'>/plugins/$plugin/page.php</a></p>";
?> 