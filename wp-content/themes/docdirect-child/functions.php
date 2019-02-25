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
    $parent_main_style = 'docdirect_theme_style_v2';
	wp_enqueue_style( 'docdirect_child_style',
        get_stylesheet_directory_uri() . '/style.css',
        array( $parent_style ),
		$child_theme_version->get('Version')
    );

    wp_enqueue_style( $parent_style, get_template_directory_uri() . '/style.css', array('bootstrap.min','choosen'),$parent_theme_version->get( 'Version' ));
    
	wp_enqueue_style( 'docdirect_child_style2',
        get_stylesheet_directory_uri() . '/css/version-2.css',
        array( $parent_main_style ),
		$child_theme_version->get('Version')
    );
    
	wp_enqueue_script( 'docdirect_child_user_profile_tag',
        get_stylesheet_directory_uri() . '/js/user_profile_tag.js',
        array()
    );

    wp_enqueue_script('docdirect_bookings',
    	get_stylesheet_directory_uri() . '/js/bookings.js',
    	array()
    );
}

if ( ! function_exists( 'docdirect_custom_account_settings' ) ) {
    function sendCustomEmailForKeyword($to, $from, $subject, $message) {
        $headers = "From: ". $from . "\r\nReply-To: ".$from."\r\n";

        //Here put your Validation and send mail
        $sent = wp_mail($to, $subject, strip_tags($message), $headers);

        return $sent;
    }

	function docdirect_custom_account_settings(){ 
		global $current_user, $wp_roles,$userdata,$post;
		$user_identity	= $current_user->ID;

		
		//Update Socials
		if( isset( $_POST['socials'] ) && !empty( $_POST['socials'] ) ){
			foreach( $_POST['socials'] as $key=>$value ){
				update_user_meta( $user_identity, $key, esc_attr( $value ) );
			}
		}
		
		//Update Basics
		if( !empty( $_POST['basics'] ) ){
			foreach( $_POST['basics'] as $key => $value ){
				update_user_meta( $user_identity, $key, esc_attr( $value ) );
			}
		}

		//Professional Statements
		if( !empty( $_POST['professional_statements'] ) ){
			$professional_statements	= docdirect_sanitize_wp_editor($_POST['professional_statements']);
			update_user_meta( $user_identity, 'professional_statements', $professional_statements);
		}
		
		//update username
		$full_name = docdirect_get_username($user_identity);
		update_user_meta( $user_identity, 'full_name', esc_attr( $full_name ) );
		update_user_meta( $user_identity, 'username', esc_attr( $full_name ) );
		
		//Update General settings
		
		update_user_meta( $user_identity, 'video_url', esc_url( $_POST['video_url'] ) );
		wp_update_user( array( 'ID' => $user_identity, 'user_url' => esc_url($_POST['basics']['user_url']) ) );
		
		//Awards
		$awards	= array();
		if( !empty( $_POST['awards'] ) ){
			
			$counter	= 0;
			foreach( $_POST['awards'] as $key=>$value ){
				$awards[$counter]['name']	= esc_attr( $value['name'] ); 
				$awards[$counter]['date']	= esc_attr( $value['date'] );
				$awards[$counter]['date_formated']	= date_i18n('d M, Y',strtotime(esc_attr( $value['date'])));  
				$awards[$counter]['description']	  = esc_attr( $value['description'] ); 
				$counter++;
			}
			$json['awards']	= $awards;
		}
		update_user_meta( $user_identity, 'awards', $awards );
		
		//Gallery
		$user_gallery	= array();
		if( !empty( $_POST['user_gallery'] ) ){
			$counter	= 0;
			foreach( $_POST['user_gallery'] as $key=>$value ){
				$user_gallery[$value['attachment_id']]['url']	= esc_url( $value['url'] ); 
				$user_gallery[$value['attachment_id']]['id']	= esc_attr( $value['attachment_id']); 
				$counter++;
			}

		}
		update_user_meta( $user_identity, 'user_gallery', $user_gallery );
		
		//Specialities
		$db_directory_type	 = get_user_meta( $user_identity, 'directory_type', true);
		if( isset( $db_directory_type ) && !empty( $db_directory_type ) ) {
			$specialities_list	 = docdirect_prepare_taxonomies('directory_type','specialities',0,'array');
		}
		
		$specialities	= array();
		$submitted_specialities	= docdirect_sanitize_array($_POST['specialities']);
		
		//limit specialities
		if (function_exists('fw_get_db_settings_option')) {
			$speciality_limit 		= fw_get_db_settings_option('speciality_limit');
		}
		$speciality_limit	= !empty( $speciality_limit ) ? $speciality_limit : '50';
		if(!empty($submitted_specialities)) {
			$submitted_specialities	= array_slice($submitted_specialities, 0, $speciality_limit);
		}
		if( isset( $specialities_list ) && !empty( $specialities_list ) ){
			$counter	= 0;
			foreach( $specialities_list as $key => $speciality ){
				if( isset( $submitted_specialities ) 
				   	&& is_array( $submitted_specialities ) 
				    && in_array( $speciality->slug, $submitted_specialities ) 
				 ){
					update_user_meta( $user_identity, $speciality->slug, esc_attr( $speciality->slug ) );
					$specialities[$speciality->slug]	= $speciality->name;
				}else{
					update_user_meta( $user_identity, $speciality->slug, '' );
				}
				
				$counter++;
			}
		}
		
		update_user_meta( $user_identity, 'user_profile_specialities', $specialities );
		
		//Education
		$educations	= array();
		if( !empty( $_POST['education'] ) ){
			$counter	= 0;
			foreach( $_POST['education'] as $key=>$value ){
				if( !empty( $value['title'] ) && !empty( $value['institute'] ) ) {
					$educations[$counter]['title']		 = esc_attr( $value['title'] ); 
					$educations[$counter]['institute']	 = esc_attr( $value['institute'] ); 
					$educations[$counter]['start_date']	 = esc_attr( $value['start_date'] ); 
					$educations[$counter]['end_date']	 = esc_attr( $value['end_date'] ); 
					$educations[$counter]['start_date_formated']  = date_i18n('M,Y',strtotime(esc_attr( $value['start_date']))); 
					$educations[$counter]['end_date_formated']	= date_i18n('M,Y',strtotime(esc_attr( $value['end_date']))); 
					$educations[$counter]['description']		= esc_attr( $value['description'] ); 
					$counter++;
				}
			}
			$json['education']	= $educations;
		}
		update_user_meta( $user_identity, 'education', $educations );
		
		//Experience
		$experiences	= array();
		if( !empty( $_POST['experience'] ) ){
			$counter	= 0;
			foreach( $_POST['experience'] as $key=>$value ){
				if( !empty( $value['title'] ) && !empty( $value['company'] ) ) {
					$experiences[$counter]['title']			= esc_attr( $value['title'] ); 
					$experiences[$counter]['company']	 	= esc_attr( $value['company'] ); 
					$experiences[$counter]['start_date']	= esc_attr( $value['start_date'] ); 
					$experiences[$counter]['end_date']	  	= esc_attr( $value['end_date'] ); 
					$experiences[$counter]['start_date_formated']   = date_i18n('M,Y',strtotime(esc_attr( $value['start_date']))); 
					$experiences[$counter]['end_date_formated']		= date_i18n('M,Y',strtotime(esc_attr( $value['end_date']))); 
					$experiences[$counter]['description']			= esc_attr( $value['description'] ); 
					$counter++;
				}
			}
			$json['experience']	= $experiences;
		}
		update_user_meta( $user_identity, 'experience', $experiences );
		
		//Experience
		$prices	= array();
		if( !empty( $_POST['prices'] ) ){
			$counter	= 0;
			foreach( $_POST['prices'] as $key=>$value ){
				if( !empty( $value['title'] ) ) {
					$prices[$counter]['title']	= 	esc_attr( $value['title'] ); 
					$prices[$counter]['price']	 = 	esc_attr( $value['price'] ); 
					$prices[$counter]['description']	= 	esc_attr( $value['description'] ); 
					$counter++;
				}
			}
			$json['prices_list']	= $prices;
		}
		
		update_user_meta( $user_identity, 'prices_list', $prices );
		
		//Languages
		$languages	= array();
		if( isset( $_POST['language'] ) && !empty( $_POST['language'] ) ){
			$counter	= 0;
			foreach( $_POST['language'] as $key=>$value ){
				$db_value	 = esc_attr($value);
				$languages[$db_value]	= 	$db_value; 
				$counter++;
			}
		}
		update_user_meta( $user_identity, 'languages', $languages );
		
		
		//Insurance
		$insurance	= array();

		if( isset( $_POST['insurance'] ) && !empty( $_POST['insurance'] ) ){
			$counter	= 0;
			foreach( $_POST['insurance'] as $key => $value ){
				$db_value	 = esc_attr($value);
				$insurance[$db_value]	= $db_value; 
				$counter++;
			}
			
			$insurance	= array_filter($insurance);
		}
		
		update_user_meta( $user_identity, 'insurance', $insurance );



        $newAddedTags = [];
        if(($_POST["user-new-tags-data"]))  {
            $allTags = [$_POST["user-new-tags-data"]];
            if(strpos($_POST["user-new-tags-data"],",")) {
                $allTags = explode(",",$_POST["user-new-tags-data"]);
            }
            $allNewAddedIds = [];
            $tempTag = "";
            $all_added_tags = "";
            foreach($allTags as $oneTag) {
                if(!empty($oneTag)) {
                    $tempTag = esc_attr($oneTag);
                    $isExist = wp_get_post_terms($db_directory_type, 'sub_category', array("name" => $tempTag));
                    if(empty($isExist)) {
                        $ret = wp_insert_term( $tempTag, "sub_category" );
                        if (!is_wp_error($ret)) {
                            $term_detail = get_term_by('id', $ret['term_id'], 'sub_category');
                            if (!is_wp_error($term_detail)) {
                                $newAddedTags[$tempTag] = $term_detail->slug;//$tempTag;//$ret['term_id'];
                                $allNewAddedIds[] = $ret['term_id'];
                                $all_added_tags[] = $tempTag;
                            }
                        }
                    }
                }
            }
            if(count($allNewAddedIds)>0) {
                $to = "akifersoy@gmail.com";
                $from = "akifersoy@gmail.com";
                $subject = "Yeni Etiket Girildi";
                $message_content = $current_user->nickname." kullan覺c覺s覺 ".implode(",",$all_added_tags)." etiket(ler)ini ekledi. Toplam yeni etiket : ".count($allNewAddedIds);
                $result = sendCustomEmailForKeyword($to, $from, $subject, $message_content);
            }
        }

        //Update sub categories
		if(!empty($_POST['subcategory']) ){
			$subcategories	= array();
			$counter	= 0;
			foreach( $_POST['subcategory'] as $key => $value ){
				$db_value	 				= esc_attr($value);
				$subcategories[$db_value]	= $db_value; 
				$counter++;
			}

            if( isset( $db_directory_type ) && !empty( $db_directory_type) && count($newAddedTags)>0) {

                $post_term_list = wp_get_post_terms($db_directory_type, 'sub_category', array("fields" => "all"));

                $all_old_term_ids = [];
                if(count($post_term_list)>0) {
                    foreach($post_term_list as $one_term) {
                        $all_old_term_ids[] = $one_term->term_id;
                    }
                }
                if(count($all_old_term_ids)>0) {
                    $allNewAddedIds = array_merge($all_old_term_ids, $allNewAddedIds);
                }
                $ret = wp_set_post_terms( $db_directory_type, $allNewAddedIds, "sub_category" );
                if (!is_wp_error($ret)) {
                    $subcategories = array_merge($newAddedTags, $subcategories);
                }
            }
            $subcategories	= array_filter($subcategories);

			update_user_meta( $user_identity, 'doc_sub_categories', $subcategories );
		}
		update_user_meta( $user_identity, 'show_admin_bar_front', false );
		
		do_action('docdirect_do_update_profile_settings',$_POST); //Save custom data
		
		$json['type']	= 'success';
		$json['message']	= esc_html__('Settings saved.','docdirect');
		echo json_encode($json);
		die;
	}
	
	
	add_action('wp_ajax_docdirect_custom_account_settings','docdirect_custom_account_settings');
	add_action( 'wp_ajax_nopriv_docdirect_custom_account_settings', 'docdirect_custom_account_settings' );
	
}

