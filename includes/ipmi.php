<?php
/*
 * Supermicro IPMI Plugin - IPMI Interface
 * 
 * This file handles all communication with the BMC using the IPMICFG utility
 */

$plugin = "supermicro-ipmi";
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// Execute IPMICFG command
function execute_ipmicfg($command, $args = []) {
    global $plugin;
    
    $config = load_config();
    $ipmicfg_path = $config['local_bmc']['ipmicfg_path'];
    
    // Check if IPMICFG exists
    if (!file_exists($ipmicfg_path)) {
        return handle_error("IPMICFG not found at $ipmicfg_path. Please download from Supermicro website.");
    }
    
    // Build command
    $cmd = escapeshellcmd($ipmicfg_path) . ' ' . escapeshellarg($command);
    
    // Add arguments
    foreach ($args as $arg) {
        $cmd .= ' ' . escapeshellarg($arg);
    }
    
    // Add remote BMC parameters if enabled
    if ($config['remote_bmc']['enabled'] && !empty($config['remote_bmc']['host'])) {
        $cmd .= ' -h ' . escapeshellarg($config['remote_bmc']['host']);
        $cmd .= ' -p ' . escapeshellarg($config['remote_bmc']['port']);
        $cmd .= ' -u ' . escapeshellarg($config['remote_bmc']['username']);
        $cmd .= ' -pw ' . escapeshellarg($config['remote_bmc']['password']);
    }
    
    // Execute command
    $output = [];
    $return_var = 0;
    
    exec($cmd . ' 2>&1', $output, $return_var);
    
    log_message("IPMICFG command: $cmd", 'DEBUG');
    log_message("IPMICFG output: " . implode("\n", $output), 'DEBUG');
    
    return [
        'success' => $return_var === 0,
        'output' => $output,
        'return_var' => $return_var,
        'command' => $cmd
    ];
}

// Get BMC status
function get_bmc_status() {
    $result = execute_ipmicfg('-s');
    
    if (!$result['success']) {
        return [
            'connected' => false,
            'power' => false,
            'message' => implode("\n", $result['output'])
        ];
    }
    
    $output = implode("\n", $result['output']);
    
    // Parse power status
    $power_on = false;
    if (preg_match('/Power Status\s*:\s*(.+)/i', $output, $matches)) {
        $power_on = stripos($matches[1], 'on') !== false;
    }
    
    return [
        'connected' => true,
        'power' => $power_on,
        'message' => 'BMC connected successfully'
    ];
}

// Execute power action
function execute_power_action($action) {
    $valid_actions = ['on', 'off', 'reset', 'cycle'];
    
    if (!in_array($action, $valid_actions)) {
        return handle_error("Invalid power action: $action");
    }
    
    $ipmi_actions = [
        'on' => '-power on',
        'off' => '-power off',
        'reset' => '-power reset',
        'cycle' => '-power cycle'
    ];
    
    $result = execute_ipmicfg($ipmi_actions[$action]);
    
    if ($result['success']) {
        return handle_success("Power action '$action' executed successfully");
    } else {
        return handle_error("Failed to execute power action '$action': " . implode("\n", $result['output']));
    }
}

// Get system information
function get_system_info() {
    $info = [];
    
    // Get product information
    $result = execute_ipmicfg('-s');
    if ($result['success']) {
        $output = implode("\n", $result['output']);
        
        // Parse product name
        if (preg_match('/Product Name\s*:\s*(.+)/i', $output, $matches)) {
            $info['product_name'] = trim($matches[1]);
        }
        
        // Parse serial number
        if (preg_match('/Serial Number\s*:\s*(.+)/i', $output, $matches)) {
            $info['serial_number'] = trim($matches[1]);
        }
    }
    
    // Get BMC version
    $result = execute_ipmicfg('-v');
    if ($result['success']) {
        $output = implode("\n", $result['output']);
        if (preg_match('/BMC Version\s*:\s*(.+)/i', $output, $matches)) {
            $info['bmc_version'] = trim($matches[1]);
        }
    }
    
    // Get BIOS version
    $result = execute_ipmicfg('-b');
    if ($result['success']) {
        $output = implode("\n", $result['output']);
        if (preg_match('/BIOS Version\s*:\s*(.+)/i', $output, $matches)) {
            $info['bios_version'] = trim($matches[1]);
        }
    }
    
    return $info;
}

