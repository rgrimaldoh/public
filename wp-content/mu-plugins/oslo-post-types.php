<?php

function oslo_post_types() {
    register_post_type('continente', array(
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
        'public' => true,
        'show_in_rest' => true,
        'labels' => array(
            'name' => 'Continentes',
            'add_new_item' => 'Adicionar novo Continente',
            'edit_item' => 'Modificar Continente',
            'all_items' => 'Todos os Continentes',
            'singular_name' => 'Continente'
        ),
        'menu_icon' => 'dashicons-admin-site'
    ));

    register_post_type('pais', array(
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
        'public' => true,
        'show_in_rest' => true,
        'labels' => array(
            'name' => 'Paises',
            'add_new_item' => 'Adicionar novo Pais',
            'edit_item' => 'Modificar Pais',
            'all_items' => 'Todos os Paises',
            'singular_name' => 'Pais'
        ),
        'menu_icon' => 'dashicons-location-alt'
    ));

    register_post_type('ciudad', array(
        'supports' => array('title', 'editor', 'excerpt', 'thumbnail'),
        'public' => true,
        'show_in_rest' => true,
        'has_archive' => true,
        'labels' => array(
            'name' => 'Cidades',
            'add_new_item' => 'Adicionar nova Cidade',
            'edit_item' => 'Modificar Cidade',
            'all_items' => 'Todos as Cidades',
            'singular_name' => 'Cidade'
        ),
        'menu_icon' => 'dashicons-airplane'
    ));
}

add_action('init', 'oslo_post_types');

?>
