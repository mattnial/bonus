document.addEventListener('DOMContentLoaded', () => {
    // Si ya hay sesión, ir al dashboard
    if (localStorage.getItem('vilcanet_staff')) {
        window.location.href = CONFIG.endpoints.dashboard;
    }

    const loginForm = document.getElementById('loginForm');
    if (loginForm) {
        loginForm.addEventListener('submit', handleLogin);
    }
});

async function handleLogin(e) {
    e.preventDefault();

    // 1. Obtener referencias a los elementos
    const emailInput = document.getElementById('email');
    const passwordInput = document.getElementById('password');

    // AQUÍ ESTABA EL ERROR: Aseguramos que los IDs coincidan con el HTML
    const btn = document.getElementById('btnLogin');
    const btnText = document.getElementById('btnText'); // El span del texto
    const btnIcon = document.getElementById('btnIcon'); // El icono de carga
    const errorMsg = document.getElementById('errorMsg');

    // Verificación de seguridad: Si no encuentra el botón, detener para no dar error en consola
    if (!btn || !btnText || !btnIcon) {
        console.error("Error: No se encontraron los elementos del botón (btnLogin, btnText, btnIcon) en el HTML.");
        return;
    }

    // 2. UI Loading (Cambiar estado visual)
    btn.disabled = true;
    btnText.innerText = 'Verificando...'; // Cambiamos el texto
    btnIcon.classList.remove('hidden');   // Mostramos el spinner
    errorMsg.classList.add('hidden');     // Ocultamos errores previos

    try {
        const response = await fetch(CONFIG.endpoints.login, {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({
                email: emailInput.value,
                password: passwordInput.value
            })
        });

        const data = await response.json();

        if (response.ok) {
            localStorage.setItem('vilcanet_staff', JSON.stringify(data.user));
            window.location.href = CONFIG.endpoints.dashboard;
        } else {
            throw new Error(data.message || 'Error de acceso');
        }
    } catch (error) {
        // Restaurar estado del botón si hubo error
        errorMsg.innerText = error.message;
        errorMsg.classList.remove('hidden');
        btn.disabled = false;
        btnText.innerText = 'INGRESAR';
        btnIcon.classList.add('hidden');
    }
}