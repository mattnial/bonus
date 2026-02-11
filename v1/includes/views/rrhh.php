<div class="p-6">
    <div class="flex justify-between items-center mb-6">
        <h2 class="text-2xl font-bold text-gray-800">Gestión de Personal</h2>
        <button onclick="loadRRHH()" class="bg-blue-100 text-blue-600 px-4 py-2 rounded-lg hover:bg-blue-200 transition font-bold">
            <i class="fas fa-sync"></i> Actualizar
        </button>
    </div>

    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6" id="rrhhGrid">
        <p class="text-gray-400 col-span-full text-center py-8">
            <i class="fas fa-spinner fa-spin"></i> Cargando personal...
        </p>
    </div>
</div>

<div id="rrhhModal" class="fixed inset-0 bg-black/60 backdrop-blur-sm hidden z-[95] flex items-center justify-center p-4">
    <div class="bg-white rounded-xl shadow-2xl w-full max-w-2xl overflow-hidden flex flex-col max-h-[90vh]">
        
        <div class="bg-gray-800 text-white px-6 py-4 flex justify-between items-center shrink-0">
            <h3 class="font-bold text-lg">
                <i class="fas fa-id-card"></i> Hoja de Vida: <span id="rrhhName">...</span>
            </h3>
            <button onclick="document.getElementById('rrhhModal').classList.add('hidden')" class="hover:text-gray-300 transition">
                <i class="fas fa-times text-xl"></i>
            </button>
        </div>

        <div class="flex-1 overflow-y-auto p-6">
            
            <div class="bg-red-50 border border-red-100 rounded-xl p-4 mb-6">
                <h4 class="text-red-700 font-bold text-sm uppercase mb-3">
                    <i class="fas fa-gavel"></i> Aplicar Sanción / Memo
                </h4>
                <form id="sanctionForm" onsubmit="handleSanctionSubmit(event)" class="space-y-3">
                    <div class="grid grid-cols-2 gap-4">
                        <select id="sancType" class="border rounded px-3 py-2 text-sm bg-white focus:ring-2 focus:ring-red-500 outline-none" onchange="toggleAmount(this.value)">
                            <option value="" disabled selected>-- Seleccione Tipo --</option>
                            <option value="LLAMADO_ATENCION">Llamado de Atención</option>
                            <option value="MEMO">Memorando Escrito</option>
                            <option value="MULTA">Multa Económica</option>
                            <option value="DESPIDO">Despido</option>
                        </select>
                        <input type="number" id="sancAmount" placeholder="Monto ($)" class="border rounded px-3 py-2 text-sm hidden focus:ring-2 focus:ring-red-500 outline-none" step="0.01">
                    </div>
                    
                    <textarea id="sancReason" rows="2" class="w-full border rounded px-3 py-2 text-sm focus:ring-2 focus:ring-red-500 outline-none" placeholder="Motivo de la sanción..." required></textarea>

                    <div class="flex items-center gap-2 mt-2">
                        <input type="file" id="sancFile" class="text-xs text-gray-500 file:mr-2 file:py-1 file:px-2 file:rounded file:border-0 file:bg-red-100 file:text-red-700 hover:file:bg-red-200 cursor-pointer" accept=".pdf,image/*">
                        <button type="submit" class="bg-red-600 hover:bg-red-700 text-white px-4 py-2 rounded text-sm font-bold ml-auto shadow transition">
                            REGISTRAR
                        </button>
                    </div>
                </form>
            </div>

            <h4 class="font-bold text-gray-700 mb-3 border-b pb-2">Historial de Sanciones</h4>
            <div id="sanctionsList" class="space-y-3">
                <p class="text-gray-400 text-sm text-center py-4">Cargando historial...</p>
            </div>
        </div>
    </div>
</div>