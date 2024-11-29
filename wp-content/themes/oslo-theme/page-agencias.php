<?php
/* Template Name: Agencias */

if (!is_user_logged_in()) {
    wp_safe_redirect(site_url('/home'));
    exit;
}
get_header();
?>
<div class="espacio-menu-pagina"></div>
<div class="espacio-menu-pagina"></div>

<div class="container contenedor-registro">
    <h2>Bienvenido a Agencias</h2>
    <p>Aquí podrás gestionar tus agencias.</p>
</div>

<div class="container contenedor-registro">
  <br><br><br>

  <div id="filtros">
      <select id="continentes">
          <option value="">Seleccione un continente</option>
      </select>

      <select id="paises" disabled>
          <option value="">Seleccione un país</option>
      </select>
  </div>

  <div id="galeria" class="galeria-ciudades"></div>


</div>


<?php
get_footer();
?>