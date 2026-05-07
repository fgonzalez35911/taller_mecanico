<?php
// ajax_permisos.php
session_start();
require_once 'conexion.php';

header('Content-Type: application/json');
$accion = $_REQUEST['accion'] ?? '';

try {
    if ($accion === 'crear_permiso') {
        $nombre = $_POST['nombre'] ?? '';
        $desc = $_POST['descripcion'] ?? '';
        
        if(empty($nombre)){
            echo json_encode(['status' => 'error', 'msg' => 'Nombre obligatorio']); exit;
        }
        
        // Convertimos el nombre a formato amigable (ej: "Ver Presupuestos" -> "ver_presupuestos")
        $nombre_limpio = strtolower(str_replace(' ', '_', trim($nombre)));

        $stmt = $pdo->prepare("INSERT INTO permisos (nombre_permiso, descripcion) VALUES (?, ?)");
        $stmt->execute([$nombre_limpio, $desc]);
        echo json_encode(['status' => 'ok']);
        exit;
    }

    if ($accion === 'listar_permisos') {
        $stmt = $pdo->query("SELECT * FROM permisos ORDER BY nombre_permiso ASC");
        echo json_encode($stmt->fetchAll());
        exit;
    }

    if ($accion === 'listar_permisos_rol') {
        $id_rol = $_POST['id_rol'] ?? 0;
        $stmt = $pdo->prepare("SELECT id_permiso FROM rol_permiso WHERE id_rol = ?");
        $stmt->execute([$id_rol]);
        $ids = $stmt->fetchAll(PDO::FETCH_COLUMN);
        echo json_encode(['status' => 'ok', 'permisos' => $ids]);
        exit;
    }

    if ($accion === 'guardar_asignacion') {
        $id_rol = $_POST['id_rol'] ?? 0;
        $permisos = json_decode($_POST['permisos'], true) ?? [];

        if(empty($id_rol)) {
            echo json_encode(['status' => 'error', 'msg' => 'Rol no válido']); exit;
        }

        // Borramos los permisos actuales del rol para poner los nuevos
        $stmtDelete = $pdo->prepare("DELETE FROM rol_permiso WHERE id_rol = ?");
        $stmtDelete->execute([$id_rol]);

        // Insertamos los nuevos
        if(count($permisos) > 0) {
            $stmtInsert = $pdo->prepare("INSERT INTO rol_permiso (id_rol, id_permiso) VALUES (?, ?)");
            foreach($permisos as $id_perm) {
                $stmtInsert->execute([$id_rol, $id_perm]);
            }
        }
        echo json_encode(['status' => 'ok']);
        exit;
    }

    echo json_encode(['status' => 'error', 'msg' => 'Acción no válida']);

} catch (PDOException $e) {
    if ($e->getCode() == 23000) {
        echo json_encode(['status' => 'error', 'msg' => 'El permiso ya existe.']);
    } else {
        echo json_encode(['status' => 'error', 'msg' => 'Error BD: ' . $e->getMessage()]);
    }
}
?>