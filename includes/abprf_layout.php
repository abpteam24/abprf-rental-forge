<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPRF_Layout' ) ) {
		class ABPRF_Layout {
			public function __construct() {
				add_action( 'abprf_load_date_picker', [ $this, 'load_date_picker' ], 10, 2 );
				add_action( 'abprf_generate_script_data', [ $this, 'generate_script_data' ], 10, 2 );
				//==============================//
				add_action( 'abprf_add_icon', array( $this, 'load_icon' ), 10, 2 );
				add_action( 'abprf_add_image', array( $this, 'add_single_image' ), 10, 2 );
				add_action( 'abprf_add_image_multiple', array( $this, 'add_image_multi' ), 10, 2 );
				add_action( 'abprf_add_image_icon', array( $this, 'selection_icon_image' ), 10, 3 );
				//==============================//
				add_action( 'abprf_slider', array( $this, 'full_slider' ) );
				add_action( 'abprf_slider_only', array( $this, 'slider_only' ), 10, 2 );
				//==============================//
			}

			public function load_date_picker( $selector, $dates ): void {
				$start_date  = current( $dates );
				$start_year  = gmdate( 'Y', strtotime( $start_date ) );
				$start_month = ( gmdate( 'n', strtotime( $start_date ) ) - 1 );
				$start_day   = gmdate( 'j', strtotime( $start_date ) );
				$end_date    = end( $dates );
				$end_year    = gmdate( 'Y', strtotime( $end_date ) );
				$end_month   = ( gmdate( 'n', strtotime( $end_date ) ) - 1 );
				$end_day     = gmdate( 'j', strtotime( $end_date ) );
				$all_date    = [];
				foreach ( $dates as $date ) {
					$all_date[] = '"' . gmdate( 'j-n-Y', strtotime( $date ) ) . '"';
				}
				?>
                <script>
                    jQuery(document).ready(function () {
                        jQuery("<?php echo esc_attr( $selector ); ?>").datepicker({
                            dateFormat: abprf_var.date_format,
                            autoSize: true, changeMonth: true, changeYear: true,
                            minDate: new Date(<?php echo esc_attr( $start_year ); ?>, <?php echo esc_attr( $start_month ); ?>, <?php echo esc_attr( $start_day ); ?>),
                            maxDate: new Date(<?php echo esc_attr( $end_year ); ?>, <?php echo esc_attr( $end_month ); ?>, <?php echo esc_attr( $end_day ); ?>),
                            beforeShowDay: available_check,
                            onSelect: function (dateString, data) {
                                let date = data.selectedYear + '-' + ('0' + (parseInt(data.selectedMonth) + 1)).slice(-2) + '-' + ('0' + parseInt(data.selectedDay)).slice(-2);
                                jQuery(this).closest('label').find('input[type="hidden"]').val(date).trigger('change');
                            }
                        });
                        function available_check(date) {
                            let availableDates = [<?php echo wp_kses_post( implode( ',', $all_date ) ); ?>];
                            let dmy = date.getDate() + "-" + (date.getMonth() + 1) + "-" + date.getFullYear();
                            if (jQuery.inArray(dmy, availableDates) !== -1) {
                                return [true, "", "<?php esc_attr_e( 'Available', 'abprf-rental-forge' ); ?>"];
                            } else {
                                return [false, "", "<?php esc_attr_e( 'Unavailable', 'abprf-rental-forge' ); ?>"];
                            }
                        }
                    });
                </script>
				<?php
			}

			public function generate_script_data( $js_all_info = [] ): void {
				wp_enqueue_script( 'abprf_infos', ABPRF_URL . '/assets/js/abprf.js', array( 'jquery' ), time(), true );
				$rental_data = array(
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'abprf_ajax_nonce' ),
					'date_info' => json_encode( $js_all_info ),
					'now' => current_time( 'Y-m-d H:i' ),
					'msg' => [
						'property_loading' => __( 'Property List Loading.............', 'abprf-rental-forge' ),
						'property_loading_success' => __( 'Property List already Loaded !', 'abprf-rental-forge' ),
						'select_rent_start_date' => __( 'Please Select rent Start Date', 'abprf-rental-forge' ),
						'select_rent_start_time' => __( 'Please Select rent Start Time', 'abprf-rental-forge' ),
						'select_rent_end_time' => __( 'Please Select rent End Time', 'abprf-rental-forge' ),
						'free' => __( 'FREE', 'abprf-rental-forge' ),
					],
				);
				wp_localize_script( 'abprf_infos', 'abprf_infos', $rental_data );
			}

			//==============================//
			public static function load_admin_globally(): void {
				ABPRF_Layout::popup_empty( '#abprf_property_popup' );
				ABPRF_Layout::popup_empty( '#abprf_category_popup' );
				ABPRF_Layout::popup_empty( '#abprf_location_popup' );
				ABPRF_Layout::popup_empty( '#abprf_brand_popup' );
				//ABPRF_Layout::popup_empty( '#abprf_feature_popup' );
				ABPRF_Layout::icon_popup(); ?>
                <div class="toast_msg_area"></div>
				<?php
			}

			//==============================//
			public static function button_add( $button_text, $class = '', $button_class = '', $icon_class = '', $change_input_name = '' ): void {
				$class        = $class ?: 'add_new_hook';
				$button_class = $button_class ?: '_btn_default';
				$icon_class   = $icon_class ?: 'fas fa-plus';
				?>
                <button class="<?php echo esc_attr( $button_class . ' ' . $class ); ?>" type="button">
                    <span class="_mar_r_xs <?php echo esc_attr( $icon_class ); ?>"></span><span data-input-change="<?php echo esc_attr( $change_input_name ); ?>"><?php echo esc_html( $button_text ); ?></span>
                </button>
				<?php
			}

			public static function button_delete_sort_edit(): void {
				?>
                <div class="_all_center">
                    <div class="_group_content">
						<?php
							self::button_edit();
							self::button_sort();
							self::button_delete();
						?>
                    </div>
                </div>
				<?php
			}

			public static function button_delete_sort(): void {
				?>
                <div class="_all_center">
                    <div class="_group_content">
						<?php
							self::button_sort();
							self::button_delete();
						?>
                    </div>
                </div>
				<?php
			}

			public static function button_edit( $class_edit = 'edit_hook' ): void {
				?>
                <button class="_btn_navy_blue_xs <?php echo esc_attr( $class_edit ); ?>" type="button" title="<?php esc_attr_e( 'Edit This Item', 'abprf-rental-forge' ); ?>">
                    <span class="fas fa-edit"></span>
                </button>
				<?php
			}

			public static function button_delete( $class = 'delete_hook' ): void {
				?>
                <button class="_btn_danger_xs <?php echo esc_attr( $class ); ?>" type="button" title="<?php esc_attr_e( 'Delete This Item', 'abprf-rental-forge' ); ?>">
                    <span class="fas fa-times"></span>
                </button>
				<?php
			}

			public static function button_sort(): void {
				?>
                <div class="_btn_warning_xs sortable_handle" type="button" title="<?php esc_attr_e( 'Move This Item', 'abprf-rental-forge' ); ?>">
                    <span class="fas fa-arrows-alt"></span>
                </div>
				<?php
			}

			//=============================//
			public static function popup_button( $target_popup_id, $text ): void {
				?>
                <button type="button" class="_btn_default_bg_blue" data-target-popup="<?php echo esc_attr( $target_popup_id ); ?>"><span class="fas fa-plus-square"></span> <?php echo esc_html( $text ); ?></button>
				<?php
			}

			public static function popup_button_xs( $target_popup_id, $text ): void {
				?>
                <button type="button" class="_btn_default_xs_bg_blue" data-target-popup="<?php echo esc_attr( $target_popup_id ); ?>"><span class="fas fa-plus-square"></span> <?php echo esc_html( $text ); ?></button>
				<?php
			}

			public static function popup_empty( $target_popup_id, $class = '' ): void {
				?>
                <div class="abprf_popup abprf_area <?php echo esc_attr( $class ); ?>" data-popup="<?php echo esc_attr( $target_popup_id ); ?>">
                    <div class="popup_area">
                        <span class="popup_close"><i class="fas fa-times"></i></span>
                        <div class="popup_body"></div>
                    </div>
                </div>
				<?php
			}

			public static function icon_popup(): void {
				?>
                <div class="popup_icon abprf_popup" data-popup="#abprf_popup_icon">
                    <div class="popup_area">
                        <div class="popup_head _all_center">
                            <div class="abp_dropdown _max_400">
                                <label class="_abprf_all_center">
                                    <input type="hidden" class="abp_icon_search_hidden" name="abp_icon_search" value=""/>
                                    <input type="text" class="_form_control_text_center validation_name abprf_allow abp_icon_search" name="" placeholder="<?php esc_attr_e( 'Search  icon', 'abprf-rental-forge' ); ?>" value=""/>
                                </label>
                                <div class="dropdown_list"></div>
                            </div>
                            <span class="popup_close"><i class="fas fa-times"></i></span>
                        </div>
                        <div class="popup_body">
                            <h4 class="_abprf_text_center item_icon_title"></h4>
                            <div class="item_icon_area"></div>
                        </div>
                    </div>
                </div>
				<?php
			}

			//=============================//
			public static function info_text( $key ): void {
				$data = ABPRF_Layout::array_info( $key );
				if ( $data ) {
					?>
                    <span class="info_text">
                        <span class="_mar_r_xxs">ℹ️</span>
                        <?php self::load_more_text( $data ); ?>
                    </span>
					<?php
				}
			}

			public static function layout_warning_info( $key ): void {
				$data = ABPRF_Layout::array_info( $key );
				if ( $data ) {
					echo '<div class="_section_bg_warning_mar_zero"><h4 class="_abprf_text_center_color_white">' . esc_html( $data ) . '</h4></div>';
				}
			}

			public static function layout_warning_info_xs( $key ): void {
				$data = ABPRF_Layout::array_info( $key );
				if ( $data ) {
					echo '<div class="_abprf_text_center_color_white_bg_warning_padding_xxs_fs_label">' . esc_html( $data ) . '</div>';
				}
			}

			public static function bg_image( $post_id = '', $image_id = '', $url = '', $class = '' ): void {
				$image_url = ( $post_id > 0 || $image_id ) ? ABPRF_Function::get_image_url( $post_id, $image_id ) : $url;
				$post_url  = $post_id > 0 ? get_the_permalink( $post_id ) : '';
				$image_url = $image_url ?: ABPRF_BLANK_IMG_URL;
				if ( $image_url ) {
					?>
                    <div class="bg_img  <?php echo esc_attr( $class ); ?>" data-href="<?php echo esc_url( $post_url ); ?>" data-placeholder>
                        <div data-bg-image="<?php echo esc_url( $image_url ); ?>"></div>
                    </div>
					<?php
				}
			}

			public static function load_more_text( $text = '', $length = 200 ): void {
				$text_length = strlen( $text );
				if ( $text && $text_length > $length ) {
					?>
                    <span class="load_more">
                        <span data-content><?php echo esc_html( substr( $text, 0, $length ) ); ?> .... <span data-read><?php esc_html_e( 'Load More', 'abprf-rental-forge' ); ?></span></span>
                        <span data-content class="_d_none"><?php echo esc_html( $text ); ?>.... <span data-read><?php esc_html_e( 'Less More', 'abprf-rental-forge' ); ?></span></span>
                    </span>
					<?php
				} else {
					?>
                    <span><?php echo esc_html( $text ); ?></span>
					<?php
				}
			}

			public static function on(): bool|string {
				ob_start();
				?>
                <strong class="_abprf_color_theme"> <?php esc_html_e( 'ON', 'abprf-rental-forge' ); ?></strong>
				<?php
				return ob_get_clean();
			}

			public static function off(): bool|string {
				ob_start();
				?>
                <strong class="_abprf_color_theme"> <?php esc_html_e( 'OFF', 'abprf-rental-forge' ); ?></strong>
				<?php
				return ob_get_clean();
			}

			//==============Input field===============//
			public static function input_dropdown( $infos, $icon = '' ): void {
				if ( is_array( $infos ) && sizeof( $infos ) > 0 ) {
					asort( $infos );
					?>
                    <div class="dropdown_list">
                        <ul class="_abprf">
							<?php foreach ( $infos as $info ) { ?>
                                <li data-value="<?php echo esc_attr( $info ); ?>"><span class="<?php echo esc_attr( $icon ); ?> _mar_r_xxs"></span><span data-text><?php echo esc_html( $info ); ?></span></li>
							<?php } ?>
                        </ul>
                    </div>
					<?php
				}
			}

			public static function quantity_input( $input_info = [] ): void {
				$name        = array_key_exists( 'name', $input_info ) ? $input_info['name'] : '';
				$price       = array_key_exists( 'price', $input_info ) ? $input_info['price'] : 0;
				$min_qty     = array_key_exists( 'min_qty', $input_info ) ? $input_info['min_qty'] : 1;
				$max_qty     = array_key_exists( 'max_qty', $input_info ) ? $input_info['max_qty'] : 1;
				$class       = array_key_exists( 'class', $input_info ) ? $input_info['class'] : '';
				$collapse_id = array_key_exists( 'collapse_id', $input_info ) ? $input_info['collapse_id'] : '';
				if ( $name && $max_qty >= $min_qty ) {
					if ( ! empty( $collapse_id ) ) {
						?> <div data-collapse="<?php echo esc_attr( $collapse_id ); ?>"><?php
					}
					?>
                    <div class="_group_content qty_input">
                        <div class="qty_decrease _ag_content"> ➖</div>
                        <label>
                            <input type="text" class="_form_control  validation_number <?php echo esc_attr( $class ); ?>"
                                   name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $min_qty ); ?>"
                                   data-price="<?php echo esc_attr( $price ); ?>" data-min="<?php echo esc_attr( $min_qty ); ?>" data-max="<?php echo esc_attr( $max_qty ); ?>"
                            />
                        </label>
                        <div class="qty_increase _ag_content">➕</div>
                    </div>
					<?php
					if ( ! empty( $collapse_id ) ) {
						?></div><?php
					}
				}
			}

			public static function switch_checkbox( $name, $value = '' ): void {
				$value = in_array( $value, [ 'on', 'off', '' ], true ) ? $value : '';
				?>
                <div class="_br <?php echo esc_attr( $value === 'on' ? 'rf_active' : '' ); ?>" data-switch data-collapse-target="#<?php echo esc_attr( $name ); ?>">
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>">
                </div>
				<?php
			}

			public static function input_title( $label = '', $required = '' ): void {
				if ( $label ) { ?>
                    <span class="_mar_b_xxs">
							<?php echo esc_html( $label ); ?>
						<?php if ( $required ) { ?>
                            <sup class="_color_required">*</sup>
						<?php } ?>
						</span>
					<?php
				}
			}

			public static function input_date( $name, $date = '', $label = '', $required = '' ): void {
				$date_format  = ABPRF_Function::date_picker_format();
				$now          = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
				$hidden_date  = $date ? gmdate( 'Y-m-d', strtotime( $date ) ) : '';
				$visible_date = $date ? date_i18n( $date_format, strtotime( $date ) ) : '';
				?>
                <label class="_input_item">
					<?php self::input_title( $label, $required ); ?>
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $hidden_date ); ?>" <?php echo esc_attr( $required ); ?>/>
                    <input type="text" name="" class="_form_control abp_datepicker" value="<?php echo esc_attr( $visible_date ); ?>" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                    <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abprf-rental-forge' ); ?>"></span>
                </label>
				<?php
			}

			public static function input_time( $name, $time = '', $label = '', $required = '' ): void {
				?>
                <label class="_input_item">
					<?php self::input_title( $label, $required ); ?>
                    <input type="time" class="_form_control" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $time ); ?>" <?php echo esc_attr( $required ); ?>/>
                    <span class="fas fa-times time_close_icon" title="<?php esc_attr_e( 'Clear Time', 'abprf-rental-forge' ); ?>"></span>
                </label>
				<?php
			}

			public static function textarea( $name, $value = '', $label = '', $required = '' ): void {
				?>
                <label class="abprf_textarea _input_item">
					<?php self::input_title( $label, $required ); ?>
                    <textarea name="<?php echo esc_attr( $name ); ?>" rows="3" class="_form_control" placeholder="<?php echo esc_attr( $label ); ?>" title="<?php echo esc_attr( $label ); ?>"  <?php echo esc_attr( $required ); ?>><?php echo esc_textarea( $value ); ?></textarea>
                </label>
				<?php
			}

			public static function select( $name, $value = '', $label = '', $required = '', $options = [] ): void {
				if ( is_array( $options ) && sizeof( $options ) > 0 ) {
					?>
                    <label class="_input_item">
						<?php self::input_title( $label, $required ); ?>
                        <select name="<?php echo esc_attr( $name ); ?>" class="_form_control" title="<?php echo esc_attr( $label ); ?>" <?php echo esc_attr( $required ); ?>>
                            <option value="" disabled selected><?php echo esc_html__( 'Please Select', 'abprf-rental-forge' ) . ' ' . esc_html( $label ); ?></option>
							<?php foreach ( $options as $option ) { ?>
                                <option value="<?php echo esc_attr( $option ); ?>" <?php echo esc_attr( $option == $value ? 'selected' : '' ); ?>><?php echo esc_html( $option ); ?></option>
							<?php } ?>
                        </select>
                    </label>
					<?php
				}
			}

			public static function checkbox( $name, $value = '', $label = '', $required = '', $options = [] ): void {
				if ( is_array( $options ) && sizeof( $options ) > 0 ) {
					?>
                    <div class="custom_checkbox _input_item">
                        <span class="_fs_label"> <?php self::input_title( $label, $required ); ?></span>
                        <input type="hidden" class="_form_control" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                        <div class="_f_wrap">
							<?php foreach ( $options as $option ) { ?>
                                <div class="checkbox_item">
                                    <button type="button" class="_btn_white_xs <?php echo esc_attr( $option == $value ? 'rf_active' : '' ); ?>" data-checked="<?php echo esc_attr( $option ); ?>" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr( $option == $value ? 'far fa-check-square' : 'far fa-square' ); ?>"></span><?php echo esc_html( $option ); ?>
                                    </button>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}
			}

			public static function radio( $name, $value = '', $label = '', $required = '', $options = [] ): void {
				if ( is_array( $options ) && sizeof( $options ) > 0 ) {
					?>
                    <div class="custom_radio _input_item">
                        <span class="_fs_label"> <?php self::input_title( $label, $required ); ?></span>
                        <input type="hidden" class="_form_control" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                        <div class="_f_wrap">
							<?php foreach ( $options as $option ) { ?>
                                <div class="radio_item">
                                    <button type="button" class="_btn_white_xs <?php echo esc_attr( $option == $value ? 'rf_active' : '' ); ?>" data-radio="<?php echo esc_attr( $option ); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                        <span data-icon class="_mar_r_xs <?php echo esc_attr( $option == $value ? 'far fa-check-circle' : 'far fa-circle' ); ?>"></span><?php echo esc_html( $option ); ?>
                                    </button>
                                </div>
							<?php } ?>
                        </div>
                    </div>
					<?php
				}
			}

			//=============slider / Image / Icon================//
			public function load_icon( $name, $value = '' ): void {
				$button_active_class = $value ? '_d_none' : '';
				$icon                = $emoji = '';
				if ( preg_match( '/\s/', $value ) ) {
					$icon = $value;
				} else {
					$emoji = $value;
				}
				$icon_class = ( $icon || $emoji ) ? '' : '_d_none';
				?>
                <div class="icon_image_selection_area">
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                    <div class="icon_item  <?php echo esc_attr( $icon_class ); ?>">
                        <div class="_all_center"><span class="<?php echo esc_attr( $icon ); ?>" data-add-icon><?php echo esc_html( $emoji ); ?></span></div>
                        <span class="fas fa-times icon_close icon_delete" title="<?php esc_html_e( 'Remove Icon', 'abprf-rental-forge' ); ?>"></span>
                    </div>
                    <div class="image_icon_select_area <?php echo esc_attr( $button_active_class ); ?>">
                        <button class="_btn_info_xs icon_add" type="button" data-target-popup="#abprf_popup_icon"><span class="fas fa-icons _fs_h6"></span></button>
                    </div>
                </div>
				<?php
			}

			public function add_single_image( $name, $image_id = '' ): void {
				?>
                <div class="add_image">
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $image_id ); ?>"/>
					<?php if ( $image_id ) { ?>
                        <div class="add_image_item" data-image-id="<?php echo esc_attr( $image_id ); ?>'">
                            <span class="fas fa-times _circle_icon_xs remove_image"></span>
                            <img class="_img_control" src="<?php echo esc_url( wp_get_attachment_image_url( $image_id, 'medium' ) ); ?>" alt="<?php echo esc_attr( $image_id ); ?>"/>
                        </div>
					<?php } ?>
                    <button type="button" class="_btn_default_xs_bg_color_light_1_w_full <?php echo esc_attr( $image_id ? '_d_none' : '' ); ?>">
                        <span class="fas fa-image _mar_r_xs"></span><?php esc_html_e( 'Image', 'abprf-rental-forge' ); ?>
                    </button>
                </div>
				<?php
			}

			public function add_image_multi( $name, $images ): void {
				$images = is_array( $images ) ? ABPRF_Function::array_to_string( $images ) : $images;
				?>
                <div class="multiple_image_area">
                    <input type="hidden" class="multiple_image_ids" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $images ); ?>"/>
                    <div class="multiple_image">
						<?php
							$all_images = explode( ',', $images );
							if ( $images && sizeof( $all_images ) > 0 ) {
								foreach ( $all_images as $image ) {
									$img_url = ABPRF_Function::get_image_url( '', $image, 'medium' ) ?: ABPRF_BLANK_IMG_URL;
									?>
                                    <div class="multiple_image_item" data-image-id="<?php echo esc_attr( $image ); ?>">
                                        <span class="fas fa-times _circle_icon_xs remove_image_multi"></span>
                                        <img class="_img_control" src="<?php echo esc_attr( $img_url ); ?>" alt="<?php echo esc_attr( $image ); ?>"/>
                                    </div>
									<?php
								}
							}
						?>
                    </div>
                    <div class="_divider_xs"></div>
					<?php ABPRF_Layout::button_add( __( 'Add  Image', 'abprf-rental-forge' ), 'add_image_multi' ); ?>
                </div>
				<?php
			}

			public function selection_icon_image( $name, $value = '' ): void {
				$icon = $image = $emoji = '';
				if ( is_numeric( $value ) ) {
					$image = $value;
				} elseif ( preg_match( '/\s/', $value ) ) {
					$icon = $value;
				} else {
					$emoji = $value;
				}
				$icon_class          = ( $icon || $emoji ) ? '' : '_d_none';
				$image_class         = $image ? '' : '_d_none';
				$button_active_class = ( $icon || $image || $emoji ) ? '_d_none' : '';
				?>
                <div class="icon_image_selection_area _fd_column">
                    <input type="hidden" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                    <div class="icon_item <?php echo esc_attr( $icon_class ); ?>">
                        <div class="_all_center"><span class="<?php echo esc_attr( $icon ); ?>" data-add-icon><?php echo esc_html( $emoji ); ?></span></div>
                        <span class="fas fa-times icon_close icon_delete" title="<?php esc_html_e( 'Remove Icon', 'abprf-rental-forge' ); ?>"></span>
                    </div>
                    <div class="image_item <?php echo esc_attr( $image_class ); ?>">
                        <img class="_img_control" src="<?php echo esc_url( ABPRF_Function::get_image_url( '', $image, 'medium' ) ); ?>" alt="image">
                        <span class="fas fa-times icon_close image_delete" title="<?php esc_html_e( 'Remove Image', 'abprf-rental-forge' ); ?>"></span>
                    </div>
                    <div class="image_icon_select_area <?php echo esc_attr( $button_active_class ); ?>">
                        <div class="_group_content_f_equal_w_full">
                            <button class="_btn_info_xs image_select" type="button"><span class="fas fa-image _fs_h6"></span></button>
                            <button class="_btn_info_xs icon_add" type="button" data-target-popup="#abprf_popup_icon"><span class="fas fa-icons _fs_h6"></span></button>
                        </div>
                    </div>
                </div>
				<?php
			}

			public static function image_icon( $icon_image, $class = '' ): void {
				$icon = $image = $emoji = '';
				if ( is_numeric( $icon_image ) ) {
					$image = $icon_image;
				} elseif ( preg_match( '/\s/', $icon_image ) ) {
					$icon = $icon_image;
				} else {
					$emoji = $icon_image;
				}
				if ( $image ) {
					ABPRF_Layout::bg_image( '', $image );
				} else { ?>
                    <span class="<?php echo esc_attr( $icon . ' ' . $class ); ?>"><?php echo esc_html( $emoji ); ?></span>
				<?php }
			}

			public function full_slider( $abprf_infos ): void {
				$post_id        = array_key_exists( 'post_id', $abprf_infos ) ? $abprf_infos['post_id'] : 0;
				$display_slider = array_key_exists( 'display_slider', $abprf_infos ) ? $abprf_infos['display_slider'] : 'on';
				$abprf_slider   = ABPRF_Function::get_option( 'abprf_slider' );
				$image_ids      = array_unique( ABPRF_Function::get_post_info( $post_id, 'abprf_sliders', array() ) );
				//echo '<pre>';print_r($image_ids);echo '</pre>';
				if ( sizeof( $image_ids ) > 0 && $display_slider == 'on' ) {
					if ( sizeof( $image_ids ) > 1 ) {
						$this->slider( $abprf_slider, $post_id, $image_ids );
					} else {
						$thumb_id = $image_ids[0];
						$thumb_id = $thumb_id ?: get_post_thumbnail_id( $post_id );
						ABPRF_Layout::bg_image( '', $thumb_id, ABPRF_BLANK_IMG_URL, 'abprf_slider' );
					}
				} else {
					$thumb_id = get_post_thumbnail_id( $post_id );
					ABPRF_Layout::bg_image( '', $thumb_id, ABPRF_BLANK_IMG_URL, 'abprf_slider' );
				}
			}

			public function slider_only( $abprf_infos, $class = '' ): void {
				$post_id        = array_key_exists( 'post_id', $abprf_infos ) ? $abprf_infos['post_id'] : 0;
				$display_slider = array_key_exists( 'display_slider', $abprf_infos ) ? $abprf_infos['display_slider'] : 'on';
				$abprf_slider   = ABPRF_Function::get_option( 'abprf_slider' );
				$image_ids      = array_unique( ABPRF_Function::get_post_info( $post_id, 'abprf_sliders', array() ) );
				if ( sizeof( $image_ids ) > 0 && $display_slider == 'on' ) {
					if ( sizeof( $image_ids ) > 1 ) { ?>
                        <div class="abprf_slider abprf_cover <?php echo esc_attr( $class ); ?>">
							<?php $this->slider_all_item( $abprf_slider, $image_ids ); ?>
                        </div>
					<?php } else {
						$thumb_id = $image_ids[0];
						$thumb_id = $thumb_id ?: get_post_thumbnail_id( $post_id );
						ABPRF_Layout::bg_image( '', $thumb_id, ABPRF_BLANK_IMG_URL );
					}
				} else {
					$thumb_id = get_post_thumbnail_id( $post_id );
					ABPRF_Layout::bg_image( '', $thumb_id, ABPRF_BLANK_IMG_URL );
				}
			}

			public function slider( $abprf_slider, $post_id, $image_ids ): void {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$showcase_position = isset( $abprf_slider['showcase_position'] ) && $abprf_slider['showcase_position'] ? $abprf_slider['showcase_position'] : 'right';
					$slider_style      = isset( $abprf_slider['slider_style'] ) && $abprf_slider['slider_style'] ? $abprf_slider['slider_style'] : 'style_1';
					$slider_indicator  = isset( $abprf_slider['indicator_visible'] ) && $abprf_slider['indicator_visible'] ? $abprf_slider['indicator_visible'] : 'on';
					$icon              = isset( $abprf_slider['indicator_type'] ) && $abprf_slider['indicator_type'] ? $abprf_slider['indicator_type'] : 'icon';
					$column_class      = $showcase_position == 'top' || $showcase_position == 'bottom' ? 'area_column' : '';
					?>
                    <div class="abprf_slider abprf_cover _fd_column">
                        <div class="_d_flex _w_full  <?php echo esc_attr( $column_class ); ?>">
							<?php
								if ( $showcase_position == 'top' || $showcase_position == 'left' ) {
									$this->slider_showcase( $abprf_slider, $image_ids );
								}
								$this->slider_all_item( $abprf_slider, $image_ids );
								if ( $showcase_position == 'bottom' || $showcase_position == 'right' ) {
									$this->slider_showcase( $abprf_slider, $image_ids );
								}
								if ( $slider_style == 'style_2' ) {
									?>
                                    <div class="abTopLeft">
                                        <button type="button" class="_btn_default_bg_white_color_default" data-target-popup="abprf_slider" data-slide-index="1">
											<?php echo esc_html__( 'View All', 'abprf-rental-forge' ) . ' ' . esc_html( sizeof( $image_ids ) ) . ' ' . esc_html__( 'Images', 'abprf-rental-forge' ); ?>
                                        </button>
                                    </div>
									<?php
								}
							?>
                        </div>
						<?php
							if ( $slider_indicator == 'on' && $icon == 'image' ) {
								$this->image_indicator( $image_ids );
							}
							$this->slider_popup( $abprf_slider, $post_id, $image_ids ); ?>
                    </div>
					<?php
				}
			}

			public function slider_all_item( $abprf_slider, $image_ids, $popup_slider_icon = '' ): void {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$icon = isset( $abprf_slider['indicator_type'] ) && $abprf_slider['indicator_type'] ? $abprf_slider['indicator_type'] : 'icon';
					?>
                    <div class="slider_item_area">
						<?php $count = 1;
							foreach ( $image_ids as $id ) {
								$image_url = ABPRF_Function::get_image_url( '', $id ); ?>
                                <div class="slider_item" data-slide-index="<?php echo esc_attr( $count ); ?>" <?php if ( $popup_slider_icon == 'on' ) { ?> data-target-popup="abprf_slider" <?php } ?> data-placeholder>
                                    <div data-bg-image="<?php echo esc_url( $image_url ); ?>"></div>
                                </div>
								<?php
								$count ++;
							}
							if ( ( $icon == 'icon' || $popup_slider_icon == 'on' ) && sizeof( $image_ids ) > 1 ) {
								$slider_indicator = isset( $abprf_slider['indicator_visible'] ) && $abprf_slider['indicator_visible'] ? $abprf_slider['indicator_visible'] : 'on';
								if ( $slider_indicator == 'on' || $popup_slider_icon == 'on' ) {
									?>
                                    <div class="icon_direction prev_item">
                                        <span class="fas fa-chevron-left"></span>
                                    </div>
                                    <div class="icon_direction next_item">
                                        <span class="fas fa-chevron-right"></span>
                                    </div>
									<?php
								}
							}
						?>
                    </div>
					<?php
				}
			}

			public function slider_showcase( $abprf_slider, $image_ids ): void {
				$showcase          = isset( $abprf_slider['showcase_visible'] ) && $abprf_slider['showcase_visible'] ? $abprf_slider['showcase_visible'] : 'on';
				$showcase_position = isset( $abprf_slider['showcase_position'] ) && $abprf_slider['showcase_position'] ? $abprf_slider['showcase_position'] : 'right';
				$slider_style      = isset( $abprf_slider['slider_style'] ) && $abprf_slider['slider_style'] ? $abprf_slider['slider_style'] : 'style_1';
				if ( $showcase == 'on' && is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
                    <div class="slider_img_list <?php echo esc_attr( $showcase_position . ' ' . $slider_style ); ?>">
						<?php
							if ( $slider_style == 'style_1' ) {
								$this->slider_showcase_style_1( $image_ids );
							} else {
								$this->slider_showcase_style_2( $image_ids );
							}
						?>
                    </div>
					<?php
				}
			}

			public function slider_showcase_style_1( $image_ids ): void {
				$count = 1;
				foreach ( $image_ids as $id ) {
					$image_url = ABPRF_Function::get_image_url( '', $id );
					if ( $count < 4 ) {
						?>
                        <div class="slider_img_list_item" data-slide-target="<?php echo esc_attr( $count ); ?>" data-placeholder>
                            <div data-bg-image="<?php echo esc_url( $image_url ); ?>"></div>
                        </div>
						<?php
					}
					if ( $count == 4 ) {
						?>
                        <div class="slider_img_list_item" data-target-popup="abprf_slider" data-placeholder>
                            <div data-bg-image="<?php echo esc_url( $image_url ); ?>"></div>
                            <div class="slider_more_item">
                                <span class="fas fa-plus"></span>
								<?php echo esc_html( sizeof( $image_ids ) - 4 ); ?>
                                <span class="far fa-image"></span>
                            </div>
                        </div>
						<?php
					}
					$count ++;
				}
			}

			public function slider_showcase_style_2( $image_ids ): void {
				$count = 1;
				foreach ( $image_ids as $id ) {
					$image_url = ABPRF_Function::get_image_url( '', $id );
					if ( $count > 1 && $count < 5 ) {
						?>
                        <div class="slider_img_list_item" data-target-popup="abprf_slider" data-slide-index="<?php echo esc_attr( $count ); ?>" data-placeholder>
                            <div data-bg-image="<?php echo esc_url( $image_url ); ?>"></div>
                        </div>
						<?php
					}
					$count ++;
				}
			}

			public function image_indicator( $image_ids ): void {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					?>
                    <div class="slide_direction">
						<?php
							$count = 1;
							foreach ( $image_ids as $id ) {
								$image_url = ABPRF_Function::get_image_url( '', $id, array( 150, 100 ) );
								?>
                                <div class="slider_direction_item" data-slide-target="<?php echo esc_attr( $count ); ?>">
                                    <div data-bg-image="<?php echo esc_url( $image_url ); ?>"></div>
                                </div>
								<?php
								$count ++;
							}
						?>
                    </div>
					<?php
				}
			}

			public function slider_popup( $abprf_slider, $post_id, $image_ids ): void {
				if ( is_array( $image_ids ) && sizeof( $image_ids ) > 0 ) {
					$active_popup         = isset( $abprf_slider['visible_popup'] ) && $abprf_slider['visible_popup'] ? $abprf_slider['visible_popup'] : 'on';
					$popup_icon_indicator = isset( $abprf_slider['popup_icon_indicator'] ) && $abprf_slider['popup_icon_indicator'] ? $abprf_slider['popup_icon_indicator'] : 'on';
					$indicator            = isset( $abprf_slider['popup_image_indicator'] ) && $abprf_slider['popup_image_indicator'] ? $abprf_slider['popup_image_indicator'] : 'on';
					if ( $active_popup == 'on' ) {
						?>
                        <div class="slider_popup" data-popup="abprf_slider">
                            <div class="abprf_slider">
                                <div class="popup_head">
                                    <h2 class="_abprf"><?php echo esc_html( get_the_title( $post_id ) ); ?></h2>
                                    <span class="popup_close _circle"><i class="fas fa-times"></i></span>
                                </div>
                                <div class="popup_body">
									<?php $this->slider_all_item( $abprf_slider, $image_ids, $popup_icon_indicator ); ?>
                                </div>
                                <div class="popup_foot">
									<?php if ( $indicator == 'on' ) {
										$this->image_indicator( $image_ids );
									} ?>
                                </div>
                            </div>
                        </div>
						<?php
					}
				}
			}

			//=============static array================//
			public static function week_day(): array {
				return [
					'monday' => __( 'Monday', 'abprf-rental-forge' ),
					'tuesday' => __( 'Tuesday', 'abprf-rental-forge' ),
					'wednesday' => __( 'Wednesday', 'abprf-rental-forge' ),
					'thursday' => __( 'Thursday', 'abprf-rental-forge' ),
					'friday' => __( 'Friday', 'abprf-rental-forge' ),
					'saturday' => __( 'Saturday', 'abprf-rental-forge' ),
					'sunday' => __( 'Sunday', 'abprf-rental-forge' ),
				];
			}

			public static function date_option_rules(): array {
				$rules = [
					'specific_of_date' => __( 'Specific Off Dates', 'abprf-rental-forge' ),
					'off_date_range' => __( 'Off Dates Range', 'abprf-rental-forge' ),
					'weekend' => __( 'Weekend', 'abprf-rental-forge' ),
					'special_on_dates' => __( 'Special On Dates', 'abprf-rental-forge' ),
					'day_wise_time' => __( 'Operation Time day Wise', 'abprf-rental-forge' ),
				];

				return apply_filters( 'abprf_filter_rent_rule', $rules );
			}

			public static function rent_rules(): array {
				$rules = [
					'hourly' => __( 'Hourly Rate', 'abprf-rental-forge' ),
					'daily' => __( 'Daily Rate', 'abprf-rental-forge' ),
					'multi_day' => __( 'Days & Hours Rate', 'abprf-rental-forge' ),
					'monthly' => __( 'Monthly Rate', 'abprf-rental-forge' ),
					'multi_month' => __( 'Months & Days Rate', 'abprf-rental-forge' )
				];

				return apply_filters( 'abprf_filter_rent_rule', $rules );
			}

			public static function array_date_format(): array {
				$current_date = current_time( 'Y-m-d' );

				return [
					'yy-mm-dd' => $current_date,
					'yy/mm/dd' => date_i18n( 'Y/m/d', strtotime( $current_date ) ),
					'yy-dd-mm' => date_i18n( 'Y-d-m', strtotime( $current_date ) ),
					'yy/dd/mm' => date_i18n( 'Y/d/m', strtotime( $current_date ) ),
					'dd-mm-yy' => date_i18n( 'd-m-Y', strtotime( $current_date ) ),
					'dd/mm/yy' => date_i18n( 'd/m/Y', strtotime( $current_date ) ),
					'mm-dd-yy' => date_i18n( 'm-d-Y', strtotime( $current_date ) ),
					'mm/dd/yy' => date_i18n( 'm/d/Y', strtotime( $current_date ) ),
					'd M , yy' => date_i18n( 'j M , Y', strtotime( $current_date ) ),
					'D d M , yy' => date_i18n( 'D j M , Y', strtotime( $current_date ) ),
					'M d , yy' => date_i18n( 'M  j, Y', strtotime( $current_date ) ),
					'D M d , yy' => date_i18n( 'D M  j, Y', strtotime( $current_date ) ),
				];
			}

			public static function array_info( $key ) {
				$current_date = current_time( 'Y-m-d H:i' );
				$des          = array(
					'sub_title' => __( 'Note: Add a Sub-title to enable the Post sub-tile. Leave this blank if you dont want to show any Sub-title information for this Post.', 'abprf-rental-forge' ),
					'rent_continue' => __( 'Note: This switch indicate property rent close/continue . You can  rent close/continue  by this switch. By default rent will be  continue', 'abprf-rental-forge' ),
					'post_sku' => __( 'Note: Here you can add an SKU for this post. You can also show or hide it on the frontend by turning the switch On or Off.', 'abprf-rental-forge' ),
					'abprf_template' => __( 'Note: Here You can change your details page template.', 'abprf-rental-forge' ),
					'display_category' => __( 'Note : This switch indicate Post/Property Category . You can also show or hide it on the frontend by turning the switch On or Off.', 'abprf-rental-forge' ),
					'display_location' => __( 'Note : This switch indicate Office/store Location. You can also show or hide it on the frontend by turning the switch On or Off.', 'abprf-rental-forge' ),
					'cat_name' => __( 'Note: Please enter a category name — the field cannot be empty. ', 'abprf-rental-forge' ),
					'cat_slug' => __( 'Note: Category slug is optional — leave it blank to auto-generate from the name. ', 'abprf-rental-forge' ),
					'cat_des' => __( 'Note: Category description is optional — you can add details to better explain this category. ', 'abprf-rental-forge' ),
					'loc_name' => __( 'Note: Please enter a Location name — the field cannot be empty. ', 'abprf-rental-forge' ),
					'loc_slug' => __( 'Note: Location slug is optional — leave it blank to auto-generate from the name. ', 'abprf-rental-forge' ),
					'loc_des' => __( 'Note: Location Address is optional — you can add details to better explain this Location Full  Address. ', 'abprf-rental-forge' ),
					'feature_name' => __( 'Note: Please enter a Feature Label — the field cannot be empty. ', 'abprf-rental-forge' ),
					'feature_slug' => __( 'Note: Feature slug is optional — leave it blank to auto-generate from the name. ', 'abprf-rental-forge' ),
					'feature_des' => __( 'Note: Please enter a Feature Value  — the field cannot be empty. ', 'abprf-rental-forge' ),
					//=============================//
					'date_format' => __( 'Note:  If you want to change the Date  Format, simply choose a different format. The default date is: ', 'abprf-rental-forge' ) . ' ' . date_i18n( 'D j M , Y', strtotime( $current_date ) ),
					'time_format' => __( 'Note : If you want to change the Time Format, simply choose a different format. The default Time Format is: ', 'abprf-rental-forge' ) . ' ' . date_i18n( get_option( 'time_format' ), strtotime( $current_date ) ),
					'sale_close_before' => __( 'Note: Enter the time in minutes to close  rent before current time. If not specified, it will default to 0 (e.g. 1 hour equals 60 minutes).', 'abprf-rental-forge' ),
					'sale_close_after' => __( 'Note: Enter the time in minutes to close  rent after current time. If not specified, it will default to 0 (e.g. 1 hour equals 60 minutes).', 'abprf-rental-forge' ),
					'advance_date_number' => __( 'Note: Kindly provide the number of days in advance for booking. By default, the advance booking period is set to 28 days.(optional) ', 'abprf-rental-forge' ),
					'active_global_dates' => __( 'Note: Keep this switch ON to apply the global date settings.Switch it OFF if you want to set special date rules for this property.Date configuration options will open when turned OFF. ', 'abprf-rental-forge' ),
					'date_type' => __( 'Note: Please Select your property operational date type. Default operational date will be Periodic', 'abprf-rental-forge' ),
					'specific_dates' => __( 'Note: Please add your property operational Specific Date lists and Operation time length(optional). If operation time empty that means it will be default operation time.', 'abprf-rental-forge' ),
					'operation_time' => __( 'Note: Please add your property rent  Operation time length(optional). If operation time empty that means it will be 24 hours(optional)', 'abprf-rental-forge' ),
					'periodic_start_date' => __( 'Note: Please add your property rent Launching Date otherwise it will be start today ', 'abprf-rental-forge' ),
					'periodic_end_date' => __( 'Note: Please add your property rent Terminate  Date otherwise it will be Continuously running periodically', 'abprf-rental-forge' ),
					'periodic_after' => __( 'Note: Please add your periodically after days. if  your property rent operation day everyday this will be one(1).(optional)', 'abprf-rental-forge' ),
					'date_rule' => __( 'Note: Enable this checkbox to configure special on/off date and time settings. This option is optional. If you set a date/time in the special “On” date, that date will remain active even if it falls within an “Off” date range or on weekends.', 'abprf-rental-forge' ),
					'special_on_dates' => __( 'Note: If you add any date and time in Special On Dates, it will always remain active—even if that date falls within an off date range or on weekends.', 'abprf-rental-forge' ),
					'weekend' => __( 'Note: Please select your weekend.Default all days open(optional)', 'abprf-rental-forge' ),
					'day_wise_time' => __( 'Note: Day-wise operation time will apply only if the date does not fall within any Special On Date range. If the time field is left empty in Special On Dates, then the day-wise operation time will be applied for that date.', 'abprf-rental-forge' ),
					'specific_off_dates' => __( 'Note: please add your specific Operation off dates.(optional)', 'abprf-rental-forge' ),
					'off_date_range' => __( 'Note: If you have off days between two dates which can add here.(optional)', 'abprf-rental-forge' ),
					'abprf_dates' => __( 'Note: Set a global date configuration for your property rentals that can be reused across all posts, with options to import and customize anytime.', 'abprf-rental-forge' ),
					//=============================//
					'post_id' => __( 'Note: You must select the category under which this property belongs here. Selecting a category is required — the data will not be saved if no category is selected.', 'abprf-rental-forge' ),
					'name' => __( 'Note: You must enter the property name in the field above. This field is required — the data will not be saved if the property name is not provided.', 'abprf-rental-forge' ),
					'icon' => __( 'Note: Here You can set an image, icon, or emoji for each property directly', 'abprf-rental-forge' ),
					'qty_reserve_min_max' => __( 'Note: Set the total stock quantity available for rent. This field is required to save the property. You can also set reserve, minimum, and maximum quantity limits for customer bookings. Reserve quantity keeps specific items unavailable, minimum quantity defaults to 1, and maximum quantity will follow the available stock if left empty.', 'abprf-rental-forge' ),
					'hourly_min_max' => __( 'Note: Enter the hourly rental rate to enable hourly booking for this property. You can also set minimum and maximum rental hours for customers. The default minimum is 1 hour, while the maximum will follow available time slots if left empty. These limits apply when the rent rule is set to Hourly or Multi-Date Hourly.', 'abprf-rental-forge' ),
					'daily_min_max' => __( 'Note: Enter the daily rental rate to enable daily booking for this property. This rate applies to Daily, Multi-Day, and Multi-Month rent rules. You can also set minimum and maximum rental days for customers. The minimum defaults to 1 day if left empty, while the maximum depends on available booking dates. If no daily rate is provided, daily rental will remain disabled.', 'abprf-rental-forge' ),
					'monthly_min_max' => __( 'Note: Enter the monthly rental rate to enable monthly booking for this property. This rate will apply only for the Monthly rent rule. You can also set minimum and maximum rental months. The default minimum is 1 month, while the maximum depends on available booking months.', 'abprf-rental-forge' ),
					'active_deposit' => __( 'Note: If you enable this switch, the rental deposit feature will be activated. Turn it on if you want to collect a deposit amount for renting the property.', 'abprf-rental-forge' ),
					'deposit_type' => __( 'Note: There are three(3) types of deposit options: Fixed Amount (a set deposit regardless of quantity), Percentage of Total Price (calculated based on the total rental cost), and Fixed Amount per Quantity (applied for each item rented).', 'abprf-rental-forge' ),
					'deposit_value' => __( 'Note: The deposit value depends on the selected deposit type. The entered amount will be applied based on the chosen deposit option.', 'abprf-rental-forge' ),
					'brand' => __( 'Note: Add a brand name to enable the property sub-tile. Leave this blank if you dont want to show any brand information for this item.', 'abprf-rental-forge' ),
					'description' => __( 'Note: Add short description about this property. Leave this blank if you dont want to show any property description for this item.', 'abprf-rental-forge' ),
					'price_rule' => __( 'Note: At least one option must be selected — otherwise the data will not be saved. The price will be calculated based on the time selected by the client.', 'abprf-rental-forge' ),
					'property_feature' => __( 'Note: If you want to add feature for this property, you can add Here. These feature will be show with this properties . You may leave this section empty if you do not want to show frontend. ', 'abprf-rental-forge' ),
					'abprf_sliders' => __( 'Note: If you want to add an image gallery for this property, you can upload images below. These images will be merged with all properties under the same category. You may leave this section empty if you do not want to add images. ', 'abprf-rental-forge' ),
					'time_slot_length' => __( 'Note: You can define the time slot interval for frontend time selection here. This controls how frequently time options will appear for users. By default, it is set to 60 minutes, meaning time slots will be available in 1-hour intervals.', 'abprf-rental-forge' ),
					'day_time_start_end' => __( 'Note: You can define the start and end time of a rental day here. By default, a rental day runs from 10:00 AM to 10:00 AM the next day. The first time applies to the start of the first day, and the second time applies to the end of the following day. The total duration between these times must not exceed 24 hours.', 'abprf-rental-forge' ),
					'hour_threshold' => __( 'Note: You can define how many hours will be counted as one full day here. By default, it is set to 24 hours. Adjust this value to control when a booking duration should be considered as a full day.', 'abprf-rental-forge' ),
					'cut_off_date' => __( 'Note: You can set the cutoff date for allowing bookings in the next month. By default, users can make bookings for the current month up to the 10th. After this date, next month’s rental slots will become available for booking.', 'abprf-rental-forge' ),
					'day_threshold' => __( 'Note: You can define how many days will be counted as one full month here. By default, it is set to 30 days. Adjust this value to control when a booking duration should be considered as a full month.', 'abprf-rental-forge' ),
					'rent_rule' => __( 'Note: The items displayed are filtered by your selected Rent Time Rules. Properties not matching these rules are still available via the main Property List.', 'abprf-rental-forge' ),
					//=============================//
					'_tax_class' => __( 'Note: If you want to add any new tax class , Please go to WooCommerce ->configuration->Tax Area', 'abprf-rental-forge' ),
					'enable_tax_msg' => __( 'Note: Your Woo-commerce Tax setting already disable. If you want to enable tax please enable woo-commerce tax.', 'abprf-rental-forge' ),
					//=============================//
					'display_additional_services' => __( 'Note: If you want sale/rent additional product/equipment with regular property then active this button and add additional service. Additional item not depends on  operation time.', 'abprf-rental-forge' ),
					'additional_services' => __( 'Note: Add extra services for products/equipment with your property—import or set per Post (also usable globally); stock applies per Post, empty quantity = unlimited, empty max qty = no limit, empty/Zero price = free.', 'abprf-rental-forge' ),
					'active_global_additional' => __( 'Note: Keep this switch ON to apply the global additional settings.Switch it OFF if you want to set special additional rules for this property.additional configuration options will open when turned OFF. ', 'abprf-rental-forge' ),
					//=============================//
					'client_form_option' => __( 'Use comma( , ) to separate option.', 'abprf-rental-forge' ),
					'display_client_form' => __( 'Note: If you want to get Client information then active this button and add form/import global form or use global form as a client form', 'abprf-rental-forge' ),
					'active_global_form' => __( 'Note: Keep this switch ON to apply the global Client Form settings.Switch it OFF if you want to set special  Client Form rules for this property. Client Form configuration options will open when turned OFF. ', 'abprf-rental-forge' ),
					'global_client_forms' => __( 'Note: This is a flexibility global form system. Once you design the structure here, it serves as a global form. You can effortlessly import this form into any property or use this setting at any property,', 'abprf-rental-forge' ),
					//=============================//
					'abprf_tc' => __( 'You can set all rental-related Term & Condition here and use them globally across all properties. You can also import these Term & Condition into any individual property and customize them as needed.', 'abprf-rental-forge' ),
					'tc_item' => __( 'Use the editor to customize and design your Terms & Conditions as you prefer. The content and formatting you create here will be displayed the same way on the frontend.', 'abprf-rental-forge' ),
					'display_tc' => __( 'Use this switch to control whether the Term & Condition is displayed on the frontend. Turn the switch ON to show the Term & Condition, and OFF to hide it. By default, this option is set to ON.', 'abprf-rental-forge' ),
					'active_global_tc' => __( 'Enable this switch to apply the global Term & Condition to this post. If you want to add custom Term & Condition specifically for this post, turn the switch OFF and add your custom Term & Condition below.You can also use the Import button to bring in global Term & Condition, which you can then edit or delete based on your needs.', 'abprf-rental-forge' ),
					//=============================//
					'abprf_faqs' => __( 'You can set all rental-related FAQs here and use them globally across all properties. You can also import these FAQs into any individual property and customize them as needed.', 'abprf-rental-forge' ),
					'faq_item' => __( 'Both the Title and Description fields are required. If either field is left empty, this FAQ item will not be displayed on the frontend.', 'abprf-rental-forge' ),
					'display_faq' => __( 'Use this switch to control whether the FAQ is displayed on the frontend. Turn the switch ON to show the FAQ, and OFF to hide it. By default, this option is set to ON.', 'abprf-rental-forge' ),
					'active_global_faq' => __( 'Enable this switch to apply the global FAQ to this post. If you want to add custom FAQs specifically for this post, turn the switch OFF and add your custom FAQs below.You can also use the Import button to bring in global FAQs, which you can then edit or delete based on your needs.', 'abprf-rental-forge' ),
					//=============================//
					'search_get_wrong_data_info' => __( 'Somethings went Wrong ! Please Try again', 'abprf-rental-forge' ),
					'sale_close_msg' => __( 'This Property rent close shortly. please try another Property.', 'abprf-rental-forge' ),
					'not_found' => __( 'No Post Found !', 'abprf-rental-forge' ),
					'not_post_found' => __( 'No Post Found !', 'abprf-rental-forge' ),
					'not_property_found' => __( 'No Property Found !', 'abprf-rental-forge' ),
					'no_category' => __( 'No Category Found ! Please add Category to use Category feature', 'abprf-rental-forge' ),
					'no_brand' => __( 'No Brand Found ! Please add Brand to use Brand feature', 'abprf-rental-forge' ),
					'no_location' => __( 'No Location Found ! Please add Location to use Location feature', 'abprf-rental-forge' ),
					'no_feature' => __( 'No Feature Found ! Please add Feature to use Feature', 'abprf-rental-forge' ),
					'property_not_available' => __( 'The property is not available for the selected date and time. Please choose a different schedule.', 'abprf-rental-forge' ),
					//=============================//
					//=============================//
					'abptm_pickup' => __( 'Here you can set traveller Pickup Point . If you want visible Pickup point select option for traveller , please switch on. default pickup point off', 'abprf-rental-forge' ),
					'required_pickup' => __( 'Here you can set traveller Pickup Point mandatory or not . If you want mandatory Pickup point select option for traveller , please switch on. default mandatory pickup point off', 'abprf-rental-forge' ),
					'abptm_drop' => __( 'Here you can set traveller Drop-off Point . If you want visible Drop-off point select option for traveller , please switch on. default Drop-off point off', 'abprf-rental-forge' ),
					'required_drop' => __( 'Here you can set traveller Drop-off Point mandatory or not . If you want mandatory Drop-off point select option for traveller , please switch on. default mandatory Drop-off point off', 'abprf-rental-forge' ),
					//=============================//
					'sign_up_msg' => __( 'Please Login your account to Download/View ticket !', 'abprf-rental-forge' ),
					'no_permit_msg' => __( 'You are not permitted to Download/View this ticket !', 'abprf-rental-forge' ),
					'wrong_msg_id' => __( 'We see, this id are not valid !', 'abprf-rental-forge' ),
					'no_property_found' => __( 'Property not found or  rent close shortly', 'abprf-rental-forge' ),
					'no_order_found' => __( 'Sorry ! We can not find any Order in your criteria.', 'abprf-rental-forge' ),
					//''          => __( '', 'abprf-rental-forge' ),
				);
				$des          = apply_filters( 'abprf_info_array_filter', $des );

				return $des[ $key ];
			}

			public static function static_form( $key = '' ): array {
				$form['pass_name']    = [ 'type' => 'text', 'required' => 'on', 'label' => __( 'First Name', 'abprf-rental-forge' ) ];
				$form['pass_name_2']  = [ 'type' => 'text', 'required' => 'on', 'label' => __( 'Last Name', 'abprf-rental-forge' ) ];
				$form['pass_email']   = [ 'type' => 'email', 'required' => 'on', 'label' => __( 'E-Mail', 'abprf-rental-forge' ) ];
				$form['pass_phone']   = [ 'type' => 'text', 'required' => 'on', 'label' => __( 'Phone', 'abprf-rental-forge' ) ];
				$form['pass_gender']  = [ 'type' => 'select', 'required' => 'off', 'label' => __( 'Gender', 'abprf-rental-forge' ), 'option' => 'male,female' ];
				$form['pass_date']    = [ 'type' => 'date', 'required' => 'off', 'label' => __( 'Date of Birth', 'abprf-rental-forge' ) ];
				$form['pass_address'] = [ 'type' => 'textarea', 'required' => 'off', 'label' => __( 'Address', 'abprf-rental-forge' ) ];

				return $key && array_key_exists( $key, $form ) ? $form[ $key ] : $form;
			}

			public static function static_additional(): array {
				return [
					'additional_service_1' => [ 'icon' => 'fas fa-helmet-un', 'name' => 'Helmet', 'qty' => 50, 'max_qty' => 1, 'price' => 0, 'returnable' => 'yes', 'description' => '1x Safety Helmet per order. Keep your head protected at no extra cost. Your safety is our priority!', ],
					'additional_service_2' => [ 'icon' => 'fas fa-suitcase', 'name' => 'Storage', 'qty' => 30, 'max_qty' => 3, 'price' => 2.99, 'returnable' => 'no', 'description' => 'Optional baggage support is available as a paid service to help carry your essentials with ease.', ],
					'additional_service_3' => [ 'icon' => 'fas fa-user-tie', 'name' => 'Tie', 'qty' => 100, 'price' => 1.00, 'returnable' => 'no', 'description' => 'Multiple color available', ],
					'additional_service_4' => [ 'icon' => 'fas fa-shoe-prints', 'name' => 'Shoes', 'qty' => 100, 'price' => 1.00, 'returnable' => 'yes', 'description' => 'Multiple Size available', ]
				];
			}

			//=============================//
			public static function rent_start_date( $all_dates = [], $date = '' ): void {
				if ( is_array( $all_dates ) && sizeof( $all_dates ) > 0 ) {
					$date_format = ABPRF_Function::date_picker_format();
					$now         = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
					$date        = $date ?: current( $all_dates );
					if ( sizeof( $all_dates ) > 10 ) {
						$hidden_date  = $date ? gmdate( 'Y-m-d', strtotime( $date ) ) : '';
						$visible_date = $date ? date_i18n( $date_format, strtotime( $date ) ) : '';
						?>
                        <label>
                            <span><i class="fas fa-calendar-check _mar_r_xxs"></i><?php esc_html_e( 'Pickup Date', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></span>
                            <input type="hidden" name="rent_start_date" value="<?php echo esc_attr( $hidden_date ); ?>" required/>
                            <input id="start_date" type="text" value="<?php echo esc_attr( $visible_date ); ?>" class="_form_control" placeholder="<?php echo esc_attr( $now ); ?>" data-alert="<?php esc_attr_e( 'Please Select Start Date', 'abprf-rental-forge' ); ?>" readonly required/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abprf-rental-forge' ); ?>"></span>
                        </label>
						<?php
						do_action( 'abprf_load_date_picker', '#start_date', $all_dates );
					}
				}
			}

			public static function title( $post_id ): void {
				$post_sku = ABPRF_Function::get_post_info( $post_id, 'post_sku' );
				echo esc_html( get_the_title( $post_id ) ); ?>
                <p class="_abprf">
					<?php if ( ! empty( $post_sku ) ) { ?>
                        <small class=" _abprf_color_gray"><?php echo esc_html__( 'Post SKU : ', 'abprf-rental-forge' ) . esc_html( $post_sku ); ?></small>
					<?php } ?>
                </p>
				<?php
			}

			public static function property_condition( $rent_rule, $min_hour, $max_hour = '' ) {
				$condition = '';
				if ( $rent_rule == 'hourly' ) {
					if ( $min_hour == $max_hour ) {
						$condition .= sprintf(
						/* translators: %s = minimum number of hours */
							_n( 'Rental is available for %s hour Only', 'Rental is available for  %s hours Only', $min_hour, 'abprf-rental-forge' ), $min_hour );
					} else {
						$condition .= '📉 ';
						$condition .= sprintf(
						/* translators: %s = minimum number of hours */
							_n( 'Min. %s hour', 'Min. %s hours', $min_hour, 'abprf-rental-forge' ), $min_hour );
						if ( ! empty( $max_hour ) ) {
							$condition .= '  📈  ';
							$condition .= sprintf(
							/* translators: %s = maximum number of hours */
								_n( 'Max. %s hour', 'Max. %s hours', $max_hour, 'abprf-rental-forge' ), $max_hour );
						}
					}
				}

				return $condition;
			}

			public static function create_client_form( $form, $name ): void {
				$type             = array_key_exists( 'type', $form ) ? $form['type'] : '';
				$required         = array_key_exists( 'required', $form ) && $form['required'] == 'on' ? 'required' : '';
				$label            = array_key_exists( 'label', $form ) ? $form['label'] : '';
				$d_value          = array_key_exists( 'd_value', $form ) ? $form['d_value'] : '';
				$validation_class = '';
				if ( $type == 'text' || $type == 'number' || $type == 'email' ) {
					$validation_class = $type == 'text' ? 'validation_name' : $validation_class;
					$validation_class = $type == 'number' ? 'validation_number' : $validation_class;
					?>
                    <label class="_input_item">
						<?php ABPRF_Layout::input_title( $label, $required ); ?>
                        <input type="<?php echo esc_attr( $type ); ?>" name="<?php echo esc_attr( $name ); ?>" value="<?php echo esc_attr( $d_value ); ?>" class="_form_control <?php echo esc_attr( $validation_class ); ?>" placeholder="<?php echo esc_attr( $label ); ?>" title="<?php echo esc_attr( $label ); ?>" <?php echo esc_attr( $required ); ?> />
                    </label>
					<?php
				}
				if ( $type == 'date' ) {
					ABPRF_Layout::input_date( $name, $d_value, $label, $required );
				}
				if ( $type == 'textarea' ) {
					ABPRF_Layout::textarea( $name, $d_value, $label, $required );
				}
				if ( $type == 'select' ) {
					$options = array_key_exists( 'option', $form ) ? $form['option'] : '';
					$options = $options ? explode( ',', $options ) : '';
					ABPRF_Layout::select( $name, $d_value, $label, $required, $options );
				}
				if ( $type == 'checkbox' ) {
					$options = array_key_exists( 'option', $form ) ? $form['option'] : '';
					$options = $options ? explode( ',', $options ) : '';
					ABPRF_Layout::checkbox( $name, $d_value, $label, $required, $options );
				}
				if ( $type == 'radio' ) {
					$options = array_key_exists( 'option', $form ) ? $form['option'] : '';
					$options = $options ? explode( ',', $options ) : '';
					ABPRF_Layout::radio( $name, $d_value, $label, $required, $options );
				}
			}

			//=============================//
			public static function filter_post_list( $abprf_info = [], $post_id = 0 ): void {
				$label        = isset( $abprf_info['label'] ) && $abprf_info['label'] ? $abprf_info['label'] : __( 'RentalForge', 'abprf-rental-forge' );
				$all_post_ids = isset( $abprf_info['post_ids'] ) && $abprf_info['post_ids'] ? $abprf_info['post_ids'] : ABPRF_Query::get_post_id();
				$value        = $post_id > 0 ? $post_id : '';
				$brand_icon   = isset( $abprf_info['brand_icon'] ) && $abprf_info['brand_icon'] ? $abprf_info['brand_icon'] : 'fas fa-hammer';
				// echo '<pre>';print_r($configuration);echo '</pre>';
				?>
                <div class="_input_item abp_dropdown">
                    <label>
                        <span><?php ABPRF_Layout::image_icon( $brand_icon, '_mar_r_xs' ); ?><?php echo esc_html( $label ); ?></span>
                        <input type="hidden" name="post_id" value="<?php echo esc_attr( $value ); ?>"/>
                        <input type="text" class="_form_control_w_full" name="" placeholder="<?php echo esc_attr( $label ); ?>" value="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"/>
                    </label>
					<?php if ( sizeof( $all_post_ids ) > 0 ) { ?>
                        <div class="dropdown_list">
                            <ul class="_abprf ">
								<?php foreach ( $all_post_ids as $all_post_id ) {
									$sku      = ABPRF_Function::get_post_info( $all_post_id, 'post_sku' );
									$category = ABPRF_Function::get_post_info( $all_post_id, 'category' );
									$category = ! empty( $category ) ? get_term( $category )->name : '';
									$title    = get_the_title( $all_post_id );
									?>
                                    <li data-value="<?php echo esc_attr( $all_post_id ); ?>" data-text="<?php echo esc_attr( $title ); ?>">
										<?php ABPRF_Layout::image_icon( $brand_icon, '_mar_r_xs' ); ?>
                                        <span class="_fs_label"><?php echo esc_html( $title ); ?></span>
										<?php if ( ! empty( $category ) ) { ?>
                                            <sub class="_abprf_color_gray"> - <?php echo esc_html( $category ); ?></sub>
										<?php } ?>
										<?php if ( ! empty( $sku ) ) { ?>
                                            <sub class="_abprf_color_info"> - <?php echo esc_html( $sku ); ?></sub>
										<?php } ?>
                                    </li>
								<?php } ?>
                            </ul>
                        </div>
					<?php } ?>
                </div>
				<?php
			}

			public static function filter_booking_date(): void {
				$date_format = ABPRF_Function::date_picker_format();
				$now         = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
				?>
                <div class="_input_item">
                    <label class="_fd_column">
                        <span>📅 <?php esc_html_e( 'Booking Date', 'abprf-rental-forge' ) ?></span>
                        <input type="hidden" name="start_time" value=""/>
                        <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abprf-rental-forge' ); ?>"></span>
                    </label>
                </div>
				<?php
			}

			public static function filter_order_date(): void {
				$date_format = ABPRF_Function::date_picker_format();
				$now         = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
				?>
                <div class="_input_item">
                    <label class="_fd_column">
                        <span>🗓️ <?php esc_html_e( 'Order Date', 'abprf-rental-forge' ) ?></span>
                        <input type="hidden" name="order_date" value=""/>
                        <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                        <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abprf-rental-forge' ); ?>"></span>
                    </label>
                </div>
				<?php
			}

			public static function filter_booking_date_between(): void {
				$date_format = ABPRF_Function::date_picker_format();
				$now         = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
				?>
                <div class="_g_input_input_item_fd_column">
                    <label><span>⏰ <?php esc_html_e( 'Booking Date Between', 'abprf-rental-forge' ); ?></span></label>
                    <div class="_f_equal">
                        <label>
                            <input type="hidden" name="booking_time_from" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abprf-rental-forge' ); ?>"></span>
                        </label>
                        <label>
                            <input type="hidden" name="booking_time_to" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abprf-rental-forge' ); ?>"></span>
                        </label>
                    </div>
                </div>
				<?php
			}

			public static function filter_order_date_between(): void {
				$date_format = ABPRF_Function::date_picker_format();
				$now         = date_i18n( $date_format, strtotime( current_time( 'Y-m-d' ) ) );
				?>
                <div class="_g_input_input_item_fd_column" data-collapse="#view_more_filter_option">
                    <label><span>⏰ <?php esc_html_e( 'Order Date Between', 'abprf-rental-forge' ); ?></span></label>
                    <div class="_f_equal">
                        <label>
                            <input type="hidden" name="order_date_from" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abprf-rental-forge' ); ?>"></span>
                        </label>
                        <label>
                            <input type="hidden" name="order_date_to" value=""/>
                            <input type="text" value="" class="_form_control abp_datepicker" placeholder="<?php echo esc_attr( $now ); ?>" readonly/>
                            <span class="fas fa-times date_close_icon" title="<?php esc_attr_e( 'Clear Date', 'abprf-rental-forge' ); ?>"></span>
                        </label>
                    </div>
                </div>
				<?php
			}

			public static function filter_user_id(): void {
				$all_users = get_users( array(
					'fields' => array( 'ID', 'display_name' ),
				) );
				?>
                <div class="_input_item abp_dropdown ">
                    <label class="_fd_column">
                        <span>👨‍💼  <?php esc_html_e( 'User Name', 'abprf-rental-forge' ); ?></span>
                        <input type="hidden" name="user_id" value=""/>
                        <input type="text" class="_form_control_w_full" placeholder="<?php esc_attr_e( 'User Name', 'abprf-rental-forge' ); ?>" value=""/>
                    </label>
					<?php if ( ! empty( $all_users ) ) { ?>
                        <div class="dropdown_list">
                            <ul class="_abprf ">
								<?php foreach ( $all_users as $user ) { ?>
                                    <li data-value="<?php echo esc_attr( $user->ID ); ?>" data-text="<?php echo esc_attr( $user->display_name ); ?>">
                                        <span class="_fs_label"><?php echo esc_html( $user->display_name ); ?></span>
                                    </li>
								<?php } ?>
                            </ul>
                        </div>
					<?php } ?>
                </div>
				<?php
			}

			public static function filter_order_id(): void {
				?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label class="_fd_column">
                        <span>📦 <?php esc_html_e( 'Order ID', 'abprf-rental-forge' ); ?></span>
                        <input type="number" class="_form_control_w_full validation_number" name="order_id" placeholder="<?php esc_attr_e( 'Order ID', 'abprf-rental-forge' ); ?>" value=""/>
                    </label>
                </div>
				<?php
			}

			public static function filter_bill_name(): void {
				?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label class="_fd_column">
                        <span>👤 <?php esc_html_e( 'Billing Name', 'abprf-rental-forge' ); ?></span>
                        <input type="text" class="_form_control_w_full " name="billing_name" placeholder="<?php esc_attr_e( 'Billing Name', 'abprf-rental-forge' ); ?>" value=""/>
                    </label>
                </div>
				<?php
			}

			public static function filter_bill_email(): void {
				?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label class="_fd_column">
                        <span>✉️ <?php esc_html_e( 'Billing Email', 'abprf-rental-forge' ); ?></span>
                        <input type="email" class="_form_control_w_full " name="billing_email" placeholder="<?php esc_attr_e( 'Billing Email', 'abprf-rental-forge' ); ?>" value=""/>
                    </label>
                </div>
				<?php
			}

			public static function filter_bill_phone(): void {
				?>
                <div class="_input_item " data-collapse="#view_more_filter_option">
                    <label class="_fd_column">
                        <span>☎️ <?php esc_html_e( 'Billing phone', 'abprf-rental-forge' ); ?></span>
                        <input type="text" class="_form_control_w_full " name="billing_phone" placeholder="<?php esc_attr_e( 'Billing phone', 'abprf-rental-forge' ); ?>" value=""/>
                    </label>
                </div>
				<?php
			}

			//=============================//
			public static function transport_list( $form_data ): void {
				$_post_id = array_key_exists( '_post_id', $form_data ) ? $form_data['_post_id'] : 0;
				$post_id  = array_key_exists( 'post_id', $form_data ) ? $form_data['post_id'] : 0;
				$_bp      = array_key_exists( '_bp', $form_data ) ? $form_data['_bp'] : '';
				$_dp      = array_key_exists( '_dp', $form_data ) ? $form_data['_dp'] : '';
				$_j_date  = array_key_exists( '_j_date', $form_data ) ? $form_data['_j_date'] : '';
				$_r_date  = array_key_exists( '_r_date', $form_data ) ? $form_data['_r_date'] : '';
				if ( $_bp && $_dp && ( $_j_date || $_r_date ) ) {
					if ( $_post_id > 0 || $post_id > 0 ) {
						if ( $_post_id > 0 ) {
							$form_data['bp']     = $_bp;
							$form_data['dp']     = $_dp;
							$form_data['j_date'] = $_j_date;
						}
						$form_data['post_id'] = max( $_post_id, $post_id );
						do_action( 'abprf_registration', $form_data );
					} else {
						$form_data['bp']     = $_bp;
						$form_data['dp']     = $_dp;
						$form_data['j_date'] = $_j_date;
						self::transport_search( $form_data );
						if ( $_r_date ) {
							$form_data['bp']     = $_dp;
							$form_data['dp']     = $_bp;
							$form_data['j_date'] = $_r_date;
							?>
                            <div class="abptm_return_trip_area _mar_t_40">
                                <div class="_divider"></div>
                                <h3 class="_abprf_color_navy_blue_text_center"><span class="fas fa-hand-point-down _mar_r_xs"></span><?php esc_html_e( 'Return Trips', 'abprf-rental-forge' ); ?></h3>
                                <div class="_divider"></div>
								<?php self::transport_search( $form_data ); ?>
                            </div>
						<?php }
					}
				}
			}

			public static function transport_search( $form_data ): void {
				$bp              = array_key_exists( 'bp', $form_data ) ? $form_data['bp'] : '';
				$dp              = array_key_exists( 'dp', $form_data ) ? $form_data['dp'] : '';
				$j_date          = array_key_exists( 'j_date', $form_data ) ? $form_data['j_date'] : '';
				$transport_items = ABPRF_Function::get_transport_list_details( $bp, $dp, $j_date );
				if ( sizeof( $transport_items ) > 0 ) {
					foreach ( $transport_items as $transport_item ) {
						do_action( 'abprf_search_list', $form_data, $transport_item );
					}
				} else {
					ABPRF_Layout::layout_warning_info( 'no_property_found' );
				}
			}
		}
		new ABPRF_Layout();
	}