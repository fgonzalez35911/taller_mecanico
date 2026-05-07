<?php
// ajax_detalle_presupuesto.php
session_start();
require_once 'conexion.php';
require_once 'seguridad.php';

header('Content-Type: application/json');
$accion = $_REQUEST['accion'] ?? '';

// Función auxiliar para actualizar el total del presupuesto maestro
function recalcularTotal($pdo, $id_presupuesto) {
    $stmt = $pdo->prepare("UPDATE presupuestos SET total = (SELECT COALESCE(SUM(subtotal), 0) FROM presupuesto_items WHERE id_presupuesto = ?) WHERE id_presupuesto = ?");
    $stmt->execute([$id_presupuesto, $id_presupuesto]);
}

try {
    if ($accion === 'listar') {
        $id = $_GET['id'] ?? 0;
        
        $stmtP = $pdo->prepare("SELECT estado, total FROM presupuestos WHERE id_presupuesto = ?");
        $stmtP->execute([$id]);
        $presupuesto = $stmtP->fetch();

        $stmtI = $pdo->prepare("SELECT * FROM presupuesto_items WHERE id_presupuesto = ? ORDER BY id_item ASC");
        $stmtI->execute([$id]);
        $items = $stmtI->fetchAll();

        echo json_encode([
            'estado' => $presupuesto['estado'],
            'total' => $presupuesto['total'],
            'items' => $items
        ]);
        exit;
    }

    if ($accion === 'agregar') {
        if(!tienePermiso('crear_presupuesto', $pdo)) { echo json_encode(['status' => 'error', 'msg' => 'Sin permisos']); exit; }

        $id_presupuesto = $_POST['id_presupuesto'] ?? 0;
        $desc = $_POST['descripcion'] ?? '';
        $cant = $_POST['cantidad'] ?? 0;
        $precio = $_POST['precio_unitario'] ?? 0;

        $stmt = $pdo->prepare("INSERT INTO presupuesto_items (id_presupuesto, descripcion, cantidad, precio_unitario) VALUES (?, ?, ?, ?)");
        $stmt->execute([$id_presupuesto, $desc, $cant, $precio]);
        
        recalcularTotal($pdo, $id_presupuesto);
        
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($accion === 'eliminar') {
        if(!tienePermiso('crear_presupuesto', $pdo)) { echo json_encode(['status' => 'error', 'msg' => 'Sin permisos']); exit; }

        $id_item = $_POST['id_item'] ?? 0;
        $id_presupuesto = $_POST['id_presupuesto'] ?? 0;

        $stmt = $pdo->prepare("DELETE FROM presupuesto_items WHERE id_item = ? AND id_presupuesto = ?");
        $stmt->execute([$id_item, $id_presupuesto]);
        
        recalcularTotal($pdo, $id_presupuesto);
        
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($accion === 'aprobar_con_firma') {
        if(!tienePermiso('aprobar_presupuesto', $pdo)) { echo json_encode(['status' => 'error', 'msg' => 'No tienes permiso para aprobar']); exit; }

        $id_presupuesto = $_POST['id_presupuesto'] ?? 0;
        $firma = $_POST['firma'] ?? ''; // La firma viene en Base64 desde el JS

        if(empty($firma)) { echo json_encode(['status' => 'error', 'msg' => 'Firma vacía']); exit; }

        // Actualizamos estado y guardamos el código de la firma en la DB
        $stmt = $pdo->prepare("UPDATE presupuestos SET estado = 'Aprobado', firma_base64 = ? WHERE id_presupuesto = ?");
        $stmt->execute([$firma, $id_presupuesto]);

        echo json_encode(['status' => 'ok']);
        exit;
    }

    echo json_encode(['status' => 'error', 'msg' => 'Acción inválida']);
} catch (PDOException $e) {
    echo json_encode(['status' => 'error', 'msg' => 'Error de BD: ' . $e->getMessage()]);
}
?>