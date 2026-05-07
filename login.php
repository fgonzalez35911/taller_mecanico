<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login - Sistema Taller</title>
    <link rel="stylesheet" href="style.css">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body style="display: flex; justify-content: center; align-items: center; min-height: 100vh; background-color: var(--bg-color);">

<div class="glass-card" style="width: 100%; max-width: 400px; text-align: center;">
    <h2 style="margin-bottom: 20px;">Ingreso al Sistema</h2>
    <form id="formLogin" onsubmit="event.preventDefault(); procesarLogin();">
        <div style="margin-bottom: 15px;">
            <input type="email" id="login_email" class="swal2-input" placeholder="Correo Electrónico" style="width: 80%; margin: 0 auto; display: block;" required>
        </div>
        <div style="margin-bottom: 20px;">
            <input type="password" id="login_pass" class="swal2-input" placeholder="Contraseña" style="width: 80%; margin: 0 auto; display: block;" required>
        </div>
        <button type="submit" class="btn-ripple" style="width: 80%; background-color: #007bff; color: white; padding: 12px; font-size: 1rem; margin-bottom: 15px;">Ingresar</button>
    </form>
    
    <a href="#" onclick="recuperarClave()" style="color: var(--text-color); font-size: 0.9rem; text-decoration: none;">¿Olvidaste tu contraseña?</a>
</div>

<script>
    const Toast = Swal.mixin({ toast: true, position: 'bottom-end', showConfirmButton: false, timer: 3000, background: 'var(--card-bg)', color: 'var(--text-color)' });

    function procesarLogin() {
        let email = document.getElementById('login_email').value;
        let pass = document.getElementById('login_pass').value;

        let fd = new FormData();
        fd.append('email', email);
        fd.append('password', pass);

        fetch('ajax_login.php', { method: 'POST', body: fd })
        .then(res => res.json())
        .then(res => {
            if(res.status === 'ok') { window.location.href = 'index.php'; } 
            else { Toast.fire({icon: 'error', title: res.msg}); }
        });
    }

    // Función 16: UI de Recuperación de Contraseña
    function recuperarClave() {
        Swal.fire({
            title: 'Recuperar Contraseña',
            text: 'Ingresa tu correo para recibir una nueva clave temporal',
            input: 'email',
            inputPlaceholder: 'tu_correo@ejemplo.com',
            showCancelButton: true,
            confirmButtonText: 'Enviar Código',
            background: 'var(--bg-color)',
            color: 'var(--text-color)',
            preConfirm: (email) => {
                if(!email) { Swal.showValidationMessage('El correo es obligatorio'); return false; }
                return email;
            }
        }).then((result) => {
            if (result.isConfirmed) {
                Toast.fire({icon: 'info', title: 'Módulo de envío pendiente de conexión backend (Próxima fase)'});
            }
        });
    }
</script>
</body>
</html>