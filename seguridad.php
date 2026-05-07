<?php
// seguridad.php

function tienePermiso($permiso_requerido, $pdo) {
    // Si no hay rol en sesión, no tiene permiso
    if (!isset($_SESSION['id_rol'])) {
        return false;
    }

    $id_rol = $_SESSION['id_rol'];

    // El rol 1 (Administrador) siempre tiene acceso absoluto a todo
    if ($id_rol == 1) {
        return true;
    }

    // Buscamos si existe la relación entre su rol y el permiso pedido
    $stmt = $pdo->prepare("
        SELECT p.id_permiso 
        FROM permisos p
        INNER JOIN rol_permiso rp ON p.id_permiso = rp.id_permiso
        WHERE rp.id_rol = ? AND p.nombre_permiso = ?
    ");
    $stmt->execute([$id_rol, $permiso_requerido]);
    
    return $stmt->rowCount() > 0;
}
?>