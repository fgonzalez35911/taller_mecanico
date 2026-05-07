<?php
session_start();
require_once 'conexion.php';
require_once 'header.php';

$id_presupuesto = $_GET['id'] ?? 0;

if(!$id_presupuesto || !tienePermiso('ver_presupuestos', $pdo)) {
    echo "<div class='glass-card'><h3>Acceso Denegado</h3></div>";
    require_once 'footer.php';
    exit;
}
?>

<div class="glass-card" style="margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <h2>Presupuesto #<?php echo $id_presupuesto; ?> <span id="badgeEstado"></span></h2>
        <div>
            <?php if(tienePermiso('aprobar_presupuesto', $pdo)): ?>
                <button id="btnAprobar" class="btn-ripple" onclick="abrirFirma()" style="background-color: var(--color-success); color: #000; display: none;">Aprobar y Firmar</button>
            <?php endif; ?>
        </div>
    </div>

    <div id="cajaAgregarItem" style="margin-top: 20px; padding: 15px; background: rgba(0,0,0,0.05); border-radius: 8px;">
        <div style="display: flex; gap: 10px; flex-wrap: wrap;">
            <input type="text" id="item_desc" class="swal2-input" placeholder="Repuesto o Mano de Obra" style="flex: 2; margin: 0; height: 40px;" oninput="guardarBorradorLocal()">
            <input type="number" id="item_cant" class="swal2-input" placeholder="Cant." value="1" style="flex: 1; margin: 0; height: 40px;" oninput="guardarBorradorLocal()">
            <input type="number" id="item_precio" class="swal2-input" placeholder="Precio U." style="flex: 1; margin: 0; height: 40px;" oninput="guardarBorradorLocal()">
            <button class="btn-ripple" onclick="agregarItem()" style="background-color: var(--color-info); color: #000; height: 40px; border-radius: 5px;">+ Agregar</button>
        </div>
        <small style="color: gray; margin-top: 5px; display: block;">* Autoguardado local activado (Función 11)</small>
    </div>

    <div style="margin-top: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;">
            <thead>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <th style="padding: 10px;">Descripción</th>
                    <th style="padding: 10px;">Cant.</th>
                    <th style="padding: 10px;">Precio U.</th>
                    <th style="padding: 10px;">Subtotal</th>
                    <th style="padding: 10px; text-align: center;">Acción</th>
                </tr>
            </thead>
            <tbody id="tbodyItems">
                <tr><td colspan="5" style="text-align: center; padding: 20px;">Cargando ítems...</td></tr>
            </tbody>
            <tfoot>
                <tr style="border-top: 2px solid var(--text-color);">
                    <td colspan="3" style="padding: 10px; text-align: right; font-weight: bold; font-size: 1.2rem;">TOTAL:</td>
                    <td colspan="2" style="padding: 10px; font-weight: bold; font-size: 1.2rem; color: var(--color-success);">$<span id="totalPresupuesto">0.00</span></td>
                </tr>
            </tfoot>
        </table>
    </div>
</div>

<script>
const idPresupuesto = <?php echo $id_presupuesto; ?>;
let presupuestoEstado = 'Borrador';

document.addEventListener("DOMContentLoaded", () => {
    cargarItems();
    recuperarBorradorLocal();
});

// Función 11: Autoguardado en LocalStorage
function guardarBorradorLocal() {
    let datos = {
        desc: document.getElementById('item_desc').value,
        cant: document.getElementById('item_cant').value,
        precio: document.getElementById('item_precio').value
    };
    localStorage.setItem('borrador_presupuesto_' + idPresupuesto, JSON.stringify(datos));
}

function recuperarBorradorLocal() {
    let datos = localStorage.getItem('borrador_presupuesto_' + idPresupuesto);
    if(datos) {
        datos = JSON.parse(datos);
        document.getElementById('item_desc').value = datos.desc;
        document.getElementById('item_cant').value = datos.cant;
        document.getElementById('item_precio').value = datos.precio;
    }
}

