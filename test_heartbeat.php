<!DOCTYPE html>
<html>
<head>
    <title>Heartbeat Test</title>
    <style>
        body { font-family: Arial; padding: 20px; background: #f5f5f5; }
        .log { background: white; padding: 10px; margin: 10px 0; border-left: 3px solid #007bff; }
        .success { border-color: #28a745; }
        .error { border-color: #dc3545; }
        h1 { color: #333; }
        .info { background: #d1ecf1; padding: 10px; border-radius: 5px; margin: 20px 0; }
    </style>
</head>
<body>
    <h1>🔍 Online Status Heartbeat Test</h1>
    
    <div class="info">
        <strong>This page tests if the heartbeat system is working.</strong><br>
        You should see heartbeat updates every 30 seconds below.
    </div>
    
    <button onclick="testHeartbeat()">Send Heartbeat Now</button>
    <button onclick="checkStatus()">Check My Online Status</button>
    <button onclick="clearLogs()">Clear Logs</button>
    
    <h2>Heartbeat Logs:</h2>
    <div id="logs"></div>
    
    <h2>Admin Status:</h2>
    <div id="status"></div>

    <script>
        let heartbeatInterval = null;
        
        // Auto-start heartbeat
        startHeartbeat();
        
        function addLog(message, type = 'info') {
            const logs = document.getElementById('logs');
            const log = document.createElement('div');
            log.className = 'log ' + type;
            log.innerHTML = `<strong>${new Date().toLocaleTimeString()}</strong>: ${message}`;
            logs.insertBefore(log, logs.firstChild);
            
            // Keep only last 10 logs
            while (logs.children.length > 10) {
                logs.removeChild(logs.lastChild);
            }
        }
        
        function startHeartbeat() {
            addLog('Starting heartbeat system...', 'info');
            
            // Send immediately
            testHeartbeat();
            
            // Then every 30 seconds
            heartbeatInterval = setInterval(testHeartbeat, 30000);
        }
        
        function testHeartbeat() {
            fetch('api/admin_heartbeat.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    addLog(`✅ Heartbeat successful! Online count: ${data.online_count}, Admin ID: ${data.admin_id}`, 'success');
                } else {
                    addLog(`❌ Heartbeat failed: ${data.message}`, 'error');
                }
            })
            .catch(error => {
                addLog(`❌ Heartbeat error: ${error.message}`, 'error');
            });
        }
        
        function checkStatus() {
            fetch('api/admin_management_enhanced.php?action=list')
            .then(response => response.json())
            .then(data => {
                const statusDiv = document.getElementById('status');
                
                if (data.success && data.admins) {
                    let html = '<table border="1" cellpadding="10" style="width:100%; background:white;">';
                    html += '<tr><th>ID</th><th>Username</th><th>Last Activity</th><th>Last Seen</th></tr>';
                    
                    data.admins.forEach(admin => {
                        const rowColor = admin.is_currently_active ? '#d4edda' : '#f8f9fa';
                        const icon = admin.is_currently_active ? '🟢' : '⚫';
                        const lastSeenText = admin.is_currently_active ? `<strong>${icon} ${admin.last_seen}</strong>` : `${icon} ${admin.last_seen}`;
                        html += `<tr style="background:${rowColor}">`;
                        html += `<td>${admin.id}</td>`;
                        html += `<td><strong>${admin.username}</strong></td>`;
                        html += `<td>${admin.last_activity || 'Never'}</td>`;
                        html += `<td>${lastSeenText}</td>`;
                        html += '</tr>';
                    });
                    
                    html += '</table>';
                    statusDiv.innerHTML = html;
                    addLog(`✅ Status refreshed: ${data.admins.length} admins found`, 'success');
                } else {
                    statusDiv.innerHTML = `<div class="log error">Error: ${data.message}</div>`;
                    addLog(`❌ Failed to get status: ${data.message}`, 'error');
                }
            })
            .catch(error => {
                addLog(`❌ Status check error: ${error.message}`, 'error');
            });
        }
        
        function clearLogs() {
            document.getElementById('logs').innerHTML = '';
        }
        
        // Auto-refresh status every 60 seconds
        setInterval(checkStatus, 60000);
        
        // Initial status check
        checkStatus();
    </script>
</body>
</html>
