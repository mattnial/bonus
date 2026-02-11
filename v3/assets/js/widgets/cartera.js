/**
 * WIDGET: CARTERA DE CLIENTES (Patrón IIFE / Encapsulado)
 * Este archivo se protege a sí mismo para no mezclar variables con otros módulos.
 */

(function (global) {
    'use strict';

    // --- 1. CONFIGURACIÓN Y VARIABLES PRIVADAS (Nadie más puede tocarlas) ---
    const CONTAINER_ID = 'main-content';
    const API_URL_DEBTORS = 'api/admin/get_debtors.php'; // Ajusta si tu ruta es diferente

    console.log("📦 Módulo Cartera: Cargado en memoria.");

    // --- 2. LA FUNCIÓN PRINCIPAL DE RENDERIZADO ---
    // Esta función pintará la pantalla.
    const renderView = function () {
        console.log("🎨 Módulo Cartera: Intentando pintar interfaz...");

        const container = document.getElementById(CONTAINER_ID);
        if (!container) {
            console.error(`❌ Error Crítico: No encuentro el contenedor #${CONTAINER_ID}`);
            alert("Error: No se encontró el área de contenido principal.");
            return;
        }

        // Pintamos la estructura base
        container.innerHTML = `
            <div class="p-6 animate-fade-in-up">
                <div class="flex justify-between items-center mb-6">
                    <div>
                        <h1 class="text-2xl font-bold text-slate-800">Cartera de Deuda</h1>
                        <p class="text-slate-500 text-sm">Gestión de clientes y cortes</p>
                    </div>
                    <div class="flex gap-2">
                        <button id="btn-refresh-cartera" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700 transition shadow">
                            <i class="fas fa-sync-alt mr-2"></i>Actualizar
                        </button>
                    </div>
                </div>

                <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
                    <div class="bg-white p-4 rounded-lg border border-slate-200 shadow-sm flex items-center gap-4">
                        <div class="p-3 bg-red-100 text-red-600 rounded-full"><i class="fas fa-hand-holding-usd text-xl"></i></div>
                        <div>
                            <p class="text-xs text-slate-500 font-bold uppercase">Acción Requerida</p>
                            <p class="text-sm text-slate-700">Revisar lista abajo</p>
                        </div>
                    </div>
                </div>

                <div class="bg-white rounded-lg shadow-sm border border-slate-200 overflow-hidden">
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm text-left">
                            <thead class="bg-slate-50 text-slate-500 uppercase font-bold text-xs">
                                <tr>
                                    <th class="px-6 py-3">Cliente</th>
                                    <th class="px-6 py-3 text-right">Deuda</th>
                                    <th class="px-6 py-3 text-center">Gestión</th>
                                </tr>
                            </thead>
                            <tbody id="cartera-list-body">
                                <tr>
                                    <td colspan="3" class="text-center py-8">
                                        <i class="fas fa-spinner fa-spin text-blue-500 text-2xl"></i>
                                        <p class="mt-2 text-slate-400">Cargando datos...</p>
                                    </td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
        `;

        // Asignamos evento al botón actualizar (ahora que existe en el DOM)
        document.getElementById('btn-refresh-cartera').addEventListener('click', loadData);

        // Cargamos los datos reales
        loadData();
    };

    // --- 3. FUNCIONES DE DATOS (Privadas) ---
    const loadData = async function () {
        const tbody = document.getElementById('cartera-list-body');
        if (!tbody) return;

        try {
            // Simulamos carga si no hay API, o llamamos a la API real
            // const res = await fetch(API_URL_DEBTORS);
            // const data = await res.json();

            // NOTA: Para probar que FUNCIONA VISUALMENTE, usaré datos falsos momentáneos.
            // Cuando veas la tabla, descomenta el fetch de arriba.
            const data = [
                { id: 1, name: 'Prueba Cliente 1', debt: 50.00, phone: '0999999999' },
                { id: 2, name: 'Prueba Cliente 2', debt: 25.50, phone: '0988888888' }
            ];

            // Renderizar filas
            tbody.innerHTML = '';
            if (data.length === 0) {
                tbody.innerHTML = '<tr><td colspan="3" class="text-center py-4">Sin deudas pendientes.</td></tr>';
                return;
            }

            data.forEach(c => {
                tbody.innerHTML += `
                    <tr class="border-b hover:bg-slate-50">
                        <td class="px-6 py-4 font-medium text-slate-900">
                            ${c.name} <br> <span class="text-xs text-slate-400">${c.phone}</span>
                        </td>
                        <td class="px-6 py-4 text-right font-bold text-slate-700">$${c.debt}</td>
                        <td class="px-6 py-4 text-center">
                            <button onclick="alert('Gestionar ${c.name}')" class="text-blue-600 hover:text-blue-800 font-medium text-xs border border-blue-200 bg-blue-50 px-3 py-1 rounded">
                                Gestionar
                            </button>
                        </td>
                    </tr>
                `;
            });

        } catch (error) {
            console.error(error);
            tbody.innerHTML = '<tr><td colspan="3" class="text-center text-red-500 py-4">Error cargando datos API</td></tr>';
        }
    };

    // --- 4. EXPOSICIÓN PÚBLICA (El truco) ---
    // Aquí es donde "rompemos" un poquito la encapsulación para que Core.js pueda llamar a la función.

    global.renderCarteraView = renderView;

})(window);