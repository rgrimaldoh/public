<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);

add_action( 'init', 'my_script_enqueuer' );

function my_script_enqueuer() {
    wp_enqueue_script( 'jquery' );
    # before enqueue,if not register then register  
    wp_enqueue_script( 'add-order-front' );
    wp_localize_script( 'add-order-front', 'ajax_object', array( 'ajax_url' => admin_url( 'admin-ajax.php' ) ) );
}

function custom_ajaxurl() {
    echo '<script type="text/javascript">
        var ajaxurl = "' . admin_url('admin-ajax.php') . '";
    </script>';
}

add_action('wp_head', 'custom_ajaxurl');

function oslo_files() {
    wp_enqueue_script('main-oslo-js', get_theme_file_uri('/build/index.js'), array('jquery'), '1.0', true);
    wp_enqueue_style('oslo_styles', get_theme_file_uri('/style.css'));
    wp_enqueue_style('font-awesome', '//maxcdn.bootstrapcdn.com/font-awesome/4.7.0/css/font-awesome.min.css');
    wp_enqueue_style('oslo_main_styles', get_theme_file_uri('/build/style-index.css'));
    wp_enqueue_style('oslo_extra_styles', get_theme_file_uri('/build/index.css')); 
}

add_action('wp_enqueue_scripts', 'oslo_files');

// function agregar_estilos_personalizados() {
//   wp_enqueue_style('estilos-personalizados', get_theme_file_uri('/build/style-index.css'));
// }

// add_action('wp_enqueue_scripts', 'agregar_estilos_personalizados');

function oslo_features() {
    register_nav_menu('headerMenuLocation', 'Header Menu Location');
    add_theme_support('title-tag');
    add_theme_support('post-thumbnails');
    add_image_size('destinosLandscape1200', 1216,470, true);
    add_image_size('destinosLandscape600', 595,447, true);
    add_image_size('destinosLandscape', 400,260, true);
    add_image_size('destinosPortrait',480, 650, true);
    add_image_size('blogVertical', 384,319, true);
  }
  
add_action('after_setup_theme', 'oslo_features');


function consultar_cnpj() {
    error_log("Inicio de consultar_cnpj");
    if (!isset($_GET['cnpj'])) {
        error_log("CNPJ no especificado");
        wp_send_json_error('CNPJ no especificado');
        return;
    }

    $cnpj = sanitize_text_field($_GET['cnpj']);
    error_log("CNPJ recibido: $cnpj");

    $response = wp_remote_get("https://receitaws.com.br/v1/cnpj/{$cnpj}");
    if (is_wp_error($response)) {
        error_log("Error al consultar la API: " . $response->get_error_message());
        wp_send_json_error('Error al consultar la API');
        return;
    }

    $data = wp_remote_retrieve_body($response);
    error_log("Respuesta de la API: $data");

    wp_send_json_success(json_decode($data));
}

add_action('wp_ajax_consultar_cnpj', 'consultar_cnpj');
add_action('wp_ajax_nopriv_consultar_cnpj', 'consultar_cnpj');



//Formularios Inicio

function formulario_registro_usuario() {
    ob_start();
    ?>

    <form method="POST" action="">
        <input type="text" name="cnpj" placeholder="CNPJ" required><br>
        <input type="text" name="empresa" placeholder="Nombre da Empresa" required><br>
        <input type="text" id="first_name" name="first_name" placeholder="Nome" required><br>
        <input type="text" id="last_name" name="last_name" placeholder="Sobrenome" required><br>
        <input type="email" name="email" placeholder="E-mail" required><br>
        <input type="password" name="password" placeholder="Senha" required><br>
        <input type="passwordConfirma" name="passwordConfirma" placeholder="Confirma a senha" required><br>
        <button type="submit" name="registro_usuario">Registrar</button>
    </form>

    <?php
    if (isset($_POST['registro_usuario'])) {
        registrar_usuario();
    }
    return ob_get_clean();
}

add_shortcode('registro_usuario_form', 'formulario_registro_usuario');


