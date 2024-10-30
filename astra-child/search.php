<?php
/**
 * The template for displaying search results pages.
 *
 * @link https://developer.wordpress.org/themes/basics/template-hierarchy/#search-result
 *
 * @package Astra
 * @since 1.0.0
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly.
}

get_header(); ?>

<?php if ( astra_page_layout() == 'left-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

	<div id="primary" <?php astra_primary_class(); ?>>

		<?php astra_primary_content_top(); ?>

		<?php astra_archive_header(); ?>

		<div class="customized-search-field">
    <form role="search" method="get" class="search-form" action="<?php echo esc_url( home_url( '/' ) ); ?>">
        <label>
            <input type="search" class="search-field" placeholder="Szukaj …" value="" name="s" />
        </label>
        
        <button type="submit" class="custom-search-submit"><img src="<?php echo esc_url(home_url('/wp-content/uploads/2024/07/search.svg')); ?>" class="search-icon" alt="Search icon" /></button>
    </form>
</div>

 <?php if ( have_posts() ) : ?>
        <?php astra_content_loop(); ?>
        <?php astra_pagination(); ?>
    <?php else : ?>
        <!-- Show only the search form with no additional message if no results are found -->
    <?php endif; ?>

    <?php astra_primary_content_bottom(); ?>

	</div><!-- #primary -->

<?php if ( astra_page_layout() == 'right-sidebar' ) : ?>

	<?php get_sidebar(); ?>

<?php endif ?>

<?php get_footer(); ?>
