// State
const token = () => localStorage.getItem('airo_token');

// Section switching
function showLogin(message = null) {
    document.getElementById('login-section').classList.remove('hidden');
    document.getElementById('quotation-section').classList.add('hidden');
    if (message) showAlert('login-error', message);
}

function showQuotation() {
    document.getElementById('login-section').classList.add('hidden');
    document.getElementById('quotation-section').classList.remove('hidden');
    hideAlert('login-error');
}

function showAlert(id, message) {
    const el = document.getElementById(id);
    el.textContent = message;
    el.classList.add('visible');
}

function hideAlert(id) {
    const el = document.getElementById(id);
    el.textContent = '';
    el.classList.remove('visible');
}

function clearFieldErrors() {
    document.querySelectorAll('.field-error').forEach(el => {
        el.textContent = '';
        el.classList.remove('visible');
    });
}

function showFieldError(field, message) {
    const el = document.getElementById('error-' + field);
    if (el) {
        el.textContent = message;
        el.classList.add('visible');
    }
}

// Restrict date inputs to today or later
const today = new Date().toISOString().split('T')[0];
document.getElementById('start_date').min = today;
document.getElementById('end_date').min = today;

document.getElementById('start_date').addEventListener('change', (e) => {
    document.getElementById('end_date').min = e.target.value || today;
});

// On page load: restore session if token exists
if (token()) {
    showQuotation();
} else {
    showLogin();
}

// Login form submit
document.getElementById('login-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    hideAlert('login-error');

    const btn = document.getElementById('login-btn');
    btn.disabled = true;
    btn.textContent = 'Signing in…';

    try {
        const res = await fetch('/api/auth/login', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email:    document.getElementById('email').value,
                password: document.getElementById('password').value,
            }),
        });

        const data = await res.json();

        if (res.ok) {
            localStorage.setItem('airo_token', data.token);
            showQuotation();
        } else {
            showAlert('login-error', data.message || 'Login failed. Please try again.');
        }
    } catch {
        showAlert('login-error', 'Network error. Please check your connection.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Login';
    }
});

// Logout
document.getElementById('logout-link').addEventListener('click', () => {
    localStorage.removeItem('airo_token');
    document.getElementById('quotation-form').reset();
    document.getElementById('result-box').classList.remove('visible');
    clearFieldErrors();
    showLogin();
});

// Quotation form submit
document.getElementById('quotation-form').addEventListener('submit', async (e) => {
    e.preventDefault();
    clearFieldErrors();
    hideAlert('quotation-error');
    document.getElementById('result-box').classList.remove('visible');

    const btn = document.getElementById('quote-btn');
    btn.disabled = true;
    btn.textContent = 'Calculating…';

    try {
        const res = await fetch('/api/quotation', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'Authorization': 'Bearer ' + token(),
            },
            body: JSON.stringify({
                age:         document.getElementById('age').value,
                currency_id: document.getElementById('currency_id').value,
                start_date:  document.getElementById('start_date').value,
                end_date:    document.getElementById('end_date').value,
            }),
        });

        const data = await res.json();

        if (res.status === 201) {
            document.getElementById('result-total').textContent = data.total.toFixed(2) + ' ' + data.currency_id;
            document.getElementById('result-box').classList.add('visible');
        } else if (res.status === 422) {
            const errors = data.errors ?? {};
            Object.entries(errors).forEach(([field, messages]) => {
                showFieldError(field, messages[0]);
            });
            if (data.message && Object.keys(errors).length === 0) {
                showAlert('quotation-error', data.message);
            }
        } else if (res.status === 401) {
            localStorage.removeItem('airo_token');
            showLogin('Session expired, please log in again.');
        } else {
            showAlert('quotation-error', data.message || 'An unexpected error occurred.');
        }
    } catch {
        showAlert('quotation-error', 'Network error. Please check your connection.');
    } finally {
        btn.disabled = false;
        btn.textContent = 'Get Quote';
    }
});
