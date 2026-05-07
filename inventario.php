<?php
session_start();
require_once 'conexion.php';
require_once 'header.php';

if(!tienePermiso('ver_stock', $pdo)) {
    echo "<div class='glass-card'><h3>Acceso Denegado</h3></div>"; require_once 'footer.php'; exit;
}
?>

<div class="glass-card" style="margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <h2>Inventario y Stock</h2>
        <?php if(tienePermiso('gestionar_stock', $pdo)): ?>
            <button class="btn-ripple" onclick="nuevoRepuesto()" style="background-color: var(--color-success); color: #000;">+ Nuevo Repuesto</button>
        <?php endif; ?>
    </div>

    <div style="margin-top: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <th style="padding: 10px;">Cód. Barras</th>
                    <th style="padding: 10px;">Repuesto</th>
                    <th style="padding: 10px;">Stock Actual</th>
                    <th style="padding: 10px;">Precio Venta</th>
                    <th style="padding: 10px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyInventario">
                <tr><td colspan="5" style="text-align: center; padding: 20px;">Cargando inventario...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => { cargarInventario(); });

function cargarInventario() {
    fetch('ajax_inventario.php?accion=listar')
    .then(res => res.json())
    .then(data => {
        let html = '';
        if(data.length === 0) {
            html = '<tr><td colspan="5" style="text-align: center; padding: 20px;">Inventario vacío</td></tr>';
        } else {
            data.forEach(r => {
                let colorStock = r.cantidad <= r.stock_minimo ? 'var(--color-danger)' : 'var(--color-success)';
                html += `<tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;">${r.codigo_barras || '-'}</td>
                    <td style="padding: 10px;"><strong>${r.nombre}</strong></td>
                    <td style="padding: 10px;"><span style="background: ${colorStock}; color: #000; padding: 3px 8px; border-radius: 10px;">${r.cantidad}</span></td>
                    <td style="padding: 10px;">$${r.precio_venta}</td>
                    <td style="padding: 10px; text-align: center;">
                        <button class="btn-ripple" onclick="eliminarRepuesto(${r.id_repuesto})" style="background-color: var(--color-danger); padding: 5px 10px; font-size: 0.8rem; color: #000;">X</button>
                    </td>
                </tr>`;
            });
        }
        document.getElementById('tbodyInventario').innerHTML = html;
    });
}

function nuevoRepuesto() {
    Swal.fire({
        title: 'Agregar Repuesto',
        html: `
            <div style="display: flex; gap: 5px;">
                <input id="sw-codigo" class="swal2-input" placeholder="Código de Barras" style="flex: 2; margin-top: 0;">
                <button onclick="escanearCodigo()" class="btn-ripple" style="background: #333; color: white; padding: 0 10px;">📷</button>
            </div>
            <input id="sw-nombre" class="swal2-input" placeholder="Nombre (Ej. Filtro de Aceite)">
            <div style="display: flex; gap: 10px;">
                <input id="sw-cant" type="number" class="swal2-input" placeholder="Stock Actual" style="margin-top: 0;">
                <input id="sw-min" type="number" class="swal2-input" placeholder="Stock Mínimo" value="5" style="margin-top: 0;">
            </div>
            <input id="sw-precio" type="number" step="0.01" class="swal2-input" placeholder="Precio de Venta ($)">
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        background: 'var(--bg-color)',
        color: 'var(--text-color)',
        preConfirm: () => {
            return {
                codigo: document.getElementById('sw-codigo').value,
                nombre: document.getElementById('sw-nombre').value,
                cant: document.getElementById('sw-cant').value,
                min: document.getElementById('sw-min').value,
                precio: document.getElementById('sw-precio').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let fd = new FormData();
            fd.append('accion', 'crear');
            for (let key in result.value) { fd.append(key, result.value[key]); }

            fetch('ajax_inventario.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') { Toast.fire({icon: 'success', title: 'Guardado'}); cargarInventario(); }
                else { Toast.fire({icon: 'error', title: res.msg}); }
            });
        }
    });
}

function escanearCodigo() {
    Swal.fire({
        title: 'Escaneando...',
        html: '<div id="lector-camara" style="width: 100%; height: 250px; overflow: hidden; border-radius: 10px;"></div>',
        showConfirmButton: false,
        showCancelButton: true,
        cancelButtonText: 'Cancelar',
        background: 'var(--bg-color)', color: 'var(--text-color)',
        didOpen: () => {
            Quagga.init({
                inputStream: { name: "Live", type: "LiveStream", target: document.querySelector('#lector-camara') },
                decoder: { readers: ["ean_reader", "code_128_reader", "upc_reader"] }
            }, function(err) {
                if (err) { Toast.fire({icon: 'error', title: 'Error de cámara'}); return; }
                Quagga.start();
            });
            Quagga.onDetected(function(result) {
                let code = result.codeResult.code;
                document.getElementById('sw-codigo').value = code;
                Quagga.stop();
                Swal.close();
                Toast.fire({icon: 'success', title: 'Código leído'});
            });
        },
        willClose: () => { Quagga.stop(); }
    });
}

function eliminarRepuesto(id) {
    let fd = new FormData(); fd.append('accion', 'eliminar'); fd.append('id', id);
    fetch('ajax_inventario.php', { method: 'POST', body: fd }).then(res => res.json()).then(res => {
        if(res.status === 'ok') cargarInventario();
    });
}
</script>
<?php require_once 'footer.php'; ?>