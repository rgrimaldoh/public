<footer class="site-footer">

  <div class="div-newsletter">
    <div class="container">
        <form action="<?php echo esc_url(admin_url('admin-post.php')); ?>" method="post">
            <input type="hidden" name="action" value="process_newsletter_form">
            <div class="full-width-split group">
              <div class="full-width-split__one">
                <div class="box-texto-newsletter">
                  <div class="texto-newsletters">
                    <h2 class="titulos-geral texto-blanco">footer</h2>
                  </div><br>
                  <p class="parrafos-geral texto-blanco">
                  </p>
                </div>
              </div>
              <div class="full-width-split__two">
                <div class="box-email inline">
                  <div class="fila-post">
                  </div>
                  <div class="fila-post">
                  </div>
                </div>
              </div>            
            </div>
        </form>
      </div>
  </div>

  <div class="espacio-entre-texto-contenido"></div>

</footer>

<?php wp_footer(); ?>
</body>
</html>