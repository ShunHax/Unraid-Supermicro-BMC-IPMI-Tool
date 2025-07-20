<?php
/*
 * Supermicro IPMI Plugin - GUI Helper Functions
 * 
 * This file contains helper functions for the web interface
 */

$plugin = "supermicro-ipmi";
$docroot = $docroot ?: $_SERVER['DOCUMENT_ROOT'] ?: '/usr/local/emhttp';

// Display message if present
function display_message() {
    if (isset($_GET['message'])) {
        $message = htmlspecialchars($_GET['message']);
        $type = isset($_GET['type']) ? $_GET['type'] : 'success';
        
        $class = $type === 'error' ? 'alert-error' : 'alert-success';
        
        echo "<div class='alert $class'>";
        echo "<i class='fas fa-" . ($type === 'error' ? 'exclamation-triangle' : 'check-circle') . "'></i>";
        echo "<span>$message</span>";
        echo "<button class='alert-close' onclick='this.parentElement.remove()'>&times;</button>";
        echo "</div>";
    }
}

// Format sensor value for display
function format_sensor_value($sensor) {
    $value = $sensor['value'];
    $unit = $sensor['unit'];
    
    switch (strtolower($unit)) {
        case 'c':
        case '°c':
        case 'celsius':
            return format_temperature($value);
        case '%':
        case 'percent':
            return format_percentage($value);
        case 'v':
        case 'volts':
            return format_voltage($value);
        case 'hz':
        case 'hertz':
            return format_frequency($value);
        case 'rpm':
            return format_speed($value);
        case 'w':
        case 'watts':
            return round($value, 1) . 'W';
        case 'a':
        case 'amps':
            return round($value, 2) . 'A';
        default:
            return $value . ' ' . $unit;
    }
}

// Get sensor icon based on type
function get_sensor_icon($sensor_name) {
    $name = strtolower($sensor_name);
    
    if (strpos($name, 'temp') !== false || strpos($name, 'thermal') !== false) {
        return 'fas fa-thermometer-half';
    } elseif (strpos($name, 'fan') !== false) {
        return 'fas fa-fan';
    } elseif (strpos($name, 'voltage') !== false || strpos($name, 'volt') !== false) {
        return 'fas fa-bolt';
    } elseif (strpos($name, 'power') !== false || strpos($name, 'watt') !== false) {
        return 'fas fa-plug';
    } elseif (strpos($name, 'current') !== false || strpos($name, 'amp') !== false) {
        return 'fas fa-tachometer-alt';
    } elseif (strpos($name, 'cpu') !== false) {
        return 'fas fa-microchip';
    } elseif (strpos($name, 'memory') !== false || strpos($name, 'ram') !== false) {
        return 'fas fa-memory';
    } else {
        return 'fas fa-chart-line';
    }
}

// Get event icon based on level
function get_event_icon($level) {
    switch (strtolower($level)) {
        case 'info':
        case 'information':
            return 'fas fa-info-circle';
        case 'warning':
        case 'caution':
            return 'fas fa-exclamation-triangle';
        case 'critical':
        case 'error':
        case 'fatal':
            return 'fas fa-times-circle';
        default:
            return 'fas fa-circle';
    }
}

// Get privilege level display name
function get_privilege_display_name($privilege) {
    switch (strtoupper($privilege)) {
        case 'USER':
            return 'User';
        case 'OPERATOR':
            return 'Operator';
        case 'ADMINISTRATOR':
        case 'ADMIN':
            return 'Administrator';
        default:
            return $privilege;
    }
}

// Get privilege level color class
function get_privilege_color_class($privilege) {
    switch (strtoupper($privilege)) {
        case 'USER':
            return 'privilege-user';
        case 'OPERATOR':
            return 'privilege-operator';
        case 'ADMINISTRATOR':
        case 'ADMIN':
            return 'privilege-admin';
        default:
            return 'privilege-unknown';
    }
}

