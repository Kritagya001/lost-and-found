// ==================== CONFIGURATION ====================
// IMPORTANT: Fix this path - remove any duplication
const API_BASE = 'http://localhost/lost-and-found/backend';
// If you're accessing via different URL, try these alternatives:
// const API_BASE = 'http://localhost/backend';
// const API_BASE = '/lost-and-found/backend';
// const API_BASE = '/backend';

// ==================== GLOBAL VARIABLES ====================
let selectedImage = null;

// ==================== LOAD ITEMS FUNCTION ====================
async function loadItems() {
    console.log('📋 Loading items...');
    console.log('API URL:', `${API_BASE}/get_items.php`);
    
    const itemList = document.getElementById('itemList');
    if (!itemList) return;
    
    // Show loading state
    itemList.innerHTML = '<div class="loading">Loading items...</div>';
    
    try {
        const response = await fetch(`${API_BASE}/get_items.php`);
        
        console.log('Response status:', response.status);
        console.log('Response URL:', response.url);
        
        if (!response.ok) {
            throw new Error(`HTTP error! status: ${response.status} - ${response.statusText}`);
        }
        
        const responseText = await response.text();
        console.log('Raw response:', responseText.substring(0, 200) + '...');
        
        let items;
        try {
            items = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            throw new Error('Invalid JSON response from server');
        }
        
        console.log('✅ Loaded items:', items);
        
        itemList.innerHTML = '';
        
        if (!items || items.length === 0) {
            itemList.innerHTML = '<div class="no-items">No items found</div>';
            return;
        }
        
        items.forEach(item => {
            displayItem(item, itemList);
        });
        
    } catch (error) {
        console.error('❌ Error:', error);
        itemList.innerHTML = `
            <div class="error-container">
                <div style="color: #dc2626; padding: 30px; text-align: center; background: #fee2e2; border-radius: 8px;">
                    <div style="font-size: 48px; margin-bottom: 15px;">⚠️</div>
                    <h3 style="margin-bottom: 10px;">Error Loading Items</h3>
                    <p style="margin-bottom: 15px;">${error.message}</p>
                    <p style="font-size: 14px; color: #666; margin-bottom: 15px;">
                        Check if backend is accessible at: ${API_BASE}/get_items.php
                    </p>
                    <button onclick="loadItems()" style="
                        padding: 10px 20px;
                        background: #3b82f6;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 14px;
                    ">Retry</button>
                    <button onclick="debugConnection()" style="
                        padding: 10px 20px;
                        background: #6b7280;
                        color: white;
                        border: none;
                        border-radius: 5px;
                        cursor: pointer;
                        font-size: 14px;
                        margin-left: 10px;
                    ">Debug</button>
                </div>
            </div>
        `;
    }
}

// Debug function to test connection
async function debugConnection() {
    alert('Testing connection to: ' + API_BASE + '/get_items.php\n\nCheck console for details (F12)');
    
    try {
        const response = await fetch(API_BASE + '/get_items.php');
        console.log('Debug - Response status:', response.status);
        console.log('Debug - Response headers:', [...response.headers.entries()]);
        
        const text = await response.text();
        console.log('Debug - Response text:', text.substring(0, 500));
        
        if (response.ok) {
            alert('✅ Connection successful! Status: ' + response.status);
        } else {
            alert('❌ Connection failed! Status: ' + response.status);
        }
    } catch (error) {
        console.error('Debug - Error:', error);
        alert('❌ Connection error: ' + error.message);
    }
}

