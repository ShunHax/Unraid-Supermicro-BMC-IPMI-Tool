#!/bin/bash
#
# Supermicro IPMI Plugin - Monitoring Script
# This script runs via cron to monitor BMC status and log issues
#

# Configuration
PLUGIN_DIR="/usr/local/emhttp/plugins/supermicro-ipmi"
LOG_FILE="/var/log/plugins/supermicro-ipmi.log"
CONFIG_FILE="/var/local/plugins/supermicro-ipmi/config.json"
IPMICFG_PATH="/usr/local/sbin/ipmicfg"

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
NC='\033[0m' # No Color

# Log function
log_message() {
    local level="$1"
    local message="$2"
    local timestamp=$(date '+%Y-%m-%d %H:%M:%S')
    echo "[$timestamp] [$level] $message" >> "$LOG_FILE"
}

# Check if IPMICFG exists
check_ipmicfg() {
    if [ ! -f "$IPMICFG_PATH" ]; then
        log_message "ERROR" "IPMICFG not found at $IPMICFG_PATH"
        return 1
    fi
    
    if [ ! -x "$IPMICFG_PATH" ]; then
        log_message "ERROR" "IPMICFG is not executable"
        return 1
    fi
    
    return 0
}

# Load configuration
load_config() {
    if [ ! -f "$CONFIG_FILE" ]; then
        log_message "ERROR" "Configuration file not found: $CONFIG_FILE"
        return 1
    fi
    
    # Parse JSON using jq if available, otherwise use basic parsing
    if command -v jq >/dev/null 2>&1; then
        LOCAL_BMC_ENABLED=$(jq -r '.local_bmc.enabled' "$CONFIG_FILE" 2>/dev/null)
        REMOTE_BMC_ENABLED=$(jq -r '.remote_bmc.enabled' "$CONFIG_FILE" 2>/dev/null)
        REMOTE_BMC_HOST=$(jq -r '.remote_bmc.host' "$CONFIG_FILE" 2>/dev/null)
        REMOTE_BMC_USERNAME=$(jq -r '.remote_bmc.username' "$CONFIG_FILE" 2>/dev/null)
        REMOTE_BMC_PASSWORD=$(jq -r '.remote_bmc.password' "$CONFIG_FILE" 2>/dev/null)
    else
        # Basic parsing without jq
        LOCAL_BMC_ENABLED=$(grep -o '"enabled": *true' "$CONFIG_FILE" | grep -q "local_bmc" && echo "true" || echo "false")
        REMOTE_BMC_ENABLED=$(grep -o '"enabled": *true' "$CONFIG_FILE" | grep -q "remote_bmc" && echo "true" || echo "false")
        REMOTE_BMC_HOST=$(grep -o '"host": *"[^"]*"' "$CONFIG_FILE" | cut -d'"' -f4)
        REMOTE_BMC_USERNAME=$(grep -o '"username": *"[^"]*"' "$CONFIG_FILE" | cut -d'"' -f4)
        REMOTE_BMC_PASSWORD=$(grep -o '"password": *"[^"]*"' "$CONFIG_FILE" | cut -d'"' -f4)
    fi
}

# Test BMC connection
test_bmc_connection() {
    local connection_type="$1"
    
    if [ "$connection_type" = "local" ]; then
        if [ "$LOCAL_BMC_ENABLED" != "true" ]; then
            return 1
        fi
        
        # Test local BMC
        if "$IPMICFG_PATH" -s >/dev/null 2>&1; then
            log_message "INFO" "Local BMC connection successful"
            return 0
        else
            log_message "ERROR" "Local BMC connection failed"
            return 1
        fi
    else
        if [ "$REMOTE_BMC_ENABLED" != "true" ] || [ -z "$REMOTE_BMC_HOST" ]; then
            return 1
        fi
        
        # Test remote BMC
        if "$IPMICFG_PATH" -h "$REMOTE_BMC_HOST" -u "$REMOTE_BMC_USERNAME" -pw "$REMOTE_BMC_PASSWORD" -s >/dev/null 2>&1; then
            log_message "INFO" "Remote BMC connection successful to $REMOTE_BMC_HOST"
            return 0
        else
            log_message "ERROR" "Remote BMC connection failed to $REMOTE_BMC_HOST"
            return 1
        fi
    fi
}

# Check system power status
check_power_status() {
    local connection_type="$1"
    local power_status
    
    if [ "$connection_type" = "local" ]; then
        power_status=$("$IPMICFG_PATH" -s 2>/dev/null | grep -i "Power Status" | grep -i "on")
    else
        power_status=$("$IPMICFG_PATH" -h "$REMOTE_BMC_HOST" -u "$REMOTE_BMC_USERNAME" -pw "$REMOTE_BMC_PASSWORD" -s 2>/dev/null | grep -i "Power Status" | grep -i "on")
    fi
    
    if [ -n "$power_status" ]; then
        log_message "INFO" "System power is ON"
        return 0
    else
        log_message "WARNING" "System power is OFF"
        return 1
    fi
}

