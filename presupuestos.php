<?php
session_start();
require_once 'conexion.php';
require_once 'header.php';

if(!tienePermiso('ver_presupuestos', $pdo)) {
    echo "<div class='glass-card'><h3>Acceso Denegado</h3></div>";
    require_once 'footer.php';
    exit;
}

// Obtenemos los vehículos para el select del nuevo presupuesto
$stmt = $pdo->query("SELECT id_vehiculo, patente, marca, modelo FROM vehiculos ORDER BY patente ASC");
$vehiculos = $stmt->fetchAll();
$optionsVehiculos = '<option value="">Seleccione un vehículo...</option>';
foreach($vehiculos as $v) {
    $optionsVehiculos .= '<option value="'.$v['id_vehiculo'].'">'.strtoupper($v['patente']).' - '.$v['marca'].' '.$v['modelo'].'</option>';
}
?>

<div class="glass-card" style="margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <h2>Gestión de Presupuestos y Tickets</h2>
        <?php if(tienePermiso('crear_presupuesto', $pdo)): ?>
            <button class="btn-ripple" onclick="iniciarPresupuesto()" style="background-color: var(--color-success); color: #000;">+ Nuevo Presupuesto</button>
        <?php endif; ?>
    </div>

    <div style="margin-top: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;" id="tablaPresupuestos">
            <thead>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Fecha</th>
                    <th style="padding: 10px;">Patente</th>
                    <th style="padding: 10px;">Total</th>
                    <th style="padding: 10px;">Estado</th>
                    <th style="padding: 10px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyPresupuestos">
                <tr><td colspan="6" style="text-align: center; padding: 20px;">Cargando presupuestos...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => { cargarPresupuestos(); });

function cargarPresupuestos() {
    fetch('ajax_presupuestos.php?accion=listar')
    .then(res => res.json())
    .then(data => {
        let html = '';
        if(data.length === 0) {
            html = '<tr><td colspan="6" style="text-align: center; padding: 20px;">No hay presupuestos generados</td></tr>';
        } else {
            data.forEach(p => {
                let colorEst = p.estado === 'Borrador' ? 'var(--color-warning)' : 'var(--color-success)';
                html += `<tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;">#${p.id_presupuesto}</td>
                    <td style="padding: 10px;">${p.fecha}</td>
                    <td style="padding: 10px;"><strong>${p.patente.toUpperCase()}</strong></td>
                    <td style="padding: 10px;">$${p.total}</td>
                    <td style="padding: 10px;"><span style="background: ${colorEst}; color: #000; padding: 3px 8px; border-radius: 10px; font-size: 0.8rem;">${p.estado}</span></td>
                    <td style="padding: 10px; text-align: center;">
                        <button class="btn-ripple" onclick="window.location.href='detalle_presupuesto.php?id=${p.id_presupuesto}'" style="background-color: var(--color-info); padding: 5px 10px; font-size: 0.8rem; color: #000; margin-bottom: 5px;">Abrir Detalle</button><br>
                        <button class="btn-ripple" onclick="enviarWhatsApp(${p.id_presupuesto}, ${p.total})" style="background-color: #25D366; padding: 5px 10px; font-size: 0.8rem; color: #000; margin-top: 5px;">📱 WhatsApp</button>
                    </td>
                </tr>`;
            });
        }
        document.getElementById('tbodyPresupuestos').innerHTML = html;
    });
}

// Función 10: Enlaces automáticos a WhatsApp
function enviarWhatsApp(id_presupuesto, total) {
    Swal.fire({
        title: 'Enviar por WhatsApp',
        input: 'text',
        inputLabel: 'Número (Ej: 5491122334455)',
        showCancelButton: true,
        confirmButtonText: 'Enviar',
        background: 'var(--bg-color)',
        color: 'var(--text-color)'
    }).then((result) => {
        if (result.isConfirmed && result.value) {
            let mensaje = encodeURIComponent(`Hola! El presupuesto #${id_presupuesto} está listo por un total de $${total}.`);
            window.open(`https://api.whatsapp.com/send?phone=${result.value}&text=${mensaje}`, '_blank');
        }
    });
}

function iniciarPresupuesto() {
    Swal.fire({
        title: 'Seleccionar Vehículo',
        html: `
            <select id="swal-vehiculo" class="swal2-select" style="width: 90%;">
                <?php echo $optionsVehiculos; ?>
            </select>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Crear Presupuesto',
        preConfirm: () => {
            return document.getElementById('swal-vehiculo').value;
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let idVehiculo = result.value;
            if(!idVehiculo) {
                Toast.fire({icon: 'error', title: 'Debes seleccionar un vehículo'}); return;
            }

            let fd = new FormData();
            fd.append('accion', 'crear');
            fd.append('id_vehiculo', idVehiculo);

            fetch('ajax_presupuestos.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') {
                    Toast.fire({icon: 'success', title: 'Borrador creado'});
                    cargarPresupuestos();
                } else {
                    Toast.fire({icon: 'error', title: res.msg});
                }
            });
        }
    });
}
</script>

<?php require_once 'footer.php'; ?>