// Generate sensor chart data
function generate_sensor_chart_data($sensors) {
    $chart_data = [
        'labels' => [],
        'datasets' => []
    ];
    
    $temperature_data = [];
    $fan_data = [];
    $voltage_data = [];
    
    foreach ($sensors as $sensor) {
        $name = $sensor['name'];
        $value = floatval($sensor['value']);
        $unit = strtolower($sensor['unit']);
        
        $chart_data['labels'][] = $name;
        
        if ($unit === 'c' || $unit === '°c') {
            $temperature_data[] = $value;
        } elseif ($unit === 'rpm') {
            $fan_data[] = $value;
        } elseif ($unit === 'v') {
            $voltage_data[] = $value;
        }
    }
    
    if (!empty($temperature_data)) {
        $chart_data['datasets'][] = [
            'label' => 'Temperature (°C)',
            'data' => $temperature_data,
            'borderColor' => '#ff6384',
            'backgroundColor' => 'rgba(255, 99, 132, 0.1)',
            'yAxisID' => 'y'
        ];
    }
    
    if (!empty($fan_data)) {
        $chart_data['datasets'][] = [
            'label' => 'Fan Speed (RPM)',
            'data' => $fan_data,
            'borderColor' => '#36a2eb',
            'backgroundColor' => 'rgba(54, 162, 235, 0.1)',
            'yAxisID' => 'y1'
        ];
    }
    
    if (!empty($voltage_data)) {
        $chart_data['datasets'][] = [
            'label' => 'Voltage (V)',
            'data' => $voltage_data,
            'borderColor' => '#ffce56',
            'backgroundColor' => 'rgba(255, 206, 86, 0.1)',
            'yAxisID' => 'y2'
        ];
    }
    
    return $chart_data;
}

// Generate event timeline data
function generate_event_timeline_data($events) {
    $timeline_data = [];
    
    foreach ($events as $event) {
        $timeline_data[] = [
            'id' => $event['id'],
            'timestamp' => $event['timestamp'],
            'level' => $event['level'],
            'message' => $event['message'],
            'icon' => get_event_icon($event['level']),
            'class' => get_event_level_class($event['level'])
        ];
    }
    
    return $timeline_data;
}

// Generate system health summary
function generate_health_summary() {
    $sensors = get_sensors();
    $events = get_events();
    
    $summary = [
        'total_sensors' => count($sensors),
        'critical_sensors' => 0,
        'warning_sensors' => 0,
        'ok_sensors' => 0,
        'total_events' => count($events),
        'critical_events' => 0,
        'warning_events' => 0,
        'info_events' => 0
    ];
    
    foreach ($sensors as $sensor) {
        $status = strtolower($sensor['status']);
        if ($status === 'critical') {
            $summary['critical_sensors']++;
        } elseif ($status === 'warning') {
            $summary['warning_sensors']++;
        } else {
            $summary['ok_sensors']++;
        }
    }
    
    foreach ($events as $event) {
        $level = strtolower($event['level']);
        if ($level === 'critical') {
            $summary['critical_events']++;
        } elseif ($level === 'warning') {
            $summary['warning_events']++;
        } else {
            $summary['info_events']++;
        }
    }
    
    return $summary;
}

// Get system status color
function get_system_status_color($summary) {
    if ($summary['critical_sensors'] > 0 || $summary['critical_events'] > 0) {
        return '#dc3545'; // Red
    } elseif ($summary['warning_sensors'] > 0 || $summary['warning_events'] > 0) {
        return '#ffc107'; // Yellow
    } else {
        return '#28a745'; // Green
    }
}

// Format relative time
function format_relative_time($timestamp) {
    $time = strtotime($timestamp);
    $now = time();
    $diff = $now - $time;
    
    if ($diff < 60) {
        return 'Just now';
    } elseif ($diff < 3600) {
        $minutes = floor($diff / 60);
        return $minutes . ' minute' . ($minutes > 1 ? 's' : '') . ' ago';
    } elseif ($diff < 86400) {
        $hours = floor($diff / 3600);
        return $hours . ' hour' . ($hours > 1 ? 's' : '') . ' ago';
    } else {
        $days = floor($diff / 86400);
        return $days . ' day' . ($days > 1 ? 's' : '') . ' ago';
    }
}

// Generate breadcrumb navigation
function generate_breadcrumbs($current_page = '') {
    $breadcrumbs = [
        ['title' => 'Dashboard', 'url' => '/plugins/supermicro-ipmi/supermicro-ipmi.php', 'active' => $current_page === 'dashboard']
    ];
    
    if ($current_page !== 'dashboard') {
        $breadcrumbs[] = [
            'title' => ucfirst($current_page),
            'url' => '#',
            'active' => true
        ];
    }
    
    return $breadcrumbs;
}

// Display breadcrumbs
function display_breadcrumbs($current_page = '') {
    $breadcrumbs = generate_breadcrumbs($current_page);
    
    echo '<nav class="breadcrumb">';
    foreach ($breadcrumbs as $index => $crumb) {
        if ($index > 0) {
            echo '<span class="breadcrumb-separator">/</span>';
        }
        
        if ($crumb['active']) {
            echo '<span class="breadcrumb-item active">' . htmlspecialchars($crumb['title']) . '</span>';
        } else {
            echo '<a href="' . htmlspecialchars($crumb['url']) . '" class="breadcrumb-item">' . htmlspecialchars($crumb['title']) . '</a>';
        }
    }
    echo '</nav>';
}