// Get sensors
function get_sensors() {
    $sensors = [];
    
    $result = execute_ipmicfg('-sensor');
    if (!$result['success']) {
        return $sensors;
    }
    
    $output = $result['output'];
    
    foreach ($output as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '---') === 0) {
            continue;
        }
        
        // Parse sensor line (format may vary by BMC version)
        if (preg_match('/^(.+?)\s+([0-9.]+)\s+([A-Z%]+)\s+(.+)$/i', $line, $matches)) {
            $sensor = [
                'name' => trim($matches[1]),
                'value' => trim($matches[2]),
                'unit' => trim($matches[3]),
                'status' => trim($matches[4])
            ];
            
            // Normalize status
            $status = strtolower($sensor['status']);
            if (in_array($status, ['ok', 'normal', 'good'])) {
                $sensor['status'] = 'OK';
            } elseif (in_array($status, ['warning', 'caution'])) {
                $sensor['status'] = 'Warning';
            } elseif (in_array($status, ['critical', 'error', 'failed'])) {
                $sensor['status'] = 'Critical';
            }
            
            $sensors[] = $sensor;
        }
    }
    
    return $sensors;
}

// Get event log
function get_events() {
    $events = [];
    
    $result = execute_ipmicfg('-sel');
    if (!$result['success']) {
        return $events;
    }
    
    $output = $result['output'];
    
    foreach ($output as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '---') === 0) {
            continue;
        }
        
        // Parse event log line
        if (preg_match('/^(\d+)\s+(\d{4}-\d{2}-\d{2}\s+\d{2}:\d{2}:\d{2})\s+(.+?)\s+(.+)$/i', $line, $matches)) {
            $event = [
                'id' => $matches[1],
                'timestamp' => $matches[2],
                'level' => trim($matches[3]),
                'message' => trim($matches[4])
            ];
            
            // Normalize level
            $level = strtolower($event['level']);
            if (in_array($level, ['info', 'information'])) {
                $event['level'] = 'Info';
            } elseif (in_array($level, ['warning', 'caution'])) {
                $event['level'] = 'Warning';
            } elseif (in_array($level, ['critical', 'error', 'fatal'])) {
                $event['level'] = 'Critical';
            }
            
            $events[] = $event;
        }
    }
    
    // Sort by timestamp (newest first)
    usort($events, function($a, $b) {
        return strtotime($b['timestamp']) - strtotime($a['timestamp']);
    });
    
    return array_slice($events, 0, 100); // Limit to 100 most recent events
}

// Get users
function get_users() {
    $users = [];
    
    $result = execute_ipmicfg('-user list');
    if (!$result['success']) {
        return $users;
    }
    
    $output = $result['output'];
    
    foreach ($output as $line) {
        $line = trim($line);
        if (empty($line) || strpos($line, '---') === 0) {
            continue;
        }
        
        // Parse user line
        if (preg_match('/^(\d+)\s+(.+?)\s+(.+?)\s+(.+)$/i', $line, $matches)) {
            $user = [
                'id' => $matches[1],
                'username' => trim($matches[2]),
                'privilege' => trim($matches[3]),
                'enabled' => stripos($matches[4], 'enabled') !== false
            ];
            
            $users[] = $user;
        }
    }
    
    return $users;
}

// Add IPMI user
function add_ipmi_user($username, $password, $privilege) {
    if (empty($username) || empty($password)) {
        return handle_error("Username and password are required");
    }
    
    // Find next available user ID
    $users = get_users();
    $next_id = 2; // Start from 2 (1 is usually admin)
    foreach ($users as $user) {
        if ($user['id'] >= $next_id) {
            $next_id = $user['id'] + 1;
        }
    }
    
    // Add user
    $result = execute_ipmicfg('-user add', [$next_id, $username, $password]);
    if (!$result['success']) {
        return handle_error("Failed to add user: " . implode("\n", $result['output']));
    }
    
    // Set privilege level
    $privilege_map = [
        'USER' => 1,
        'OPERATOR' => 2,
        'ADMINISTRATOR' => 3,
        'ADMIN' => 3
    ];
    
    $priv_level = $privilege_map[strtoupper($privilege)] ?? 1;
    $result = execute_ipmicfg('-user priv', [$next_id, $priv_level]);
    if (!$result['success']) {
        return handle_error("Failed to set user privilege: " . implode("\n", $result['output']));
    }
    
    // Enable user
    $result = execute_ipmicfg('-user enable', [$next_id]);
    if (!$result['success']) {
        return handle_error("Failed to enable user: " . implode("\n", $result['output']));
    }
    
    return handle_success("User '$username' added successfully with ID $next_id");
}

// Edit IPMI user
function edit_ipmi_user($user_id, $username, $privilege, $password = '') {
    if (empty($user_id) || empty($username)) {
        return handle_error("User ID and username are required");
    }
    
    // Update username
    $result = execute_ipmicfg('-user name', [$user_id, $username]);
    if (!$result['success']) {
        return handle_error("Failed to update username: " . implode("\n", $result['output']));
    }
    
    // Update password if provided
    if (!empty($password)) {
        $result = execute_ipmicfg('-user setpwd', [$user_id, $password]);
        if (!$result['success']) {
            return handle_error("Failed to update password: " . implode("\n", $result['output']));
        }
    }
    
    // Update privilege level
    $privilege_map = [
        'USER' => 1,
        'OPERATOR' => 2,
        'ADMINISTRATOR' => 3,
        'ADMIN' => 3
    ];
    
    $priv_level = $privilege_map[strtoupper($privilege)] ?? 1;
    $result = execute_ipmicfg('-user priv', [$user_id, $priv_level]);
    if (!$result['success']) {
        return handle_error("Failed to update privilege: " . implode("\n", $result['output']));
    }
    
    return handle_success("User updated successfully");
}

