<?php
/*
 * Supermicro IPMI Plugin - Main Interface
 */

$plugin = "supermicro-ipmi";
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// Include required files
require_once "$docroot/plugins/$plugin/includes/functions.php";
require_once "$docroot/plugins/$plugin/includes/ipmi.php";
require_once "$docroot/plugins/$plugin/includes/gui.php";

// Load configuration
$config = load_config();

// Handle form submissions
if ($_POST) {
    handle_form_submission();
}

// Get current BMC status
$bmc_status = get_bmc_status();
$sensors = get_sensors();
$events = get_events();
$users = get_users();
$system_info = get_system_info();

?>
<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Supermicro BMC/IPMI Tool Management</title>
    <link rel="stylesheet" href="/plugins/<?php echo $plugin; ?>/css/style.css">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.0.0/css/all.min.css" rel="stylesheet">
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
</head>
<body>
    <div class="container">
        <!-- Header -->
        <header class="header">
            <div class="header-content">
                <div class="logo">
                    <i class="fas fa-server"></i>
                    <h1>Supermicro BMC/IPMI Tool</h1>
                </div>
                <div class="header-actions">
                    <button class="btn btn-secondary" onclick="refreshData()">
                        <i class="fas fa-sync-alt"></i> Refresh
                    </button>
                    <button class="btn btn-primary" onclick="openSettings()">
                        <i class="fas fa-cog"></i> Settings
                    </button>
                </div>
            </div>
        </header>

        <!-- BMC Status Bar -->
        <div class="status-bar">
            <div class="status-item">
                <span class="status-label">BMC Status:</span>
                <span class="status-value <?php echo $bmc_status['connected'] ? 'status-ok' : 'status-error'; ?>">
                    <?php echo $bmc_status['connected'] ? 'Connected' : 'Disconnected'; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">System Power:</span>
                <span class="status-value <?php echo $bmc_status['power'] ? 'status-ok' : 'status-warning'; ?>">
                    <?php echo $bmc_status['power'] ? 'ON' : 'OFF'; ?>
                </span>
            </div>
            <div class="status-item">
                <span class="status-label">Connection:</span>
                <span class="status-value">
                    <?php echo $config['local_bmc']['enabled'] ? 'Local' : 'Remote'; ?>
                </span>
            </div>
        </div>

        <!-- Main Content -->
        <div class="main-content">
            <!-- Power Control Section -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-power-off"></i> Power Control</h2>
                </div>
                <div class="card-body">
                    <div class="power-controls">
                        <button class="btn btn-success btn-large" onclick="powerAction('on')" <?php echo $bmc_status['power'] ? 'disabled' : ''; ?>>
                            <i class="fas fa-play"></i> Power On
                        </button>
                        <button class="btn btn-warning btn-large" onclick="powerAction('off')" <?php echo !$bmc_status['power'] ? 'disabled' : ''; ?>>
                            <i class="fas fa-stop"></i> Power Off
                        </button>
                        <button class="btn btn-danger btn-large" onclick="powerAction('reset')" <?php echo !$bmc_status['power'] ? 'disabled' : ''; ?>>
                            <i class="fas fa-redo"></i> Reset
                        </button>
                        <button class="btn btn-info btn-large" onclick="powerAction('cycle')" <?php echo !$bmc_status['power'] ? 'disabled' : ''; ?>>
                            <i class="fas fa-sync"></i> Power Cycle
                        </button>
                    </div>
                </div>
            </div>

            <!-- System Information -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-info-circle"></i> System Information</h2>
                </div>
                <div class="card-body">
                    <div class="info-grid">
                        <div class="info-item">
                            <label>Product Name:</label>
                            <span><?php echo htmlspecialchars($system_info['product_name'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>Serial Number:</label>
                            <span><?php echo htmlspecialchars($system_info['serial_number'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>BMC Version:</label>
                            <span><?php echo htmlspecialchars($system_info['bmc_version'] ?? 'N/A'); ?></span>
                        </div>
                        <div class="info-item">
                            <label>BIOS Version:</label>
                            <span><?php echo htmlspecialchars($system_info['bios_version'] ?? 'N/A'); ?></span>
                        </div>
                    </div>
                </div>
            </div>

            <!-- Sensors -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-thermometer-half"></i> Sensors</h2>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-secondary" onclick="refreshSensors()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="sensors-grid">
                        <?php foreach ($sensors as $sensor): ?>
                        <div class="sensor-item">
                            <div class="sensor-name"><?php echo htmlspecialchars($sensor['name']); ?></div>
                            <div class="sensor-value <?php echo get_sensor_status_class($sensor); ?>">
                                <?php echo htmlspecialchars($sensor['value']); ?>
                                <span class="sensor-unit"><?php echo htmlspecialchars($sensor['unit']); ?></span>
                            </div>
                            <div class="sensor-status">
                                <span class="status-indicator <?php echo get_sensor_status_class($sensor); ?>"></span>
                                <?php echo htmlspecialchars($sensor['status']); ?>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- Event Log -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-list-alt"></i> Event Log</h2>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-secondary" onclick="refreshEvents()">
                            <i class="fas fa-sync-alt"></i>
                        </button>
                        <button class="btn btn-sm btn-danger" onclick="clearEventLog()">
                            <i class="fas fa-trash"></i> Clear
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="event-log">
                        <?php foreach ($events as $event): ?>
                        <div class="event-item">
                            <div class="event-time"><?php echo htmlspecialchars($event['timestamp']); ?></div>
                            <div class="event-level <?php echo get_event_level_class($event['level']); ?>">
                                <?php echo htmlspecialchars($event['level']); ?>
                            </div>
                            <div class="event-message"><?php echo htmlspecialchars($event['message']); ?></div>
                        </div>
                        <?php endforeach; ?>
                    </div>
                </div>
            </div>

            <!-- User Management -->
            <div class="card">
                <div class="card-header">
                    <h2><i class="fas fa-users"></i> User Management</h2>
                    <div class="card-actions">
                        <button class="btn btn-sm btn-primary" onclick="addUser()">
                            <i class="fas fa-plus"></i> Add User
                        </button>
                    </div>
                </div>
                <div class="card-body">
                    <div class="users-table">
                        <table>
                            <thead>
                                <tr>
                                    <th>User ID</th>
                                    <th>Username</th>
                                    <th>Privilege Level</th>
                                    <th>Enabled</th>
                                    <th>Actions</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php foreach ($users as $user): ?>
                                <tr>
                                    <td><?php echo htmlspecialchars($user['id']); ?></td>
                                    <td><?php echo htmlspecialchars($user['username']); ?></td>
                                    <td><?php echo htmlspecialchars($user['privilege']); ?></td>
                                    <td>
                                        <span class="status-indicator <?php echo $user['enabled'] ? 'status-ok' : 'status-error'; ?>"></span>
                                        <?php echo $user['enabled'] ? 'Yes' : 'No'; ?>
                                    </td>
                                    <td>
                                        <button class="btn btn-sm btn-secondary" onclick="editUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-edit"></i>
                                        </button>
                                        <button class="btn btn-sm btn-danger" onclick="deleteUser(<?php echo $user['id']; ?>)">
                                            <i class="fas fa-trash"></i>
                                        </button>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        </div>
    </div>

    <!-- Modals -->
    <div id="settingsModal" class="modal">
        <div class="modal-content">
            <div class="modal-header">
                <h2>Settings</h2>
                <span class="close" onclick="closeModal('settingsModal')">&times;</span>
            </div>
            <div class="modal-body">
                <form id="settingsForm" method="post">
                    <input type="hidden" name="action" value="save_settings">
                    
                    <div class="form-group">
                        <label>Local BMC</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="local_bmc_enabled" name="local_bmc[enabled]" <?php echo $config['local_bmc']['enabled'] ? 'checked' : ''; ?>>
                            <label for="local_bmc_enabled">Enable local BMC</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>IPMICFG Path</label>
                        <input type="text" name="local_bmc[ipmicfg_path]" value="<?php echo htmlspecialchars($config['local_bmc']['ipmicfg_path']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Remote BMC</label>
                        <div class="checkbox-group">
                            <input type="checkbox" id="remote_bmc_enabled" name="remote_bmc[enabled]" <?php echo $config['remote_bmc']['enabled'] ? 'checked' : ''; ?>>
                            <label for="remote_bmc_enabled">Enable remote BMC</label>
                        </div>
                    </div>
                    
                    <div class="form-group">
                        <label>Remote Host</label>
                        <input type="text" name="remote_bmc[host]" value="<?php echo htmlspecialchars($config['remote_bmc']['host']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Username</label>
                        <input type="text" name="remote_bmc[username]" value="<?php echo htmlspecialchars($config['remote_bmc']['username']); ?>">
                    </div>
                    
                    <div class="form-group">
                        <label>Password</label>
                        <input type="password" name="remote_bmc[password]" value="<?php echo htmlspecialchars($config['remote_bmc']['password']); ?>">
                    </div>
                    
                    <div class="form-actions">
                        <button type="submit" class="btn btn-primary">Save Settings</button>
                        <button type="button" class="btn btn-secondary" onclick="closeModal('settingsModal')">Cancel</button>
                    </div>
                </form>
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
    <script src="/plugins/<?php echo $plugin; ?>/js/script.js"></script>
</body>
</html> 