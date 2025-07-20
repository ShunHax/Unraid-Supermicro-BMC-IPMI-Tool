<?php
// Simple test file that should work
?>
<!DOCTYPE html>
<html>
<head>
    <title>Supermicro IPMI Plugin Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .success { color: green; }
        .error { color: red; }
    </style>
</head>
<body>
    <h1>Supermicro IPMI Plugin Test</h1>
    
    <div class="success">
        ✅ Plugin is working! PHP is functioning correctly.
    </div>
    
    <h2>Plugin Information:</h2>
    <ul>
        <li><strong>Plugin Name:</strong> Supermicro BMC/IPMI Tool</li>
        <li><strong>Version:</strong> 1.0.0</li>
        <li><strong>Author:</strong> ShunHax</li>
        <li><strong>Current Directory:</strong> <?php echo __DIR__; ?></li>
    </ul>
    
    <h2>File Check:</h2>
    <?php
    $files = ['plugin.php', 'settings.php', 'supermicro-ipmi.php', 'includes/functions.php', 'includes/ipmi.php'];
    foreach ($files as $file) {
        if (file_exists(__DIR__ . '/' . $file)) {
            echo "<div class='success'>✅ $file exists</div>";
        } else {
            echo "<div class='error'>❌ $file missing</div>";
        }
    }
    ?>
    
    <h2>Next Steps:</h2>
    <p>If you can see this page, the plugin is installed correctly. The main interface should be available at:</p>
    <ul>
        <li><a href="supermicro-ipmi.php">Main Dashboard</a></li>
        <li><a href="settings.php">Settings Page</a></li>
    </ul>
</body>
</html> 