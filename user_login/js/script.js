document.addEventListener('DOMContentLoaded', function() {
    // Login form handling
    const loginForm = document.getElementById('loginForm');
    const errorMessage = document.getElementById('error-message');

    if (loginForm) {
        loginForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const username = document.getElementById('username').value;
            const password = document.getElementById('password').value;

            try {
                const response = await fetch('/Rental-house-management-system/user_login/api/login.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    },
                    body: JSON.stringify({ username, password })
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Store tenant data in localStorage
                    localStorage.setItem('tenant_data', JSON.stringify(data.user));
                    // Redirect to tenant dashboard
                    window.location.href = 'tenant_dashboard.php';
                } else {
                    errorMessage.textContent = data.message || 'Login failed. Please try again.';
                    errorMessage.classList.remove('d-none');
                }
            } catch (error) {
                console.error('Login error:', error);
                errorMessage.textContent = 'An error occurred. Please try again later.';
                errorMessage.classList.remove('d-none');
            }
        });
    }

    // Dashboard functionality
    const uploadForm = document.getElementById('uploadForm');
    if (uploadForm) {
        uploadForm.addEventListener('submit', async function(e) {
            e.preventDefault();
            
            const formData = new FormData(this);
            
            try {
                const response = await fetch('api/upload_payment.php', {
                    method: 'POST',
                    body: formData
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    alert('Payment screenshot uploaded successfully!');
                    location.reload();
                } else {
                    alert(data.message || 'Upload failed. Please try again.');
                }
            } catch (error) {
                console.error('Upload error:', error);
                alert('An error occurred. Please try again later.');
            }
        });
    }

    // Logout functionality
    const logoutBtn = document.getElementById('logoutBtn');
    if (logoutBtn) {
        logoutBtn.addEventListener('click', async function() {
            try {
                const response = await fetch('api/logout.php', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json'
                    }
                });

                if (!response.ok) {
                    throw new Error(`HTTP error! status: ${response.status}`);
                }

                const data = await response.json();

                if (data.success) {
                    // Clear tenant data from localStorage
                    localStorage.removeItem('tenant_data');
                    // Redirect to login page
                    window.location.href = 'index.html';
                }
            } catch (error) {
                console.error('Logout error:', error);
                alert('Logout failed. Please try again.');
            }
        });
    }
}); 