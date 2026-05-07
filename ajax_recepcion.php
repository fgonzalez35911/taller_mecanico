<?php
// ajax_recepcion.php
session_start();
require_once 'conexion.php';
require_once 'seguridad.php';

header('Content-Type: application/json');
$accion = $_REQUEST['accion'] ?? '';

try {
    if ($accion === 'listar') {
        $stmt = $pdo->query("
            SELECT v.id_vehiculo, v.patente, v.marca, v.modelo, v.estado, c.nombre_completo AS cliente_nombre 
            FROM vehiculos v 
            JOIN clientes c ON v.id_cliente = c.id_cliente 
            ORDER BY v.fecha_ingreso DESC
        ");
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($accion === 'crear') {
        if(!tienePermiso('ingresar_vehiculo', $pdo)) {
            echo json_encode(['status' => 'error', 'msg' => 'No tienes permiso para ingresar vehículos']); 
            exit;
        }

        $patente = strtoupper(trim($_POST['patente'] ?? ''));
        $marca = trim($_POST['marca'] ?? '');
        $modelo = trim($_POST['modelo'] ?? '');
        $cliente = trim($_POST['cliente'] ?? '');
        $telefono = trim($_POST['telefono'] ?? '');

        if(empty($patente) || empty($marca) || empty($modelo) || empty($cliente)) {
            echo json_encode(['status' => 'error', 'msg' => 'Patente, Marca, Modelo y Cliente son obligatorios']);
            exit;
        }

        // 1. Crear el cliente
        $stmtCli = $pdo->prepare("INSERT INTO clientes (nombre_completo, telefono) VALUES (?, ?)");
        $stmtCli->execute([$cliente, $telefono]);
        $id_cliente = $pdo->lastInsertId();

        // 2. Insertar el vehículo asociado a ese cliente
        $stmtVeh = $pdo->prepare("INSERT INTO vehiculos (patente, marca, modelo, id_cliente) VALUES (?, ?, ?, ?)");
        $stmtVeh->execute([$patente, $marca, $modelo, $id_cliente]);

        echo json_encode(['status' => 'ok']);
        exit;
    }

    echo json_encode(['status' => 'error', 'msg' => 'Acción no válida']);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'msg' => 'Esa patente ya está registrada en el taller.']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Error de Base de Datos.']);
    }
}
?>