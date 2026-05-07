<?php
session_start();
require_once 'conexion.php';
require_once 'header.php';

$id_vehiculo = $_GET['id'] ?? 0;

if(!$id_vehiculo || !tienePermiso('ver_vehiculos', $pdo)) {
    echo "<div class='glass-card'><h3>Acceso Denegado o Vehículo no encontrado</h3></div>";
    require_once 'footer.php';
    exit;
}
?>
<div class="grid-dashboard" style="grid-template-columns: 1fr 2fr;">
    <div class="glass-card">
        <h2>Ficha del Vehículo</h2>
        <div id="infoVehiculo" style="margin-top: 15px; margin-bottom: 20px;">Cargando datos...</div>
        
        <div style="text-align: center; margin-top: 20px; padding: 15px; background: white; border-radius: 10px; display: inline-block;">
            <div id="qrcode"></div>
            <small style="color: black; font-weight: bold; margin-top: 10px; display: block;">Código de Identificación Única</small>
        </div>
    </div>

    <div class="glass-card">
        <div style="display: flex; justify-content: space-between; align-items: center;">
            <h2>Historia Clínica (Reparaciones)</h2>
            <?php if(tienePermiso('editar_vehiculo', $pdo)): ?>
                <button class="btn-ripple" onclick="nuevaReparacion()" style="background-color: var(--color-success); color: #000;">+ Agregar Registro</button>
            <?php endif; ?>
        </div>
        
        <div id="historialTimeline" style="margin-top: 20px;">
            <p>Cargando historial...</p>
        </div>
    </div>
</div>

<script>
const idVehiculo = <?php echo $id_vehiculo; ?>;

document.addEventListener("DOMContentLoaded", () => {
    cargarFicha();
    cargarHistorial();
});

function cargarFicha() {
    fetch('ajax_ficha.php?accion=datos_auto&id=' + idVehiculo)
    .then(res => res.json())
    .then(data => {
        if(data.status === 'ok') {
            document.getElementById('infoVehiculo').innerHTML = `
                <p><strong>Patente:</strong> <span style="text-transform: uppercase; font-size: 1.2rem;">${data.auto.patente}</span></p>
                <p><strong>Vehículo:</strong> ${data.auto.marca} ${data.auto.modelo}</p>
                <p><strong>Cliente:</strong> ${data.auto.cliente_nombre} (${data.auto.telefono})</p>
                <p><strong>Estado:</strong> <span style="background: var(--color-warning); color: #000; padding: 2px 5px; border-radius: 5px;">${data.auto.estado}</span></p>
            `;
            // Generar QR (Función 1)
            new QRCode(document.getElementById("qrcode"), {
                text: window.location.href, // El QR al escanear te lleva a esta misma página
                width: 128,
                height: 128
            });
        }
    });
}

function cargarHistorial() {
    fetch('ajax_ficha.php?accion=listar_historial&id=' + idVehiculo)
    .then(res => res.json())
    .then(data => {
        let html = '';
        if(data.length === 0) {
            html = '<p style="padding: 15px; background: rgba(0,0,0,0.05); border-radius: 5px;">No hay registros de reparaciones para este vehículo.</p>';
        } else {
            data.forEach(reg => {
                html += `
                <div style="background: rgba(0,0,0,0.05); padding: 15px; border-radius: 8px; margin-bottom: 10px; border-left: 4px solid var(--color-info);">
                    <div style="display: flex; justify-content: space-between;">
                        <strong>Fecha: ${reg.fecha}</strong>
                        <span style="font-size: 0.8rem; opacity: 0.7;">Mecánico: ${reg.mecanico}</span>
                    </div>
                    <p style="margin-top: 10px;">${reg.detalle}</p>
                    <small>Kilometraje: ${reg.kilometraje || 'No especificado'} km</small>
                </div>`;
            });
        }
        document.getElementById('historialTimeline').innerHTML = html;
    });
}

function nuevaReparacion() {
    Swal.fire({
        title: 'Agregar al Historial',
        html: `
            <textarea id="sw-detalle" class="swal2-textarea" placeholder="Describe el trabajo realizado, repuestos cambiados, etc..."></textarea>
            <input id="sw-km" type="number" class="swal2-input" placeholder="Kilometraje actual">
        `,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        background: 'var(--bg-color)',
        color: 'var(--text-color)',
        preConfirm: () => {
            return {
                detalle: document.getElementById('sw-detalle').value,
                km: document.getElementById('sw-km').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let fd = new FormData();
            fd.append('accion', 'guardar_registro');
            fd.append('id_vehiculo', idVehiculo);
            fd.append('detalle', result.value.detalle);
            fd.append('kilometraje', result.value.km);

            fetch('ajax_ficha.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') {
                    Toast.fire({icon: 'success', title: 'Registro guardado'});
                    cargarHistorial();
                } else {
                    Toast.fire({icon: 'error', title: res.msg});
                }
            });
        }
    });
}
</script>
<?php require_once 'footer.php'; ?>