<?php
/*
 * Supermicro IPMI Plugin - Settings Page for Unraid
 */

$plugin = "supermicro-ipmi";
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// Include required files
require_once "$docroot/plugins/$plugin/plugin.php";
require_once "$docroot/plugins/$plugin/includes/functions.php";

// Load configuration
$config = load_config();

// Handle form submissions
if ($_POST) {
    if (isset($_POST['action']) && $_POST['action'] === 'save_settings') {
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
        $message = "Settings saved successfully!";
    }
}

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermicro IPMI Plugin Settings</title>
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
                    <h1>Supermicro IPMI Plugin Settings</h1>
                </div>
                <div class="header-actions">
                    <a href="/plugins/<?php echo $plugin; ?>/page.php" class="btn btn-secondary">
                        <i class="fas fa-arrow-left"></i> Back to Main
                    </a>
                </div>
            </div>
        </header>

        <?php if (isset($message)): ?>
        <div class="alert alert-success">
            <i class="fas fa-check-circle"></i>
            <span><?php echo htmlspecialchars($message); ?></span>
        </div>
        <?php endif; ?>

        <!-- Settings Form -->
        <div class="main-content">
            <form method="post" class="settings-form">
                <input type="hidden" name="action" value="save_settings">
                
                <!-- Local BMC Settings -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-server"></i> Local BMC Settings</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="local_bmc[enabled]" <?php echo $config['local_bmc']['enabled'] ? 'checked' : ''; ?>>
                                Enable Local BMC
                            </label>
                        </div>
                        <div class="form-group">
                            <label>IPMICFG Path:</label>
                            <input type="text" name="local_bmc[ipmicfg_path]" value="<?php echo htmlspecialchars($config['local_bmc']['ipmicfg_path']); ?>" placeholder="/usr/local/sbin/ipmicfg">
                        </div>
                        <div class="form-group">
                            <label>Device:</label>
                            <input type="text" name="local_bmc[device]" value="<?php echo htmlspecialchars($config['local_bmc']['device']); ?>" placeholder="/dev/ipmi0">
                        </div>
                    </div>
                </div>

                <!-- Remote BMC Settings -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-network-wired"></i> Remote BMC Settings</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="remote_bmc[enabled]" <?php echo $config['remote_bmc']['enabled'] ? 'checked' : ''; ?>>
                                Enable Remote BMC
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Host:</label>
                            <input type="text" name="remote_bmc[host]" value="<?php echo htmlspecialchars($config['remote_bmc']['host']); ?>" placeholder="192.168.1.100">
                        </div>
                        <div class="form-group">
                            <label>Port:</label>
                            <input type="number" name="remote_bmc[port]" value="<?php echo htmlspecialchars($config['remote_bmc']['port']); ?>" placeholder="623">
                        </div>
                        <div class="form-group">
                            <label>Username:</label>
                            <input type="text" name="remote_bmc[username]" value="<?php echo htmlspecialchars($config['remote_bmc']['username']); ?>" placeholder="admin">
                        </div>
                        <div class="form-group">
                            <label>Password:</label>
                            <input type="password" name="remote_bmc[password]" value="<?php echo htmlspecialchars($config['remote_bmc']['password']); ?>" placeholder="password">
                        </div>
                        <div class="form-group">
                            <label>Privilege Level:</label>
                            <select name="remote_bmc[privilege_level]">
                                <option value="USER" <?php echo $config['remote_bmc']['privilege_level'] === 'USER' ? 'selected' : ''; ?>>User</option>
                                <option value="OPERATOR" <?php echo $config['remote_bmc']['privilege_level'] === 'OPERATOR' ? 'selected' : ''; ?>>Operator</option>
                                <option value="ADMINISTRATOR" <?php echo $config['remote_bmc']['privilege_level'] === 'ADMINISTRATOR' ? 'selected' : ''; ?>>Administrator</option>
                            </select>
                        </div>
                    </div>
                </div>

                <!-- GUI Settings -->
                <div class="card">
                    <div class="card-header">
                        <h2><i class="fas fa-desktop"></i> GUI Settings</h2>
                    </div>
                    <div class="card-body">
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="gui_settings[auto_refresh]" <?php echo $config['gui_settings']['auto_refresh'] ? 'checked' : ''; ?>>
                                Enable Auto Refresh
                            </label>
                        </div>
                        <div class="form-group">
                            <label>Refresh Interval (seconds):</label>
                            <input type="number" name="gui_settings[refresh_interval]" value="<?php echo htmlspecialchars($config['gui_settings']['refresh_interval']); ?>" min="5" max="300">
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="gui_settings[show_sensors]" <?php echo $config['gui_settings']['show_sensors'] ? 'checked' : ''; ?>>
                                Show Sensors Section
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="gui_settings[show_events]" <?php echo $config['gui_settings']['show_events'] ? 'checked' : ''; ?>>
                                Show Events Section
                            </label>
                        </div>
                        <div class="form-group">
                            <label>
                                <input type="checkbox" name="gui_settings[show_users]" <?php echo $config['gui_settings']['show_users'] ? 'checked' : ''; ?>>
                                Show Users Section
                            </label>
                        </div>
                    </div>
                </div>

                <!-- Form Actions -->
                <div class="form-actions">
                    <button type="submit" class="btn btn-primary">
                        <i class="fas fa-save"></i> Save Settings
                    </button>
                    <a href="/plugins/<?php echo $plugin; ?>/page.php" class="btn btn-secondary">
                        <i class="fas fa-times"></i> Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>

    <script src="/plugins/<?php echo $plugin; ?>/js/script.js"></script>
</body>
</html> 