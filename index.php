<?php 
session_start();
require_once 'conexion.php';
require_once 'header.php'; 

// Lógica Real del Dashboard (Métricas)
$vehiculosRevision = $pdo->query("SELECT COUNT(*) FROM vehiculos WHERE estado = 'En Revisión'")->fetchColumn();
$presupuestosAprob = $pdo->query("SELECT COUNT(*) FROM presupuestos WHERE estado = 'Aprobado'")->fetchColumn();

// Función 6: Detectar cuántos repuestos están por debajo del stock mínimo
$stmtStock = $pdo->query("SHOW TABLES LIKE 'inventario'");
$stockBajo = 0;
if($stmtStock->rowCount() > 0) {
    $stockBajo = $pdo->query("SELECT COUNT(*) FROM inventario WHERE cantidad <= stock_minimo")->fetchColumn();
}
?>

<h1 style="margin-bottom: 20px; font-size: var(--font-title);">Panel de Control General</h1>

<div class="grid-dashboard">
    <div class="glass-card" style="border-left: 5px solid var(--color-warning);">
        <h3>En Revisión</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 10px;"><?php echo $vehiculosRevision; ?></p>
        <small>Vehículos esperando diagnóstico</small>
    </div>

    <div class="glass-card" style="border-left: 5px solid var(--color-danger);">
        <h3>Alertas de Stock</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 10px; color: var(--color-danger);"><?php echo $stockBajo; ?></p>
        <small>Repuestos bajo el límite mínimo</small>
        <?php if($stockBajo > 0): ?>
            <br><button onclick="window.location.href='inventario.php'" class="btn-ripple" style="background-color: var(--color-danger); padding: 5px 10px; font-size: 0.8rem; color: #000; margin-top: 10px;">Revisar Stock</button>
        <?php endif; ?>
    </div>

    <div class="glass-card" style="border-left: 5px solid var(--color-success);">
        <h3>Trabajos Aprobados</h3>
        <p style="font-size: 2rem; font-weight: bold; margin-top: 10px;"><?php echo $presupuestosAprob; ?></p>
        <small>Total de presupuestos cerrados</small>
    </div>
</div>

<div class="glass-card" style="margin-top: 20px; min-height: 300px;">
    <h3>Gráfico de Rendimiento (Función 14)</h3>
    <canvas id="graficoDashboard" width="400" height="150"></canvas>
</div>

<script>
document.addEventListener("DOMContentLoaded", () => {
    // Inicialización de Chart.js con datos de ejemplo (se conectará a la Caja Diaria luego)
    const ctx = document.getElementById('graficoDashboard').getContext('2d');
    new Chart(ctx, {
        type: 'bar',
        data: {
            labels: ['Lun', 'Mar', 'Mié', 'Jue', 'Vie', 'Sáb'],
            datasets: [{
                label: 'Ingresos Simbólicos ($)',
                data: [12000, 19000, 3000, 5000, 20000, 30000],
                backgroundColor: 'rgba(163, 228, 215, 0.5)',
                borderColor: 'rgba(163, 228, 215, 1)',
                borderWidth: 1
            }]
        },
        options: { responsive: true }
    });
});
</script>

<?php require_once 'footer.php'; ?>