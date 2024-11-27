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



function formulario_registro_usuario() {
    ob_start();
    ?>

    <form method="POST" action="">
        <input type="text" name="cnpj" placeholder="CNPJ" required><br>
        <input type="text" name="empresa" placeholder="Nombre da Empresa" required><br>
        <input type="text" name="nombre" placeholder="Nome" required><br>
        <input type="text" name="apellido" placeholder="Sobrenome" required><br>
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
    $nombre = sanitize_text_field($_POST['nombre']);
    $apellido = sanitize_text_field($_POST['apellido']);
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

function incluir_sweetalert() {
    wp_enqueue_script('sweetalert-js', 'https://cdn.jsdelivr.net/npm/sweetalert2@11', [], null, true);
}
add_action('wp_enqueue_scripts', 'incluir_sweetalert');


function registro_usuario_menu() {
    add_menu_page(
        'Usuarios', 
        'Usuarios', 
        'manage_options', 
        'usuarios', 
        'mostrar_usuarios_registrados', 
        'dashicons-building', 
        20
    );
}
add_action('admin_menu', 'registro_usuario_menu');

function mostrar_usuarios_registrados() {
    $users = get_users(['meta_key' => 'cnpj']);
    echo '<h2>Usuarios Registrados</h2>';
    echo '<table class="wp-list-table widefat fixed striped">';
    echo '<thead><tr><th>Empresa</th><th>CNPJ</th><th>Email</th><th>Nome</th><th>Sobrenome</th><th>Acciones</th></tr></thead><tbody>';
    foreach ($users as $user) {
        $empresa = get_user_meta($user->ID, 'empresa', true);
        $cnpj = get_user_meta($user->ID, 'cnpj', true);
        $nombre = get_user_meta($user->ID, 'nombre', true);
        $apellido = get_user_meta($user->ID, 'apellido', true);
        echo "<tr>
                <td>{$empresa}</td>
                <td>{$cnpj}</td>
                <td>{$user->user_email}</td>
                <td>{$nombre}</td>
                <td>{$apellido}</td>
                <td>
                    <a href='" . admin_url('user-edit.php?user_id=' . $user->ID) . "'>Editar</a> |
                    <a href='" . wp_nonce_url(admin_url('users.php?action=delete&user=' . $user->ID), 'delete-user_' . $user->ID) . "'>Eliminar</a>
                </td>
              </tr>";
    }
    echo '</tbody></table>';
}

function consultar_cnpj() {
    if (!isset($_GET['cnpj'])) {
        wp_send_json_error('CNPJ no especificado');
        return;
    }

    $cnpj = sanitize_text_field($_GET['cnpj']);
    $response = wp_remote_get("https://receitaws.com.br/v1/cnpj/{$cnpj}");

    if (is_wp_error($response)) {
        wp_send_json_error('Error al consultar la API');
        return;
    }

    $data = wp_remote_retrieve_body($response);
    wp_send_json_success(json_decode($data));
}
add_action('wp_ajax_consultar_cnpj', 'consultar_cnpj');
add_action('wp_ajax_nopriv_consultar_cnpj', 'consultar_cnpj');


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

add_action('after_setup_theme', function () {
    if (!current_user_can('administrator') && !is_admin()) {
        show_admin_bar(false);
    }
});


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
        $archivo = get_post_meta($ciudad->ID, 'arquivo_pronta_referencia', true);
        $result[] = [
            'id'      => $ciudad->ID,
            'nombre'  => $ciudad->post_title,
            'imagen'  => get_the_post_thumbnail_url($ciudad->ID, 'thumbnail'),
            'archivo' => $archivo,
        ];
    }

    return rest_ensure_response($result);
}


//Codigo de los combos Fin

function cargar_scripts_agencias() {
    wp_enqueue_script('agencias-js', get_theme_file_uri('/src/modules/agencias.js'), ['jquery'], '1.0', true);
}

add_action('wp_enqueue_scripts', 'cargar_scripts_agencias');



?>
