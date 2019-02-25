<?php
/**
 * User Profile Main
 * return html
 */

global $current_user, $wp_roles,$userdata,$post;
$user_identity  		= $current_user->ID;
$db_subcategory		    = get_user_meta( $user_identity, 'doc_sub_categories', true);
$db_directory_type	 	= get_user_meta( $user_identity, 'directory_type', true);
$sub_category_terms 	= wp_get_post_terms($db_directory_type, 'sub_category', array("fields" => "all"));

if( apply_filters('docdirect_get_theme_settings', 'sub_category') === 'enable' ){
	if( apply_filters('docdirect_do_check_user_type',$user_identity ) === true ){
		if( !empty( $sub_category_terms ) ) {
?>

		<div class="tg-bordertop tg-haslayout">

			<div class="tg-formsection">
				<div class="tg-heading-border tg-small">
					<h3><?php esc_html_e('Sub Categories','docdirect');?></h3>
				</div>
				<div class="row">
					<div class="col-lg-12 col-md-12 col-sm-12 col-xs-12">
                         <div class="email-params">
                        <p><strong><?php esc_html_e('You can add a tag that is not listed. Insertion will be completed after pressing the update button at the bottom of the page','docdirect');?></strong></p>
                    </div>


                        <div class="user-taxonomy-wrapper">
                            <table class="form-table user-profile-taxonomy">
                                <tr>
                                    <th>
                                        <label for="new-tag-user_tag_categories"></label>
                                    </th>
                                    <td class="ajaxtag">
                                        <input type="text" id="new-tag-user_tag_categories" name="user-tags-data"
                                               class="newtag form-input-tip float-left hide-on-blur" size="16" autocomplete="off" value="">
                                        <input type="button" class="tg-btn button tagadd float-left" value="ADD NEW TAG">
                                        <div class="tagchecklist"></div>
                                        <input type="hidden" name="user-new-tags-data" id="user-tags-categories" value=""/>
                                    </td>
                                </tr>
                            </table>
                        </div>
                        <hr>
                        

						<div class="form-group">
							<select name="subcategory[]" class="chosen-select" multiple>
								<?php docdirect_get_linked_term_options($db_subcategory,'sub_category',$sub_category_terms);?>
							</select>
						</div>
					</div>
				</div>
			</div>
		</div>
<?php }}}?>