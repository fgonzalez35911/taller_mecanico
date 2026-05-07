<?php
session_start();
require_once 'conexion.php';
require_once 'seguridad.php';

header('Content-Type: application/json');
$accion = $_REQUEST['accion'] ?? '';

try {
    if ($accion === 'listar') {
        $stmt = $pdo->query("SELECT * FROM inventario ORDER BY nombre ASC");
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($accion === 'crear') {
        if(!tienePermiso('gestionar_stock', $pdo)) { echo json_encode(['status' => 'error', 'msg' => 'Sin permisos']); exit; }

        $codigo = trim($_POST['codigo'] ?? '');
        $nombre = trim($_POST['nombre'] ?? '');
        $cant = $_POST['cant'] ?? 0;
        $min = $_POST['min'] ?? 5;
        $precio = $_POST['precio'] ?? 0;

        if(empty($nombre)) { echo json_encode(['status' => 'error', 'msg' => 'El nombre es obligatorio']); exit; }
        if(empty($codigo)) { $codigo = null; }

        $stmt = $pdo->prepare("INSERT INTO inventario (codigo_barras, nombre, cantidad, stock_minimo, precio_venta) VALUES (?, ?, ?, ?, ?)");
        $stmt->execute([$codigo, $nombre, $cant, $min, $precio]);
        
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($accion === 'eliminar') {
        if(!tienePermiso('gestionar_stock', $pdo)) { echo json_encode(['status' => 'error', 'msg' => 'Sin permisos']); exit; }
        $id = $_POST['id'] ?? 0;
        $stmt = $pdo->prepare("DELETE FROM inventario WHERE id_repuesto = ?");
        $stmt->execute([$id]);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    echo json_encode(['status' => 'error', 'msg' => 'Acción inválida']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { echo json_encode(['status' => 'error', 'msg' => 'Ese código de barras ya existe.']); } 
    else { echo json_encode(['status' => 'error', 'msg' => 'Error BD: ' . $e->getMessage()]); }
}
?>