function registrar_usuario() {
    $cnpj = sanitize_text_field($_POST['cnpj']);
    $empresa = sanitize_text_field($_POST['empresa']);
    $nombre = sanitize_text_field($_POST['first_name']);
    $apellido = sanitize_text_field($_POST['last_name']);
    $email = sanitize_email($_POST['email']);
    $password = $_POST['password'];

    // Crear usuario
    $user_id = wp_create_user($email, $password, $email);
    if (is_wp_error($user_id)) {
        echo 'Error al registrar: ' . $user_id->get_error_message();
        return;
    }
    // Asignar rol de administrador de empresa
    wp_update_user([
        'ID' => $user_id,
        'first_name' => $nombre,
        'last_name' => $apellido,
    ]);
    add_user_meta($user_id, 'cnpj', $cnpj);
    add_user_meta($user_id, 'empresa', $empresa);
    wp_update_user(['ID' => $user_id, 'role' => 'company_admin']);

    echo 'Empresa registrada con éxito. Por favor espera la autorización.';
}

//Alerta de usuario registrado
function incluir_sweetalert() {
    wp_enqueue_script('sweetalert-js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], null, true);
}
add_action('wp_enqueue_scripts', 'incluir_sweetalert');

//Formularios Inicio

//Codigo de los combos Inicio
add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/continentes', [
        'methods' => 'GET',
        'callback' => 'obtener_continentes',
        'permission_callback' => '__return_true', // Permitir acceso público
    ]);
});

function obtener_continentes() {
    $continentes = get_posts([
        'post_type'      => 'continente',
        'posts_per_page' => -1,
    ]);

    if (empty($continentes)) {
        return rest_ensure_response([]); // Si no hay continentes, devuelve un array vacío
    }

    $result = [];
    foreach ($continentes as $continente) {
        $result[] = [
            'id'     => $continente->ID,
            'nombre' => $continente->post_title,
        ];
    }
    return rest_ensure_response($result);
}

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/paises/(?P<id_continente>\d+)', [
        'methods' => 'GET',
        'callback' => 'obtener_paises_por_continente',
        'permission_callback' => '__return_true',
    ]);
});

function obtener_paises_por_continente($request) {
    $id_continente = $request['id_continente'];

    if (empty($id_continente) || !is_numeric($id_continente)) {
        return new WP_Error('invalid_continent', 'ID de continente inválido', ['status' => 400]);
    }

    $paises = get_posts([
        'post_type'      => 'pais',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => 'paises_en_continentes',
                'value'   => '"' . $id_continente . '"',
                'compare' => 'LIKE',
            ],
        ],
    ]);

    if (empty($paises)) {
        return rest_ensure_response([]); // Si no hay países, devuelve un array vacío
    }

    $result = [];
    $idsUnicos = [];

    foreach ($paises as $pais) {
        if (!in_array($pais->ID, $idsUnicos)) {
            $result[] = [
                'id'     => $pais->ID,
                'nombre' => $pais->post_title,
            ];
            $idsUnicos[] = $pais->ID;
        }
    }

    return rest_ensure_response($result);
}

add_action('rest_api_init', function () {
    register_rest_route('custom/v1', '/ciudades/(?P<pais>\d+)', [
        'methods' => 'GET',
        'callback' => 'obtener_ciudades',
        'permission_callback' => '__return_true',
    ]);
});


function obtener_ciudades($data) {
    $pais_id = $data['pais'];

    if (empty($pais_id) || !is_numeric($pais_id)) {
        return new WP_Error('invalid_country', 'ID de país inválido', ['status' => 400]);
    }

    $ciudades = get_posts([
        'post_type'      => 'ciudad',
        'posts_per_page' => -1,
        'meta_query'     => [
            [
                'key'     => 'destinos_en_paises',
                'value'   => '"' . $pais_id . '"',
                'compare' => 'LIKE',
            ],
            [
                'key'     => 'pronta_referencia',
                'value'   => '1',
                'compare' => '=',
            ],
        ],
    ]);

    if (empty($ciudades)) {
        return rest_ensure_response([]); // Si no hay ciudades, devuelve un array vacío
    }

    $result = [];
    foreach ($ciudades as $ciudad) {
        $archivo_meta = get_post_meta($ciudad->ID, 'arquivo_pronta_referencia', true);
        $archivo_url = is_array($archivo_meta) ? $archivo_meta['url'] : wp_get_attachment_url($archivo_meta);

        $result[] = [
            'id' => $ciudad->ID,
            'nombre' => $ciudad->post_title,
            'imagen' => get_the_post_thumbnail_url($ciudad->ID, 'thumbnail'),
            'archivo' => $archivo_url,
        ];
    }

    return rest_ensure_response($result);
}

//Codigo de los combos Fin

//Otros Inicio

