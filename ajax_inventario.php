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
    if ($accion === 'carga_masiva') {
        if(!tienePermiso('carga_masiva', $pdo)) { echo json_encode(['status' => 'error', 'msg' => 'Sin permisos']); exit; }
        if(!isset($_FILES['archivo_csv']) || $_FILES['archivo_csv']['error'] !== UPLOAD_ERR_OK) { echo json_encode(['status' => 'error', 'msg' => 'Error al subir el archivo']); exit; }
        
        $file = fopen($_FILES['archivo_csv']['tmp_name'], 'r');
        $insertados = 0;
        
        // Saltamos la cabecera si existe
        fgetcsv($file, 1000, ",");
        
        while (($linea = fgetcsv($file, 1000, ",")) !== FALSE) {
            if(count($linea) >= 4) {
                $codigo = trim($linea[0]) ?: null;
                $nombre = trim($linea[1]);
                $cant = floatval($linea[2]);
                $precio = floatval($linea[3]);
                
                if(!empty($nombre)) {
                    $stmt = $pdo->prepare("INSERT IGNORE INTO inventario (codigo_barras, nombre, cantidad, precio_venta) VALUES (?, ?, ?, ?)");
                    if($stmt->execute([$codigo, $nombre, $cant, $precio])) {
                        $insertados++;
                    }
                }
            }
        }
        fclose($file);
        echo json_encode(['status' => 'ok', 'msg' => "$insertados repuestos cargados correctamente."]);
        exit;
    }
    echo json_encode(['status' => 'error', 'msg' => 'Acción inválida']);
} catch (PDOException $e) {
    if ($e->getCode() == 23000) { echo json_encode(['status' => 'error', 'msg' => 'Ese código de barras ya existe.']); } 
    else { echo json_encode(['status' => 'error', 'msg' => 'Error BD: ' . $e->getMessage()]); }
}
?>