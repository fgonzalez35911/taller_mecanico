</main> </div> <nav class="bottom-nav">
    <a href="index.php" style="text-decoration: none; color: var(--text-color); font-weight: bold;">Inicio</a>
    <a href="#" style="text-decoration: none; color: var(--text-color); font-weight: bold;">Vehículos</a>
    <a href="#" style="text-decoration: none; color: var(--text-color); font-weight: bold;">Caja</a>
</nav>

<script>
    // Lógica para Modo Oscuro
    function toggleDarkMode() {
        const body = document.body;
        if (body.getAttribute('data-theme') === 'dark') {
            body.removeAttribute('data-theme');
            localStorage.setItem('theme', 'light');
        } else {
            body.setAttribute('data-theme', 'dark');
            localStorage.setItem('theme', 'dark');
        }
    }

    // Comprobar preferencia guardada al cargar
    if(localStorage.getItem('theme') === 'dark') {
        document.body.setAttribute('data-theme', 'dark');
    }

    // Toggle Sidebar para móviles
    function toggleSidebar() {
        document.getElementById('sidebar').classList.toggle('active');
    }

    // Mostrar botón menú solo en móviles
    if (window.innerWidth <= 768) {
        document.getElementById('btn-menu-mobile').style.display = 'block';
    }

    // Configuración global de SweetAlert2 (Toasts elegantes)
    const Toast = Swal.mixin({
        toast: true,
        position: 'bottom-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true,
        background: 'var(--card-bg)',
        color: 'var(--text-color)'
    });

    // Función 20: Bloqueo por Inactividad (15 minutos = 900000 ms)
    let tiempoInactividad;
    function resetearTiempo() {
        clearTimeout(tiempoInactividad);
        tiempoInactividad = setTimeout(() => {
            Swal.fire({
                title: 'Sesión Expirada por Inactividad',
                icon: 'warning',
                allowOutsideClick: false,
                confirmButtonText: 'Volver a ingresar',
                background: 'var(--bg-color)',
                color: 'var(--text-color)'
            }).then(() => {
                window.location.href = 'logout.php';
            });
        }, 900000);
    }
    window.onload = resetearTiempo;
    document.onmousemove = resetearTiempo;
    document.onkeypress = resetearTiempo;
</script>

<script src="https://cdn.jsdelivr.net/npm/quagga@0.12.1/dist/quagga.min.js"></script>

</body>
</html>