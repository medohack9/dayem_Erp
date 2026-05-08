const csrfToken = document.querySelector('meta[name="csrf-token"]')?.content || '';

async function fetchApi(url, options = {}) {
    const method = (options.method || 'GET').toUpperCase();
    const headers = { ...options.headers };

    if (method !== 'GET') {
        headers['X-CSRF-Token'] = csrfToken;

        if (options.body instanceof FormData) {
            options.body.append('_csrf_token', csrfToken);
        } else if (options.body && typeof options.body === 'object') {
            const body = { ...options.body, _csrf_token: csrfToken };
            options.body = JSON.stringify(body);
            headers['Content-Type'] = 'application/json';
        }
    }

    const response = await fetch(url, { ...options, headers });

    if (response.headers.get('content-type')?.includes('application/json')) {
        return response.json();
    }

    return response;
}

function showNotification(message, type = 'success') {
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
    };

    const colorClass = colors[type] || colors.success;

    const notification = document.createElement('div');
    notification.className = `fixed top-4 left-4 z-50 ${colorClass} text-white px-6 py-3 rounded-lg shadow-lg transition-opacity duration-300`;
    notification.textContent = message;
    document.body.appendChild(notification);

    setTimeout(() => {
        notification.style.opacity = '0';
        setTimeout(() => notification.remove(), 300);
    }, 3000);
}