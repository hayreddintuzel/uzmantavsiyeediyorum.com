<?php
/**
 * User Made Bookings
 * return html
 */

global $current_user, $wp_roles, $userdata, $post, $paged;
$dir_obj  = new DocDirect_Scripts();
$user_identity  = $current_user->ID;
$url_identity = $user_identity;

if( isset( $_GET['identity'] ) && !empty( $_GET['identity'] ) ){
  $url_identity = $_GET['identity'];
}

if (function_exists('fw_get_db_settings_option')) {
  $currency_select = fw_get_db_settings_option('currency_select');
} else{
  $currency_select = 'USD';
}

if (empty($paged)) $paged = 1;
$limit = get_option('posts_per_page');


$meta_query_args[] = array(
  'key' => 'bk_user_from',
  'value' => $user_identity,
  'compare' => '=',
  'type' => 'NUMERIC'
);
$meta_query_args[] = array(
  'key' => 'bk_status',
  'value' => 'cancelled',
  'compare' => '!=',
);
if( !empty( $_GET['by_date'] ) ){
  $meta_query_args[] = array(
    'key' => 'bk_timestamp',
    'value' => strtotime($_GET['by_date']),
    'compare' => '=',
    'type' => 'NUMERIC'
  );
}
$query_relation = array('relation' => 'AND',);
$meta_queries = array_merge($query_relation, $meta_query_args);

$args = array(
  'posts_per_page' => -1, 
  'post_type' => 'docappointments', 
  'post_status' => 'publish', 
  'ignore_sticky_posts' => 1,
  'meta_query' => $meta_queries,
);
$query = new WP_Query($args);
$count_post = $query->post_count;

$show_posts = get_option('posts_per_page') ? get_option('posts_per_page') : '-1';
$args = array(
  'posts_per_page' => $show_posts, 
  'post_type' => 'docappointments', 
  'post_status' => 'publish', 
  'ignore_sticky_posts' => 1,
  'order' => 'DESC',
  'orderby' => 'ID',
  'paged' => $paged,
  'meta_query' => $meta_queries,
);

$dir_profile_page = '';
if (function_exists('fw_get_db_settings_option')) {
  $dir_profile_page = fw_get_db_settings_option('dir_profile_page', $default_value = null);
}

$profile_page = isset($dir_profile_page[0]) ? $dir_profile_page[0] : '';

$booking_all_services = array();
$booking_user_services = array();

$date_format = get_option('date_format');
$time_format = get_option('time_format');
$current_date = date('Y-m-d');