// Generate pagination
function generate_pagination($total_items, $items_per_page, $current_page, $base_url) {
    $total_pages = ceil($total_items / $items_per_page);
    
    if ($total_pages <= 1) {
        return '';
    }
    
    $pagination = '<div class="pagination">';
    
    // Previous button
    if ($current_page > 1) {
        $pagination .= '<a href="' . $base_url . '?page=' . ($current_page - 1) . '" class="pagination-item">&laquo; Previous</a>';
    }
    
    // Page numbers
    $start_page = max(1, $current_page - 2);
    $end_page = min($total_pages, $current_page + 2);
    
    if ($start_page > 1) {
        $pagination .= '<a href="' . $base_url . '?page=1" class="pagination-item">1</a>';
        if ($start_page > 2) {
            $pagination .= '<span class="pagination-ellipsis">...</span>';
        }
    }
    
    for ($i = $start_page; $i <= $end_page; $i++) {
        if ($i == $current_page) {
            $pagination .= '<span class="pagination-item active">' . $i . '</span>';
        } else {
            $pagination .= '<a href="' . $base_url . '?page=' . $i . '" class="pagination-item">' . $i . '</a>';
        }
    }
    
    if ($end_page < $total_pages) {
        if ($end_page < $total_pages - 1) {
            $pagination .= '<span class="pagination-ellipsis">...</span>';
        }
        $pagination .= '<a href="' . $base_url . '?page=' . $total_pages . '" class="pagination-item">' . $total_pages . '</a>';
    }
    
    // Next button
    if ($current_page < $total_pages) {
        $pagination .= '<a href="' . $base_url . '?page=' . ($current_page + 1) . '" class="pagination-item">Next &raquo;</a>';
    }
    
    $pagination .= '</div>';
    
    return $pagination;
}

// Generate search form
function generate_search_form($placeholder = 'Search...', $current_query = '') {
    $form = '<form class="search-form" method="get">';
    $form .= '<div class="search-input-group">';
    $form .= '<input type="text" name="search" placeholder="' . htmlspecialchars($placeholder) . '" value="' . htmlspecialchars($current_query) . '" class="search-input">';
    $form .= '<button type="submit" class="search-button">';
    $form .= '<i class="fas fa-search"></i>';
    $form .= '</button>';
    $form .= '</div>';
    $form .= '</form>';
    
    return $form;
}

// Filter data by search query
function filter_data_by_search($data, $search_query, $search_fields) {
    if (empty($search_query)) {
        return $data;
    }
    
    $filtered_data = [];
    $query = strtolower($search_query);
    
    foreach ($data as $item) {
        $match = false;
        
        foreach ($search_fields as $field) {
            if (isset($item[$field]) && stripos(strtolower($item[$field]), $query) !== false) {
                $match = true;
                break;
            }
        }
        
        if ($match) {
            $filtered_data[] = $item;
        }
    }
    
    return $filtered_data;
}

// Generate export options
function generate_export_options($data_type) {
    $options = '<div class="export-options">';
    $options .= '<span class="export-label">Export:</span>';
    $options .= '<a href="?action=export&type=' . $data_type . '&format=csv" class="export-link">';
    $options .= '<i class="fas fa-file-csv"></i> CSV';
    $options .= '</a>';
    $options .= '<a href="?action=export&type=' . $data_type . '&format=json" class="export-link">';
    $options .= '<i class="fas fa-file-code"></i> JSON';
    $options .= '</a>';
    $options .= '</div>';
    
    return $options;
}

// Export data to CSV
function export_to_csv($data, $filename) {
    if (empty($data)) {
        return false;
    }
    
    $headers = array_keys($data[0]);
    
    header('Content-Type: text/csv');
    header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
    
    $output = fopen('php://output', 'w');
    
    // Add headers
    fputcsv($output, $headers);
    
    // Add data
    foreach ($data as $row) {
        fputcsv($output, $row);
    }
    
    fclose($output);
    exit;
}

// Export data to JSON
function export_to_json($data, $filename) {
    header('Content-Type: application/json');
    header('Content-Disposition: attachment; filename="' . $filename . '.json"');
    
    echo json_encode($data, JSON_PRETTY_PRINT);
    exit;
}
?> 