//Alteración del menu si está logeado o no
function personalizar_menu_segun_usuario($items, $args) {
    // Verifica si el usuario está logueado
    if (is_user_logged_in()) {
        // Añadir la opción "Agencias" al menú
        $items .= '<li class="menu-item menu-options"><a href="' . site_url('/agencias') . '">Agencias</a></li>';
    } else {
        // Opcional: Añadir un enlace de login si el usuario no está logueado
        $items .= '<li class="menu-item menu-options"><a href="' . site_url('/registro-usuarios') . '">Iniciar Sesión</a></li>';
    }
    return $items;
}
add_filter('wp_nav_menu_items', 'personalizar_menu_segun_usuario', 10, 2);


//Mostrar la barra de wp-admin solo si es rol administrador
add_action('after_setup_theme', function () {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
});

//Otros Fim


//Usuarios Inicio

//Registro menu usuario
function registro_usuario_menu() {
    add_menu_page(
        'Usuarios', 
        'Usuarios', 
        'autorizar_usuarios', // Cambiado para permitir acceso a ambos roles
        'usuarios', 
        'mostrar_usuarios_registrados', 
        'dashicons-building', 
        20
    );
}
add_action('admin_menu', 'registro_usuario_menu');

//Mostrar lista de usuarios dentro de wp-admin
function mostrar_usuarios_registrados() {
    // Verificar si el usuario tiene permiso
    if (!current_user_can('autorizar_usuarios')) {
        wp_die(__('No tienes permiso para acceder a esta página.'));
    }

    // Mostrar la lista de usuarios como antes
    $users = get_users(['meta_key' => 'cnpj']);
    echo '<h2>Usuarios Registrados</h2>';
    echo '<form method="post">';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Empresa</th><th>CNPJ</th><th>Email</th><th>Nombre</th><th>Apellido</th><th>Autorización</th></tr></thead><tbody>';
    foreach ($users as $user) {
        $empresa = get_user_meta($user->ID, 'empresa', true);
        $cnpj = get_user_meta($user->ID, 'cnpj', true);
        $nombre = get_user_meta($user->ID, 'first_name', true);
        $apellido = get_user_meta($user->ID, 'last_name', true);

        $autorizado = get_user_meta($user->ID, 'acceso_agencias', true) ? 'checked' : '';

        echo "<tr>
                <td>{$empresa}</td>
                <td>{$cnpj}</td>
                <td>{$user->user_email}</td>
                <td>{$nombre}</td>
                <td>{$apellido}</td>
                <td>
                    <label>
                        <input type='checkbox' name='autorizados[{$user->ID}]' value='1' {$autorizado}> Autorizado
                    </label>
                </td>
              </tr>";
    }
    echo '</tbody></table>';
    echo '<input type="submit" name="guardar_autorizaciones" value="Guardar cambios" class="button-primary">';
    echo '</form>';

    if (isset($_POST['guardar_autorizaciones'])) {
        foreach ($users as $user) {
            $permitir = isset($_POST['autorizados'][$user->ID]) ? 1 : 0;
            update_user_meta($user->ID, 'acceso_agencias', $permitir);
        }
        echo '<div class="updated"><p>Autorizaciones actualizadas correctamente.</p></div>';
    }
}

//Usuarios Fin


function actualizar_acceso_agencias($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return;
    }

    error_log("Estado antes: " . get_user_meta($user_id, 'acceso_agencias', true));

    $nuevo_estado = isset($_POST['acceso_agencias']) ? intval($_POST['acceso_agencias']) : 0;

    update_user_meta($user_id, 'acceso_agencias', $nuevo_estado);
    $user = new WP_User($user_id);

    if ($nuevo_estado) {
        $user->add_cap('acceso_agencias');
    } else {
        $user->remove_cap('acceso_agencias');
    }

    error_log("Estado después: " . get_user_meta($user_id, 'acceso_agencias', true));
}


function cargar_scripts_agencias() {
    wp_enqueue_script('agencias-js', get_theme_file_uri('/src/modules/agencias.js'), ['jquery'], '1.0', true);
}

add_action('wp_enqueue_scripts', 'cargar_scripts_agencias');


//Codigo para aceptación de usuarios Inicio

