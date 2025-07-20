// Supermicro IPMI Plugin - JavaScript

$(document).ready(function() {
    // Initialize the application
    initApp();
    
    // Set up auto-refresh if enabled
    setupAutoRefresh();
    
    // Set up event listeners
    setupEventListeners();
});

// Initialize the application
function initApp() {
    console.log('Supermicro IPMI Plugin initialized');
    
    // Check for messages in URL parameters
    checkForMessages();
    
    // Initialize tooltips
    initTooltips();
    
    // Initialize charts if Chart.js is available
    if (typeof Chart !== 'undefined') {
        initCharts();
    }
}

// Check for messages in URL parameters
function checkForMessages() {
    const urlParams = new URLSearchParams(window.location.search);
    const message = urlParams.get('message');
    const type = urlParams.get('type') || 'success';
    
    if (message) {
        showAlert(message, type);
        
        // Remove message from URL
        const newUrl = window.location.pathname;
        window.history.replaceState({}, document.title, newUrl);
    }
}

// Show alert message
function showAlert(message, type = 'success') {
    const alertClass = type === 'error' ? 'alert-error' : 'alert-success';
    const iconClass = type === 'error' ? 'exclamation-triangle' : 'check-circle';
    
    const alertHtml = `
        <div class="alert ${alertClass}">
            <i class="fas fa-${iconClass}"></i>
            <span>${message}</span>
            <button class="alert-close" onclick="this.parentElement.remove()">&times;</button>
        </div>
    `;
    
    // Insert alert at the top of the container
    $('.container').prepend(alertHtml);
    
    // Auto-hide after 5 seconds
    setTimeout(() => {
        $('.alert').fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);
}

// Initialize tooltips
function initTooltips() {
    // Add tooltips to buttons and interactive elements
    $('[data-tooltip]').each(function() {
        const tooltip = $(this).attr('data-tooltip');
        $(this).attr('title', tooltip);
    });
}

// Initialize charts
function initCharts() {
    // Create sensor charts if sensor data is available
    if (typeof sensorChartData !== 'undefined') {
        createSensorChart();
    }
    
    // Create event timeline if event data is available
    if (typeof eventTimelineData !== 'undefined') {
        createEventTimeline();
    }
}

// Create sensor chart
function createSensorChart() {
    const ctx = document.getElementById('sensorChart');
    if (!ctx) return;
    
    new Chart(ctx, {
        type: 'bar',
        data: sensorChartData,
        options: {
            responsive: true,
            maintainAspectRatio: false,
            scales: {
                y: {
                    type: 'linear',
                    display: true,
                    position: 'left',
                    title: {
                        display: true,
                        text: 'Temperature (°C)'
                    }
                },
                y1: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Fan Speed (RPM)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                },
                y2: {
                    type: 'linear',
                    display: true,
                    position: 'right',
                    title: {
                        display: true,
                        text: 'Voltage (V)'
                    },
                    grid: {
                        drawOnChartArea: false,
                    },
                }
            },
            plugins: {
                legend: {
                    position: 'top',
                },
                title: {
                    display: true,
                    text: 'System Sensors'
                }
            }
        }
    });
}

// Create event timeline
function createEventTimeline() {
    const timelineContainer = $('#eventTimeline');
    if (!timelineContainer.length) return;
    
    let timelineHtml = '';
    eventTimelineData.forEach(event => {
        timelineHtml += `
            <div class="timeline-item ${event.class}">
                <div class="timeline-icon">
                    <i class="${event.icon}"></i>
                </div>
                <div class="timeline-content">
                    <div class="timeline-header">
                        <span class="timeline-time">${event.timestamp}</span>
                        <span class="timeline-level">${event.level}</span>
                    </div>
                    <div class="timeline-message">${event.message}</div>
                </div>
            </div>
        `;
    });
    
    timelineContainer.html(timelineHtml);
}

