<?php 
require_once 'seguridad.php'; 

// Verificamos que el usuario esté logueado de verdad
if (!isset($_SESSION['id_usuario']) || !isset($_SESSION['id_rol'])) {
    header("Location: login.php");
    exit;
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Sistema Taller</title>
    
    <link rel="stylesheet" href="style.css">
    
    <link rel="manifest" href="manifest.json">
    <meta name="theme-color" content="#f4f7f6">

    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script>
        // Registrar Service Worker para PWA Instalable
        if ('serviceWorker' in navigator) {
            window.addEventListener('load', () => {
                navigator.serviceWorker.register('sw.js').catch(err => console.log('SW Falló: ', err));
            });
        }
    </script>
    <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/qrcodejs/1.0.0/qrcode.min.js"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/compressorjs/1.2.1/compressor.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/signature_pad@4.1.7/dist/signature_pad.umd.min.js"></script>
</head>
<body>

<div class="main-wrapper">
    <aside class="sidebar" id="sidebar">
        <div style="padding: 20px; border-bottom: 1px solid var(--glass-border);">
            <h2>Menú Taller</h2>
        </div>
        <nav style="padding: 20px;">
            <ul style="list-style: none;">
                <?php if(tienePermiso('ver_dashboard', $pdo)): ?>
                    <li style="margin-bottom: 15px;"><a href="index.php" style="text-decoration: none; color: var(--text-color);">Inicio / Dashboard</a></li>
                <?php endif; ?>
                
                <?php if(tienePermiso('ver_vehiculos', $pdo)): ?>
                    <li style="margin-bottom: 15px;"><a href="recepcion.php" style="text-decoration: none; color: var(--text-color);">Recepción Vehículos</a></li>
                <?php endif; ?>
                
                <?php if(tienePermiso('ver_presupuestos', $pdo)): ?>
                    <li style="margin-bottom: 15px;"><a href="presupuestos.php" style="text-decoration: none; color: var(--text-color);">Presupuestos</a></li>
                <?php endif; ?>
                
                <?php if(tienePermiso('ver_stock', $pdo)): ?>
                    <li style="margin-bottom: 15px;"><a href="inventario.php" style="text-decoration: none; color: var(--text-color);">Inventario</a></li>
                <?php endif; ?>
                
                <?php if(tienePermiso('ver_caja', $pdo)): ?>
                    <li style="margin-bottom: 15px;"><a href="caja.php" style="text-decoration: none; color: var(--text-color);">Caja Diaria</a></li>
                <?php endif; ?>

                <hr style="border-color: var(--glass-border); margin: 15px 0;">
                
                <?php if(tienePermiso('ver_roles', $pdo)): ?>
                    <li style="margin-bottom: 15px;"><a href="administrar_roles.php" style="text-decoration: none; color: var(--text-color);">Roles</a></li>
                    <li style="margin-bottom: 15px;"><a href="administrar_permisos.php" style="text-decoration: none; color: var(--text-color);">Permisos</a></li>
                <?php endif; ?>
                
                <?php if(tienePermiso('ver_usuarios', $pdo)): ?>
                    <li style="margin-bottom: 15px;"><a href="administrar_usuarios.php" style="text-decoration: none; color: var(--text-color);">Usuarios</a></li>
                <?php endif; ?>
                
                <hr style="border-color: var(--glass-border); margin: 15px 0;">
                <li style="margin-bottom: 15px;"><a href="logout.php" style="text-decoration: none; color: var(--color-danger); font-weight: bold;">Cerrar Sesión</a></li>
            </ul>
        </nav>
        <div style="padding: 20px; position: absolute; bottom: 0;">
            <button onclick="toggleDarkMode()" class="btn-ripple" style="background-color: #333;">Modo Oscuro/Claro</button>
        </div>
    </aside>

    <main class="content-area">
        <header style="height: var(--header-height); display: flex; justify-content: space-between; align-items: center; margin-bottom: 20px;">
            <button class="btn-ripple" onclick="toggleSidebar()" style="display: none;" id="btn-menu-mobile">Menú</button>
            <div style="flex-grow: 1; margin: 0 20px;">
                <input type="text" placeholder="Buscar patente, cliente, repuesto..." style="width: 100%; padding: 10px; border-radius: 20px; border: 1px solid #ccc;">
            </div>
        </header>