// Agregar campo personalizado en el perfil del usuario
function agregar_campo_acceso_usuario($user) {
    if (!current_user_can('manage_options')) { // Solo administradores pueden editar este campo
        return;
    }
    ?>
    <h3>Permisos de acceso</h3>
    <table class="form-table">
        <tr>
            <th><label for="acceso_agencias">Acceso a la página de agencias</label></th>
            <td>
                <input type="checkbox" name="acceso_agencias" id="acceso_agencias" value="1" <?php checked(get_user_meta($user->ID, 'acceso_agencias', true), 1); ?>>
                <span class="description">Marcar para permitir el acceso a la página de agencias.</span>
            </td>
        </tr>
    </table>
    <?php
}
add_action('show_user_profile', 'agregar_campo_acceso_usuario');
add_action('edit_user_profile', 'agregar_campo_acceso_usuario');

// Guardar el campo personalizado
function guardar_campo_acceso_usuario($user_id) {
    if (!current_user_can('edit_user', $user_id)) {
        return false;
    }
    update_user_meta($user_id, 'acceso_agencias', isset($_POST['acceso_agencias']) ? 1 : 0);
}
add_action('personal_options_update', 'guardar_campo_acceso_usuario');
add_action('edit_user_profile_update', 'guardar_campo_acceso_usuario');


function establecer_acceso_predeterminado($user_id) {
    add_user_meta($user_id, 'acceso_agencias', 0); // 0 significa sin acceso
}
add_action('user_register', 'establecer_acceso_predeterminado');


function agregar_capacidad_autorizar_usuarios() {
    // Agregar capacidad al rol "administrador_empresa"
    $empresa_role = get_role('administrador_empresa');
    if ($empresa_role && !$empresa_role->has_cap('autorizar_usuarios')) {
        $empresa_role->add_cap('autorizar_usuarios');
    }

    // Agregar capacidad al rol "administrator"
    $admin_role = get_role('administrator');
    if ($admin_role && !$admin_role->has_cap('autorizar_usuarios')) {
        $admin_role->add_cap('autorizar_usuarios');
    }
}
add_action('init', 'agregar_capacidad_autorizar_usuarios');

//asignar capacidades adicionales
function asignar_capacidades_basicas() {
    $role = get_role('administrador_empresa');
    if ($role) {
        $role->add_cap('read');
        $role->add_cap('edit_users'); // Permite gestionar usuarios
        $role->add_cap('list_users'); // Permite ver la lista de usuarios
    }
}
add_action('init', 'asignar_capacidades_basicas');

// function verificar_capacidad_usuario_actual() {
//     if (current_user_can('acceso_agencias')) {
//         echo '<div class="notice notice-success"><p>El usuario tiene permiso para acceder a la página de agencias.</p></div>';
//     } else {
//         echo '<div class="notice notice-error"><p>El usuario NO tiene permiso para acceder a la página de agencias.</p></div>';
//     }
// }
// add_action('admin_notices', 'verificar_capacidad_usuario_actual');

function autorizar_usuario_para_agencias($user_id) {
    $user = new WP_User($user_id);
    $user->add_cap('acceso_agencias'); // Agrega la capacidad al usuario
}

//verificar acceso a agencias

function verificar_acceso_agencias() {
    if (is_page('agencias') &&  !current_user_can('acceso_agencias')) {
        ?>
            <button type="button" name="registro_usuario" class="button-primary"><a href="' . site_url('/') . '">Back to Home</a></button>
        <?php
        wp_die(__('No tienes permiso para acceder a la página agencias. Solicite acceso al administrador'));

    }
}
add_action('template_redirect', 'verificar_acceso_agencias');


// //Verificación capacidad 
// function verificar_capacidade_agencia() {
//     $current_user = wp_get_current_user();
//     if ($current_user->has_cap('acceso_agencias')) {
//         echo 'El usuario tiene la capacidad "acceso_agencias".';
//     } else {
//         echo 'El usuario NO tiene la capacidad "acceso_agencias".';
//     }
    
// }
// add_action('admin_notices', 'verificar_capacidade_agencia');


//Verificar que la meta clave acceso_agencias esté sincronizada con la capacidad
add_action('init', function () {
    $current_user = wp_get_current_user();

    if ($current_user->exists()) {
        $acceso_meta = get_user_meta($current_user->ID, 'acceso_agencias', true);

        if ($acceso_meta == 1) {
            $current_user->add_cap('acceso_agencias');
        } else {
            $current_user->remove_cap('acceso_agencias');
        }
    }
});


function registrar_scripts_mascara() {
    wp_enqueue_script(
        'imask',
        'https://unpkg.com/imask',
        [],
        null,
        true
    );
}
add_action('wp_enqueue_scripts', 'registrar_scripts_mascara');


?>