// Set up auto-refresh
function setupAutoRefresh() {
    const config = getConfig();
    if (config.gui_settings && config.gui_settings.auto_refresh) {
        const interval = (config.gui_settings.refresh_interval || 30) * 1000;
        setInterval(refreshData, interval);
    }
}

// Set up event listeners
function setupEventListeners() {
    // Modal close events
    $(window).click(function(event) {
        if ($(event.target).hasClass('modal')) {
            closeModal($(event.target).attr('id'));
        }
    });
    
    // Form submissions
    $('#settingsForm').on('submit', function(e) {
        showLoading();
    });
    
    // Search functionality
    $('.search-input').on('input', function() {
        const query = $(this).val().toLowerCase();
        filterTable($(this).closest('.card').find('table'), query);
    });
}

// Get configuration
function getConfig() {
    // This would typically be loaded from the server
    // For now, return a default configuration
    return {
        gui_settings: {
            auto_refresh: true,
            refresh_interval: 30
        }
    };
}

// Power action functions
function powerAction(action) {
    if (!confirm(`Are you sure you want to ${action} the system?`)) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: window.location.href,
        method: 'POST',
        data: {
            action: 'power_action',
            power_action: action,
            ajax: true
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                showAlert(response.message, 'success');
                setTimeout(refreshData, 1000);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function() {
            hideLoading();
            showAlert('Failed to execute power action. Please try again.', 'error');
        }
    });
}

// Refresh data
function refreshData() {
    showLoading();
    
    $.ajax({
        url: window.location.href,
        method: 'GET',
        data: { ajax: true },
        success: function(response) {
            hideLoading();
            
            // Update the page content
            updatePageContent(response);
        },
        error: function() {
            hideLoading();
            showAlert('Failed to refresh data. Please try again.', 'error');
        }
    });
}

// Update page content
function updatePageContent(data) {
    // Update BMC status
    if (data.bmc_status) {
        updateBMCStatus(data.bmc_status);
    }
    
    // Update sensors
    if (data.sensors) {
        updateSensors(data.sensors);
    }
    
    // Update events
    if (data.events) {
        updateEvents(data.events);
    }
    
    // Update users
    if (data.users) {
        updateUsers(data.users);
    }
}

// Update BMC status
function updateBMCStatus(status) {
    const statusValue = $('.status-value');
    
    statusValue.each(function() {
        const label = $(this).siblings('.status-label').text();
        
        if (label.includes('BMC Status')) {
            $(this).text(status.connected ? 'Connected' : 'Disconnected')
                   .removeClass('status-ok status-error')
                   .addClass(status.connected ? 'status-ok' : 'status-error');
        } else if (label.includes('System Power')) {
            $(this).text(status.power ? 'ON' : 'OFF')
                   .removeClass('status-ok status-warning')
                   .addClass(status.power ? 'status-ok' : 'status-warning');
        }
    });
}

// Update sensors
function updateSensors(sensors) {
    const sensorsGrid = $('.sensors-grid');
    if (!sensorsGrid.length) return;
    
    let sensorsHtml = '';
    sensors.forEach(sensor => {
        const statusClass = getSensorStatusClass(sensor.status);
        sensorsHtml += `
            <div class="sensor-item ${statusClass}">
                <div class="sensor-name">${sensor.name}</div>
                <div class="sensor-value ${statusClass}">
                    ${sensor.value}
                    <span class="sensor-unit">${sensor.unit}</span>
                </div>
                <div class="sensor-status">
                    <span class="status-indicator ${statusClass}"></span>
                    ${sensor.status}
                </div>
            </div>
        `;
    });
    
    sensorsGrid.html(sensorsHtml);
}

