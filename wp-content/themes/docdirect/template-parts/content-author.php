<?php
/**
 *
 * The template part for displaying results in search pages.
 *
 * @package   Docdirect
 * @author    themographics
 * @link      https://themeforest.net/user/themographics/portfolio
 * @since 1.0
 */
if ( ! is_active_sidebar( 'sidebar-1' ) ) {
	$section_width = 'col-md-12 col-sm-12 col-xs-12';
} else{
	$section_width = 'col-md-8 col-sm-12 col-xs-12';
}

$aside_class = 'pull-right';
$content_class = 'pull-left';
			
?>
<div class="<?php echo esc_attr( $section_width );?> page-section <?php echo sanitize_html_class($content_class); ?>">
	<?php 
		get_template_part( 'template-parts/archive-templates/content', 'list' );
	?>
</div>
<?php if ( is_active_sidebar( 'sidebar-1' )  ) {?>
	<aside id="tg-sidebar" class="tg-sidebar col-md-4 col-sm-12 col-xs-12 <?php echo sanitize_html_class($aside_class); ?>">
		<?php get_sidebar(); ?>
	</aside>
<?php } ?>
			