// ==================== DISPLAY SINGLE ITEM ====================
function displayItem(item, container) {
    const itemCard = document.createElement('div');
    itemCard.className = 'item-card';
    itemCard.id = `item-${item.id}`;
    
    // Format date
    const itemDate = new Date(item.item_date);
    const formattedDate = itemDate.toLocaleDateString('en-US', {
        year: 'numeric',
        month: 'short',
        day: 'numeric'
    });
    
    // Check if item has image
    const hasImage = item.image_path && 
                    item.image_path !== '' && 
                    item.image_path !== 'null' &&
                    item.image_path !== 'undefined';
    
    const currentUserId = localStorage.getItem('user_id');
    const isOwner = currentUserId && item.user_id == currentUserId;
    
    // Status badge styling
    const statusColors = {
        'pending': { bg: '#fef3c7', color: '#92400e', text: 'Pending' },
        'found': { bg: '#d1fae5', color: '#065f46', text: 'Found' },
        'returned': { bg: '#d1fae5', color: '#065f46', text: 'Returned' }
    };
    
    const status = item.status || 'pending';
    const statusStyle = statusColors[status] || statusColors.pending;
    
    // Generate status buttons HTML based on item TYPE
    let statusButtonsHtml = '';
    if (isOwner) {
        if (item.type === 'Lost' && status === 'pending') {
            statusButtonsHtml = `
                <div style="margin-top: 10px; border-top: 1px dashed #e5e7eb; padding-top: 10px;">
                    <button onclick="updateItemStatus(${item.id}, 'found')" 
                            style="width: 100%; padding: 8px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">
                        ✓ Mark as Found
                    </button>
                </div>
            `;
        } else if (item.type === 'Found' && status === 'pending') {
            statusButtonsHtml = `
                <div style="margin-top: 10px; border-top: 1px dashed #e5e7eb; padding-top: 10px;">
                    <button onclick="updateItemStatus(${item.id}, 'returned')" 
                            style="width: 100%; padding: 8px; background: #10b981; color: white; border: none; border-radius: 6px; cursor: pointer; font-size: 13px; font-weight: 500;">
                        ✓ Mark as Returned
                    </button>
                </div>
            `;
        }
    }
    
    // Build image URL correctly
    let imageUrl = '';
    if (hasImage) {
        if (item.image_path.startsWith('http')) {
            imageUrl = item.image_path;
        } else if (item.image_path.startsWith('uploads/')) {
            imageUrl = `${API_BASE}/${item.image_path}`;
        } else {
            imageUrl = `${API_BASE}/uploads/${item.image_path}`;
        }
    }
    
    itemCard.innerHTML = `
        <div style="display: flex; justify-content: space-between; align-items: flex-start; margin-bottom: 10px;">
            <div style="display: flex; align-items: center; gap: 8px; flex-wrap: wrap; flex: 1;">
                <h3 style="margin: 0; font-size: 16px; color: #1f2937;">${escapeHtml(item.item_name)}</h3>
                <span style="background: ${statusStyle.bg}; color: ${statusStyle.color}; padding: 3px 10px; border-radius: 20px; font-size: 11px; font-weight: bold;">
                    ${statusStyle.text}
                </span>
            </div>
            
            <div style="display: flex; align-items: center; gap: 6px;">
                <span style="background: ${item.type === 'Lost' ? '#fef3c7' : '#d1fae5'}; 
                             color: ${item.type === 'Lost' ? '#92400e' : '#065f46'}; 
                             padding: 3px 10px; 
                             border-radius: 20px; 
                             font-size: 11px; 
                             font-weight: bold;">
                    ${escapeHtml(item.type)}
                </span>
                
                ${isOwner ? `
                <button class="delete-btn" 
                        onclick="deleteItem(${item.id}, '${escapeHtml(item.item_name)}')" 
                        style="background: #ef4444; color: white; border: none; padding: 4px 8px; border-radius: 4px; cursor: pointer; font-size: 11px; font-weight: bold;">
                    🗑️ Delete
                </button>
                ` : ''}
            </div>
        </div>
        
        <div style="display: flex; gap: 12px; flex-wrap: wrap;">
            ${hasImage ? `
            <div style="flex-shrink: 0;">
                <img src="${imageUrl}" 
                     alt="${escapeHtml(item.item_name)}" 
                     style="width: 80px; height: 80px; object-fit: cover; border-radius: 6px; cursor: pointer; border: 1px solid #e5e7eb;"
                     onclick="showImageModal('${imageUrl}')"
                     onerror="this.onerror=null; this.src='data:image/svg+xml,%3Csvg xmlns=\'http://www.w3.org/2000/svg\' width=\'80\' height=\'80\' viewBox=\'0 0 24 24\' fill=\'%23999\'%3E%3Cpath d=\'M4 5h16v14H4V5zm2 2v10h12V7H6zm2 2h8v6H8V9z\'/%3E%3C/svg%3E';">
            </div>
            ` : ''}
            
            <div style="flex-grow: 1; min-width: 180px;">
                <p style="margin: 4px 0; font-size: 13px;"><strong>Category:</strong> ${escapeHtml(item.category)}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Location:</strong> ${escapeHtml(item.location)}</p>
                <p style="margin: 4px 0; font-size: 13px;"><strong>Date:</strong> ${formattedDate}</p>
                ${item.item_time && item.item_time !== '00:00:00' ? 
                  `<p style="margin: 4px 0; font-size: 13px;"><strong>Time:</strong> ${item.item_time.substring(0, 5)}</p>` : ''}
            </div>
        </div>
        
        ${statusButtonsHtml}
        
        <div style="margin-top: 10px; padding-top: 8px; border-top: 1px solid #e5e7eb; font-size: 11px; color: #6b7280;">
            <p style="margin: 2px 0;">👤 Reported by: ${escapeHtml(item.reported_by || item.username || 'Anonymous')}</p>
            <p style="margin: 2px 0;">🕐 ${new Date(item.reported_at || Date.now()).toLocaleString()}</p>
        </div>
        
        <!-- CONTACT INFORMATION -->
        <div class="contact-info" style="margin-top: 10px; padding: 8px; background: #f3f4f6; border-radius: 6px; border-left: 3px solid #667eea;">
            <h4 style="margin: 0 0 5px 0; color: #374151; font-size: 12px;">👤 Contact Information</h4>
            <p style="margin: 4px 0; font-size: 11px; color: #4b5563;">
                <strong>Name:</strong> ${escapeHtml(item.contact_name || 'Not provided')}
            </p>
            <p style="margin: 4px 0; font-size: 11px; color: #4b5563;">
                <strong>Email:</strong> ${escapeHtml(item.contact_email || 'Not provided')}
            </p>
            ${(item.contact_phone && item.contact_phone !== '') ? `
            <p style="margin: 4px 0; font-size: 11px; color: #4b5563;">
                <strong>Phone:</strong> ${escapeHtml(item.contact_phone)}
            </p>
            ` : ''}
            ${(item.contact_note && item.contact_note !== '') ? `
            <p style="margin: 4px 0; font-size: 11px; color: #4b5563;">
                <strong>Note:</strong> ${escapeHtml(item.contact_note)}
            </p>
            ` : ''}
        </div>
    `;
    
    container.appendChild(itemCard);
}