// Update events
function updateEvents(events) {
    const eventLog = $('.event-log');
    if (!eventLog.length) return;
    
    let eventsHtml = '';
    events.forEach(event => {
        const levelClass = getEventLevelClass(event.level);
        eventsHtml += `
            <div class="event-item">
                <div class="event-time">${event.timestamp}</div>
                <div class="event-level ${levelClass}">${event.level}</div>
                <div class="event-message">${event.message}</div>
            </div>
        `;
    });
    
    eventLog.html(eventsHtml);
}

// Update users
function updateUsers(users) {
    const usersTable = $('.users-table tbody');
    if (!usersTable.length) return;
    
    let usersHtml = '';
    users.forEach(user => {
        const enabledClass = user.enabled ? 'status-ok' : 'status-error';
        const enabledText = user.enabled ? 'Yes' : 'No';
        
        usersHtml += `
            <tr>
                <td>${user.id}</td>
                <td>${user.username}</td>
                <td>${user.privilege}</td>
                <td>
                    <span class="status-indicator ${enabledClass}"></span>
                    ${enabledText}
                </td>
                <td>
                    <button class="btn btn-sm btn-secondary" onclick="editUser(${user.id})">
                        <i class="fas fa-edit"></i>
                    </button>
                    <button class="btn btn-sm btn-danger" onclick="deleteUser(${user.id})">
                        <i class="fas fa-trash"></i>
                    </button>
                </td>
            </tr>
        `;
    });
    
    usersTable.html(usersHtml);
}

// Get sensor status class
function getSensorStatusClass(status) {
    const statusLower = status.toLowerCase();
    if (['ok', 'normal', 'good'].includes(statusLower)) {
        return 'status-ok';
    } else if (['warning', 'caution'].includes(statusLower)) {
        return 'status-warning';
    } else if (['critical', 'error', 'failed'].includes(statusLower)) {
        return 'status-error';
    }
    return 'status-unknown';
}

// Get event level class
function getEventLevelClass(level) {
    const levelLower = level.toLowerCase();
    if (['info', 'information'].includes(levelLower)) {
        return 'level-info';
    } else if (['warning', 'caution'].includes(levelLower)) {
        return 'level-warning';
    } else if (['critical', 'error', 'fatal'].includes(levelLower)) {
        return 'level-error';
    }
    return 'level-unknown';
}

// Modal functions
function openSettings() {
    $('#settingsModal').show();
}

function closeModal(modalId) {
    $('#' + modalId).hide();
}

// User management functions
function addUser() {
    // Create a simple modal for adding users
    const modalHtml = `
        <div id="addUserModal" class="modal">
            <div class="modal-content">
                <div class="modal-header">
                    <h2>Add User</h2>
                    <span class="close" onclick="closeModal('addUserModal')">&times;</span>
                </div>
                <div class="modal-body">
                    <form method="post">
                        <input type="hidden" name="action" value="add_user">
                        <div class="form-group">
                            <label>Username</label>
                            <input type="text" name="username" required>
                        </div>
                        <div class="form-group">
                            <label>Password</label>
                            <input type="password" name="password" required>
                        </div>
                        <div class="form-group">
                            <label>Privilege Level</label>
                            <select name="privilege" required>
                                <option value="USER">User</option>
                                <option value="OPERATOR">Operator</option>
                                <option value="ADMINISTRATOR">Administrator</option>
                            </select>
                        </div>
                        <div class="form-actions">
                            <button type="submit" class="btn btn-primary">Add User</button>
                            <button type="button" class="btn btn-secondary" onclick="closeModal('addUserModal')">Cancel</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    `;
    
    $('body').append(modalHtml);
    $('#addUserModal').show();
}

function editUser(userId) {
    // This would typically load user data and show an edit modal
    showAlert('Edit user functionality not implemented yet.', 'warning');
}

