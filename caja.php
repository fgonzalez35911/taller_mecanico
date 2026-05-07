<?php
session_start();
require_once 'conexion.php';
require_once 'header.php';

if(!tienePermiso('ver_caja', $pdo)) {
    echo "<div class='glass-card'><h3>Acceso Denegado</h3></div>"; require_once 'footer.php'; exit;
}
?>

<div class="glass-card" style="margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <h2>Control de Caja Diaria</h2>
        <div style="display: flex; gap: 10px;">
            <button class="btn-ripple" onclick="exportarCSV()" style="background-color: var(--color-info); color: #000;">📥 Exportar Excel</button>
            <?php if(tienePermiso('movimiento_caja', $pdo)): ?>
                <button class="btn-ripple" onclick="nuevoMovimiento()" style="background-color: var(--color-success); color: #000;">+ Nuevo Movimiento</button>
            <?php endif; ?>
        </div>
    </div>

    <div style="margin-top: 20px; display: flex; gap: 20px; font-size: 1.2rem; font-weight: bold; flex-wrap: wrap;">
        <div style="padding: 10px; background: var(--color-success); color: #000; border-radius: 5px;">Ingresos: $<span id="lblIngresos">0.00</span></div>
        <div style="padding: 10px; background: var(--color-danger); color: #000; border-radius: 5px;">Egresos: $<span id="lblEgresos">0.00</span></div>
        <div style="padding: 10px; background: var(--color-warning); color: #000; border-radius: 5px;">Saldo Total: $<span id="lblSaldo">0.00</span></div>
    </div>

    <div style="margin-top: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;" id="tablaCaja">
            <thead>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <th style="padding: 10px;">Fecha</th>
                    <th style="padding: 10px;">Concepto</th>
                    <th style="padding: 10px;">Tipo</th>
                    <th style="padding: 10px;">Monto</th>
                    <th style="padding: 10px;">Usuario</th>
                </tr>
            </thead>
            <tbody id="tbodyCaja">
                <tr><td colspan="5" style="text-align: center; padding: 20px;">Cargando movimientos...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => { cargarCaja(); });

let datosCajaParaExportar = [];

function cargarCaja() {
    fetch('ajax_caja.php?accion=listar')
    .then(res => res.json())
    .then(data => {
        datosCajaParaExportar = data.movimientos;
        let html = '';
        if(data.movimientos.length === 0) {
            html = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No hay movimientos</td></tr>';
        } else {
            data.movimientos.forEach(m => {
                let colorTipo = m.tipo_movimiento === 'Ingreso' ? 'var(--color-success)' : 'var(--color-danger)';
                html += `<tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;">${m.fecha}</td>
                    <td style="padding: 10px;">${m.concepto}</td>
                    <td style="padding: 10px;"><span style="background: ${colorTipo}; color: #000; padding: 3px 8px; border-radius: 10px;">${m.tipo_movimiento}</span></td>
                    <td style="padding: 10px; font-weight: bold;">$${m.monto}</td>
                    <td style="padding: 10px;">${m.usuario}</td>
                </tr>`;
            });
        }
        document.getElementById('tbodyCaja').innerHTML = html;
        document.getElementById('lblIngresos').innerText = data.totales.ingresos;
        document.getElementById('lblEgresos').innerText = data.totales.egresos;
        document.getElementById('lblSaldo').innerText = data.totales.saldo;
    });
}

function nuevoMovimiento() {
    Swal.fire({
        title: 'Registrar Movimiento',
        html: `
            <select id="sw-tipo" class="swal2-select" style="width: 90%;">
                <option value="Ingreso">Ingreso de Dinero (+)</option>
                <option value="Egreso">Egreso de Dinero (-)</option>
            </select>
            <input id="sw-concepto" class="swal2-input" placeholder="Concepto (Ej. Pago repuestos, Cobro cliente)">
            <input id="sw-monto" type="number" step="0.01" class="swal2-input" placeholder="Monto ($)">
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        background: 'var(--bg-color)',
        color: 'var(--text-color)',
        preConfirm: () => {
            return {
                tipo: document.getElementById('sw-tipo').value,
                concepto: document.getElementById('sw-concepto').value,
                monto: document.getElementById('sw-monto').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let fd = new FormData();
            fd.append('accion', 'crear');
            for (let key in result.value) { fd.append(key, result.value[key]); }

            fetch('ajax_caja.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') { Toast.fire({icon: 'success', title: 'Registrado'}); cargarCaja(); }
                else { Toast.fire({icon: 'error', title: res.msg}); }
            });
        }
    });
}

// Función 9: Exportación Nativa a CSV sin librerías
function exportarCSV() {
    if(datosCajaParaExportar.length === 0) {
        Toast.fire({icon: 'error', title: 'No hay datos para exportar'}); return;
    }
    let csvContent = "data:text/csv;charset=utf-8,Fecha,Concepto,Tipo,Monto,Usuario\n";
    datosCajaParaExportar.forEach(m => {
        let fila = `${m.fecha},"${m.concepto}",${m.tipo_movimiento},${m.monto},"${m.usuario}"`;
        csvContent += fila + "\n";
    });
    let encodedUri = encodeURI(csvContent);
    let link = document.createElement("a");
    link.setAttribute("href", encodedUri);
    link.setAttribute("download", "Caja_Diaria_" + new Date().toISOString().split('T')[0] + ".csv");
    document.body.appendChild(link);
    link.click();
    document.body.removeChild(link);
}
</script>
<?php require_once 'footer.php'; ?>