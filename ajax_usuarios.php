<?php
// ajax_usuarios.php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');
$accion = $_REQUEST['accion'] ?? '';

try {
    if ($accion === 'listar') {
        $stmt = $pdo->query("SELECT u.id_usuario, u.nombre_completo, u.email, u.estado, r.nombre_rol FROM usuarios u JOIN roles r ON u.id_rol = r.id_rol ORDER BY u.id_usuario ASC");
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($accion === 'crear') {
        $nombre = $_POST['nombre'] ?? '';
        $email = $_POST['email'] ?? '';
        $password = $_POST['password'] ?? '';
        $id_rol = $_POST['id_rol'] ?? '';

        if(empty($nombre) || empty($email) || empty($password) || empty($id_rol)){
            echo json_encode(['status' => 'error', 'msg' => 'Todos los campos son obligatorios']);
            exit;
        }

        $hash = password_hash($password, PASSWORD_DEFAULT);
        
        $stmt = $pdo->prepare("INSERT INTO usuarios (nombre_completo, email, password_hash, id_rol) VALUES (?, ?, ?, ?)");
        $stmt->execute([$nombre, $email, $hash, $id_rol]);
        
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($accion === 'eliminar') {
        $id = $_POST['id'] ?? 0;
        
        if($id == 1) { // Protección para no borrar el admin root
            echo json_encode(['status' => 'error', 'msg' => 'No puedes eliminar al administrador principal']);
            exit;
        }
        
        $stmt = $pdo->prepare("DELETE FROM usuarios WHERE id_usuario = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    echo json_encode(['status' => 'error', 'msg' => 'Acción no válida']);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) { // Código de error para duplicados en MySQL
        echo json_encode(['status' => 'error', 'msg' => 'El correo electrónico ya está registrado.']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Error de BD: ' . $e->getMessage()]);
    }
}
?>