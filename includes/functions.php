<?php
/*
 * Supermicro IPMI Plugin - Core Functions
 */

$plugin = "supermicro-ipmi";
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// Load configuration from JSON file
function load_config() {
    global $plugin_data, $plugin_default_config, $plugin_config_file;
    
    if (file_exists($plugin_config_file)) {
        $config = json_decode(file_get_contents($plugin_config_file), true);
        if ($config === null) {
            // Invalid JSON, use default config
            $config = $plugin_default_config;
            save_config($config);
        }
    } else {
        $config = $plugin_default_config;
        save_config($config);
    }
    
    return $config;
}

// Save configuration to JSON file
function save_config($config) {
    global $plugin_config_file;
    
    return file_put_contents($plugin_config_file, json_encode($config, JSON_PRETTY_PRINT));
}

// Handle form submissions
function handle_form_submission() {
    global $plugin;
    
    if (!isset($_POST['action'])) {
        return;
    }
    
    switch ($_POST['action']) {
        case 'save_settings':
            handle_save_settings();
            break;
        case 'power_action':
            handle_power_action();
            break;
        case 'add_user':
            handle_add_user();
            break;
        case 'edit_user':
            handle_edit_user();
            break;
        case 'delete_user':
            handle_delete_user();
            break;
        case 'clear_events':
            handle_clear_events();
            break;
    }
}

// Handle settings save
function handle_save_settings() {
    global $plugin;
    
    $config = load_config();
    
    // Update local BMC settings
    if (isset($_POST['local_bmc'])) {
        $config['local_bmc'] = array_merge($config['local_bmc'], $_POST['local_bmc']);
        $config['local_bmc']['enabled'] = isset($_POST['local_bmc']['enabled']);
    }
    
    // Update remote BMC settings
    if (isset($_POST['remote_bmc'])) {
        $config['remote_bmc'] = array_merge($config['remote_bmc'], $_POST['remote_bmc']);
        $config['remote_bmc']['enabled'] = isset($_POST['remote_bmc']['enabled']);
    }
    
    // Update GUI settings
    if (isset($_POST['gui_settings'])) {
        $config['gui_settings'] = array_merge($config['gui_settings'], $_POST['gui_settings']);
        $config['gui_settings']['auto_refresh'] = isset($_POST['gui_settings']['auto_refresh']);
        $config['gui_settings']['show_sensors'] = isset($_POST['gui_settings']['show_sensors']);
        $config['gui_settings']['show_events'] = isset($_POST['gui_settings']['show_events']);
        $config['gui_settings']['show_users'] = isset($_POST['gui_settings']['show_users']);
    }
    
    save_config($config);
    
    // Redirect to prevent form resubmission
    header("Location: /plugins/$plugin/supermicro-ipmi.php?message=Settings saved successfully");
    exit;
}

// Handle power actions
function handle_power_action() {
    global $plugin;
    
    if (!isset($_POST['power_action'])) {
        return;
    }
    
    $action = $_POST['power_action'];
    $result = execute_power_action($action);
    
    // Return JSON response for AJAX requests
    if (isset($_POST['ajax'])) {
        header('Content-Type: application/json');
        echo json_encode(['success' => $result['success'], 'message' => $result['message']]);
        exit;
    }
    
    // Redirect for regular form submissions
    $message = $result['success'] ? "Power action '$action' executed successfully" : "Error: " . $result['message'];
    header("Location: /plugins/$plugin/supermicro-ipmi.php?message=" . urlencode($message));
    exit;
}

// Handle user management
function handle_add_user() {
    global $plugin;
    
    if (!isset($_POST['username']) || !isset($_POST['password']) || !isset($_POST['privilege'])) {
        return;
    }
    
    $result = add_ipmi_user($_POST['username'], $_POST['password'], $_POST['privilege']);
    
    $message = $result['success'] ? "User added successfully" : "Error: " . $result['message'];
    header("Location: /plugins/$plugin/supermicro-ipmi.php?message=" . urlencode($message));
    exit;
}

function handle_edit_user() {
    global $plugin;
    
    if (!isset($_POST['user_id']) || !isset($_POST['username']) || !isset($_POST['privilege'])) {
        return;
    }
    
    $result = edit_ipmi_user($_POST['user_id'], $_POST['username'], $_POST['privilege'], $_POST['password'] ?? '');
    
    $message = $result['success'] ? "User updated successfully" : "Error: " . $result['message'];
    header("Location: /plugins/$plugin/supermicro-ipmi.php?message=" . urlencode($message));
    exit;
}