function cargarItems() {
    fetch('ajax_detalle_presupuesto.php?accion=listar&id=' + idPresupuesto)
    .then(res => res.json())
    .then(data => {
        presupuestoEstado = data.estado;
        
        let colorBadge = presupuestoEstado === 'Aprobado' ? 'var(--color-success)' : 'var(--color-warning)';
        document.getElementById('badgeEstado').innerHTML = `<span style="background: ${colorBadge}; color: #000; padding: 3px 8px; border-radius: 10px; font-size: 0.8rem;">${presupuestoEstado}</span>`;
        
        if(presupuestoEstado === 'Aprobado') {
            document.getElementById('cajaAgregarItem').style.display = 'none';
            if(document.getElementById('btnAprobar')) document.getElementById('btnAprobar').style.display = 'none';
        } else {
            if(document.getElementById('btnAprobar')) document.getElementById('btnAprobar').style.display = 'inline-block';
        }

        let html = '';
        if(data.items.length === 0) {
            html = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No hay ítems cargados</td></tr>';
        } else {
            data.items.forEach(i => {
                let btnBorrar = presupuestoEstado === 'Borrador' ? `<button class="btn-ripple" onclick="eliminarItem(${i.id_item})" style="background-color: var(--color-danger); padding: 5px 10px; font-size: 0.8rem; color: #000;">X</button>` : '-';
                html += `<tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;">${i.descripcion}</td>
                    <td style="padding: 10px;">${i.cantidad}</td>
                    <td style="padding: 10px;">$${i.precio_unitario}</td>
                    <td style="padding: 10px;"><strong>$${i.subtotal}</strong></td>
                    <td style="padding: 10px; text-align: center;">${btnBorrar}</td>
                </tr>`;
            });
        }
        document.getElementById('tbodyItems').innerHTML = html;
        document.getElementById('totalPresupuesto').innerText = data.total;
    });
}

function agregarItem() {
    let desc = document.getElementById('item_desc').value;
    let cant = document.getElementById('item_cant').value;
    let precio = document.getElementById('item_precio').value;

    if(!desc || !cant || !precio) {
        Toast.fire({icon: 'error', title: 'Completá todos los campos del ítem'}); return;
    }

    let fd = new FormData();
    fd.append('accion', 'agregar');
    fd.append('id_presupuesto', idPresupuesto);
    fd.append('descripcion', desc);
    fd.append('cantidad', cant);
    fd.append('precio_unitario', precio);

    fetch('ajax_detalle_presupuesto.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(res => {
        if(res.status === 'ok') {
            document.getElementById('item_desc').value = '';
            document.getElementById('item_cant').value = '1';
            document.getElementById('item_precio').value = '';
            localStorage.removeItem('borrador_presupuesto_' + idPresupuesto); // Limpiar autoguardado local
            cargarItems();
        } else {
            Toast.fire({icon: 'error', title: res.msg});
        }
    });
}

function eliminarItem(idItem) {
    let fd = new FormData();
    fd.append('accion', 'eliminar');
    fd.append('id_item', idItem);
    fd.append('id_presupuesto', idPresupuesto);

    fetch('ajax_detalle_presupuesto.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(res => {
        if(res.status === 'ok') cargarItems();
    });
}

// Función 2: Modal de Firma Digital
let padFirmas;
function abrirFirma() {
    Swal.fire({
        title: 'Firma de Conformidad',
        html: `
            <p style="margin-bottom: 10px;">Firme aquí para aprobar el presupuesto de $${document.getElementById('totalPresupuesto').innerText}</p>
            <canvas id="canvasFirma" class="canvas-firma" width="400" height="200"></canvas>
            <br>
            <button class="btn-ripple" onclick="padFirmas.clear()" style="margin-top: 10px; background-color: var(--color-warning); color: #000; padding: 5px 10px; font-size: 0.8rem;">Limpiar Firma</button>
        `,
        showCancelButton: true,
        confirmButtonText: 'Aprobar y Guardar',
        background: 'var(--bg-color)',
        color: 'var(--text-color)',
        didOpen: () => {
            let canvas = document.getElementById('canvasFirma');
            padFirmas = new SignaturePad(canvas, { backgroundColor: 'rgb(255, 255, 255)' });
        },
        preConfirm: () => {
            if (padFirmas.isEmpty()) {
                Swal.showValidationMessage('El cliente debe firmar para aprobar');
                return false;
            }
            return padFirmas.toDataURL(); // Retorna la imagen en base64
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let firmaBase64 = result.value;
            let fd = new FormData();
            fd.append('accion', 'aprobar_con_firma');
            fd.append('id_presupuesto', idPresupuesto);
            fd.append('firma', firmaBase64);

            fetch('ajax_detalle_presupuesto.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') {
                    Swal.fire('¡Aprobado!', 'El presupuesto ha sido firmado y cerrado.', 'success');
                    cargarItems();
                } else {
                    Toast.fire({icon: 'error', title: res.msg});
                }
            });
        }
    });
}
</script>

<?php require_once 'footer.php'; ?>