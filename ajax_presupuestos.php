<?php
// ajax_presupuestos.php
session_start();
require_once 'conexion.php';
require_once 'seguridad.php';

header('Content-Type: application/json');
$accion = $_REQUEST['accion'] ?? '';

try {
    if ($accion === 'listar') {
        $stmt = $pdo->query("
            SELECT p.id_presupuesto, DATE_FORMAT(p.fecha_creacion, '%d/%m/%Y') as fecha, p.total, p.estado, v.patente 
            FROM presupuestos p 
            JOIN vehiculos v ON p.id_vehiculo = v.id_vehiculo 
            ORDER BY p.id_presupuesto DESC
        ");
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($accion === 'crear') {
        if(!tienePermiso('crear_presupuesto', $pdo)) {
            echo json_encode(['status' => 'error', 'msg' => 'Sin permisos']); exit;
        }

        $id_vehiculo = $_POST['id_vehiculo'] ?? 0;
        $id_usuario = $_SESSION['id_usuario'];

        if(empty($id_vehiculo)) {
            echo json_encode(['status' => 'error', 'msg' => 'Faltan datos']); exit;
        }

        $stmt = $pdo->prepare("INSERT INTO presupuestos (id_vehiculo, id_usuario) VALUES (?, ?)");
        $stmt->execute([$id_vehiculo, $id_usuario]);
        
        echo json_encode(['status' => 'ok', 'id_presupuesto' => $pdo->lastInsertId()]);
        exit;
    }

    echo json_encode(['status' => 'error', 'msg' => 'Acción inválida']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'msg' => 'Error de BD: ' . $e->getMessage()]);
}
?>