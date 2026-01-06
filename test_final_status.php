<!DOCTYPE html>
<html>
<head>
    <title>Final Status Test</title>
    <style>
        body { font-family: Arial, sans-serif; margin: 20px; }
        .test-result { margin: 10px 0; padding: 10px; border-radius: 5px; }
        .success { background: #d4edda; color: #155724; }
        .error { background: #f8d7da; color: #721c24; }
        pre { background: #f8f9fa; padding: 10px; border-radius: 3px; }
    </style>
</head>
<body>
    <h1>Final Status Switch Test</h1>
    
    <div id="testResult"></div>
    
    <h2>Test Status Switch Updates</h2>
    <button onclick="testStatusUpdate(1, 'inactive')">Test Update Office 1 to Inactive</button>
    <button onclick="testStatusUpdate(1, 'active')">Test Update Office 1 to Active</button>
    <button onclick="testStatusUpdate(2, 'inactive')">Test Update Office 2 to Inactive</button>
    <button onclick="testStatusUpdate(2, 'active')">Test Update Office 2 to Active</button>
    
    <h3>Response:</h3>
    <pre id="responseOutput"></pre>
    
    <script>
        async function testStatusUpdate(officeId, newStatus) {
            document.getElementById('testResult').innerHTML = '<div class="test-result">Testing...</div>';
            document.getElementById('responseOutput').textContent = '';
            
            try {
                const response = await fetch('SYSTEM_ADMIN/ajax/update_office_status.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/x-www-form-urlencoded',
                    },
                    body: `office_id=${officeId}&status=${newStatus}`
                });
                
                const responseText = await response.text();
                document.getElementById('responseOutput').textContent = responseText;
                
                console.log('Raw response:', responseText);
                
                // Try to parse as JSON
                try {
                    const data = JSON.parse(responseText);
                    console.log('Parsed data:', data);
                    
                    if (data.success) {
                        document.getElementById('testResult').innerHTML = 
                            '<div class="test-result success">✅ Success: ' + data.message + '</div>';
                    } else {
                        document.getElementById('testResult').innerHTML = 
                            '<div class="test-result error">❌ Error: ' + data.message + '</div>';
                    }
                } catch (parseError) {
                    document.getElementById('testResult').innerHTML = 
                        '<div class="test-result error">❌ JSON Parse Error: ' + parseError.message + '</div>';
                }
                
            } catch (error) {
                console.error('Test error:', error);
                document.getElementById('testResult').innerHTML = 
                    '<div class="test-result error">❌ Network Error: ' + error.message + '</div>';
            }
        }
    </script>
</body>
</html>