// ==================== ADD ITEM FUNCTION ====================
async function addItem() {
    console.log('➕ Add item function called');
    
    const userId = localStorage.getItem("user_id");
    const username = localStorage.getItem("username");
    
    if (!userId) {
        alert('Please login first');
        window.location.href = "login.html";
        return;
    }
    
    // Get form values
    const itemName = document.getElementById('itemName')?.value.trim();
    const category = document.getElementById('category')?.value;
    const location = document.getElementById('location')?.value.trim();
    const date = document.getElementById('date')?.value;
    const time = document.getElementById('time')?.value;
    const type = document.getElementById('type')?.value;
    const contactName = document.getElementById('contactName')?.value.trim();
    const contactEmail = document.getElementById('contactEmail')?.value.trim();
    const contactPhone = document.getElementById('contactPhone')?.value.trim();
    const contactNote = document.getElementById('contactNote')?.value.trim();
    const imageFile = document.getElementById('itemImage')?.files[0];
    
    // Validate required fields
    if (!itemName || !category || !location || !date || !contactName || !contactEmail) {
        alert('Please fill all required fields including contact information');
        return;
    }

    // Validate email
    const emailRegex = /^[^\s@]+@[^\s@]+\.[^\s@]+$/;
    if (!emailRegex.test(contactEmail)) {
        alert('Please enter a valid email address');
        return;
    }

    // Validate date is not in future
    const today = new Date().toISOString().split('T')[0];
    if (date > today) {
        alert('The reported date cannot be in the future');
        return;
    }

    // Create FormData
    const formData = new FormData();
    
    formData.append('item_name', itemName);
    formData.append('category', category);
    formData.append('location', location);
    formData.append('item_date', date);
    formData.append('item_time', time || '00:00:00');
    formData.append('type', type);
    formData.append('user_id', userId);
    formData.append('username', username || 'User');
    formData.append('contact_name', contactName);
    formData.append('contact_email', contactEmail);
    formData.append('contact_phone', contactPhone || '');
    formData.append('contact_note', contactNote || '');
    
    if (imageFile) {
        formData.append('image', imageFile);
    }
    
    // Show loading state
    const submitBtn = document.getElementById('submitBtn');
    const originalText = submitBtn ? submitBtn.textContent : 'Submit';
    if (submitBtn) {
        submitBtn.textContent = 'Submitting...';
        submitBtn.disabled = true;
    }
    
    try {
        const response = await fetch(`${API_BASE}/add_item.php`, {
            method: 'POST',
            body: formData
        });
        
        const responseText = await response.text();
        console.log('📥 Response:', responseText);
        
        let data;
        try {
            data = JSON.parse(responseText);
        } catch (parseError) {
            console.error('JSON parse error:', parseError);
            alert('Server error: Invalid response');
            return;
        }
        
        if (data.success) {
            alert('✅ Item reported successfully!');
            document.getElementById('reportForm').reset();
            document.getElementById('imagePreview').innerHTML = '';
            setTimeout(() => {
                window.location.href = 'index.html';
            }, 1500);
        } else {
            alert('❌ ' + (data.message || 'Failed to submit report'));
        }
    } catch (error) {
        console.error('❌ Network error:', error);
        alert('Network error: ' + error.message);
    } finally {
        if (submitBtn) {
            submitBtn.textContent = originalText;
            submitBtn.disabled = false;
        }
    }
}

