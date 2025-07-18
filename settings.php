<?php
/*
 * Supermicro IPMI Plugin - Settings Page
 */

$plugin = "supermicro-ipmi";
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// Include required files
require_once "$docroot/plugins/$plugin/includes/functions.php";
require_once "$docroot/plugins/$plugin/includes/ipmi.php";

// Load configuration
$config = load_config();

// Handle form submissions
if ($_POST) {
    handle_form_submission();
}

// Test BMC connection
$test_result = null;
if (isset($_GET['test_connection'])) {
    $test_result = test_bmc_connection();
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermicro BMC/IPMI Tool Settings</title>
    <link rel="stylesheet" href="/plugins/<?php echo $plugin; ?>/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-cog"></i>
                    <h1>Supermicro BMC/IPMI Tool Settings</h1>
                </div>
                <div class="header-actions">
                    <a href="/plugins/<?php echo $plugin; ?>/supermicro-ipmi.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Dashboard
                    </a>
                </div>
            </div>
        </header>

        <!-- Settings Form -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-cog"></i> Plugin Configuration</h2>
            </div>
            <div class="card-body">
                <form method="post" id="settingsForm">
                    <input type="hidden" name="action" value="save_settings">
                    
                    <!-- Local BMC Settings -->
                    <div class="settings-section">
                        <h3><i class="fas fa-server"></i> Local BMC Configuration</h3>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="local_bmc_enabled" name="local_bmc[enabled]" <?php echo $config['local_bmc']['enabled'] ? 'checked' : ''; ?>>
                                <label for="local_bmc_enabled">Enable local BMC management</label>
                            </div>
                            <small class="form-text">Enable direct communication with the local BMC without network connectivity</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="ipmicfg_path">IPMICFG Path</label>
                            <input type="text" id="ipmicfg_path" name="local_bmc[ipmicfg_path]" value="<?php echo htmlspecialchars($config['local_bmc']['ipmicfg_path']); ?>" placeholder="/usr/local/sbin/ipmicfg">
                            <small class="form-text">Path to the IPMICFG utility from Supermicro</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="bmc_device">BMC Device</label>
                            <input type="text" id="bmc_device" name="local_bmc[device]" value="<?php echo htmlspecialchars($config['local_bmc']['device']); ?>" placeholder="/dev/ipmi0">
                            <small class="form-text">IPMI device path (usually /dev/ipmi0)</small>
                        </div>
                    </div>
                    
                    <!-- Remote BMC Settings -->
                    <div class="settings-section">
                        <h3><i class="fas fa-network-wired"></i> Remote BMC Configuration</h3>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="remote_bmc_enabled" name="remote_bmc[enabled]" <?php echo $config['remote_bmc']['enabled'] ? 'checked' : ''; ?>>
                                <label for="remote_bmc_enabled">Enable remote BMC management</label>
                            </div>
                            <small class="form-text">Enable network-based BMC management for remote servers</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="remote_host">Remote Host</label>
                            <input type="text" id="remote_host" name="remote_bmc[host]" value="<?php echo htmlspecialchars($config['remote_bmc']['host']); ?>" placeholder="192.168.1.100">
                            <small class="form-text">IP address or hostname of the remote BMC</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="remote_port">Port</label>
                            <input type="number" id="remote_port" name="remote_bmc[port]" value="<?php echo htmlspecialchars($config['remote_bmc']['port']); ?>" min="1" max="65535" placeholder="623">
                            <small class="form-text">IPMI port (default: 623)</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="remote_username">Username</label>
                            <input type="text" id="remote_username" name="remote_bmc[username]" value="<?php echo htmlspecialchars($config['remote_bmc']['username']); ?>" placeholder="admin">
                            <small class="form-text">BMC username</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="remote_password">Password</label>
                            <input type="password" id="remote_password" name="remote_bmc[password]" value="<?php echo htmlspecialchars($config['remote_bmc']['password']); ?>" placeholder="password">
                            <small class="form-text">BMC password</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="privilege_level">Privilege Level</label>
                            <select id="privilege_level" name="remote_bmc[privilege_level]">
                                <option value="USER" <?php echo $config['remote_bmc']['privilege_level'] === 'USER' ? 'selected' : ''; ?>>User</option>
                                <option value="OPERATOR" <?php echo $config['remote_bmc']['privilege_level'] === 'OPERATOR' ? 'selected' : ''; ?>>Operator</option>
                                <option value="ADMINISTRATOR" <?php echo $config['remote_bmc']['privilege_level'] === 'ADMINISTRATOR' ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                            <small class="form-text">User privilege level for remote BMC access</small>
                        </div>
                    </div>
                    
                    <!-- GUI Settings -->
                    <div class="settings-section">
                        <h3><i class="fas fa-desktop"></i> Interface Settings</h3>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="auto_refresh" name="gui_settings[auto_refresh]" <?php echo $config['gui_settings']['auto_refresh'] ? 'checked' : ''; ?>>
                                <label for="auto_refresh">Enable auto-refresh</label>
                            </div>
                            <small class="form-text">Automatically refresh data at regular intervals</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="refresh_interval">Refresh Interval (seconds)</label>
                            <input type="number" id="refresh_interval" name="gui_settings[refresh_interval]" value="<?php echo htmlspecialchars($config['gui_settings']['refresh_interval']); ?>" min="5" max="300" placeholder="30">
                            <small class="form-text">How often to refresh data (5-300 seconds)</small>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="show_sensors" name="gui_settings[show_sensors]" <?php echo $config['gui_settings']['show_sensors'] ? 'checked' : ''; ?>>
                                <label for="show_sensors">Show sensors section</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="show_events" name="gui_settings[show_events]" <?php echo $config['gui_settings']['show_events'] ? 'checked' : ''; ?>>
                                <label for="show_events">Show events section</label>
                            </div>
                        </div>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="show_users" name="gui_settings[show_users]" <?php echo $config['gui_settings']['show_users'] ? 'checked' : ''; ?>>
                                <label for="show_users">Show users section</label>
                            </div>
                        </div>
                    </div>
                    
                    <!-- Security Settings -->
                    <div class="settings-section">
                        <h3><i class="fas fa-shield-alt"></i> Security Settings</h3>
                        
                        <div class="form-group">
                            <div class="checkbox-group">
                                <input type="checkbox" id="require_auth" name="security[require_authentication]" <?php echo $config['security']['require_authentication'] ? 'checked' : ''; ?>>
                                <label for="require_auth">Require authentication</label>
                            </div>
                            <small class="form-text">Require user authentication to access the plugin</small>
                        </div>
                        
                        <div class="form-group">
                            <label for="session_timeout">Session Timeout (seconds)</label>
                            <input type="number" id="session_timeout" name="security[session_timeout]" value="<?php echo htmlspecialchars($config['security']['session_timeout']); ?>" min="300" max="86400" placeholder="3600">
                            <small class="form-text">How long before sessions expire (300-86400 seconds)</small>
                        </div>
                    </div>
                    
                    <!-- Form Actions -->
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-save"></i> Save Settings
                        </button>
                        <button type="button" class="btn btn-secondary" onclick="testConnection()">
                            <i class="fas fa-plug"></i> Test Connection
                        </button>
                        <button type="button" class="btn btn-warning" onclick="resetSettings()">
                            <i class="fas fa-undo"></i> Reset to Defaults
                        </button>
                    </div>
                </form>
            </div>
        </div>
        
        <!-- Connection Test Results -->
        <?php if ($test_result): ?>
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-plug"></i> Connection Test Results</h2>
            </div>
            <div class="card-body">
                <div class="alert <?php echo $test_result['success'] ? 'alert-success' : 'alert-error'; ?>">
                    <i class="fas fa-<?php echo $test_result['success'] ? 'check-circle' : 'exclamation-triangle'; ?>"></i>
                    <span><?php echo htmlspecialchars($test_result['message']); ?></span>
                </div>
            </div>
        </div>
        <?php endif; ?>
        
        <!-- System Information -->
        <div class="card">
            <div class="card-header">
                <h2><i class="fas fa-info-circle"></i> System Information</h2>
            </div>
            <div class="card-body">
                <div class="info-grid">
                    <div class="info-item">
                        <label>Plugin Version:</label>
                        <span>1.0.0</span>
                    </div>
                    <div class="info-item">
                        <label>PHP Version:</label>
                        <span><?php echo PHP_VERSION; ?></span>
                    </div>
                    <div class="info-item">
                        <label>IPMICFG Available:</label>
                        <span><?php echo file_exists($config['local_bmc']['ipmicfg_path']) ? 'Yes' : 'No'; ?></span>
                    </div>
                    <div class="info-item">
                        <label>Configuration File:</label>
                        <span><?php echo file_exists($plugin_config_file) ? 'Valid' : 'Missing'; ?></span>
                    </div>
                </div>
            </div>
        </div>
    </div>
    
    <!-- Loading Overlay -->
    <div id="loadingOverlay" class="loading-overlay">
        <div class="loading-spinner">
            <i class="fas fa-spinner fa-spin"></i>
            <span>Processing...</span>
        </div>
    </div>
    
    <!-- Scripts -->
    <script>
        function testConnection() {
            showLoading();
            window.location.href = '?test_connection=1';
        }
        
        function resetSettings() {
            if (confirm('Are you sure you want to reset all settings to defaults? This cannot be undone.')) {
                showLoading();
                // This would typically send a reset request to the server
                alert('Settings reset functionality not implemented yet.');
                hideLoading();
            }
        }
        
        function showLoading() {
            document.getElementById('loadingOverlay').style.display = 'block';
        }
        
        function hideLoading() {
            document.getElementById('loadingOverlay').style.display = 'none';
        }
        
        // Auto-save settings after 2 seconds of inactivity
        let settingsTimeout;
        document.querySelectorAll('#settingsForm input, #settingsForm select').forEach(function(element) {
            element.addEventListener('change', function() {
                clearTimeout(settingsTimeout);
                settingsTimeout = setTimeout(function() {
                    document.getElementById('settingsForm').submit();
                }, 2000);
            });
        });
    </script>
</body>
</html> 