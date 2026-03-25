<?php
// test_login.php - Simple test page
?>
<!DOCTYPE html>
<html>
<head>
    <title>Test Login</title>
    <style>
        body { font-family: Arial; padding: 20px; }
        input { display: block; margin: 10px 0; padding: 8px; width: 200px; }
        button { padding: 10px 20px; background: #4CAF50; color: white; border: none; cursor: pointer; }
        pre { background: #f4f4f4; padding: 10px; }
    </style>
</head>
<body>
    <h2>Test Login</h2>
    
    <div>
        <h3>Test Credentials:</h3>
        <p>Username: testuser<br>Password: password123</p>
        <p>Username: admin<br>Password: admin123</p>
    </div>
    
    <input type="text" id="username" placeholder="Username" value="testuser">
    <input type="password" id="password" placeholder="Password" value="password123">
    <button onclick="testLogin()">Test Login</button>
    
    <h3>Response:</h3>
    <pre id="result"></pre>
    
    <script>
    async function testLogin() {
        const username = document.getElementById('username').value;
        const password = document.getElementById('password').value;
        
        document.getElementById('result').textContent = 'Loading...';
        
        try {
            const response = await fetch('login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                },
                body: JSON.stringify({ username, password })
            });
            
            const text = await response.text();
            document.getElementById('result').textContent = text;
            
            try {
                const data = JSON.parse(text);
                if (data.success) {
                    alert('✅ Login successful!');
                } else {
                    alert('❌ Login failed: ' + data.message);
                }
            } catch(e) {
                alert('Invalid JSON response');
            }
        } catch(error) {
            document.getElementById('result').textContent = 'Error: ' + error.message;
        }
    }
    </script>
</body>
</html>