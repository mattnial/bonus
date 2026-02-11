window.initDashboard_default = function () {
    document.getElementById('dashboard-dynamic-content').innerHTML = `
        <div class="flex flex-col items-center justify-center h-96 text-center">
            <img src="assets/img/logo.png" class="h-20 opacity-20 mb-4 grayscale">
            <h2 class="text-2xl font-bold text-gray-300">Bienvenido a Vilcanet CRM</h2>
            <p class="text-gray-400 mt-2">Selecciona una opción del menú para comenzar.</p>
        </div>
    `;
};