<?php
// administrar_roles.php
session_start();
require_once 'conexion.php';
require_once 'header.php';
?>

<div class="glass-card" style="margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <h2>Gestión de Roles</h2>
        <button class="btn-ripple" onclick="nuevoRol()" style="background-color: var(--color-success); color: #000;">+ Nuevo Rol</button>
    </div>
    
    <div style="margin-top: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;" id="tablaRoles">
            <thead>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Nombre del Rol</th>
                    <th style="padding: 10px;">Descripción</th>
                    <th style="padding: 10px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyRoles">
                <tr><td colspan="4" style="text-align: center; padding: 20px;">Cargando roles...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    cargarRoles();
});

function cargarRoles() {
    fetch('ajax_roles.php?accion=listar')
    .then(res => res.json())
    .then(data => {
        let html = '';
        if(data.length === 0) {
            html = '<tr><td colspan="4" style="text-align: center; padding: 20px;">No hay roles creados</td></tr>';
        } else {
            data.forEach(rol => {
                html += `<tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;">${rol.id_rol}</td>
                    <td style="padding: 10px;"><strong>${rol.nombre_rol}</strong></td>
                    <td style="padding: 10px;">${rol.descripcion || '-'}</td>
                    <td style="padding: 10px; text-align: center;">
                        <button class="btn-ripple" onclick="eliminarRol(${rol.id_rol})" style="background-color: var(--color-danger); padding: 5px 10px; font-size: 0.8rem; color: #000;">Eliminar</button>
                    </td>
                </tr>`;
            });
        }
        document.getElementById('tbodyRoles').innerHTML = html;
    })
    .catch(error => console.error('Error:', error));
}

function nuevoRol() {
    Swal.fire({
        title: 'Crear Nuevo Rol',
        html: `
            <input id="swal-input1" class="swal2-input" placeholder="Nombre (ej. Mecánico)">
            <input id="swal-input2" class="swal2-input" placeholder="Descripción breve">
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Guardar',
        cancelButtonText: 'Cancelar',
        background: 'var(--card-bg)',
        color: 'var(--text-color)',
        preConfirm: () => {
            return [
                document.getElementById('swal-input1').value,
                document.getElementById('swal-input2').value
            ]
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let nombre = result.value[0];
            let desc = result.value[1];
            
            if(!nombre) { 
                Toast.fire({icon: 'error', title: 'El nombre es obligatorio'}); 
                return; 
            }
            
            let formData = new FormData();
            formData.append('accion', 'crear');
            formData.append('nombre', nombre);
            formData.append('descripcion', desc);

            fetch('ajax_roles.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') {
                    Toast.fire({icon: 'success', title: 'Rol creado exitosamente'});
                    cargarRoles(); // Refrescar la tabla sin recargar la página
                } else {
                    Toast.fire({icon: 'error', title: res.msg});
                }
            });
        }
    });
}

function eliminarRol(id) {
    Swal.fire({
        title: '¿Eliminar este rol?',
        text: "Los usuarios con este rol podrían perder acceso",
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí, eliminar',
        cancelButtonText: 'Cancelar',
        background: 'var(--card-bg)',
        color: 'var(--text-color)'
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('id', id);

            fetch('ajax_roles.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') {
                    Toast.fire({icon: 'success', title: 'Rol eliminado'});
                    cargarRoles();
                } else {
                    Toast.fire({icon: 'error', title: res.msg});
                }
            });
        }
    })
}
</script>

<?php require_once 'footer.php'; ?>