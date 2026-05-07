<?php
// administrar_permisos.php
session_start();
require_once 'conexion.php';
require_once 'header.php';

// Traer roles para el selector
$stmt = $pdo->query("SELECT id_rol, nombre_rol FROM roles ORDER BY nombre_rol ASC");
$roles = $stmt->fetchAll();
?>

<div class="glass-card" style="margin-top: 20px;">
    <div style="display: flex; justify-content: space-between; align-items: center; flex-wrap: wrap;">
        <h2>Matriz de Permisos</h2>
        <button class="btn-ripple" onclick="nuevoPermisoMaestro()" style="background-color: var(--color-warning); color: #000;">+ Crear Permiso Sistema</button>
    </div>
    
    <div style="margin-top: 20px; padding: 20px; background: rgba(0,0,0,0.05); border-radius: 8px;">
        <label><strong>Seleccione un Rol a configurar:</strong></label><br>
        <select id="selectRol" style="padding: 10px; width: 100%; max-width: 400px; margin-top: 10px; border-radius: 5px;" onchange="cargarMatriz()">
            <option value="">-- Elija un rol --</option>
            <?php foreach($roles as $r): ?>
                <option value="<?php echo $r['id_rol']; ?>"><?php echo $r['nombre_rol']; ?></option>
            <?php endforeach; ?>
        </select>
    </div>

    <div id="contenedorPermisos" style="display: none; margin-top: 20px;">
        <h3>Asignar Permisos a este Rol</h3>
        <div class="grid-dashboard" id="gridCheckboxes" style="margin-top: 15px; grid-template-columns: repeat(auto-fill, minmax(200px, 1fr));">
            </div>
        
        <button class="btn-ripple" onclick="guardarCambios()" style="background-color: var(--color-success); color: #000; margin-top: 20px; width: 100%;">💾 Guardar Cambios</button>
    </div>
</div>

<script>
let todosLosPermisos = [];

// 1. Cargar la lista completa de permisos que existen en la BD al entrar
document.addEventListener("DOMContentLoaded", () => {
    fetch('ajax_permisos.php?accion=listar_permisos')
    .then(res => res.json())
    .then(data => {
        todosLosPermisos = data;
    });
});

// 2. Crear un nuevo permiso maestro en el sistema (ej. "ver_facturas")
function nuevoPermisoMaestro() {
    Swal.fire({
        title: 'Crear Permiso Raíz',
        html: `
            <input id="swal-p-nom" class="swal2-input" placeholder="Nombre (ej. Editar Clientes)">
            <input id="swal-p-desc" class="swal2-input" placeholder="Descripción">
        `,
        focusConfirm: false,
        showCancelButton: true,
        confirmButtonText: 'Crear',
        background: 'var(--card-bg)',
        color: 'var(--text-color)',
        preConfirm: () => {
            return {
                nombre: document.getElementById('swal-p-nom').value,
                desc: document.getElementById('swal-p-desc').value
            }
        }
    }).then((result) => {
        if (result.isConfirmed) {
            let d = result.value;
            let fd = new FormData();
            fd.append('accion', 'crear_permiso');
            fd.append('nombre', d.nombre);
            fd.append('descripcion', d.desc);

            fetch('ajax_permisos.php', { method: 'POST', body: fd })
            .then(res => res.json())
            .then(res => {
                if(res.status === 'ok') {
                    Toast.fire({icon: 'success', title: 'Permiso guardado'});
                    setTimeout(() => location.reload(), 1000); // Recarga para actualizar matriz global
                } else {
                    Toast.fire({icon: 'error', title: res.msg});
                }
            });
        }
    });
}

// 3. Cuando eliges un rol en el Select, se arman los checkboxes y se tildan los que ya tiene
function cargarMatriz() {
    let id_rol = document.getElementById('selectRol').value;
    let contenedor = document.getElementById('contenedorPermisos');
    let grid = document.getElementById('gridCheckboxes');
    
    if(!id_rol) {
        contenedor.style.display = 'none';
        return;
    }

    contenedor.style.display = 'block';
    
    // Buscar qué permisos tiene tildados este rol
    let fd = new FormData();
    fd.append('accion', 'listar_permisos_rol');
    fd.append('id_rol', id_rol);

    fetch('ajax_permisos.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(data => {
        let permisosDelRol = data.permisos || []; // Array de IDs numéricos
        
        let html = '';
        if(todosLosPermisos.length === 0) {
            grid.innerHTML = '<p>No hay permisos creados. Usa el botón amarillo arriba.</p>';
            return;
        }

        todosLosPermisos.forEach(p => {
            let checked = permisosDelRol.includes(p.id_permiso) ? 'checked' : '';
            html += `
            <div style="background: var(--card-bg); padding: 10px; border-radius: 5px; border: 1px solid var(--glass-border);">
                <label style="cursor: pointer; display: flex; align-items: center;">
                    <input type="checkbox" class="chk-permiso" value="${p.id_permiso}" ${checked} style="margin-right: 10px; width: 20px; height: 20px;">
                    <div>
                        <strong>${p.nombre_permiso}</strong><br>
                        <small style="opacity: 0.7;">${p.descripcion || ''}</small>
                    </div>
                </label>
            </div>`;
        });
        grid.innerHTML = html;
    });
}

// 4. Guardar los tildados
function guardarCambios() {
    let id_rol = document.getElementById('selectRol').value;
    let checkboxes = document.querySelectorAll('.chk-permiso');
    let permisosMarcados = [];
    
    checkboxes.forEach(chk => {
        if(chk.checked) {
            permisosMarcados.push(chk.value);
        }
    });

    let fd = new FormData();
    fd.append('accion', 'guardar_asignacion');
    fd.append('id_rol', id_rol);
    fd.append('permisos', JSON.stringify(permisosMarcados));

    fetch('ajax_permisos.php', { method: 'POST', body: fd })
    .then(res => res.json())
    .then(res => {
        if(res.status === 'ok') {
            Toast.fire({icon: 'success', title: 'Permisos actualizados'});
        } else {
            Toast.fire({icon: 'error', title: res.msg});
        }
    });
}
</script>

<?php require_once 'footer.php'; ?>