function handle_delete_user() {
    global $plugin;
    
    if (!isset($_POST['user_id'])) {
        return;
    }
    
    $result = delete_ipmi_user($_POST['user_id']);
    
    $message = $result['success'] ? "User deleted successfully" : "Error: " . $result['message'];
    header("Location: /plugins/$plugin/supermicro-ipmi.php?message=" . urlencode($message));
    exit;
}

// Handle event log clearing
function handle_clear_events() {
    global $plugin;
    
    $result = clear_event_log();
    
    $message = $result['success'] ? "Event log cleared successfully" : "Error: " . $result['message'];
    header("Location: /plugins/$plugin/supermicro-ipmi.php?message=" . urlencode($message));
    exit;
}

// Utility functions
function log_message($message, $level = 'INFO') {
    global $plugin_log;
    
    $timestamp = date('Y-m-d H:i:s');
    $log_entry = "[$timestamp] [$level] $message" . PHP_EOL;
    
    file_put_contents($plugin_log, $log_entry, FILE_APPEND | LOCK_EX);
}

function sanitize_input($input) {
    return htmlspecialchars(trim($input), ENT_QUOTES, 'UTF-8');
}

function validate_ip($ip) {
    return filter_var($ip, FILTER_VALIDATE_IP);
}

function validate_port($port) {
    return is_numeric($port) && $port >= 1 && $port <= 65535;
}

function get_sensor_status_class($sensor) {
    switch (strtolower($sensor['status'])) {
        case 'ok':
        case 'normal':
            return 'status-ok';
        case 'warning':
        case 'caution':
            return 'status-warning';
        case 'critical':
        case 'error':
        case 'failed':
            return 'status-error';
        default:
            return 'status-unknown';
    }
}

function get_event_level_class($level) {
    switch (strtolower($level)) {
        case 'info':
        case 'information':
            return 'level-info';
        case 'warning':
        case 'caution':
            return 'level-warning';
        case 'critical':
        case 'error':
        case 'fatal':
            return 'level-error';
        default:
            return 'level-unknown';
    }
}

function format_bytes($bytes, $precision = 2) {
    $units = array('B', 'KB', 'MB', 'GB', 'TB');
    
    for ($i = 0; $bytes > 1024 && $i < count($units) - 1; $i++) {
        $bytes /= 1024;
    }
    
    return round($bytes, $precision) . ' ' . $units[$i];
}

function format_temperature($celsius) {
    return round($celsius, 1) . '°C';
}

function format_percentage($value) {
    return round($value, 1) . '%';
}

function format_voltage($volts) {
    return round($volts, 2) . 'V';
}

function format_frequency($hz) {
    if ($hz >= 1000000000) {
        return round($hz / 1000000000, 2) . ' GHz';
    } elseif ($hz >= 1000000) {
        return round($hz / 1000000, 2) . ' MHz';
    } elseif ($hz >= 1000) {
        return round($hz / 1000, 2) . ' kHz';
    } else {
        return $hz . ' Hz';
    }
}

function format_speed($rpm) {
    return number_format($rpm) . ' RPM';
}

function is_ajax_request() {
    return isset($_SERVER['HTTP_X_REQUESTED_WITH']) && 
           strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
}

function send_json_response($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
    exit;
}

function redirect_with_message($url, $message, $type = 'success') {
    $params = ['message' => $message];
    if ($type !== 'success') {
        $params['type'] = $type;
    }
    
    $query_string = http_build_query($params);
    header("Location: $url?$query_string");
    exit;
}

// Error handling
function handle_error($error, $context = '') {
    log_message("Error: $error" . ($context ? " (Context: $context)" : ''), 'ERROR');
    
    if (is_ajax_request()) {
        send_json_response(['success' => false, 'message' => $error]);
    }
    
    return ['success' => false, 'message' => $error];
}

function handle_success($message, $data = []) {
    log_message("Success: $message", 'INFO');
    
    if (is_ajax_request()) {
        send_json_response(array_merge(['success' => true, 'message' => $message], $data));
    }
    
    return ['success' => true, 'message' => $message, 'data' => $data];
}
?> 