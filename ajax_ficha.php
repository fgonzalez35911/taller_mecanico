<?php
// ajax_ficha.php
session_start();
require_once 'conexion.php';
require_once 'seguridad.php';

header('Content-Type: application/json');
$accion = $_REQUEST['accion'] ?? '';

try {
    if ($accion === 'datos_auto') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT v.*, c.nombre_completo AS cliente_nombre, c.telefono FROM vehiculos v JOIN clientes c ON v.id_cliente = c.id_cliente WHERE v.id_vehiculo = ?");
        $stmt->execute([$id]);
        $auto = $stmt->fetch();
        
        if($auto) {
            echo json_encode(['status' => 'ok', 'auto' => $auto]);
        } else {
            echo json_encode(['status' => 'error']);
        }
        exit;
    }

    if ($accion === 'listar_historial') {
        $id = $_GET['id'] ?? 0;
        $stmt = $pdo->prepare("SELECT r.detalle, r.kilometraje, DATE_FORMAT(r.fecha_registro, '%d/%m/%Y %H:%i') as fecha, u.nombre_completo AS mecanico FROM reparaciones r JOIN usuarios u ON r.id_usuario = u.id_usuario WHERE r.id_vehiculo = ? ORDER BY r.fecha_registro DESC");
        $stmt->execute([$id]);
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($accion === 'guardar_registro') {
        if(!tienePermiso('editar_vehiculo', $pdo)) {
            echo json_encode(['status' => 'error', 'msg' => 'Sin permisos']); exit;
        }

        $id_vehiculo = $_POST['id_vehiculo'] ?? 0;
        $detalle = $_POST['detalle'] ?? '';
        $km = empty($_POST['kilometraje']) ? null : $_POST['kilometraje'];
        $id_usuario = $_SESSION['id_usuario']; // Auditoría automática de quién guardó

        if(empty($detalle)) {
            echo json_encode(['status' => 'error', 'msg' => 'El detalle no puede estar vacío']); exit;
        }

        $stmt = $pdo->prepare("INSERT INTO reparaciones (id_vehiculo, id_usuario, detalle, kilometraje) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_vehiculo, $id_usuario, $detalle, $km]);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    echo json_encode(['status' => 'error', 'msg' => 'Acción inválida']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'msg' => 'Error de BD: ' . $e->getMessage()]);
}
?>