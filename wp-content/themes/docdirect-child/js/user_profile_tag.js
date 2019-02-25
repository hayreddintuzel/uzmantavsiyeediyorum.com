/* ---------------------------------------
 Confirm Box
 --------------------------------------- */
jQuery(document).ready(function ($) {
    var loder_html	= '<div class="docdirect-site-wrap"><div class="docdirect-loader"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>';
        
    jQuery(".process-account-settings").removeClass("process-account-settings").addClass("process-custom-account-settings");
    //Do Process Account Settings
    jQuery(document).on('click','.process-custom-account-settings',function(e){
        e.preventDefault();
        if( jQuery('.txt-professional').val() === 'txt-professional' ) {
            tinyMCE.triggerSave();
        }

        var $this 	= jQuery(this);
        var serialize_data	= jQuery('.do-account-setitngs').serialize();
        var dataString = serialize_data+'&action=docdirect_custom_account_settings';

        jQuery('body').append(loder_html);
        jQuery.ajax({
            type: "POST",
            url: scripts_vars.ajaxurl,
            data: dataString,
            dataType:"json",
            success: function(response) {
                jQuery('body').find('.docdirect-site-wrap').remove();
                if( response.type == 'error' ) {
                    jQuery.sticky(response.message, {classList: 'important', speed: 200, autoclose: 5000});
                } else{
                    if( response.education ){
                        var append_educations = wp.template( 'append-educations' );
                        var load_educations	= append_educations(response.education);
                        jQuery( '.educations_wrap' ).html(load_educations);
                    }
                    if( response.awards ){
                        var append_awards = wp.template( 'append-awards' );
                        var append_awards	= append_awards(response.awards);
                        jQuery( '.awards_wrap' ).html(append_awards);
                    }

                    if( response.experience ){
                        var append_experiences = wp.template( 'append-experiences' );
                        var load_experiences	= append_experiences(response.experience);
                        jQuery( '.experiences_wrap' ).html(load_experiences);
                    }

                    if( response.prices_list ){
                        var append_prices = wp.template( 'append-prices' );
                        var load_prices	= append_prices(response.prices_list);
                        jQuery( '.prices_wrap' ).html(load_prices);
                    }

                    jQuery.sticky(response.message, {classList: 'success', speed: 200, autoclose: 5000,position: 'top-right',});
                    location.reload();
                }
            }
        });
        return false;
    });
    
    $.confirm = function (params) {

        if ($('#confirmOverlay').length) {
            // A confirm is already shown on the page:
            return false;
        }

        var buttonHTML = '';
        $.each(params.buttons, function (name, obj) {

            // Generating the markup for the buttons:
            if( name == 'Yes' ){
                name	= scripts_vars.yes;
            } else if( name == 'No' ){
                name	= scripts_vars.no;
            } else{
                name	= name;
            }

            buttonHTML += '<a href="#" class="button ' + obj['class'] + '">' + name + '<span></span></a>';

            if (!obj.action) {
                obj.action = function () {
                };
            }
        });

        var markup = [
            '<div id="confirmOverlay">',
            '<div id="confirmBox">',
            '<h1>', params.title, '</h1>',
            '<p>', params.message, '</p>',
            '<div id="confirmButtons">',
            buttonHTML,
            '</div></div></div>'
        ].join('');

        $(markup).hide().appendTo('body').fadeIn();

        var buttons = $('#confirmBox .button'),
            i = 0;

        $.each(params.buttons, function (name, obj) {
            buttons.eq(i++).click(function () {

                // Calling the action attribute when a
                // click occurs, and hiding the confirm.

                obj.action();
                $.confirm.hide();
                return false;
            });
        });
    }

    $.confirm.hide = function () {
        $('#confirmOverlay').fadeOut(function () {
            $(this).remove();
        });
    }


    /**
     * Check for empty fields
     */
    function validate_form(parameters) {
        var $empty_fields = [];
        var $i = 0;
        jQuery('#editusertaxonomy input, #editusertaxonomy textarea').each(function () {
            if (!jQuery(this).is('textarea')) {
                var $input_value = jQuery(this).val();
            }
            if (!$input_value && jQuery(this).attr('data-required')) {
                jQuery(this).parents().eq(1).addClass('form-invalid');
                $empty_fields[$i] = jQuery(this).attr('name');
                $i++;
            }
        });
        return $empty_fields;
    }
    /**
     * Insert Tags
     * @param {type} $this
     * @param {type} $taxonomy_name
     * @param {type} $term
     * @param {type} $tag_html
     * @returns {undefined}
     */
    function insert_tags($tag_input, $taxonomy_name, $term, $tag_html,$root_element) {
        //Fetch current values and split from comma to array
        var $this = jQuery(this);
        var $tag_checklist = $root_element;

        var $user_tag_input = jQuery('#user-tags-' + $taxonomy_name);
        var $user_tag_input_val = $user_tag_input.val();
        if ($user_tag_input_val) {
            var $user_tag_input_val_array = $user_tag_input_val.split(',');
            var $insert = true;
            for (var $i = 0; $i < $user_tag_input_val_array.length; $i++) {
                if (jQuery.trim($user_tag_input_val_array[$i]) == jQuery.trim($term)) {
                    $insert = false;
                    break;
                }
            }
            if ($insert && $user_tag_input_val_array.length < 5) {
                $user_tag_input.val($user_tag_input_val + ',' + $term);
                $tag_checklist.append($tag_html);
            }
        } else {
            $user_tag_input.val($term);
            $tag_checklist.append($tag_html);
        }
        $tag_input.val('');
        jQuery('body .tag-suggestion').remove();
    }
    jQuery('body').on('submit', '#editusertaxonomy', function (e) {
        var $empty_fields = validate_form();
        if (!$empty_fields.length) {
            return true;
        } else {
            return false;
        }
    });
    jQuery('#editusertaxonomy input').on('keyup', function () {
        if (jQuery(this).parents().eq(1).hasClass('form-invalid')) {
            var $input_value = jQuery(this).val();
            if ($input_value) {
                jQuery(this).parents().eq(1).removeClass('form-invalid');
            }
        }
    });
    //Delete Taxonomy
    jQuery('body').on('click', '.delete-taxonomy a', function (e) {
        e.preventDefault();
        if( !confirm("Are you sure, you want to delete the taxonomy?") ) {
            return false;
        }
        var $this = jQuery(this);
        var $taxonomy_id = $this.attr('id');
        if ($taxonomy_id) {
            $taxonomy_id = $taxonomy_id.split('-');
            $taxonomy_id = $taxonomy_id[1];
        }
        var $taxonomy_name = $this.attr('data-name');
        var $nonce = jQuery('#delete-taxonomy-' + $taxonomy_id).val();
    });
    var delay = (function () {
        var timer = 0;
        return function (callback, ms) {
            clearTimeout(timer);
            timer = setTimeout(callback, ms);
        };
    })();
    jQuery('.user-profile-taxonomy').on('keyup', '.newtag', function () {
        var $this = jQuery(this);
        var $tag_input_value = $this.val().split(',');
        var $tag_input_value = jQuery.trim($tag_input_value[$tag_input_value.length - 1]);

        if ($tag_input_value.length >= 2) {
            delay(function () {
                var $tag_id = $this.attr('id');
                var $tag_name = $tag_id.split('new-tag-user_tag_');

                jQuery('.tag-suggestion').remove();
            }, 200);
        }
        else {
            jQuery('.tag-suggestion').remove();
        }
    });
    //Tags UI
    jQuery('body').on('click', '.tag-suggestion li', function () {
        var $this = jQuery(this);
        var $taxonomy_name = '';
        var $term = $this.html();
        var $tag_checklist = $this.parent().siblings('.tagchecklist');
        var $num = ( $tag_checklist.length );

        var $taxonomy_id = $this.parent().siblings('.newtag').attr('id');
        if ($taxonomy_id) {
            $taxonomy_id = $taxonomy_id.split('new-tag-user_tag_');
            $taxonomy_name = $taxonomy_id[1];
        }
        var $tag_html = '<div class="tag-hldr"><span><a id="user_tag-' + $taxonomy_name + '-check-num-' + $num + '" class="ntdelbutton">x</a></span>&nbsp;<a href="#" class="term-link">' + $term + '</a></div';
        //Taxonomy Name
        insert_tags($this.parent().siblings('.newtag'), $taxonomy_name, $term, $tag_html, $tag_checklist);
    });
    jQuery(document).mouseup(function (e) {
        var container = jQuery(".hide-on-blur");

        if (!container.is(e.target) && container.has(e.target).length === 0) {
            jQuery('.tag-suggestion').remove();
        }
    });

    jQuery('body').on('click', '.button.tagadd', function () {
        var $this = jQuery(this);
        var $total_tags = jQuery('.tag-hldr').length;

        if($total_tags + 1 < 6) {
            var $sibling = $this.siblings('.newtag');
            var $newtag_val = $sibling.val();
            if (!$newtag_val) return;
            $newtag_val = $newtag_val.split(',');
            var $taxonomy_name = $sibling.attr('id').split('new-tag-user_tag_');
            $taxonomy_name = $taxonomy_name[1];
            var $tag_checklist = $this.siblings('.tagchecklist');
            for (var $i = 0; $i < $newtag_val.length; $i++) {
                var $num = ( $tag_checklist.length );
                var $tag_html = '<div class="tag-hldr"><span><a id="post_tag-' + $taxonomy_name + '-check-num-' + $num + '" class="ntdelbutton">x</a></span>&nbsp;<a href="#" class="term-link">' + jQuery.trim($newtag_val[$i]) + '</a></div>';
                insert_tags($sibling, $taxonomy_name, jQuery.trim($newtag_val[$i]), $tag_html, $tag_checklist);
            }
        }

        jQuery('.tag-suggestion').remove();
    });
    //Delete Tag
    jQuery('body').on('click', '.ntdelbutton', function () {
        var $this = jQuery(this);
        var $term = jQuery.trim($this.parent().next('.term-link').html());
        var $tags_input = $this.parents().eq(2).siblings('input[type="hidden"]').val();
        $tags_input = $tags_input.split(',');

        $tags_input = jQuery.grep($tags_input, function (value) {
            return value != $term;
        });

        $this.parents().eq(2).siblings('input[type="hidden"]').val($tags_input.join(','));
        $this.parent().next('.term-link').remove();
        $this.parent().parent().remove();
    });
    jQuery('body').on('click', '.term-link', function (e) {
        if (jQuery(this).attr('href') != '#') return true;
        else {
            e.preventDefault();
            return false;
        }
    });
    var doing_ajax = false;
    //Most Popular tag list
    jQuery('body').on('click', '.tagcloud-link.user-taxonomy', function (e) {
        e.preventDefault();
        if (doing_ajax) {
            return false;
        }
        if (jQuery(this).parent().find('.the-tagcloud').length) {
            jQuery(this).parent().find('.the-tagcloud').remove();
            return true;
        }
        doing_ajax = true;
        var id = jQuery(this).attr('id');
        var tax = id.substr(id.indexOf("-") + 1);
        jQuery.post(ajaxurl, {'action': 'get-tagcloud', 'tax': tax}, function (r, stat) {
            doing_ajax = false;
            if (0 === r || 'success' != stat)
                r = wpAjax.broken;

            r = jQuery('<p id="tagcloud-' + tax + '" class="the-tagcloud">' + r + '</p>');
            jQuery('a', r).click(function () {
                var $this = jQuery(this);
                var $taxonomy_name = '';
                var $term = $this.html();
                var $tag_checklist = $this.parents().eq(1).siblings('.tagchecklist');
                var $sibling = $this.parents().eq(1).siblings('.newtag');
                if ($tag_checklist.length === 0) {
                    $tag_checklist = $this.parents().eq(1).siblings('.taxonomy-wrapper').find('.tagchecklist');
                }
                if ($sibling.length === 0) {
                    $sibling = $this.parents().eq(1).siblings('.taxonomy-wrapper').find('.newtag');
                }
                var $num = ( $tag_checklist.length );

                var $taxonomy_id = $sibling.attr('id');
                if ($taxonomy_id) {
                    $taxonomy_id = $taxonomy_id.split('new-tag-user_tag_');
                    $taxonomy_name = $taxonomy_id[1];
                }
                var $tag_html = '<div class="tag-hldr"><span><a id="user_tag-' + $taxonomy_name + '-check-num-' + $num + '" class="ntdelbutton">x</a></span>&nbsp;<a href="#" class="term-link">' + $term + '</a></div';
                //Taxonomy Name
                insert_tags($sibling, $taxonomy_name, $term, $tag_html, $tag_checklist);
                return false;
            });

            jQuery('#' + id).after(r);
        });
    });
    //Remove notices
    setInterval(function () {
        jQuery('#message.below-h2').hide('slow', function () {
            jQuery('.user-taxonomies-page #message.below-h2').remove();
        });
    }, 3000);
    // User Taxonomy Filters
    jQuery('.users-php select.ut-taxonomy-filter').each( function() {
        if ($(this).val() != '') {
            $('select.ut-taxonomy-filter').not(this).prop('disabled', true);
        }
    });

    jQuery('.users-php').on('change', 'select.ut-taxonomy-filter', function() {
        if ($(this).val() == '') {
            $('select.ut-taxonomy-filter').prop('disabled', false);
        } else {
            $('select.ut-taxonomy-filter').not(this).prop('disabled', true);
        }
    });


    jQuery('#sort_by').on('change', function() {
        if($(this).val()==="sp_articles") {
            $("#order option[value='ASC']").attr('disabled','disabled');
            $("#order option[value='DESC']").removeAttr('disabled');
            $("#order option[value='DESC']").attr('selected', 'selected');
		}
		else if($(this).val()==="distance") {
            $("#order option[value='DESC']").attr('disabled','disabled');
            $("#order option[value='ASC']").removeAttr('disabled');
            $("#order option[value='ASC']").attr('selected', 'selected');
        }
        else if($(this).val()==="likes") {
            $("#order option[value='ASC']").attr('disabled','disabled');
            $("#order option[value='DESC']").removeAttr('disabled');
            $("#order option[value='DESC']").attr('selected', 'selected');
        }
        else if($(this).val()==="featured") {
            $("#order option[value='ASC']").attr('disabled','disabled');
            $("#order option[value='DESC']").attr('disabled','disabled');
        }
        else if($(this).val()==="title") {
            $("#order option[value='ASC']").removeAttr('disabled');
            $("#order option[value='DESC']").removeAttr('disabled');
        }
        else if($(this).val()==="recent") {
            $("#order option[value='ASC']").removeAttr('disabled');
            $("#order option[value='DESC']").removeAttr('disabled');
        }
        else {
            $("#order option[value='ASC']").attr('disabled','disabled');
            $("#order option[value='DESC']").attr('disabled','disabled');
		}

    }).trigger('change');

});