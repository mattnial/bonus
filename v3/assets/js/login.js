document.addEventListener('DOMContentLoaded', () => {

    // Toggle Password Visibility
    const togglePassword = document.querySelector('#togglePassword');
    const password = document.querySelector('#password');

    togglePassword.addEventListener('click', function () {
        const type = password.getAttribute('type') === 'password' ? 'text' : 'password';
        password.setAttribute('type', type);
        this.classList.toggle('fa-eye');
        this.classList.toggle('fa-eye-slash');
    });

    // Handle Login
    document.getElementById('loginForm').addEventListener('submit', async function (e) {
        e.preventDefault();

        const btn = document.getElementById('btnLogin');
        const originalContent = btn.innerHTML;
        btn.disabled = true;
        btn.innerHTML = '<i class="fas fa-circle-notch fa-spin"></i> VERIFICANDO...';

        const user = document.getElementById('username').value;
        const pass = document.getElementById('password').value;

        try {
            // Updated Path for v3
            const res = await fetch('api/auth/login.php', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json'
                },
                body: JSON.stringify({ email: user, password: pass })
            });

            const data = await res.json();

            if (data.success) {
                showToast('¡Bienvenido! Redirigiendo...', 'success');

                // Store session info if needed on client side (optional, PHP handles session)
                localStorage.setItem('vilcanet_staff', JSON.stringify(data.user));

                setTimeout(() => {
                    window.location.href = 'index.php';
                }, 1000);
            } else {
                showToast(data.message || 'Credenciales incorrectas', 'error');
                btn.disabled = false;
                btn.innerHTML = originalContent;
            }

        } catch (error) {
            console.error(error);
            showToast('Error de conexión con el servidor', 'error');
            btn.disabled = false;
            btn.innerHTML = originalContent;
        }
    });
});

// Toast Notification System
function showToast(message, type = 'info') {
    const container = document.getElementById('toast-container');
    const toast = document.createElement('div');

    // Colors
    const colors = {
        success: 'bg-green-500',
        error: 'bg-red-500',
        info: 'bg-blue-500',
        warning: 'bg-yellow-500'
    };

    const icon = {
        success: 'fa-check-circle',
        error: 'fa-exclamation-circle',
        info: 'fa-info-circle',
        warning: 'fa-exclamation-triangle'
    }[type];

    toast.className = `${colors[type]} text-white px-6 py-4 rounded-lg shadow-xl flex items-center gap-3 min-w-[300px] toast-enter`;
    toast.innerHTML = `
        <i class="fas ${icon} text-xl"></i>
        <p class="font-bold text-sm">${message}</p>
    `;

    container.appendChild(toast);

    // Animate In
    requestAnimationFrame(() => {
        toast.classList.remove('toast-enter');
        toast.classList.add('toast-enter-active');
    });

    // Remove after 3s
    setTimeout(() => {
        toast.classList.remove('toast-enter-active');
        toast.classList.add('toast-exit-active');
        setTimeout(() => {
            toast.remove();
        }, 300);
    }, 3000);
}