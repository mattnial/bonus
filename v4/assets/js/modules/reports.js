async function printReport(type) {
    const btn = document.getElementById(`btn-rep-${type}`);
    if (btn) { btn.disabled = true; btn.innerHTML = 'Generando...'; }
    try {
        const res = await fetch(`${API_URL}/admin/get_report_data.php?type=${type}`);
        const data = await res.json();
        if (!data || data.length === 0) throw new Error("Sin datos");

        let headers = [], rows = "";
        if (type === 'tickets') headers = ["ID", "Fecha", "Cliente", "Asunto", "Estado"];
        if (type === 'debtors') headers = ["Cliente", "Cedula", "Mora", "Deuda"];

        data.forEach(d => {
            rows += `<tr>`;
            Object.values(d).slice(0, 5).forEach(val => rows += `<td>${val}</td>`);
            rows += `</tr>`;
        });

        const win = window.open('', '_blank', 'width=900,height=600');
        win.document.write(`<html><head><style>table{width:100%;border-collapse:collapse}th,td{border:1px solid #ccc;padding:8px;text-align:left}th{background:#eee}</style></head><body><h1>Reporte: ${type.toUpperCase()}</h1><table><thead><tr>${headers.map(h => `<th>${h}</th>`).join('')}</tr></thead><tbody>${rows}</tbody></table><script>window.onload=function(){window.print()}</script></body></html>`);
        win.document.close();
    } catch (e) { showToast("Error: " + e.message, "error"); }
    finally { if (btn) { btn.disabled = false; btn.innerHTML = 'Imprimir PDF'; } }
}