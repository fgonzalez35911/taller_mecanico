<?php
session_start();
require_once 'conexion.php';
require_once 'seguridad.php';

header('Content-Type: application/json');
$accion = $_REQUEST['accion'] ?? '';

try {
    if ($accion === 'listar') {
        $stmt = $pdo->query("SELECT c.id_movimiento, DATE_FORMAT(c.fecha_movimiento, '%d/%m/%Y %H:%i') as fecha, c.concepto, c.tipo_movimiento, c.monto, u.nombre_completo as usuario FROM caja_diaria c JOIN usuarios u ON c.id_usuario = u.id_usuario ORDER BY c.fecha_movimiento DESC");
        $movimientos = $stmt->fetchAll();

        // Calcular totales
        $ingresos = 0; $egresos = 0;
        foreach($movimientos as $m) {
            if($m['tipo_movimiento'] === 'Ingreso') $ingresos += $m['monto'];
            else $egresos += $m['monto'];
        }

        echo json_encode([
            'movimientos' => $movimientos,
            'totales' => [
                'ingresos' => number_format($ingresos, 2, '.', ''),
                'egresos' => number_format($egresos, 2, '.', ''),
                'saldo' => number_format($ingresos - $egresos, 2, '.', '')
            ]
        ]);
        exit;
    }

    if ($accion === 'crear') {
        if(!tienePermiso('movimiento_caja', $pdo)) { echo json_encode(['status' => 'error', 'msg' => 'Sin permisos']); exit; }

        $tipo = $_POST['tipo'] ?? '';
        $concepto = trim($_POST['concepto'] ?? '');
        $monto = $_POST['monto'] ?? 0;
        $id_usuario = $_SESSION['id_usuario'];

        if(empty($concepto) || $monto <= 0) { echo json_encode(['status' => 'error', 'msg' => 'Concepto y monto válidos son obligatorios']); exit; }

        $stmt = $pdo->prepare("INSERT INTO caja_diaria (id_usuario, tipo_movimiento, concepto, monto) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_usuario, $tipo, $concepto, $monto]);
        
        echo json_encode(['status' => 'ok']);
        exit;
    }

    echo json_encode(['status' => 'error', 'msg' => 'Acción inválida']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'msg' => 'Error BD: ' . $e->getMessage()]);
}
?>