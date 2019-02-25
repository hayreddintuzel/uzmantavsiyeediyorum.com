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
$user = $current_user;
if( apply_filters('docdirect_do_check_user_type',$user_identity ) === true ) {
?>

<?php
}
?>