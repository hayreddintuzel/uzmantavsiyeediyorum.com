<?php

if (!defined('FW')) {
    die('Forbidden');
}
/**
 * Article Extension 
 * General Helpers and Functions...
 * Enable Media Button
 * IF user role is Professional or Business.
 */
if (current_user_can('professional') || current_user_can('business')) {
    if (!function_exists('fw_ext_articles_sp_allow_uploads')) {
		add_action('init', 'fw_ext_articles_sp_allow_uploads');

		function fw_ext_articles_sp_allow_uploads() {

			/**
			 * Redirect User if current user is not
			 * Admin.
			 */
			if (is_admin() && !current_user_can('administrator') && !( defined('DOING_AJAX') && DOING_AJAX )) {
				wp_redirect(home_url('/'));
			}

			//Professional users
			$professional = get_role('professional');
			$professional->add_cap('upload_files');

			$professional->add_cap('publish_posts');
			$professional->add_cap('edit_posts');
			$professional->add_cap('edit_published_posts');
			$professional->add_cap('edit_others_posts');
			$professional->add_cap('delete_posts');
			$professional->add_cap('delete_others_posts');
			$professional->add_cap('delete_published_posts');

			$professional->add_cap('publish_pages');
			$professional->add_cap('edit_pages');
			$professional->add_cap('edit_published_pages');
			$professional->add_cap('edit_others_pages');
			
		}
	}
	
	/**
	 * Upload Featured Image Using URL and ID.
	 * @return query
	 */
	if (!function_exists('fw_ext_articles_show_current_user_attachments')) {
		add_filter('ajax_query_attachments_args', 'fw_ext_articles_show_current_user_attachments', 10, 1);
		function fw_ext_articles_show_current_user_attachments($query = array()) {
			global $current_user;

			$user_id = $current_user->ID;
			if ($user_id) {
				$query['author'] = $user_id;
			}
			return $query;
		}
	}
	
	/**
	 * Upload Featured Image Using URL and ID.
	 * @param type $image_url
	 * @param type $post_id
	 */
	if (!function_exists('fw_ext_articles_feratured_image')) {
		function fw_ext_articles_feratured_image($image_url, $post_id) {
			$upload_dir = wp_upload_dir();
			$image_data = file_get_contents($image_url);
			$filename 	= basename($image_url);
			
			if (wp_mkdir_p($upload_dir['path']))
				$file = $upload_dir['path'] . '/' . $filename;
			else
				$file = $upload_dir['basedir'] . '/' . $filename;
			file_put_contents($file, $image_data);

			$wp_filetype = wp_check_filetype($filename, null);
			$attachment = array(
				'post_mime_type' => $wp_filetype['type'],
				'post_title' => sanitize_file_name($filename),
				'post_content' => '',
				'post_status' => 'inherit'
			);
			$attach_id = wp_insert_attachment($attachment, $file, $post_id);
			require_once(ABSPATH . 'wp-admin/includes/image.php');
			$attach_data = wp_generate_attachment_metadata($attach_id, $file);
			wp_update_attachment_metadata($attach_id, $attach_data);
			set_post_thumbnail($post_id, $attach_id);
		}
	}
	/**
	 * Upload Featured Image Using URL and ID.
	 * @param type $image_url
	 * @param type $post_id
	 */
	if (!function_exists('docdirect_get_article_categories')) {
		function docdirect_get_article_categories($current,$taxonomyName){
			//This gets top layer terms only.  This is done by setting parent to 0.  
			$parent_terms = get_terms( $taxonomyName, array( 'parent' => 0, 'orderby' => 'slug', 'hide_empty' => false ) ); 
			$options	= '';
			if( isset( $parent_terms ) && !empty( $parent_terms ) ) {
				foreach ( $parent_terms as $pterm ) {
					$selected	= '';

					if( !empty( $current ) 
						&& is_array($current)
						&& in_array($pterm->term_id,$current)
					){
						$selected	= 'selected';
					}

					$options	.= '<option '.$selected.' value="'.$pterm->term_id.'">'.$pterm->name.'</option>';
				}
			}

			echo force_balance_tags( $options );
		}
	}
}

/**
 * Removes the original author meta box and replaces it
 * with a customized version.
 */
if (!function_exists('docdirect_replace_post_author_meta_box')) {
	add_action( 'add_meta_boxes', 'docdirect_replace_post_author_meta_box' );
	function docdirect_replace_post_author_meta_box() {
		$post_type = get_post_type();
		$post_type_object = get_post_type_object( $post_type );
		if( $post_type == 'sp_articles' ){
			if ( post_type_supports( $post_type, 'author' ) ) {
				if ( is_super_admin() || current_user_can( $post_type_object->cap->edit_others_posts ) ) {
					remove_meta_box( 'authordiv', $post_type, 'core' );
					add_meta_box( 'authordiv', esc_html__( 'Author', 'docdirect' ), 'docdirect_post_author_meta_box', null, 'normal' );
				}
			}
		}
	}
}

/**
 * Display form field with list of authors.
 * Modified version of post_author_meta_box().
 *
 * @global int $user_ID
 *
 * @param object $post
 */
if (!function_exists('docdirect_post_author_meta_box')) {
	function docdirect_post_author_meta_box( $post ) {
		global $user_ID;
		?>
		<label class="screen-reader-text" for="post_author_override"><?php esc_html_e( 'Author', 'docdirect' ); ?></label>
		<?php
		wp_dropdown_users( array(
			'role__in' => [ 'professional' ], // Add desired roles here.
			'name' => 'post_author_override',
			'selected' => empty( $post->ID ) ? $user_ID : $post->post_author,
			'include_selected' => true,
			'show' => 'display_name_with_login',
		) );
	}
}

