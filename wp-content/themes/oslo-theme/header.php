<!DOCTYPE html>
<html <?php language_attributes(); ?>>
  <head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <?php wp_head(); ?>
  </head>
  <body <?php body_class(); ?>>
  <header class="site-header">

      <div class="user-greeting">
        <div class="site-header__util">
          <?php  
            if (is_user_logged_in()) {
              // echo '<a href="' . site_url('/agencias') . '" class="btn btn--small btn--orange float-left push-right btn-agencias">Ir a Agencias</a>';
              echo '<a href="' . wp_logout_url(home_url()) . '" class="btn-logout btn btn--small btn--orange float-left push-right btn--with-photo">Cerrar sesión</a>';
            } 
            // else {
            //     echo '<a href="' . site_url('/registro-empresa') . '" class="btn btn--small btn--dark-orange float-left btn-login">Login / Registro</a>';
            // }
          ?>
        </div>
      </div>
      <div class="site-header__util user-greeting">
        <?php $current_user = wp_get_current_user(); // Obtiene el usuario actual
          if (is_user_logged_in()) {
              echo 'Hola, ' . ucwords(esc_html($current_user->display_name)) . '!'; // Muestra el nombre del usuario
          } else {
              echo '¡Bienvenido visitante!';
          } 
        ?>
      </div>


      <a href="<?php echo esc_url(site_url('/')); ?>" class="plantel-logo">
        <img src="<?php 
          if(is_front_page()) {
            echo get_theme_file_uri('/images/logo-plantel-home.png');
          } else {
            echo get_theme_file_uri('/images/logo-plantel-azul.png');
          } ?>" alt="Logo de Oslo" class="logo">
      </a>

      <div class="<?php if(is_front_page()) { ?>texto-blanco<?php } else { ?>texto-negro<?php } ?>"><hr></div>
      
      <div class="container">

        <a href="<?php echo esc_url(site_url('/search')); ?>" class="js-search-trigger site-header__search-trigger"><i class="fa fa-search" aria-hidden="true"></i></a>
        <i class="site-header__menu-trigger fa fa-bars" aria-hidden="true"></i>
        <div class="site-header__menu group">
                    
          <?php if(is_front_page()) { ?><nav class="main-navigation texto-blanco"> <?php } else { ?> <nav class="main-navigation"> <?php } ?>
              <?php
                wp_nav_menu(array(
                  'theme_location' => 'headerMenuLocation'
                ));
              ?> 
            </nav> 





        </div>
      </div>

      <div class="<?php if(is_front_page()) { ?>texto-blanco<?php } else { ?>texto-negro<?php } ?>"><hr></div>
      
    </header>