add_action( 'wp_enqueue_scripts', 'docdirect_child_theme_enqueue_styles' );

/**
 * @User Public Profile Save
 * @return {}
 */
if (!function_exists('docdirect_custom_personal_options_save')) {

    remove_action('edit_user_profile_update', 'docdirect_personal_options_save' );
    remove_action('personal_options_update', 'docdirect_personal_options_save' );

    function docdirect_custom_personal_options_save($user_identity) {
        $current_date	= date('Y-m-d H:i:s');
        $offset = get_option('gmt_offset') * intval(60) * intval(60);
        $current_date	= strtotime($current_date) + $offset;


        $userprofile_media = !empty( $_POST['userprofile_media'] ) ? intval( $_POST['userprofile_media'] ) : '';
        update_user_meta($user_identity, 'userprofile_media', $userprofile_media);

        //Banner
        $userprofile_banner = !empty( $_POST['userprofile_banner'] ) ? intval( $_POST['userprofile_banner'] ) : '';
        update_user_meta($user_identity, 'userprofile_banner', $userprofile_banner);

        $updated_pack_id	= intval( $_POST['current_package'] );

        if( empty( $updated_pack_id ) ){ //Update only featurd and package expiry date
            //Featured
            update_user_meta( $user_identity, 'user_featured', esc_attr( $_POST['feature_time_stamp'] ) );

            if( !empty( $_POST['featured_days'] )  ){
                $user_featured_date    = get_user_meta( $user_identity, 'user_featured', true);
                $duration	    	   = !empty( $_POST['featured_days'] ) ? intval( $_POST['featured_days'] ) : 0;

                if( !empty( $user_featured_date ) && $user_featured_date > $current_date ){
                    $featured_date	= strtotime("+".$duration." days", $user_featured_date);
                    $featured_date	= date('Y-m-d H:i:s',$featured_date);
                } else{
                    $current_date	= date('Y-m-d H:i:s');
                    $duration	    = !empty( $_POST['featured_days'] ) ? intval( $_POST['featured_days'] ) : 0;
                    $featured_date	= strtotime("+".$duration." days", strtotime($current_date));
                    $featured_date	= date('Y-m-d H:i:s',$featured_date);
                }

                $featured_date	= strtotime($featured_date) + $offset;
                update_user_meta($user_identity,'user_featured', $featured_date ); //Update Expiry
            } else if( !empty( $_POST['featured_exclude'] ) ){
                $user_featured_date    = get_user_meta( $user_identity, 'user_featured', true);
                $duration	    	  = !empty( $_POST['featured_exclude'] ) ? intval( $_POST['featured_exclude'] ) : 0;

                if( isset( $user_featured_date ) && !empty( $user_featured_date ) ){
                    $featured_date	= strtotime("-".$duration." days", $user_featured_date);
                    $featured_date	= date('Y-m-d H:i:s',$featured_date);
                }

                $featured_date	= strtotime($featured_date) + $offset;
                update_user_meta($user_identity,'user_featured',$featured_date); //Update Expiry
            }

            //package expiry
            update_user_meta( $user_identity, 'user_current_package_expiry', esc_attr( $_POST['package_time_stamp'] ) );
            if( !empty( $_POST['package_days'] )  ){
                $user_package_date    = get_user_meta( $user_identity, 'user_current_package_expiry', true);
                $duration	    	   = !empty( $_POST['package_days'] ) ? intval( $_POST['package_days'] ) : 0;

                if( !empty( $user_package_date ) && $user_package_date > $current_date ){
                    $package_date	= strtotime("+".$duration." days", $user_package_date);
                    $package_date	= date('Y-m-d H:i:s',$package_date);
                } else{
                    $current_date	= date('Y-m-d H:i:s');
                    $duration	    = !empty( $_POST['package_days'] ) ? intval( $_POST['package_days'] ) : 0;
                    $package_date	= strtotime("+".$duration." days", strtotime($current_date));
                    $package_date	= date('Y-m-d H:i:s',$package_date);
                }

                $package_date	= strtotime($package_date) + $offset;
                update_user_meta($user_identity,'user_current_package_expiry',$package_date); //Update Expiry
            } else if( !empty( $_POST['package_exclude'] ) ){
                $user_package_date    = get_user_meta( $user_identity, 'user_current_package_expiry', true);
                $duration	    	  = !empty( $_POST['package_exclude'] ) ? intval( $_POST['package_exclude'] ) : 0;

                if( !empty( $user_package_date ) ){
                    $package_date	= strtotime("-".$duration." days", $user_package_date);
                    $package_date	= date('Y-m-d H:i:s',$package_date);
                }

                $package_date	= strtotime($package_date) + $offset;
                update_user_meta($user_identity,'user_current_package_expiry',$package_date); //Update Expiry
                update_user_meta( $user_identity, 'user_current_package', $updated_pack_id );
            }
        } else if( !empty( $updated_pack_id ) ){ //Update package
            if( apply_filters('docdirect_get_theme_settings', 'payments') === 'woo' ){ //If woocomerce is enabled

                $payment_date 		= date('Y-m-d H:i:s');
                $user_featured_date = get_user_meta( $user_identity, 'user_featured', true);
                $payment_date		= strtotime($payment_date) + $offset;

                if( apply_filters('docdirect_get_packages_setting','default') === 'custom' ){
                    $featured_date	= date('Y-m-d H:i:s');

                    $dd_duration 	 = get_post_meta($updated_pack_id, 'dd_duration', true);
                    $dd_featured 	 = get_post_meta($updated_pack_id, 'dd_featured', true);
                    $dd_appointments = get_post_meta($updated_pack_id, 'dd_appointments', true);
                    $dd_banner 		 = get_post_meta($updated_pack_id, 'dd_banner', true);
                    $dd_insurance 	 = get_post_meta($updated_pack_id, 'dd_insurance', true);
                    $dd_favorites 	 = get_post_meta($updated_pack_id, 'dd_favorites', true);
                    $dd_teams 		 = get_post_meta($updated_pack_id, 'dd_teams', true);
                    $dd_hours 		 = get_post_meta($updated_pack_id, 'dd_hours', true);

                    //If extension is enabled
                    if ( function_exists('fw_get_db_settings_option') && fw_ext('articles')) {
                        $dd_articles 	 = get_post_meta($updated_pack_id, 'dd_articles', true);
                    }else{
                        $dd_articles 	 = 0;
                    }

                    //If extension is enabled
                    if ( function_exists('fw_get_db_settings_option') && fw_ext('questionsanswers')) {
                        $dd_qa 	 		 = get_post_meta($updated_pack_id, 'dd_qa', true);
                    } else{
                        $dd_qa 	 		 = 'no';
                    }

                    //Featured
                    if( !empty( $user_featured_date ) && $user_featured_date >  $payment_date ){
                        $duration = get_post_meta($updated_pack_id, 'dd_featured', true); //no of days for a feature listings
                        if( $duration > 0 ){
                            $featured_date	= strtotime("+".$duration." days", $user_featured_date);
                            $featured_date	= date('Y-m-d H:i:s',$featured_date);
                        }
                    } else{
                        $current_date	= date('Y-m-d H:i:s');
                        $duration = get_post_meta($updated_pack_id, 'dd_featured', true);//no of days for a feature listings
                        if( $duration > 0 ){
                            $featured_date		 = strtotime("+".$duration." days", strtotime($current_date));
                            $featured_date	     = date('Y-m-d H:i:s',$featured_date);
                        }
                    }

                    //Package Expiry
                    $package_expiry	= date('Y-m-d H:i:s');
                    $user_package_expiry    = get_user_meta( $user_identity, 'user_current_package_expiry', true);

                    if( !empty( $user_package_expiry ) && $user_package_expiry >  $payment_date ){
                        $package_duration = get_post_meta($updated_pack_id, 'dd_duration', true);
                        if( $package_duration > 0 ){
                            $package_expiry	= strtotime("+".$package_duration." days", $user_package_expiry);
                            $package_expiry	= date('Y-m-d H:i:s',$package_expiry);
                        }
                    } else{
                        $current_date	= date('Y-m-d H:i:s');
                        $package_duration = get_post_meta($updated_pack_id, 'dd_duration', true);
                        if( $package_duration > 0 ){
                            $package_expiry		 = strtotime("+".$package_duration." days", strtotime($current_date));
                            $package_expiry	     = date('Y-m-d H:i:s',$package_expiry);
                        }
                    }

                    $package_expiry = strtotime($package_expiry) + $offset;
                    update_user_meta($user_identity,'user_current_package_expiry',$package_expiry); //package duration

                } else{

                    $dd_duration 	 = get_post_meta($updated_pack_id, 'dd_duration', true);
                    $dd_featured 	 = '';
                    $dd_appointments = '';
                    $dd_banner 		 = 'no';
                    $dd_insurance 	 = 'no';
                    $dd_favorites 	 = 'no';
                    $dd_teams 		 = 'no';
                    $dd_hours 		 = 'no';
                    $dd_articles 	 = 0;
                    $dd_qa 	 		 = 'no';

                    //Feature Listng For default settings
                    $featured_date	= date('Y-m-d H:i:s');

                    if( !empty( $user_featured_date ) && $user_featured_date >  $payment_date ){
                        $duration = get_post_meta($updated_pack_id, 'dd_duration', true);
                        if( $duration > 0 ){
                            $featured_date	= strtotime("+".$duration." days", $user_featured_date);
                            $featured_date	= date('Y-m-d H:i:s',$featured_date);
                        }
                    } else{
                        $current_date	= date('Y-m-d H:i:s');
                        $duration = get_post_meta($updated_pack_id, 'dd_duration', true);
                        if( $duration > 0 ){
                            $featured_date		 = strtotime("+".$duration." days", strtotime($current_date));
                            $featured_date	     = date('Y-m-d H:i:s',$featured_date);
                        }
                    }

                    $package_expiry = strtotime($featured_date) + $offset;
                    update_user_meta($user_identity,'user_current_package_expiry',$package_expiry); //package duration
                }

                $featured_date = strtotime($featured_date) + $offset;
                update_user_meta($user_identity,'user_featured',$featured_date); //featured Expiry
                update_user_meta($user_identity,'user_current_package',$updated_pack_id); //Current package


                //update data
                $package_data = array(
                    'subscription_id' 				=> $updated_pack_id,
                    'subscription_expiry' 			=> $package_expiry,
                    'subscription_featured_expiry' 	=> $featured_date,
                    'subscription_appointments' 	=> $dd_appointments,
                    'subscription_profile_banner' 	=> $dd_banner,
                    'subscription_insurance' 		=> $dd_insurance,
                    'subscription_favorites' 		=> $dd_favorites,
                    'subscription_teams' 			=> $dd_teams,
                    'subscription_business_hours' 	=> $dd_hours,
                    'subscription_articles'  		=> $dd_articles,
                    'subscription_questions'  		=> $dd_qa,
                );

                update_user_meta($user_identity, 'dd_subscription', $package_data);

            } else{ //If custom payments is enabled instead of woocommerce

                $payment_date 		    = date('Y-m-d H:i:s');
                $user_featured_date     = get_user_meta( $user_identity, 'user_featured', true);
                $payment_date			= strtotime($payment_date) + $offset;

                //Custom Packages Listings managment
                if( apply_filters('docdirect_get_packages_setting','default') === 'custom' ){
                    $featured_date	= date('Y-m-d H:i:s');

                    //Featured
                    if( !empty( $user_featured_date ) && $user_featured_date >  $payment_date ){
                        $duration = fw_get_db_post_option($updated_pack_id, 'featured_expiry', true); //no of days for a feature listings
                        if( $duration > 0 ){
                            $featured_date	= strtotime("+".$duration." days", $user_featured_date);
                            $featured_date	= date('Y-m-d H:i:s',$featured_date);
                        }
                    } else{
                        $current_date	= date('Y-m-d H:i:s');
                        $duration = fw_get_db_post_option($updated_pack_id, 'featured_expiry', true);//no of days for a feature listings
                        if( $duration > 0 ){
                            $featured_date		 = strtotime("+".$duration." days", strtotime($current_date));
                            $featured_date	     = date('Y-m-d H:i:s',$featured_date);
                        }
                    }

                    //Package Expiry
                    $package_expiry	= date('Y-m-d H:i:s');
                    $user_package_expiry    = get_user_meta( $user_identity, 'user_current_package_expiry', true);

                    if( !empty( $user_package_expiry ) && $user_package_expiry >  $payment_date ){
                        $package_duration = fw_get_db_post_option($updated_pack_id, 'duration', true);
                        if( $package_duration > 0 ){
                            $package_expiry	= strtotime("+".$package_duration." days", $user_package_expiry);
                            $package_expiry	= date('Y-m-d H:i:s',$package_expiry);
                        }
                    } else{
                        $current_date	= date('Y-m-d H:i:s');
                        $package_duration = fw_get_db_post_option($updated_pack_id, 'duration', true);
                        if( $package_duration > 0 ){
                            $package_expiry		 = strtotime("+".$package_duration." days", strtotime($current_date));
                            $package_expiry	     = date('Y-m-d H:i:s',$package_expiry);
                        }
                    }

                    $package_expiry = strtotime($package_expiry) + $offset;
                    update_user_meta($user_identity,'user_current_package_expiry',$package_expiry); //package duration

                } else{

                    //Feature Listng For default settings
                    $featured_date	= date('Y-m-d H:i:s');

                    if( !empty( $user_featured_date ) && $user_featured_date >  $payment_date ){
                        $duration = fw_get_db_post_option($updated_pack_id, 'duration', true);
                        if( $duration > 0 ){
                            $featured_date	= strtotime("+".$duration." days", $user_featured_date);
                            $featured_date	= date('Y-m-d H:i:s',$featured_date);
                        }
                    } else{
                        $current_date	= date('Y-m-d H:i:s');
                        $duration = fw_get_db_post_option($updated_pack_id, 'duration', true);
                        if( $duration > 0 ){
                            $featured_date		 = strtotime("+".$duration." days", strtotime($current_date));
                            $featured_date	     = date('Y-m-d H:i:s',$featured_date);
                        }
                    }

                    $package_expiry = strtotime($featured_date) + $offset;
                    update_user_meta($user_identity,'user_current_package_expiry',$package_expiry); //package duration
                }

                $featured_date = strtotime($featured_date) + $offset;
                update_user_meta($user_identity,'user_featured',$featured_date); //featured Expiry
                update_user_meta($user_identity,'user_current_package',$updated_pack_id); //Current package
            }

        }

        //Update Schedules
        $schedules	= !empty( $_POST['schedules'] ) && is_array( $_POST['schedules'] ) ? docdirect_sanitize_array( $_POST['schedules'] ) : array();
        update_user_meta( $user_identity, 'schedules', $schedules );

        //Update Professional Statements
        $professional_statements	= !empty( $_POST['professional_statements'] ) ? docdirect_sanitize_wp_editor( $_POST['professional_statements'] ) : '' ;

        update_user_meta( $user_identity, 'professional_statements', $professional_statements );
        update_user_meta( $user_identity, 'video_url', esc_url( $_POST['video_url'] ) );
        update_user_meta( $user_identity, 'directory_type', esc_attr( $_POST['directory_type'] ) );
        update_user_meta( $user_identity, 'show_admin_bar_front', false );

        //Update General settings
        if( !empty( $_POST['basics'] ) && is_array($_POST['basics']) ){
            foreach( $_POST['basics'] as $key => $value ){
                update_user_meta( $user_identity, $key, esc_attr( $value ) );
            }
        }

        if( !empty( $_POST['privacy'] ) ){
            update_user_meta( $user_identity, 'privacy', docdirect_sanitize_array( $_POST['privacy'] ) );
        }

        //Awawrds
        $awards	= array();
        if( !empty( $_POST['awards'] ) ){

            $counter	= 0;
            foreach( $_POST['awards'] as $key=>$value ){
                $awards[$counter]['name']	= 	esc_attr( $value['name'] );
                $awards[$counter]['date']	= 	esc_attr( $value['date'] );
                $awards[$counter]['date_formated']	= 	date_i18n('d M, Y',strtotime(esc_attr( $value['date'])));
                $awards[$counter]['description']	= 	esc_attr( $value['description'] );
                $counter++;
            }
            $json['awards']	= $awards;
        }
        update_user_meta( $user_identity, 'awards', $awards );

        //Gallery
        $user_gallery	= array();
        if( !empty( $_POST['user_gallery'] ) ){
            $counter	= 0;
            foreach( $_POST['user_gallery'] as $key=>$value ){
                $user_gallery[$value['attachment_id']]['url']	= 	esc_url( $value['url'] );
                $user_gallery[$value['attachment_id']]['id']	= 	esc_attr( $value['attachment_id']);
                $counter++;
            }
        }
        update_user_meta( $user_identity, 'user_gallery', $user_gallery );

        //Education
        $educations	= array();
        if( !empty( $_POST['education'] ) ){
            $counter	= 0;
            foreach( $_POST['education'] as $key=>$value ){
                $educations[$counter]['title']		 = esc_attr( $value['title'] );
                $educations[$counter]['institute']	 = esc_attr( $value['institute'] );
                $educations[$counter]['start_date']	 = esc_attr( $value['start_date'] );
                $educations[$counter]['end_date']	 = esc_attr( $value['end_date'] );
                $educations[$counter]['start_date_formated']	= date_i18n('M,Y',strtotime(esc_attr($value['start_date'])));
                $educations[$counter]['end_date_formated']	    = date_i18n('M,Y',strtotime(esc_attr($value['end_date'])));
                $educations[$counter]['description']			= esc_attr( $value['description'] );
                $counter++;
            }

            $json['education']	= $educations;

        }
        update_user_meta( $user_identity, 'education', $educations );

        //Experience
        $experiences	= array();
        if( !empty( $_POST['experience'] ) ){
            $counter	= 0;
            foreach( $_POST['experience'] as $key=>$value ){
                if( !empty( $value['title'] ) && !empty( $value['company'] ) ) {
                    $experiences[$counter]['title']			= 	esc_attr( $value['title'] );
                    $experiences[$counter]['company']	 	= 	esc_attr( $value['company'] );
                    $experiences[$counter]['start_date']	= 	esc_attr( $value['start_date'] );
                    $experiences[$counter]['end_date']	  	= 	esc_attr( $value['end_date'] );
                    $experiences[$counter]['start_date_formated']  = date_i18n('M,Y',strtotime(esc_attr( $value['start_date'])));
                    $experiences[$counter]['end_date_formated']	= date_i18n('M,Y',strtotime(esc_attr( $value['end_date'])));
                    $experiences[$counter]['description']	= 	esc_attr( $value['description'] );
                    $counter++;
                }
            }
            $json['experience']	= $experiences;
        }
        update_user_meta( $user_identity, 'experience', $experiences );

        //Experience
        $prices	= array();
        if( !empty( $_POST['prices'] ) ){
            $counter	= 0;
            foreach( $_POST['prices'] as $key=>$value ){
                if( !empty( $value['title'] ) ) {
                    $prices[$counter]['title']	= 	esc_attr( $value['title'] );
                    $prices[$counter]['price']	 = 	esc_attr( $value['price'] );
                    $prices[$counter]['description']	= 	esc_attr( $value['description'] );
                    $counter++;
                }
            }
            $json['prices_list']	= $prices;
        }

        update_user_meta( $user_identity, 'prices_list', $prices );

        //Specialities
        $db_directory_type	 = get_user_meta( $user_identity, 'directory_type', true);
        if( isset( $db_directory_type ) && !empty( $db_directory_type ) ) {
            $specialities_list	 = docdirect_prepare_taxonomies('directory_type','specialities',0,'array');
        }


        $specialities	= array();
        if( !empty( $_POST['specialities'] ) ) {
            $submitted_specialities	= docdirect_sanitize_array($_POST['specialities']);

            if( isset( $specialities_list ) && !empty( $specialities_list ) ){
                $counter	= 0;
                foreach( $specialities_list as $key => $speciality ){
                    if( isset( $submitted_specialities ) && in_array( $speciality->slug, $submitted_specialities ) ){
                        update_user_meta( $user_identity, $speciality->slug, $speciality->slug );
                        $specialities[$speciality->slug]	= $speciality->name;
                    }else{
                        update_user_meta( $user_identity, $speciality->slug, '' );
                    }

                    $counter++;
                }
            }
        }

        update_user_meta( $user_identity, 'user_profile_specialities', $specialities );


        //Languages
        $languages	= array();
        if( !empty( $_POST['language'] ) ){
            $counter	= 0;
            foreach( $_POST['language'] as $key=>$value ){
                $db_value	 = esc_attr($value);
                $languages[$db_value]	= 	$db_value;
                $counter++;
            }
        }

        update_user_meta( $user_identity, 'languages', $languages );


        //Insurance
        $insurance	= array();
        if( !empty( $_POST['insurance'] ) ){
            $counter	= 0;
            foreach( $_POST['insurance'] as $key=>$value ){
                $db_value	 = esc_attr($value);
                $insurance[$db_value]	= 	$db_value;
                $counter++;
            }

            $insurance	= array_filter($insurance);
        }

        update_user_meta( $user_identity, 'insurance', $insurance );

        //Update sub categories
        if(!empty($_POST['subcategory']) ){
            $subcategories	= array();
            $counter	= 0;
            foreach( $_POST['subcategory'] as $key => $value ){
                $db_value	 				= esc_attr($value);
                $subcategories[$db_value]	= $db_value;
                $counter++;
            }

            $subcategories	= array_filter($subcategories);
            update_user_meta( $user_identity, 'doc_sub_categories', $subcategories );
        }

        do_action('docdirect_do_update_profile_backend_settings',$_POST,$user_identity); //save settings
    }

    add_action('edit_user_profile_update', 'docdirect_custom_personal_options_save' );
    add_action('personal_options_update', 'docdirect_custom_personal_options_save' );

}

require_once ( get_stylesheet_directory() . '/directory/class-functions.php');
require_once ( get_stylesheet_directory() . '/directory/functions.php');
require_once ( get_stylesheet_directory() . '/directory/hooks.php');
require_once ( get_stylesheet_directory() . '/directory/bookings/functions.php'); //Booking
require_once ( get_stylesheet_directory() . '/directory/bookings/hooks.php'); //Booking