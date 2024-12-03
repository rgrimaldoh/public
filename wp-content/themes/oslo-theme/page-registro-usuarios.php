<?php
/* Template Name: Registro o Login de Empresa */

// Si el usuario está logueado, redirigir antes de cargar contenido

use const Avifinfo\UNDEFINED;

if (is_user_logged_in()) {
    error_log("Usuario ya logueado, redirigiendo a /agencias");
    wp_safe_redirect(site_url('/agencias'));
    exit;
}

// Procesar el formulario de registro
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['registro_usuario'])) {
    error_log("Procesando registro de empresa");
    $cnpj = sanitize_text_field($_POST['cnpj']);
    $empresa = sanitize_text_field($_POST['empresa']);
    $nombre = sanitize_text_field($_POST['first_name']);
    $apellido = sanitize_text_field($_POST['last_name']);
    $email = sanitize_email($_POST['email']);
    $password = sanitize_text_field($_POST['password']);
    $passwordConfirma = sanitize_text_field($_POST['passwordConfirma']);

    $registro_error = '';
    if (email_exists($email)) {
        $registro_error = 'El correo ya está registrado.';
    }    
    if ($password !== $passwordConfirma) {
        $registro_error = 'Las contraseñas no coinciden';
    }
    if (!is_numeric($cnpj) || strlen($cnpj) !== 14) {
        $registro_error = 'CNPJ inválido';
    }
    if ($empresa == 'Empresa no encontrada') {
        $registro_error = 'Empresa inválida';
    }    
    if($registro_error === '') {
        $user_id = wp_create_user($email, $password, $email);
        if (!is_wp_error($user_id)) {
            update_user_meta($user_id, 'cnpj', $cnpj);
            update_user_meta($user_id, 'empresa', $empresa);
            update_user_meta($user_id, 'first_name', $nombre);
            update_user_meta($user_id, 'last_name', $apellido);
            ob_end_clean();
            wp_safe_redirect(site_url('/registro-usuarios?success=1'));
            exit;
        } else {
            $registro_error = 'Error al crear el usuario.';
        }
    }
}

// Procesar el formulario de inicio de sesión
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['login_usuario'])) {
    error_log("Procesando inicio de sesión de empresa");
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];

    $user = get_user_by('email', $email);
    if ($user) {
        $creds = [
            'user_login' => $user->user_login,
            'user_password' => $password,
            'remember' => true,
        ];
        $user = wp_signon($creds, false);

        if (!is_wp_error($user)) {
            wp_safe_redirect(site_url('/agencias'));
            exit;
        } else {
            $login_error = 'Error al iniciar sesión. Verifique sus credenciales.';
        }
    } else {
        $login_error = 'Correo no encontrado. Regístrese primero.';
    }
}

get_header();
?>

<!-- HTML de la página de registro/login -->
<div class="espacio-menu-pagina"></div><div class="espacio-menu-pagina"></div>
<div class="container contenedor-registro">
    <h1 class="titulos-geral">Registro o Login de Empresa</h1><br>
    <p>
        Bienvenido a la página de registro o login de empresa. Aquí podrás registrarte como empresa o iniciar sesión si
        ya tienes una cuenta.
    </p>
</div>

<div class="container contenedor-float">
    <div class="div-float">
        <h3>Já tem cadastro?</h3>
        <?php if (!empty($login_error)) echo '<p style="color: red;">' . $login_error . '</p>'; ?>
        <form method="POST">
            <label>E-mail</label><br>
            <input type="email" name="email" required><br><br>
            <label>Senha</label><br>
            <input type="password" name="password" required><br><br>
            <button type="submit" name="login_usuario">Iniciar Sesión</button>
        </form>
    </div>

    <div class="div-float">
        <h3>Faça seu cadastro</h3>
        <?php if (!empty($registro_error)) echo '<p style="color: red;">' . $registro_error . '</p>'; ?>
        <form method="POST">
            <label>CNPJ</label><br>
            <input type="text" id="cnpj" name="cnpj" required><br><br>
            <label>Empresa</label><br>
            <input type="text" id="empresa" name="empresa" readonly required><br><br>
            <label>Nombre</label><br>
            <input type="text" id="first_name" name="first_name" required><br><br>
            <label>Apellido</label><br>
            <input type="text" id="last_name" name="last_name" required><br><br>
            <label>Email</label><br>
            <input type="email" name="email" required><br><br>
            <label>Senha</label><br>
            <input type="password" name="password" required><br><br>
            <label>Confirme sua senha</label><br>
            <input type="password" name="passwordConfirma" required><br><br>
            <button type="submit" name="registro_usuario">Registrar</button>
        </form>
    </div>
</div>

<?php get_footer(); ?>

<!-- Script para Obtener el nombre de la empresa a partir del CNPJ -->
<script>
document.getElementById('cnpj').addEventListener('blur', async function () {
    const cnpj = this.value.replace(/[^\d]/g, '');
    const empresaField = document.getElementById('empresa');

    if (cnpj.length === 14) {
        try {
            empresaField.value = "Buscando...";
            const response = await fetch(`/wp-admin/admin-ajax.php?action=consultar_cnpj&cnpj=${cnpj}`);
            const data = await response.json();

            if (data.success && data.data.status === "OK") {
                empresaField.value = data.data.nome || "Nombre no disponible";
            } else {
                empresaField.value = "Empresa no encontrada";
            }
        } catch (error) {
            empresaField.value = "Error al buscar";
            console.error("Error al consultar la API:", error);
        }
    } else {
        empresaField.value = "CNPJ inválido";
    }
});
</script>

<?php
if (isset($_GET['success']) && $_GET['success'] == 1) {
    echo '<script>
        document.addEventListener("DOMContentLoaded", function() {
            Swal.fire({
                title: "¡Registro Exitoso!",
                text: "El usuario ha sido creado correctamente.",
                icon: "success",
                confirmButtonText: "Aceptar"
            });
        });
    </script>';
}
?>