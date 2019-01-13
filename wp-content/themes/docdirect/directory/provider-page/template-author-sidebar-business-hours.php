<?php
/**
 *
 * Author Education Template.
 *
 * @package   Docdirect
 * @author    themographics
 * @link      https://themeforest.net/user/themographics/portfolio
 * @since 1.0
 */
/**
 * Get User Queried Object Data
 */
global $current_user;
$author_profile = $wp_query->get_queried_object();
$directory_type	= $author_profile->directory_type;
$schedule_time_format  = isset( $author_profile->time_format ) ? $author_profile->time_format : '12hour';
$privacy		= docdirect_get_privacy_settings($author_profile->ID); //Privacy settings
$db_timezone	= get_user_meta($author_profile->ID, 'default_timezone', true);
$time_zone 		= get_user_meta( $author_profile->ID, 'default_timezone', true );	

if( apply_filters('docdirect_is_setting_enabled',$author_profile->ID,'schedules' ) === true ){
	if( !empty( $privacy['opening_hours'] )
	  && 
	  $privacy['opening_hours'] == 'on'
	 ) {?>
		<div class="tg-userschedule">
			<h3><?php esc_html_e('Schedule','docdirect');?></h3>
			<?php if( !empty( $time_zone  ) ){?>
				<span class="timezone-display"><strong><?php esc_html_e('Timezone','listingo');?></strong>:&nbsp;<?php echo esc_attr( $time_zone );?></span>
			<?php }?>
			<ul>
				<?php 
					$week_array	= docdirect_get_week_array();
					$db_schedules	= array();
					if( isset( $author_profile->schedules ) && !empty( $author_profile->schedules ) ){
						$db_schedules	= $author_profile->schedules;
					}

					//Time format
					if( isset( $schedule_time_format ) && $schedule_time_format === '24hour' ){
						$time_format	= 'H:i';
					} else{
						$time_format	= get_option('time_format');
						$time_format	= !empty( $time_format ) ? $time_format : 'g:i A';
					}

					$date_prefix	= date('D');
		
					if( isset( $week_array ) && !empty( $week_array ) ) {
					foreach( $week_array as $key => $value ){
						$start_time_formate	 = '';
						$end_time_formate	   = '';
						$start_time  = $db_schedules[$key.'_start'];
						$end_time	 = $db_schedules[$key.'_end'];
						
						if( !empty( $start_time ) ){
							$start_time_formate	= date_i18n( $time_format, strtotime( $start_time ) );
						}

						if( isset( $end_time ) && !empty( $end_time ) ){ 
							$end_time_formate	= date_i18n( $time_format, strtotime( $end_time ) );
							$end_time_formate	= docdirect_date_24midnight($time_format,strtotime( $end_time ));
						}
						
						//user timezone
						if( !empty( $db_timezone ) )  {									
							$date = new DateTime("now", new DateTimeZone($db_timezone) );
							$current_time_date = $date->format('Y-m-d H:i:s');					
						} else {					  			  	
							$current_time_date = current_time( 'mysql' ); 
						}

						//Current Day	
						$today_day 		= date('D', strtotime($current_time_date));
						$today_day 		= strtolower($today_day);	

						$active	= '';
						if( $today_day == $key ){
							$active	= 'current';
						}

						//
						if( !empty( $start_time_formate ) && $end_time_formate ) {
							$data_key	= $start_time_formate.' - '.$end_time_formate;
						} else if( !empty( $start_time_formate ) ){
							$data_key	= $start_time_formate;
						} else if( !empty( $end_time_formate ) ){
							$data_key	= $end_time_formate;
						} else{
							$data_key	= esc_html__('Closed','docdirect');
						}
					?>
					<li class="<?php echo sanitize_html_class( $active );?>"><span><?php echo esc_attr( $value );?></span><em><?php echo esc_attr( $data_key );?></em></li>

				<?php }}?>
			</ul>
		</div>
<?php }}?>