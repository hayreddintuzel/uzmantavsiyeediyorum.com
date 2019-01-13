<?php
/**
 * User Profile Main
 * return html
 */

global $current_user, $wp_roles,$userdata,$post;
$user_identity  = $current_user->ID;
$timezones  = apply_filters('docdirect_time_zones', array());
$time_zone	= get_user_meta($user_identity, 'default_timezone', true);
?>
<div class="tg-bordertop tg-haslayout">
	<div class="tg-formsection">
		<div class="tg-heading-border tg-small">
			<h3><?php esc_attr_e('Timezone','docdirect');?></h3>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<p><?php esc_attr_e('Please select your timezone in which you are providing your services. This will be used for business hours and appointments.','docdirect');?></p>
				<?php if( !empty( $timezones ) ) {?>
					<span class="tg-select">
						<select name="basics[default_timezone]" class="chosen-select">
							<?php								
							foreach ($timezones as $key => $value) { 
								if( $time_zone == $key ){
									$selected = 'selected';
								} else {
									$selected = '';
								}	
							?>
							<option value="<?php echo esc_attr( $key ); ?>" <?php echo esc_attr( $selected ); ?>><?php echo esc_attr( $value ); ?></option>
							<?php } ?>
						</select>									
					</span>
				<?php } ?>
			</div>	
		</div>
	</div>
</div>