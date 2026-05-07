<?php
// administrar_usuarios.php
session_start();
require_once 'conexion.php';
require_once 'header.php';

// Cargar roles para el select del modal
$stmt = $pdo->query("SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol ASC");
$roles = $stmt->fetchAll();
$optionsRoles = '<option value="">Seleccione un rol...</option>';
foreach($roles as $r) {
    $optionsRoles .= '<option value="'.$r['id_rol'].'">'.$r['nombre_rol'].'</option>';
}
?>

<div class="glass-card" style="margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <h2>Gestión de Usuarios</h2>
        <?php if(tienePermiso('crear_usuarios', $pdo)): ?>
            <button class="btn-ripple" onclick="nuevoUsuario()" style="background-color: var(--color-success); color: #000;">+ Nuevo Usuario</button>
        <?php endif; ?>
    </div>
    
    <div style="margin-top: 20px; overflow-x: auto;">
        <table style="width: 100%; border-collapse: collapse; text-align: left;" id="tablaUsuarios">
            <thead>
                <tr style="border-bottom: 2px solid var(--glass-border);">
                    <th style="padding: 10px;">ID</th>
                    <th style="padding: 10px;">Nombre</th>
                    <th style="padding: 10px;">Email</th>
                    <th style="padding: 10px;">Rol</th>
                    <th style="padding: 10px; text-align: center;">Acciones</th>
                </tr>
            </thead>
            <tbody id="tbodyUsuarios">
                <tr><td colspan="5" style="text-align: center; padding: 20px;">Cargando usuarios...</td></tr>
            </tbody>
        </table>
    </div>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    cargarUsuarios();
});

function cargarUsuarios() {
    fetch('ajax_usuarios.php?accion=listar')
    .then(res => res.json())
    .then(data => {
        let html = '';
        if(data.length === 0) {
            html = '<tr><td colspan="5" style="text-align: center; padding: 20px;">No hay usuarios creados</td></tr>';
        } else {
            data.forEach(u => {
                html += `<tr style="border-bottom: 1px solid var(--glass-border);">
                    <td style="padding: 10px;">${u.id_usuario}</td>
                    <td style="padding: 10px;"><strong>${u.nombre_completo}</strong></td>
                    <td style="padding: 10px;">${u.email}</td>
                    <td style="padding: 10px;"><span style="background: var(--color-info); color: #000; padding: 3px 8px; border-radius: 10px; font-size: 0.8rem;">${u.nombre_rol}</span></td>
                    <td style="padding: 10px; text-align: center;">
                        <button class="btn-ripple" onclick="eliminarUsuario(${u.id_usuario})" style="background-color: var(--color-danger); padding: 5px 10px; font-size: 0.8rem; color: #000;">Eliminar</button>
                    </td>
                </tr>`;
            });
        }
        document.getElementById('tbodyUsuarios').innerHTML = html;
    });
}

function nuevoUsuario() {
    Swal.fire({
        title: 'Crear Usuario',
        html: `
            <input id="swal-nombre" class="swal2-input" placeholder="Nombre Completo">
            <input id="swal-email" type="email" class="swal2-input" placeholder="Correo Electrónico">
            <input id="swal-pass" type="password" class="swal2-input" placeholder="Contraseña">
            <select id="swal-rol" class="swal2-input" style="width: 100%; height: auto; padding: 10px;">
                <?php echo $optionsRoles; ?>
            </select>
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Crear',
        background: 'var(--card-bg)',
        color: 'var(--text-color)',
        preConfirm: () => {
            return {
                nombre: document.getElementById('swal-nombre').value,
                email: document.getElementById('swal-email').value,
                password: document.getElementById('swal-pass').value,
                id_rol: document.getElementById('swal-rol').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let datos = result.value;
            if(!datos.nombre || !datos.email || !datos.password || !datos.id_rol) {
                Toast.fire({icon: 'error', title: 'Todos los campos son obligatorios'});
                return;
            }

            let formData = new FormData();
            formData.append('accion', 'crear');
            formData.append('nombre', datos.nombre);
            formData.append('email', datos.email);
            formData.append('password', datos.password);
            formData.append('id_rol', datos.id_rol);

            fetch('ajax_usuarios.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') {
                    Toast.fire({icon: 'success', title: 'Usuario creado'});
                    cargarUsuarios();
                } else {
                    Toast.fire({icon: 'error', title: res.msg});
                }
            });
        }
    });
}

function eliminarUsuario(id) {
    Swal.fire({
        title: '¿Eliminar usuario?',
        icon: 'warning',
        showCancelButton: true,
        confirmButtonColor: '#3085d6',
        cancelButtonColor: '#d33',
        confirmButtonText: 'Sí',
        background: 'var(--card-bg)',
        color: 'var(--text-color)'
    }).then((result) => {
        if (result.isConfirmed) {
            let formData = new FormData();
            formData.append('accion', 'eliminar');
            formData.append('id', id);

            fetch('ajax_usuarios.php', { method: 'POST', body: formData })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') {
                    Toast.fire({icon: 'success', title: 'Eliminado'});
                    cargarUsuarios();
                } else {
                    Toast.fire({icon: 'error', title: res.msg});
                }
            });
        }
    })
}
</script>

<?php require_once 'footer.php'; ?>