# Check sensor status
check_sensors() {
    local connection_type="$1"
    local sensors_output
    local critical_count=0
    local warning_count=0
    
    if [ "$connection_type" = "local" ]; then
        sensors_output=$("$IPMICFG_PATH" -sensor 2>/dev/null)
    else
        sensors_output=$("$IPMICFG_PATH" -h "$REMOTE_BMC_HOST" -u "$REMOTE_BMC_USERNAME" -pw "$REMOTE_BMC_PASSWORD" -sensor 2>/dev/null)
    fi
    
    if [ -z "$sensors_output" ]; then
        log_message "ERROR" "Failed to retrieve sensor data"
        return 1
    fi
    
    # Count critical and warning sensors
    while IFS= read -r line; do
        if echo "$line" | grep -qi "critical\|error\|failed"; then
            ((critical_count++))
            log_message "CRITICAL" "Sensor issue: $line"
        elif echo "$line" | grep -qi "warning\|caution"; then
            ((warning_count++))
            log_message "WARNING" "Sensor warning: $line"
        fi
    done <<< "$sensors_output"
    
    if [ $critical_count -gt 0 ]; then
        log_message "CRITICAL" "Found $critical_count critical sensor(s)"
        return 2
    elif [ $warning_count -gt 0 ]; then
        log_message "WARNING" "Found $warning_count warning sensor(s)"
        return 1
    else
        log_message "INFO" "All sensors are normal"
        return 0
    fi
}

# Check event log for new critical events
check_events() {
    local connection_type="$1"
    local events_output
    local critical_events=0
    
    if [ "$connection_type" = "local" ]; then
        events_output=$("$IPMICFG_PATH" -sel 2>/dev/null | tail -10)
    else
        events_output=$("$IPMICFG_PATH" -h "$REMOTE_BMC_HOST" -u "$REMOTE_BMC_USERNAME" -pw "$REMOTE_BMC_PASSWORD" -sel 2>/dev/null | tail -10)
    fi
    
    if [ -z "$events_output" ]; then
        log_message "ERROR" "Failed to retrieve event log"
        return 1
    fi
    
    # Check for critical events in the last 10 entries
    while IFS= read -r line; do
        if echo "$line" | grep -qi "critical\|error\|fatal"; then
            ((critical_events++))
            log_message "CRITICAL" "Critical event: $line"
        fi
    done <<< "$events_output"
    
    if [ $critical_events -gt 0 ]; then
        log_message "CRITICAL" "Found $critical_events critical event(s) in recent log"
        return 1
    fi
    
    return 0
}

# Check system health
check_system_health() {
    local connection_type="$1"
    local overall_status=0
    
    # Check power status
    if ! check_power_status "$connection_type"; then
        overall_status=1
    fi
    
    # Check sensors
    local sensor_status
    check_sensors "$connection_type"
    sensor_status=$?
    if [ $sensor_status -eq 2 ]; then
        overall_status=2
    elif [ $sensor_status -eq 1 ] && [ $overall_status -eq 0 ]; then
        overall_status=1
    fi
    
    # Check events
    if ! check_events "$connection_type"; then
        if [ $overall_status -eq 0 ]; then
            overall_status=1
        fi
    fi
    
    return $overall_status
}

# Send notification (placeholder for future implementation)
send_notification() {
    local level="$1"
    local message="$2"
    
    # This could be expanded to send email, push notifications, etc.
    log_message "NOTIFICATION" "[$level] $message"
    
    # Example: Send to Unraid notification system if available
    if [ -f "/usr/local/emhttp/plugins/dynamix/scripts/notify" ]; then
        /usr/local/emhttp/plugins/dynamix/scripts/notify -s "Supermicro IPMI" -d "$message" -i "$level"
    fi
}

# Main monitoring function
main() {
    log_message "INFO" "Starting BMC monitoring check"
    
    # Check if IPMICFG is available
    if ! check_ipmicfg; then
        log_message "ERROR" "IPMICFG not available, skipping monitoring"
        exit 1
    fi
    
    # Load configuration
    if ! load_config; then
        log_message "ERROR" "Failed to load configuration"
        exit 1
    fi
    
    local connection_established=false
    local health_status=0
    
    # Try local BMC first
    if [ "$LOCAL_BMC_ENABLED" = "true" ]; then
        if test_bmc_connection "local"; then
            connection_established=true
            check_system_health "local"
            health_status=$?
        fi
    fi
    
    # Try remote BMC if local failed or not enabled
    if [ "$connection_established" = false ] && [ "$REMOTE_BMC_ENABLED" = "true" ]; then
        if test_bmc_connection "remote"; then
            connection_established=true
            check_system_health "remote"
            health_status=$?
        fi
    fi
    
    # Handle results
    if [ "$connection_established" = false ]; then
        log_message "ERROR" "No BMC connection established"
        send_notification "ERROR" "BMC connection failed"
        exit 1
    fi
    
    case $health_status in
        0)
            log_message "INFO" "System health check completed - All systems normal"
            ;;
        1)
            log_message "WARNING" "System health check completed - Warnings detected"
            send_notification "WARNING" "System warnings detected"
            ;;
        2)
            log_message "CRITICAL" "System health check completed - Critical issues detected"
            send_notification "CRITICAL" "Critical system issues detected"
            ;;
    esac
    
    log_message "INFO" "BMC monitoring check completed"
}

# Run main function
main "$@" 