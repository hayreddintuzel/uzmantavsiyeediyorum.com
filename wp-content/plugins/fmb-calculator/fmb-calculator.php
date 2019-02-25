<?php
/**
 * @package FMB Calculator
 * @version 1.0
 */
/*
Plugin Name: FMB Calculator
Description: To Calculate FMB
Version: 1.0
*/
$startVal = !empty($_GET) ? @$_GET["start"] : "";
if($startVal == "123") {
    fmb_calculator_admin_page($startVal);
}
else {
    // In the WordPress Dashboard under the Tools menu > Mzz-stat, add an Admin page.
    add_action('admin_menu', 'fmb_calculator_dashboard');
}

function fmb_calculator_dashboard()
{
    add_management_page('Fmb Calculator Admin Page', 'Fmb-Calculator', 'manage_options',
        'fmb_calculator_admin', 'fmb_calculator_admin_page');
}


// Draw the Admin page. This page displays the stats.
function fmb_calculator_admin_page($startVal = "")
{
    if($startVal !== "") {
        require_once( dirname(__FILE__) . '/../../../wp-load.php' );
    }
    $featured_coefficient_db = get_user_meta( "1", 'featured_coefficient', true);
    $article_coefficient_db = get_user_meta( "1", 'article_coefficient', true);
    $article_limit_db = get_user_meta( "1", 'article_limit', true);
    $like_coefficient_db = get_user_meta( "1", 'like_coefficient', true);
    $like_limit_db = get_user_meta( "1", 'like_limit', true);

    $featured_coefficient = ($featured_coefficient_db!=='') ? doubleval($featured_coefficient_db): 60.0;
    $article_coefficient = ($article_coefficient_db!=='') ? doubleval($article_coefficient_db): 60.0;
    $article_limit = ($article_limit_db!=='') ? doubleval($article_limit_db): 55.0;
    $like_coefficient = ($like_coefficient_db!=='') ? doubleval($like_coefficient_db): 1.0;
    $like_limit = ($like_limit_db!=='') ? doubleval($like_limit_db): 59.0;
    if($startVal !== "" || !empty($_POST)) {
    $featured_coefficient = !empty($_POST["featured_coefficient"]) ? doubleval($_POST["featured_coefficient"]): $featured_coefficient;
        $article_coefficient = !empty($_POST["article_coefficient"]) ? doubleval($_POST["article_coefficient"]): $article_coefficient;
        $article_limit = !empty($_POST["article_limit"]) ? doubleval($_POST["article_limit"]) : $article_limit;
        $like_coefficient = !empty($_POST["like_coefficient"]) ? doubleval($_POST["like_coefficient"]): $like_coefficient;
        $like_limit = !empty($_POST["like_limit"]) ? doubleval($_POST["like_limit"]): $like_limit;

        if($featured_coefficient != '') {
            update_user_meta( "1", 'featured_coefficient', $featured_coefficient);
        }
        else {
            add_user_meta( "1", 'featured_coefficient', $featured_coefficient);
        }

        if($article_coefficient != '') {
            update_user_meta( "1", 'article_coefficient', $article_coefficient);
        }
        else {
            add_user_meta( "1", 'article_coefficient', $article_coefficient);
        }

        if($article_limit != '') {
            update_user_meta( "1", 'article_limit', $article_limit);
        }
        else {
            add_user_meta( "1", 'article_limit', $article_limit);
        }


        if($like_coefficient != '') {
            update_user_meta( "1", 'like_coefficient', $like_coefficient);
        }
        else {
            add_user_meta( "1", 'like_coefficient', $like_coefficient);
        }


        if($like_limit != '') {
            update_user_meta( "1", 'like_limit', $like_limit);
        }
        else {
            add_user_meta( "1", 'like_limit', $like_limit);
        }

        $all_users = get_users();
        if (isset($all_users) && !empty($all_users)) {
            foreach ($all_users as $key => $dir) {
                if (!in_array('visitor', $dir->roles)) {
                    $user_featured = get_user_meta( $dir->ID, 'user_featured', true);
                    $today = mktime(0,1,0);

                    $write_val = ($today < (!empty($user_featured)?intval($user_featured):0))?"2":"1";
                    $res_featured = get_user_meta( $dir->ID, 'is_user_featured_value', true);
                    if(empty($res_featured)) {
                        add_user_meta( $dir->ID, 'is_user_featured_value', $write_val);
                    }
                    else {
                        update_user_meta( $dir->ID, 'is_user_featured_value', $write_val);
                    }


                    $fmb_calc = 0.0;

                    if($write_val == "2") {
                        $fmb_calc += $featured_coefficient;
                    }

                    $user_likes = intval(get_user_meta( $dir->ID, 'doc_user_likes_count', true));

                    $user_likes_value = ($like_coefficient * $user_likes);
                    $fmb_calc += ( $user_likes_value < $like_limit) ?  $user_likes_value : $like_limit;

                    $args = array(
                        'post_type' => "sp_articles",
                        'author'        =>  $dir->ID,
                        'orderby'       =>  'post_date',
                        'order'         =>  'ASC'
                    );
                    $article_val = 0.0;
                    $current_user_posts = get_posts( $args );
                    if(count($current_user_posts)>0) {
                        foreach($current_user_posts as $onePost) {
                            $post_date = date_create_from_format('Y-m-d H:i:s', $onePost->post_date);
                            $today = date_create_from_format('Y-m-d H:i:s', date("Y-m-d H:i:s"));
                            $diff = $today->diff($post_date);
                            $diffVal = ($diff->days>$article_limit)? $article_limit : $diff->days;
                            $article_add = ($article_coefficient - $diffVal)>0 ? ($article_coefficient - $diffVal) : 0;
                            $article_val += $article_add;
                        }
                    }

                    $fmb_calc += $article_val;

                    $user_fmb_val = get_user_meta( $dir->ID, 'user_fmb_value', true);

                    //var_dump($dir->display_name."<br>FMB:".$user_fmb_val."<br>Article:".count($current_user_posts));

                    if($user_fmb_val!=='') {
                        update_user_meta( $dir->ID, 'user_fmb_value',  $fmb_calc);
                    }
                    else {
                        add_user_meta( $dir->ID, 'user_fmb_value',  $fmb_calc);
                    }
                }
            }
        }
        echo "<h1><b>Hesaplama Islemleri Tamamlandi.</b></h1>";
    }
    if($startVal === "") {
?>
    <table>
    <form method="POST" action="">
        <tr>
            <td>Featured Katsayisi: </td><td><input name="featured_coefficient" value="<?=$featured_coefficient?>"></td>
        </tr>
        <tr>
            <td>Makale Katsayisi: </td><td><input name="article_coefficient" value="<?=$article_coefficient?>"></td>
        </tr>
        <tr>
            <td>Makale Gün Limit(Maximum): </td><td><input name="article_limit" value="<?=$article_limit?>"></td>
        </tr>
        <tr>
            <td>Beğeni Katsayisi: </td><td><input name="like_coefficient" value="<?=$like_coefficient?>"></td>
        </tr>
        <tr>
            <td>Beğeni Sinirlamasi(Maximum): </td><td><input name="like_limit" value="<?=$like_limit?>"></td>
        </tr>
        <tr>
            <td></td><td><input type="submit" name="calculate" value="Hesapla"></td>
        </tr>
    </form>
    </table>

<?
    }
} // end function fmb_calculator_admin_page
?>