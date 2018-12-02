<?php
/**
 * Theme functions file
 */

/**
 * Enqueue parent theme styles first
 * Replaces previous method using @import
 * <http://codex.wordpress.org/Child_Themes>
 */

function docdirect_child_theme_enqueue_styles() {
	$parent_theme_version = wp_get_theme('docdirect');
	$child_theme_version  = wp_get_theme('docdirect-child');
    $parent_style  = 'docdirect_theme_style';
	wp_enqueue_style( 'docdirect_child_style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
		$child_theme_version->get('Version')
    );

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css', array('bootstrap.min','choosen'),$parent_theme_version->get( 'Version' ));
}

add_action( 'wp_enqueue_scripts', 'docdirect_child_theme_enqueue_styles' );

function _bp_core_get_user_domain($domain, $user_id, $user_nicename = false, $user_login = false) {
    if ( empty( $user_id ) ){
        return;
    }
    if( isset($user_nicename) ){
        $user_nicename = bp_core_get_username($user_id);
    }
    $after_domain =  bp_get_members_root_slug() . '/' . $user_id;

    $domain = trailingslashit( bp_get_root_domain() . '/' . $after_domain );
    $domain = apply_filters( 'bp_core_get_user_domain_pre_cache', $domain, $user_id, $user_nicename, $user_login );
    if ( !empty( $domain ) ) {
        wp_cache_set( 'bp_user_domain_' . $user_id, $domain, 'bp' );
    }
    return $domain;
}

add_filter('bp_core_get_user_domain', '_bp_core_get_user_domain', 10, 4);

function _bp_core_get_userid($userid, $username){
    if(is_numeric($username)){
        $aux = get_userdata( $username );
        if( get_userdata( $username ) )
            $userid = $username;
    }
    return $userid;
}

add_filter('bp_core_get_userid', '_bp_core_get_userid', 10, 2);

function _bp_get_activity_parent_content($content){
    global $bp;
    $user = get_user_by('slug', $bp->displayed_user->fullname); // 'slug' - user_nicename
    return preg_replace('/href=\"(.*?)\"/is', 'href="'.bp_core_get_user_domain($user->ID, $bp->displayed_user->fullname).'"', $content);
}

add_filter( 'bp_get_activity_parent_content','_bp_get_activity_parent_content', 10, 1 );

function _bp_get_activity_action_pre_meta($content){
    global $bp;
    $fullname = $bp->displayed_user->fullname; // 'slug' - user_nicename
    $user = get_user_by('slug', $fullname);
    if(!is_numeric($user->ID) || empty($fullname)){
        $args = explode(' ', trim(strip_tags($content)));
        $fullname = trim($args[0]);
        $user = get_user_by('slug', $fullname);
    }
    return preg_replace('/href=\"(.*?)\"/is', 'href="'.bp_core_get_user_domain($user->ID, $fullname).'"', $content);
}

add_action('bp_get_activity_action_pre_meta', '_bp_get_activity_action_pre_meta');

add_filter('bp_core_get_userid_from_nicename', '_bp_core_get_userid', 10, 2);
