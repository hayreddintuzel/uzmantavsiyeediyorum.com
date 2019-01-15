<?php
/**
 * User Profile Main
 * return html
 */

global $current_user, $wp_roles,$userdata,$post;
$user_identity  = $current_user->ID;
$social_links = apply_filters('docdirect_get_social_media_icons_list',array());
?>
<div class="tg-bordertop tg-haslayout">
	<div class="tg-formsection">
		<div class="tg-heading-border tg-small">
			<h3><?php esc_html_e('Social Settings','docdirect');?></h3>
		</div>
		<p><strong><?php esc_html_e('Note: Leave them empty to hide social icons at detail page.','docdirect');?></strong></p>
		<div class="row">
			<?php 
			if( !empty( $social_links ) ){
				foreach( $social_links as $key => $social ){
					$icon		= !empty( $social['icon'] ) ? $social['icon'] : '';
					$classes	= !empty( $social['classses'] ) ? $social['classses'] : '';
					$placeholder		= !empty( $social['placeholder'] ) ? $social['placeholder'] : '';
					$color		= !empty( $social['color'] ) ? $social['color'] : '#484848';
				?>
				<div class="col-md-4 col-sm-6 col-xs-12">
					<div class="form-group">
						<input class="form-control" name="socials[<?php echo esc_attr( $key );?>]" value="<?php echo get_user_meta($user_identity, $key, true); ?>" type="text" placeholder="<?php echo esc_attr( $placeholder );?>">
					</div>
				</div>
			<?php }}?>
		</div>
	</div>
</div>