function deleteUser(userId) {
    if (!confirm('Are you sure you want to delete this user?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: window.location.href,
        method: 'POST',
        data: {
            action: 'delete_user',
            user_id: userId,
            ajax: true
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                showAlert(response.message, 'success');
                setTimeout(refreshData, 1000);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function() {
            hideLoading();
            showAlert('Failed to delete user. Please try again.', 'error');
        }
    });
}

// Event log functions
function refreshEvents() {
    refreshData();
}

function clearEventLog() {
    if (!confirm('Are you sure you want to clear the event log?')) {
        return;
    }
    
    showLoading();
    
    $.ajax({
        url: window.location.href,
        method: 'POST',
        data: {
            action: 'clear_events',
            ajax: true
        },
        success: function(response) {
            hideLoading();
            
            if (response.success) {
                showAlert(response.message, 'success');
                setTimeout(refreshData, 1000);
            } else {
                showAlert(response.message, 'error');
            }
        },
        error: function() {
            hideLoading();
            showAlert('Failed to clear event log. Please try again.', 'error');
        }
    });
}

// Sensor functions
function refreshSensors() {
    refreshData();
}

// Utility functions
function showLoading() {
    $('#loadingOverlay').show();
}

function hideLoading() {
    $('#loadingOverlay').hide();
}

function filterTable(table, query) {
    table.find('tbody tr').each(function() {
        const text = $(this).text().toLowerCase();
        $(this).toggle(text.indexOf(query) > -1);
    });
}

// Export functions
function exportData(type, format) {
    window.location.href = `?action=export&type=${type}&format=${format}`;
}

// Chart functions
function updateSensorChart(sensorData) {
    if (typeof sensorChart !== 'undefined') {
        sensorChart.data = sensorData;
        sensorChart.update();
    }
}

// Notification functions
function showNotification(message, type = 'info') {
    // Create a simple notification
    const notification = $(`
        <div class="notification notification-${type}">
            <i class="fas fa-${type === 'error' ? 'exclamation-triangle' : 'info-circle'}"></i>
            <span>${message}</span>
            <button onclick="this.parentElement.remove()">&times;</button>
        </div>
    `);
    
    $('body').append(notification);
    
    // Auto-remove after 5 seconds
    setTimeout(() => {
        notification.fadeOut(500, function() {
            $(this).remove();
        });
    }, 5000);
}

// Keyboard shortcuts
$(document).keydown(function(e) {
    // Ctrl+R to refresh
    if (e.ctrlKey && e.keyCode === 82) {
        e.preventDefault();
        refreshData();
    }
    
    // Escape to close modals
    if (e.keyCode === 27) {
        $('.modal').hide();
    }
});

// Auto-save settings
let settingsTimeout;
$('#settingsForm input, #settingsForm select').on('change', function() {
    clearTimeout(settingsTimeout);
    settingsTimeout = setTimeout(function() {
        // Auto-save settings after 2 seconds of inactivity
        $('#settingsForm').submit();
    }, 2000);
});

// Responsive table handling
function handleResponsiveTables() {
    $('.users-table').each(function() {
        const table = $(this);
        const wrapper = $('<div class="table-responsive"></div>');
        
        if (table.width() > wrapper.width()) {
            table.wrap(wrapper);
        }
    });
}

// Initialize responsive tables on load and resize
$(window).on('load resize', handleResponsiveTables);

// Performance monitoring
let lastRefreshTime = Date.now();

function logPerformance(action) {
    const now = Date.now();
    const timeSinceLastRefresh = now - lastRefreshTime;
    
    console.log(`Performance: ${action} took ${timeSinceLastRefresh}ms`);
    lastRefreshTime = now;
}

// Error handling
window.addEventListener('error', function(e) {
    console.error('JavaScript error:', e.error);
    showAlert('An error occurred. Please check the console for details.', 'error');
});

// Service Worker registration (for offline functionality)
if ('serviceWorker' in navigator) {
    navigator.serviceWorker.register('/plugins/supermicro-ipmi/sw.js')
        .then(function(registration) {
            console.log('Service Worker registered successfully');
        })
        .catch(function(error) {
            console.log('Service Worker registration failed:', error);
        });
} 