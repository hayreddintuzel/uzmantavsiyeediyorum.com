<?php
if (!defined('FW'))
    die('Forbidden');
/**
 * @var $atts
 */

$flag	= rand(1,99999);

?>
<div class="sp-sc-search-questions tg-haslayout search-<?php echo esc_attr( $flag );?>">
    <?php if (!empty($atts['news_heading']) || !empty($atts['news_description'])) { ?>
        <div class="col-xs-12 col-sm-12 col-md-10 col-md-push-1 col-lg-8 col-lg-push-2">
            <div class="doc-section-head">
                <?php if (!empty($atts['news_heading']) || !empty($atts['sub_heading'])) { ?>
                    <div class="doc-section-heading">
                        <?php if (!empty($atts['news_heading'])) { ?>
                        	<h2><?php echo esc_attr($atts['news_heading']); ?></h2>
                        <?php } ?>
                        <?php if (!empty($atts['sub_heading'])) { ?>
							<span><?php echo esc_attr($atts['sub_heading']); ?></span>
						<?php } ?>
                    </div>
                <?php } ?>
                
                <?php if (!empty($atts['news_description'])) { ?>
                    <div class="doc-description">
                        <?php echo wpautop(do_shortcode($atts['news_description'])); ?>
                    </div>
                <?php } ?>
            </div>
        </div>
    <?php } ?>
	<div class="col-sm-offset-1 col-sm-10 col-xs-offset-0 col-xs-12">
		<div class="doc-bannersearcharea">
			<fieldset>
				<div class="doc-fieldsetholder search-input-wrap">
					<div class="form-group">
					   <input class="suggestquestion autocomplete-input" type="text" name="by_title" placeholder="<?php esc_html_e('Search questions by keyword','docdirect');?>" class="form-control">
					</div>							
				</div>
			</fieldset>
		</div>
	</div>
	<?php 
		$script	= "jQuery(document).ready(function (e) {
			jQuery( '.search-".esc_attr( $flag )." .suggestquestion' ).autocomplete({
				source: function( request, response ) {
					jQuery.ajax({
						type: 'POST',
						url: scripts_vars.ajaxurl,
						data: 'keyword=' + request.term + '&action=docdirect_search_questions',
						dataType: 'json',
						success: function (data) {
							response( data );
						}
					});
				},
				select: function( event, ui ) {
					var url = jQuery.trim(ui.item.url)
					event.preventDefault();
					window.location.href = url;
				}
			} );
		} );";
		wp_add_inline_script('jquery-ui-autocomplete', $script, 'after');
	?>
</div>