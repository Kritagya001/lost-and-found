// frontend/auth.js
console.log('✅ Auth.js loaded');

const API_BASE = 'http://localhost/lost-and-found/backend';

// ==================== LOGIN FUNCTION ====================
async function login() {
    console.log('🔑 Login function called');
    
    const username = document.getElementById("loginUser").value.trim();
    const password = document.getElementById("loginPass").value;
    const errorMsg = document.getElementById("error");

    errorMsg.textContent = '';
    errorMsg.className = '';

    if (!username || !password) {
        errorMsg.textContent = 'Please fill all fields';
        errorMsg.className = 'error';
        return;
    }

    console.log('📤 Sending to:', `${API_BASE}/login.php`);
    console.log('Username:', username);
    
    try {
        const response = await fetch(`${API_BASE}/login.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                password: password
            })
        });

        const responseText = await response.text();
        console.log('📥 Raw response:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('❌ JSON parse error:', parseError);
            errorMsg.textContent = 'Server error: Invalid response';
            errorMsg.className = 'error';
            return;
        }
        
        console.log('📊 Parsed data:', data);
        
        if (data.success) {
            localStorage.setItem("logged", "true");
            localStorage.setItem("user_id", data.user.id);
            localStorage.setItem("username", data.user.username);
            localStorage.setItem("email", data.user.email || '');
            localStorage.setItem("is_admin", data.user.is_admin ? "1" : "0");
            
            console.log('✅ Login successful! is_admin:', data.user.is_admin);
            
            errorMsg.textContent = 'Login successful! Redirecting...';
            errorMsg.className = 'success';
            
            setTimeout(() => {
                window.location.href = "index.html";
            }, 1000);
        } else {
            errorMsg.textContent = data.message || 'Login failed';
            errorMsg.className = 'error';
        }
    } catch (error) {
        console.error('❌ Network error:', error);
        errorMsg.textContent = `Connection failed: ${error.message}`;
        errorMsg.className = 'error';
    }
}

// ==================== REGISTER FUNCTION ====================
async function register() {
    console.log('📝 Register function called');
    
    const username = document.getElementById("regUser").value.trim();
    const email = document.getElementById("regEmail").value.trim();
    const password = document.getElementById("regPass").value;
    const msg = document.getElementById("msg");

    msg.textContent = '';
    msg.className = '';

    if (!username || !email || !password) {
        msg.textContent = 'Please fill all fields';
        msg.className = 'error';
        return;
    }

    // Username validation - only letters
    if (!/^[A-Za-z]+$/.test(username)) {
        msg.textContent = 'Username can only contain letters (no numbers or special characters)';
        msg.className = 'error';
        return;
    }

    if (username.length < 3) {
        msg.textContent = 'Username must be at least 3 characters';
        msg.className = 'error';
        return;
    }

    if (!validateEmail(email)) {
        msg.textContent = 'Please enter a valid email';
        msg.className = 'error';
        return;
    }

    // Password validation - must have number and special character
    const hasNumber = /[0-9]/.test(password);
    const hasSpecial = /[^A-Za-z0-9]/.test(password);
    
    if (!hasNumber || !hasSpecial) {
        msg.textContent = 'Password must contain at least 1 number and 1 special character (e.g., @, #, $, %)';
        msg.className = 'error';
        return;
    }

    if (password.length < 6) {
        msg.textContent = 'Password must be at least 6 characters';
        msg.className = 'error';
        return;
    }

    try {
        const response = await fetch(`${API_BASE}/register.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            },
            body: JSON.stringify({
                username: username,
                email: email,
                password: password
            })
        });

        const responseText = await response.text();
        console.log('📥 Raw response:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('❌ JSON parse error:', parseError);
            msg.textContent = 'Server error: Invalid response';
            msg.className = 'error';
            return;
        }
        
        if (data.success) {
            msg.textContent = data.message;
            msg.className = 'success';
            
            document.getElementById("regUser").value = '';
            document.getElementById("regEmail").value = '';
            document.getElementById("regPass").value = '';
            
            setTimeout(() => {
                window.location.href = "login.html";
            }, 2000);
        } else {
            msg.textContent = data.message;
            msg.className = 'error';
        }
    } catch (error) {
        console.error('❌ Network error:', error);
        msg.textContent = `Connection failed: ${error.message}`;
        msg.className = 'error';
    }
}

// ==================== LOGOUT FUNCTION ====================
async function logout() {
    console.log('🚪 Logout function called');
    
    try {
        await fetch(`${API_BASE}/logout.php`, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            }
        });
    } catch (error) {
        console.warn('⚠️ Backend logout failed:', error);
    }
    
    localStorage.clear();
    window.location.href = "login.html";
}

// ==================== HELPER FUNCTIONS ====================
function validateEmail(email) {
    const re = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    return re.test(email);
}

// Make functions globally available
window.login = login;
window.register = register;
window.logout = logout;