// Delete IPMI user
function delete_ipmi_user($user_id) {
    if (empty($user_id)) {
        return handle_error("User ID is required");
    }
    
    // Disable user first
    $result = execute_ipmicfg('-user disable', [$user_id]);
    if (!$result['success']) {
        return handle_error("Failed to disable user: " . implode("\n", $result['output']));
    }
    
    // Delete user
    $result = execute_ipmicfg('-user del', [$user_id]);
    if (!$result['success']) {
        return handle_error("Failed to delete user: " . implode("\n", $result['output']));
    }
    
    return handle_success("User deleted successfully");
}

// Clear event log
function clear_event_log() {
    $result = execute_ipmicfg('-sel clear');
    
    if ($result['success']) {
        return handle_success("Event log cleared successfully");
    } else {
        return handle_error("Failed to clear event log: " . implode("\n", $result['output']));
    }
}

// Get network configuration
function get_network_config() {
    $config = [];
    
    $result = execute_ipmicfg('-lan print');
    if (!$result['success']) {
        return $config;
    }
    
    $output = implode("\n", $result['output']);
    
    // Parse network settings
    if (preg_match('/IP Address\s*:\s*(.+)/i', $output, $matches)) {
        $config['ip_address'] = trim($matches[1]);
    }
    
    if (preg_match('/Subnet Mask\s*:\s*(.+)/i', $output, $matches)) {
        $config['subnet_mask'] = trim($matches[1]);
    }
    
    if (preg_match('/Gateway\s*:\s*(.+)/i', $output, $matches)) {
        $config['gateway'] = trim($matches[1]);
    }
    
    if (preg_match('/MAC Address\s*:\s*(.+)/i', $output, $matches)) {
        $config['mac_address'] = trim($matches[1]);
    }
    
    return $config;
}

// Set network configuration
function set_network_config($ip_address, $subnet_mask, $gateway) {
    if (!validate_ip($ip_address)) {
        return handle_error("Invalid IP address: $ip_address");
    }
    
    if (!validate_ip($subnet_mask)) {
        return handle_error("Invalid subnet mask: $subnet_mask");
    }
    
    if (!validate_ip($gateway)) {
        return handle_error("Invalid gateway: $gateway");
    }
    
    // Set IP address
    $result = execute_ipmicfg('-lan set', ['ipaddr', $ip_address]);
    if (!$result['success']) {
        return handle_error("Failed to set IP address: " . implode("\n", $result['output']));
    }
    
    // Set subnet mask
    $result = execute_ipmicfg('-lan set', ['netmask', $subnet_mask]);
    if (!$result['success']) {
        return handle_error("Failed to set subnet mask: " . implode("\n", $result['output']));
    }
    
    // Set gateway
    $result = execute_ipmicfg('-lan set', ['defgw', $gateway]);
    if (!$result['success']) {
        return handle_error("Failed to set gateway: " . implode("\n", $result['output']));
    }
    
    return handle_success("Network configuration updated successfully");
}

// Test BMC connection
function test_bmc_connection() {
    $result = execute_ipmicfg('-s');
    
    if ($result['success']) {
        return handle_success("BMC connection test successful");
    } else {
        return handle_error("BMC connection test failed: " . implode("\n", $result['output']));
    }
}

// Get BMC health status
function get_bmc_health() {
    $health = [
        'overall' => 'Unknown',
        'details' => []
    ];
    
    // Check sensors
    $sensors = get_sensors();
    $critical_count = 0;
    $warning_count = 0;
    
    foreach ($sensors as $sensor) {
        $status = strtolower($sensor['status']);
        if ($status === 'critical') {
            $critical_count++;
            $health['details'][] = "Critical: " . $sensor['name'] . " - " . $sensor['value'] . $sensor['unit'];
        } elseif ($status === 'warning') {
            $warning_count++;
            $health['details'][] = "Warning: " . $sensor['name'] . " - " . $sensor['value'] . $sensor['unit'];
        }
    }
    
    // Determine overall health
    if ($critical_count > 0) {
        $health['overall'] = 'Critical';
    } elseif ($warning_count > 0) {
        $health['overall'] = 'Warning';
    } else {
        $health['overall'] = 'Good';
    }
    
    return $health;
}
?> 