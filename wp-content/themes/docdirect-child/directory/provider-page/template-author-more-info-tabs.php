<?php
/**
 *
 * Author Tabs Template.
 *
 * @package   Listingo
 * @author    themographics
 * @link      https://themeforest.net/user/themographics/portfolio
 * @since 1.0
 */
/**
 * Get User Queried Object Data
 */
$author_profile = $wp_query->get_queried_object();

$directory_type	= $author_profile->directory_type;
if(function_exists('fw_get_db_settings_option')) {
	$questions    = fw_get_db_post_option($directory_type, 'qa', true);
	$reviews_switch    = fw_get_db_post_option($directory_type, 'reviews', true);
}

$tabClass	= 'active';
if( ( isset( $questions )  && $questions === 'enable' ) || ( isset( $reviews_switch ) && $reviews_switch === 'enable' ) ){?>
  
<!-- For Tags -->
<?
$user_identity  		= $author_profile->ID;
$user = $current_user;
wp_nonce_field( 'user-tags', 'user-tags' ); 
?>
<div class="user-taxonomy-wrapper">
<?php
$terms		    = get_user_meta( $user_identity, 'doc_sub_categories', true);
?>
<div class="tg-bordertop tg-haslayout">
	<div class="tg-formsection">
		<div class="tg-heading-border tg-small">
			<h3><?php esc_html_e('Tags','docdirect');?></h3>
		</div>
		<div class="row">
			<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
				<div class="form-group">
					<?
					if(!empty($terms))
						foreach($terms as $oneSub) {
							echo '<a target="_blank" href="'.get_site_url().'/tag/'.$oneSub.'"><div style="border:none;color:white;padding:5px 5px;text-align:center;text-decoration:none;display:inline-block;font-size:'.rand(10,18).'px;margin:4px 2px;cursor:pointer;background-color: #008CBA;">'.$oneSub.'</div></a>';
						}
					?>
					
				</div>
			</div>
		</div>
	</div>
</div>
<!-- For Tags -->
  
  <div class="detail-panel-wrap tg-haslayout tg-companyfeaturebox">
   <div class="tg-companyfeaturebox tg-reviews">
		<ul class="tg-reviewstabs" role="tablist">
			<?php if( isset( $reviews_switch ) && $reviews_switch === 'enable' ){?>
				<li role="presentation" class="<?php echo esc_attr( $tabClass );?>"><a href="#reviews" aria-controls="reviews" role="tab" data-toggle="tab"><?php esc_html_e('Reviews', 'docdirect'); ?></a>
				</li>
			<?php $tabClass = '';}?>
			<?php  
				if( isset( $questions )  && $questions === 'enable' ){
					if (function_exists('fw_get_db_settings_option') && fw_ext('questionsanswers')) {
						if( apply_filters('docdirect_get_theme_settings', 'qa_restriction') === 'paid' ){
						   if( apply_filters('docdirect_is_setting_enabled',$author_profile->ID,'dd_qa' ) === true ){?>
							<li class="<?php echo esc_attr( $tabClass );?>" role="presentation"><a href="#consult" aria-controls="consult" role="tab" data-toggle="tab"><?php esc_html_e('Consult Q&A', 'docdirect'); ?></a></li>
						   <?php }?>
						<?php } else{?>
							<li class="<?php echo esc_attr( $tabClass );?>" role="presentation"><a href="#consult" aria-controls="consult" role="tab" data-toggle="tab"><?php esc_html_e('Consult Q&A', 'docdirect'); ?></a></li>
					  <?php }
					}
				}
			?>
		</ul>		
		<div class="tg-tabcontent tab-content">
			<?php 
				if( isset( $reviews_switch ) && $reviews_switch === 'enable' ){
					get_template_part('directory/provider-page/template-author', 'reviews');
				}
			?>
			<?php  
				if( isset( $questions )  && $questions === 'enable' ){
					if (function_exists('fw_get_db_settings_option') && fw_ext('questionsanswers')) {
						if( apply_filters('docdirect_get_theme_settings', 'qa_restriction') === 'paid' ){
						   if( apply_filters('docdirect_is_setting_enabled',$author_profile->ID,'dd_qa' ) === true ){?>
							<div role="tabpanel" class="tg-tabpane tab-pane <?php echo esc_attr( $tabClass );?>" id="consult">
								<?php do_action('render_questions_listing_view'); ?>
							</div>
						   <?php }?>
						<?php } else{?>
							<div role="tabpanel" class="tg-tabpane tab-pane <?php echo esc_attr( $tabClass );?>" id="consult">
								<?php do_action('render_questions_listing_view'); ?>
							</div>
					  <?php }
					}
				}
			?>
		</div>
	</div>
  </div>
<?php }?>