<?php
/**
 * Template Name: Galeria videos
 *
 * Description: Plantilla pagina de Galería de videos
 */
get_header(); ?>

<section id="primary" class="site-content span12" role="main">

	<section class="content row wrap">

	    <?php if ( have_posts() ) : ?>

			<?php /* The loop */ ?>
			<?php while ( have_posts() ) : the_post(); ?>
					<?php get_template_part( 'content', get_post_format() ); ?>
			<?php endwhile; ?>

			<?php themetest_paging_nav(); ?>

		<?php else : ?>
			<?php get_template_part( 'content', 'none' ); ?>
		<?php endif; ?>

	</section> <!-- .content -->
</section> <!-- #primary -->

<?php get_footer(); ?>