// ==================== DELETE ITEM ====================
async function deleteItem(itemId, itemName) {
    if (!confirm(`Are you sure you want to delete "${itemName}"?`)) {
        return;
    }
    
    const userId = localStorage.getItem('user_id');
    
    try {
        const response = await fetch(`${API_BASE}/delete_item.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: itemId, user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`✅ Item deleted successfully!`);
            loadItems(); // Reload the list
        } else {
            alert(`❌ ${data.message}`);
        }
    } catch (error) {
        console.error('Delete error:', error);
        alert('Delete failed: ' + error.message);
    }
}

// ==================== UPDATE ITEM STATUS ====================
async function updateItemStatus(itemId, newStatus) {
    const userId = localStorage.getItem('user_id');
    
    try {
        const response = await fetch(`${API_BASE}/update_status.php`, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ item_id: itemId, status: newStatus, user_id: userId })
        });
        
        const data = await response.json();
        
        if (data.success) {
            alert(`✅ ${data.message}`);
            loadItems(); // Reload the list
        } else {
            alert(`❌ ${data.message}`);
        }
    } catch (error) {
        console.error('Update error:', error);
        alert('Update failed: ' + error.message);
    }
}

// ==================== IMAGE FUNCTIONS ====================
function previewImage(event) {
    const file = event.target.files[0];
    const preview = document.getElementById('imagePreview');
    
    if (!file) {
        preview.innerHTML = '';
        return;
    }
    
    // Check file type - include webp
    const allowedTypes = ['image/jpeg', 'image/jpg', 'image/png', 'image/gif', 'image/webp'];
    if (!allowedTypes.includes(file.type)) {
        alert('Please select an image file (JPG, PNG, GIF, WEBP)');
        event.target.value = '';
        return;
    }
    
    if (file.size > 2 * 1024 * 1024) {
        alert('Image too large (max 2MB)');
        event.target.value = '';
        return;
    }
    
    const reader = new FileReader();
    reader.onload = function(e) {
        preview.innerHTML = `
            <div style="text-align: center;">
                <img src="${e.target.result}" alt="Preview" style="max-width: 200px; max-height: 200px; border-radius: 8px; margin: 10px 0;">
                <br>
                <button type="button" onclick="removeImage()" style="padding: 5px 15px; background: #ff4b4b; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Remove Image
                </button>
            </div>
        `;
    };
    reader.readAsDataURL(file);
}

function removeImage() {
    document.getElementById('itemImage').value = '';
    document.getElementById('imagePreview').innerHTML = '';
}

function showImageModal(imageUrl) {
    const modal = document.createElement('div');
    modal.style.cssText = `
        position: fixed;
        top: 0;
        left: 0;
        width: 100%;
        height: 100%;
        background: rgba(0,0,0,0.9);
        display: flex;
        justify-content: center;
        align-items: center;
        z-index: 10000;
        cursor: pointer;
    `;
    
    modal.innerHTML = `
        <img src="${imageUrl}" style="max-width: 90%; max-height: 90%; object-fit: contain;">
        <button onclick="this.parentElement.remove()" style="
            position: absolute;
            top: 20px;
            right: 20px;
            background: #ff4b4b;
            color: white;
            border: none;
            padding: 10px 15px;
            border-radius: 5px;
            cursor: pointer;
        ">✕ Close</button>
    `;
    
    document.body.appendChild(modal);
    
    modal.addEventListener('click', (e) => {
        if (e.target === modal) modal.remove();
    });
}

// ==================== HELPER FUNCTIONS ====================
function escapeHtml(text) {
    if (!text) return '';
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

function showError(message) {
    const itemList = document.getElementById('itemList');
    if (itemList) {
        itemList.innerHTML = `
            <div class="error" style="text-align: center; padding: 40px; color: #dc2626;">
                <div style="font-size: 48px; margin-bottom: 20px;">⚠️</div>
                <h3>Error Loading Items</h3>
                <p>${message}</p>
                <button onclick="loadItems()" style="margin-top: 20px; padding: 10px 20px; background: #3b82f6; color: white; border: none; border-radius: 5px; cursor: pointer;">
                    Retry
                </button>
            </div>
        `;
    }
}

function logout() {
    localStorage.clear();
    window.location.href = "login.html";
}

// ==================== INITIALIZATION ====================
document.addEventListener('DOMContentLoaded', function() {
    console.log('🚀 Initializing page...');
    console.log('API_BASE:', API_BASE);
    
    // Check if user is logged in
    const loggedIn = localStorage.getItem("logged") === "true";
    const currentPage = window.location.pathname;
    
    // Protect pages that require login
    if (!loggedIn && !currentPage.includes('login.html') && !currentPage.includes('register.html')) {
        window.location.href = "login.html";
        return;
    }
    
    // Set default date for forms
    if (document.getElementById('date')) {
        const today = new Date().toISOString().split('T')[0];
        document.getElementById('date').value = today;
        document.getElementById('date').max = today;
    }
    
    // Show username if logged in
    const username = localStorage.getItem("username");
    if (username) {
        const userDisplay = document.getElementById('currentUser');
        if (userDisplay) userDisplay.textContent = username;
    }
    
    // Load items if on main page
    if (document.getElementById('itemList')) {
        loadItems();
    }
    
    // Add form submit handler
    const reportForm = document.getElementById('reportForm');
    if (reportForm) {
        reportForm.addEventListener('submit', function(e) {
            e.preventDefault();
            addItem();
        });
    }
});

// ==================== EXPORT FUNCTIONS ====================
window.loadItems = loadItems;
window.addItem = addItem;
window.deleteItem = deleteItem;
window.updateItemStatus = updateItemStatus;
window.previewImage = previewImage;
window.removeImage = removeImage;
window.showImageModal = showImageModal;
window.logout = logout;
window.escapeHtml = escapeHtml;
window.debugConnection = debugConnection;

console.log('✅ script.js loaded successfully');