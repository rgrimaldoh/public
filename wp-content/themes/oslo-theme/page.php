<?php
  get_header();

  while(have_posts()) {
    the_post(); ?>

  <div class="container container--narrow page-section">

    <div class="generic-content">
      <h5 class="titulo-pagina texto-izquierda"><?php the_title(); ?></h5> 
      <?php the_content(); ?>
    </div>

  </div>
    
  <?php }

  get_footer();
?>