<?php
// ajax_roles.php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

$accion = $_REQUEST['accion'] ?? '';

try {
    if ($accion === 'listar') {
        $stmt = $pdo->query("SELECT * FROM roles ORDER BY id_rol ASC");
        $roles = $stmt->fetchAll();
        echo json_encode($roles);
        exit;
    }

    if ($accion === 'crear') {
        $nombre = $_POST['nombre'] ?? '';
        $descripcion = $_POST['descripcion'] ?? '';

        if(empty($nombre)){
            echo json_encode(['status' => 'error', 'msg' => 'El nombre del rol es obligatorio']);
            exit;
        }

        $stmt = $pdo->prepare("INSERT INTO roles (nombre_rol, descripcion) VALUES (?, ?)");
        $stmt->execute([$nombre, $descripcion]);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($accion === 'eliminar') {
        $id = $_POST['id'] ?? 0;
        
        // Medida de seguridad: Evitar borrar el rol 1 (Administrador principal)
        if($id == 1) {
            echo json_encode(['status' => 'error', 'msg' => 'No puedes eliminar el rol principal del sistema']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM roles WHERE id_rol = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    echo json_encode(['status' => 'error', 'msg' => 'Acción no válida']);

} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'msg' => 'Error de BD: ' . $e->getMessage()]);
}
?>