?>
<div class="doc-booking-listings dr-bookings">
  <div class="tg-dashboard tg-docappointmentlisting tg-haslayout">
    <div class="tg-heading-border tg-small">
      <h3><?php esc_html_e('My Appointments','docdirect');?></h3>
    </div>
    <form class="tg-formappointmentsearch" action="<?php DocDirect_Scripts::docdirect_profile_menu_link($profile_page, 'bookings', $user_identity); ?>" method="get">
      <fieldset>
        <h4><?php esc_html_e('Search Here','docdirect');?>:</h4>
        <div class="form-group">
          <input type="hidden" class="" value="bookings" name="ref">
          <input type="hidden" class="" value="<?php echo intval( $user_identity ); ?>" name="identity">
          <input type="text" class="form-control booking-search-date" value="<?php echo isset( $_GET['by_date'] ) && !empty( $_GET['by_date'] ) ? $_GET['by_date'] : '';?>" name="by_date" placeholder="<?php esc_html_e('Search by date','docdirect');?>">
          <button type="submit"><i class="fa fa-search"></i></button>
        </div>
      </fieldset>
    </form>
    <div class="tg-appointmenttable">
      <table class="table">
        <thead class="thead-inverse">
          <tr>
            <th><?php esc_html_e('id.','docdirect');?></th>
            <th><?php esc_html_e('Specialist','docdirect');?></th>
            <th><?php esc_html_e('Appointment date','docdirect');?></th>
            <th><?php esc_html_e('More Detail','docdirect');?></th>
            <th></th>
          </tr>
        </thead>
        <tbody>
          <?php
          $post_dates = array();
          $counter = 0;
          $query = new WP_Query($args);
          if ($query->have_posts()):
            while ($query->have_posts()):
              $query->the_post();
              global $post;

              $post_id = $post->ID;

              $bk_status        = get_post_meta($post_id, 'bk_status',true);
              $bk_code          = get_post_meta($post_id, 'bk_code',true);
              $bk_category      = get_post_meta($post_id, 'bk_category',true);
              $bk_service       = get_post_meta($post_id, 'bk_service',true);
              $bk_booking_date  = get_post_meta($post_id, 'bk_booking_date',true);
              $bk_slottime      = get_post_meta($post_id, 'bk_slottime',true);
              $bk_subject       = get_post_meta($post_id, 'bk_subject',true);
              $bk_username      = get_post_meta($post_id, 'bk_username',true);
              $bk_userphone     = get_post_meta($post_id, 'bk_userphone',true);
              $bk_useremail     = get_post_meta($post_id, 'bk_useremail',true);
              $bk_booking_note  = get_post_meta($post_id, 'bk_booking_note',true);
              $bk_payment       = get_post_meta($post_id, 'bk_payment',true);
              $bk_user_to       = get_post_meta($post_id, 'bk_user_to',true);
              $bk_timestamp     = get_post_meta($post_id, 'bk_timestamp',true);
              $bk_user_from     = get_post_meta($post_id, 'bk_user_from',true);
              $bk_currency      = get_post_meta($post_id, 'bk_currency', true);
              $bk_paid_amount   = get_post_meta($post_id, 'bk_paid_amount', true);
              $bk_transaction_status = get_post_meta($post_id, 'bk_transaction_status', true);

              $user_to = get_user_by('ID', $bk_user_to);

              $services_cats = get_user_meta($bk_user_to , 'services_cats' , true);
              $booking_services = get_user_meta($bk_user_to , 'booking_services' , true);
              if( !empty( $booking_services ) ) {
                foreach( $booking_services as $key => $value ) {
                  $booking_user_services[$bk_user_to][$key] = $value;
                  $booking_all_services[$bk_user_to][$value['category']][$key] = $value;
                }
              } else {
                $booking_user_services[$bk_user_to] = array();
              }

              $payment_amount  = $bk_currency.$bk_paid_amount;

              $time = explode('-', $bk_slottime);
              $bk_timeslot_id = $post_id . '_' . date('Hi', strtotime('2016-01-01 ' . $time[0])) . '-' . date('Hi', strtotime('2016-01-01 ' . $time[1]));
              $post_dates[$post_id] = $bk_timeslot_id;

              $counter++;

              $trClass = 'booking-odd';
              if ($counter % 2 == 0) {
                $trClass = 'booking-even';
              }
          ?>
          <tr class="<?php echo esc_attr($trClass);?> booking-<?php echo $post_id;?>">
            <td data-name="id"><?php echo esc_attr( $bk_code );?></td>
            <td data-name="specialist"><?php echo esc_attr( $user_to->first_name . ' ' . $user_to->last_name );?></td>
            <td data-name="date"><?php echo date_i18n($date_format,strtotime($bk_booking_date));?>&nbsp;&nbsp;<?php echo date_i18n($time_format,strtotime('2016-01-01 '.$time[0]) );?>-<?php echo date_i18n($time_format,strtotime('2016-01-01 '.$time[1]) );?></td>
            <td data-name="notes"><a class="get-detail" href="javascript:;"><i class="fa fa-sticky-note-o"></i></a></td>
            <td>
              <?php if(isset($bk_status) && $bk_status == 'approved'): ?>
              <a class="tg-btncheck appointment-actioned fa fa-check" href="javascript:;"><?php esc_html_e('Approved','docdirect');?></a> 
              <?php elseif (isset($bk_status) && $bk_status == 'pending'): ?>
              <a class="tg-btncheck appointment-actioned fa fa-clock-o" href="javascript:;"><?php esc_html_e('Pending','docdirect');?></a> 
              <?php endif; ?>
              <a class="tg-btnclose get-process" data-type="cancel" data-id="<?php echo $post_id; ?>" href="javascript:;"><?php esc_html_e('Cancel','docdirect');?></a>
              <a class="tg-btnedit edit-appoinment" data-id="<?php echo $post_id; ?>" href="javascript:;"><?php esc_html_e('Edit','docdirect');?></a>
            </td>
          </tr>
          <tr class="tg-appointmentdetail bk-elm-hide">
            <td colspan="6">
              <div class="appointment-data-wrap">
                <ul class="tg-leftcol">
                  <li> 
                    <strong><?php esc_html_e('tracking id','docdirect');?>:</strong> 
                    <span><?php echo esc_attr( $bk_code );?></span> 
                  </li>
                  <li>
                    <strong><?php esc_html_e('Category','docdirect');?>:</strong>
                    <?php if( !empty( $services_cats[$bk_category] ) ){?>
                    <span><?php echo esc_attr( $services_cats[$bk_category] );?></span>
                    <?php }?>
                  </li>
                  <li> 
                    <strong><?php esc_html_e('Service','docdirect');?>:</strong>
                    <?php if( !empty( $booking_services[$bk_service] ) ){?>
                    <span><?php echo esc_attr( $booking_services[$bk_service]['title'] );?></span>
                    <?php }?>
                  </li>
                  <li>
                    <strong><?php esc_html_e('Phone','docdirect');?>:</strong>
                    <span><?php echo esc_attr( $bk_userphone );?></span>
                  </li>
                  <li>
                    <strong><?php esc_html_e('User Name','docdirect');?>:</strong>
                    <span><?php echo esc_attr( $bk_username );?></span>
                  </li>
                  <li>
                    <strong><?php esc_html_e('Email','docdirect');?>:</strong>
                    <span><?php echo esc_attr( $bk_useremail );?></span>
                  </li>
                  <li> 
                    <strong><?php esc_html_e('Appointment date','docdirect');?>:</strong> 
                    <?php if (!empty($bk_booking_date)): ?>
                    <span><?php echo date_i18n($date_format,strtotime($bk_booking_date));?></span> 
                    <?php endif; ?>
                  </li>
                  <li> 
                    <strong><?php esc_html_e('Meeting Time','docdirect');?>:</strong> 
                    <span><?php echo date_i18n($time_format,strtotime('2016-01-01 '.$time[0]) );?>&nbsp;-&nbsp;<?php echo date_i18n($time_format,strtotime('2016-01-01 '.$time[1]) );?></span> 
                  </li>
                  <li> 
                    <strong><?php esc_html_e('Status','docdirect');?>:</strong>
                    <span><?php echo esc_attr( docdirect_prepare_order_status( 'value',$bk_status ) );?></span> 
                  </li>
                  <li> 
                    <strong><?php esc_html_e('Payment Type','docdirect');?>:</strong>
                    <span><?php echo esc_attr( docdirect_prepare_payment_type( 'value',$bk_payment ) );?></span> 
                  </li>
                  <?php if (!empty($payment_amount)): ?>
                  <li> 
                    <strong><?php esc_html_e('Appointment Fee','docdirect');?>:</strong>
                    <span><?php echo esc_attr( $payment_amount );?></span>
                  </li>
                  <?php endif; ?>
                  <?php if (!empty($bk_transaction_status)): ?>
                  <li> 
                    <strong><?php esc_html_e('Payment Status','docdirect');?>:</strong>
                    <span><?php echo esc_attr( docdirect_prepare_order_status( 'value',$bk_transaction_status ) );?></span>
                  </li>
                  <?php endif; ?>
                </ul>
                <div class="tg-rightcol"> <strong><?php esc_html_e('notes:','docdirect');?></strong>
                  <?php if (!empty($bk_booking_note)): ?>
                  <div class="tg-description">
                    <p><?php echo esc_attr( $bk_booking_note );?></p>
                  </div>
                  <?php endif; ?>
                </div>
              </div>
            </td>
          </tr>
          <tr class="tg-appointmentdetail bk-elm-hide">
            <td colspan="6" style="background-color: #f7f7f7 !important;">
              <form style="position: relative;" action="#" method="post" id="appoinment-form-<?php echo intval( $post_id );?>">
                <div class="appointment-data-wrap">
                  <div class="form-group">
                    <div class="doc-select">
                      <select name="bk_category" class="bk_category_update" data-id="<?php echo intval($post_id); ?>" data-user="<?php echo $bk_user_to; ?>">
                        <option value=""><?php esc_html_e('Select Category*','docdirect');?></option>
                        <?php 
                        if( !empty( $services_cats ) ) {
                          foreach( $services_cats as $key => $value ) {
                        ?>
                        <option <?php echo $key == $bk_category ? "selected" : ""; ?> value="<?php echo esc_attr( $key );?>"><?php echo esc_attr( $value );?></option>
                        <?php
                          }
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="form-group">
                    <div class="doc-select">
                      <select name="bk_service" id="bk_service<?php echo intval($post_id); ?>">
                        <option value=""><?php esc_html_e('Select Service*','docdirect');?></option>
                        <?php 
                        if( !empty($booking_all_services) && !empty($booking_all_services[$bk_user_to]) && !empty($booking_all_services[$bk_user_to][$bk_category])) {
                          foreach($booking_all_services[$bk_user_to][$bk_category] as $key => $value) {
                        ?>
                        <option <?php echo $key == $bk_service ? "selected" : ""; ?> value="<?php echo esc_attr( $key );?>"><?php echo esc_attr( $value['title'] );?>&nbsp;--&nbsp;<?php echo esc_attr( $currency_symbol );?><?php echo esc_attr( $value['price'] );?></option>
                        <?php
                          }
                        }
                        ?>
                      </select>
                    </div>
                  </div>
                  <div class="bk-booking-schedules">
                    <div class="tg-appointmenttime" style="position: relative; padding: 10px;">
                      <div id="picker<?php echo $post_id; ?>" class="tg-dayname booking-pickr"> 
                        <strong><?php echo date_i18n($date_format,strtotime($bk_booking_date));?></strong>
                        <input type="hidden" name="booking_date" class="booking_date" value="<?php echo esc_attr($bk_booking_date); ?>" />
                      </div>
                      <div class="tg-timeslots step-two-slots">
                        <div class="tg-timeslotswrapper">
                          <?php echo docdirect_prepare_booking_step_two($bk_user_to, $bk_booking_date, $post_id); ?>
                        </div>
                      </div>
                    </div>
                  </div>
                  <script type="text/javascript">
                    jQuery(function() {
                      var post_id = '<?php echo $post_id; ?>';
                      var post_date = '<?php echo $bk_booking_date; ?>';
                      var user_id = '<?php echo $bk_user_to; ?>';
                      update_calendar(post_id, post_date, user_id);
                    });
                  </script>
                  <div class="form-group">
                    <input type="text" class="form-control" name="subject" value="<?php echo esc_attr( $bk_subject );?>" placeholder="<?php esc_attr_e('Subject','docdirect');?>">
                  </div>
                  <div class="form-group">
                    <input type="text" class="form-control" name="username" value="<?php echo esc_attr( $bk_username );?>" placeholder="<?php esc_attr_e('Your Name*','docdirect');?>">
                  </div>
                  <div class="form-group">
                    <input type="text" class="form-control" name="userphone" value="<?php echo esc_attr( $bk_userphone );?>" placeholder="<?php esc_attr_e('Phone*','docdirect');?>">
                  </div>
                  <div class="form-group">
                    <input type="text" class="form-control" name="useremail" value="<?php echo esc_attr( $bk_useremail );?>" placeholder="<?php esc_attr_e('Email*','docdirect');?>">
                  </div>
                  <div class="form-group tg-textarea">
                    <textarea class="form-control" name="booking_note" placeholder="<?php esc_attr_e('Note','docdirect');?>"><?php echo esc_attr( $bk_booking_note );?></textarea>
                  </div>
                  <div class="form-group tg-textarea">
                    <button type="button" class="btn btn-danger cancel-appoinment"><?php esc_html_e('Cancel','docdirect');?></button>
                    <button type="button" class="btn btn-primary save-appoinment" data-id="<?php echo intval( $post_id );?>"><?php esc_html_e('Save','docdirect');?></button>
                  </div>
                </div>
              </form>
            </td>
          </tr>
          <?php 
            endwhile; wp_reset_postdata(); 
          else:
          ?>
          <tr>
            <td colspan="6">
              <?php DoctorDirectory_NotificationsHelper::informations(esc_html__('No appointments found.','docdirect'));?>
            </td>
          </tr>
          <?php endif; ?>
        </tbody>
      </table>
      <script>
        var services = jQuery.parseJSON('<?php echo addslashes(json_encode($booking_user_services));?>');
        var category_services = jQuery.parseJSON('<?php echo addslashes(json_encode($booking_all_services));?>');
        jQuery(document).on('change', '.bk_category_update', function (event) {
          var _this = jQuery(this)
          var appointment_id = _this.data('id');
          var user_id = _this.data('user');
          var category_id = _this.val();
          
          var load_services = wp.template('load-services');
          var selected_services = category_services[user_id] && category_services[user_id][category_id] ? category_services[user_id][category_id] : services[user_id];
          var options  = load_services(selected_services);
          jQuery('#bk_service' + appointment_id).html(options);
        });

        var loader_html = '<div class="docdirect-loader-wrap"><div class="docdirect-loader"><div class="bounce1"></div><div class="bounce2"></div><div class="bounce3"></div></div></div>';

        var post_dates = jQuery.parseJSON('<?php echo addslashes(json_encode($post_dates)); ?>');

        function correct_timeslot(post_id) {
          var input_id = post_dates[post_id];
          var input = jQuery('#' + input_id);
          input.prop('disabled', false);
          var div = input.parents('div.tg-doctimeslot');
          div.removeClass('tg-booked');
          div.addClass('tg-available');
          input.next('label').click();
        }

        function update_calendar(post_id, post_date, user_id) {
          var picker = jQuery('#picker' + post_id);
          if (post_id != '0' && picker.children('input').val() == post_date) {
            correct_timeslot(post_id);
          }
          picker.datetimepicker({
            format: scripts_vars.calendar_format,
            minDate: new Date(),
            timepicker: false,
            onChangeDateTime: function(dp, $input) {
              moment.locale(scripts_vars.calendar_locale);
              var slot_date = moment(dp).format('YYYY-MM-DD');

              picker.children('input').val(slot_date);
              picker.children('strong').html(moment(dp).format('MMM D, dddd'));

              jQuery('.tg-appointmenttime').append(loader_html);

              var data_string = 'post_id='+post_id+'&slot_date='+slot_date+'&data_id='+user_id+'&action=docdirect_get_booking_step_two';
              jQuery.ajax({
                type: "POST",
                url: scripts_vars.ajaxurl,
                data: data_string,
                dataType: "json",
                success: function(response) {
                  jQuery('body').find('.docdirect-loader-wrap').remove();
                  picker.next('div.step-two-slots').children('.tg-timeslotswrapper').html(response.data);
                  update_calendar(post_id, post_date, user_id);
                }
              });
              return false;
            }
          });
        }

        jQuery(function() {
          jQuery(document).on('click', '#new-appoinment', function(e) {
            jQuery('#new-appoinment-form').slideToggle(200);
          });

          jQuery(document).on('click', '.cancel-appoinment', function(e) {
            jQuery(this).parents('tr').slideToggle(200);
          });          

          jQuery(document).on('click', '.save-appoinment', function(e) {
            var _this = jQuery(this);
            var post_id = _this.data('id');
            var form_id = '#appoinment-form-' + post_id;
            var form = jQuery(form_id);

            // check required fields
            var values = {};
            jQuery.each(form.serializeArray(), function(i, field) {
              values[field.name] = field.value;
            });

            var error_message = '';
            if (!values['bk_category']) {
              error_message = '<?php echo esc_attr_e('Select a category', 'docdirect'); ?>';
            } else if (!values['bk_service']) {
              error_message = '<?php echo esc_attr_e('Select a service', 'docdirect'); ?>';
            } else if (!values['booking_date']) {
              error_message = '<?php echo esc_attr_e('Choose a date', 'docdirect'); ?>';
            } else if (!values['slottime']) {
              error_message = '<?php echo esc_attr_e('Choose a time slot', 'docdirect'); ?>';
            } else if (!values['username']) {
              error_message = '<?php echo esc_attr_e('Enter a name', 'docdirect'); ?>';
            } else if (!values['userphone']) {
              error_message = '<?php echo esc_attr_e('Enter a phone number', 'docdirect'); ?>';
            } else if (!values['useremail']) {
              error_message = '<?php echo esc_attr_e('Enter an email address', 'docdirect'); ?>';
            }

            if (error_message != '') {
              jQuery.sticky(error_message, {classList: 'important', speed: 200, autoclose: 5000});
              return;
            }

            _this.append(loader_html);
            var action = 'docdirect_update_booking';
            if (post_id == '0') {
              action = 'docdirect_insert_booking'
            }
            var serialize_data = form.serialize();
            var data_string = serialize_data+'&data_id='+post_id+'&action='+action;
            jQuery.ajax({
              type: "POST",
              url: scripts_vars.ajaxurl,
              data: data_string,
              dataType:"json",
              success: function(response) {
                jQuery('body').find('.docdirect-loader-wrap').remove();
                jQuery.sticky('<?php esc_attr_e('Appointment has been saved','docdirect');?>', {classList: 'success', speed: 200, autoclose: 5000});
                setTimeout(function() {
                  window.location.reload();
                }, 300);
              }
            });
          });
        });
      </script> 
      <script type="text/template" id="tmpl-load-services">
        <option value=""><?php esc_html_e('Select Service*','docdirect');?></option>
        <#
          var _option = '';
          if( !_.isEmpty(data) ) {
            _.each( data , function(element, index, attr) { #>
               <option value="{{index}}">{{element.title}}&nbsp;--&nbsp;<?php echo esc_attr( $currency_symbol );?>{{element.price}}</option>
            <#  
            });
          }
        #>
      </script> 
      <div class="col-md-xs-12">
        <?php 
        if( $count_post > $limit ) {
          docdirect_prepare_pagination($count_post,$limit);
        }
        ?>
      </div>
    </div>
  </div>
</div>
<script type="text/template" id="tmpl-status-approved">
  <a class="tg-btncheck appointment-actioned fa fa-check" href="javascript:;"><?php esc_html_e('Approved','docdirect');?></a>
  <a class="tg-btnclose get-process" data-type="cancel" data-id="{{{data.id}}}" href="javascript:;"><?php esc_html_e('Cancel','docdirect');?></a>
  <a class="tg-btnedit edit-appoinment" data-id="{{{data.id}}}" href="javascript:;"><?php esc_html_e('Edit','docdirect');?></a> 
</script>
