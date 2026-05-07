<?php
session_start();
require_once 'conexion.php';
require_once 'header.php';

if(!tienePermiso('ver_vehiculos', $pdo)) {
    echo "<div class='glass-card'><h3>Acceso Denegado</h3></div>";
    require_once 'footer.php';
    exit;
}
?>

<div class="glass-card" style="margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <h2>Recepción de Vehículos</h2>
        <?php if(tienePermiso('ingresar_vehiculo', $pdo)): ?>
            <button class="btn-ripple" onclick="ingresarVehiculo()" style="background-color: var(--color-success); color: #000;">+ Ingresar Vehículo</button>
        <?php endif; ?>
    </div>

    <div style="margin-top: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;" id="tablaVehiculos">
            <thead>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <th style="padding: 10px;">Patente</th>
                    <th style="padding: 10px;">Vehículo</th>
                    <th style="padding: 10px;">Cliente</th>
                    <th style="padding: 10px;">Estado</th>
                    <th style="padding: 10px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyVehiculos">
                <tr><td colspan="5" style="text-align: center; padding: 20px;">Cargando...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => { cargarVehiculos(); });

function cargarVehiculos() {
    fetch('ajax_recepcion.php?accion=listar')
    .then(res => res.json())
    .then(data => {
        let html = '';
        if(data.length === 0) {
            html = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No hay vehículos registrados</td></tr>';
        } else {
            data.forEach(v => {
                let colorEstado = v.estado === 'En Revisión' ? 'var(--color-danger)' : 'var(--color-success)';
                html += `<tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;"><strong>${v.patente.toUpperCase()}</strong></td>
                    <td style="padding: 10px;">${v.marca} ${v.modelo}</td>
                    <td style="padding: 10px;">${v.cliente_nombre}</td>
                    <td style="padding: 10px;"><span style="background: ${colorEstado}; color: #000; padding: 3px 8px; border-radius: 10px; font-size: 0.8rem;">${v.estado}</span></td>
                    <td style="padding: 10px; text-align: center;">
                        <button class="btn-ripple" onclick="window.location.href='ficha_vehiculo.php?id=${v.id_vehiculo}'" style="background-color: var(--color-info); padding: 5px 10px; font-size: 0.8rem; color: #000;">Ver Ficha</button>
                    </td>
                </tr>`;
            });
        }
        document.getElementById('tbodyVehiculos').innerHTML = html;
    });
}

function ingresarVehiculo() {
    Swal.fire({
        title: 'Ingreso Rápido',
        html: `
            <h4 style="text-align:left; margin-bottom:5px;">Datos del Vehículo</h4>
            <input id="sw-pat" class="swal2-input" placeholder="Patente (Ej. AB123CD)" style="text-transform: uppercase;">
            <input id="sw-mar" class="swal2-input" placeholder="Marca (Ej. Ford)">
            <input id="sw-mod" class="swal2-input" placeholder="Modelo (Ej. Fiesta)">
            <h4 style="text-align:left; margin-top:15px; margin-bottom:5px;">Datos del Cliente</h4>
            <input id="sw-cli" class="swal2-input" placeholder="Nombre Completo Cliente">
            <input id="sw-tel" class="swal2-input" placeholder="Teléfono / WhatsApp">
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Registrar Ingreso',
        background: 'var(--card-bg)',
        color: 'var(--text-color)',
        preConfirm: () => {
            return {
                patente: document.getElementById('sw-pat').value,
                marca: document.getElementById('sw-mar').value,
                modelo: document.getElementById('sw-mod').value,
                cliente: document.getElementById('sw-cli').value,
                telefono: document.getElementById('sw-tel').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let fd = new FormData();
            fd.append('accion', 'crear');
            for (let key in result.value) { fd.append(key, result.value[key]); }

            fetch('ajax_recepcion.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') {
                    Toast.fire({icon: 'success', title: 'Vehículo ingresado'});
                    cargarVehiculos();
                } else {
                    Toast.fire({icon: 'error', title: res.msg});
                }
            });
        }
    });
}
</script>
<?php require_once 'footer.php'; ?>