<?php
// ajax_login.php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');

$email = $_POST['email'] ?? '';
$password = $_POST['password'] ?? '';

if(empty($email) || empty($password)){
    echo json_encode(['status' => 'error', 'msg' => 'Completá todos los campos']);
    exit;
}

try {
    $stmt = $pdo->prepare("SELECT id_usuario, nombre_completo, password_hash, id_rol, estado FROM usuarios WHERE email = ?");
    $stmt->execute([$email]);
    $user = $stmt->fetch();

    if($user) {
        if($user['estado'] == 0) {
            echo json_encode(['status' => 'error', 'msg' => 'Usuario inactivo. Contacte al administrador.']);
            exit;
        }

        if(password_verify($password, $user['password_hash'])) {
            $_SESSION['id_usuario'] = $user['id_usuario'];
            $_SESSION['id_rol'] = $user['id_rol'];
            $_SESSION['nombre'] = $user['nombre_completo'];
            
            echo json_encode(['status' => 'ok']);
        } else {
            echo json_encode(['status' => 'error', 'msg' => 'Contraseña incorrecta']);
        }
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'El correo no existe en el sistema']);
    }
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'msg' => 'Error de conexión a la base de datos']);
}
?>