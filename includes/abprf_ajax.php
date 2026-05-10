<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPRF_Ajax' ) ) {
		class ABPRF_Ajax {
			public function __construct() {
				add_action( 'wp_ajax_abprf_load_property', [ $this, 'load_property' ] );
				add_action( 'wp_ajax_nopriv_abprf_load_property', [ $this, 'load_property' ] );
				//=============================//
				add_action( 'wp_ajax_abprf_book_continue', [ $this, 'abprf_book_continue' ] );
				add_action( 'wp_ajax_nopriv_abprf_book_continue', [ $this, 'abprf_book_continue' ] );
			}

			public function load_property() {
				if ( check_ajax_referer( 'abprf_ajax_nonce', 'nonce' ) ) {
					$abprf_infos     = [];
					$post_id         = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
					$rent_rule       = isset( $_POST['rent_rule'] ) ? sanitize_text_field( wp_unslash( $_POST['rent_rule'] ) ) : 'hourly';
					$rent_start_date = isset( $_POST['rent_start_date'] ) ? sanitize_text_field( wp_unslash( $_POST['rent_start_date'] ) ) : '';
					//$all_dates=ABPRF_Function::get_post_dates($post_id);
					$start = $end = '';
					$dif   = 0;
					if ( ! empty( $post_id ) ) {
						$abprf_infos['post_id'] = $post_id;
					}
					if ( ! empty( $rent_rule ) ) {
						$abprf_infos['rent_rule'] = $rent_rule;
					}
					if ( $rent_rule == 'hourly' && ! empty( $rent_start_date ) ) {
						$start_time                           = isset( $_POST['start_time'] ) ? sanitize_text_field( wp_unslash( $_POST['start_time'] ) ) : '';
						$end_time                             = isset( $_POST['end_time'] ) ? sanitize_text_field( wp_unslash( $_POST['end_time'] ) ) : '';
						$start                                = $rent_start_date . ' ' . $start_time;
						$end                                  = $rent_start_date . ' ' . $end_time;
						$differ                               = ABPRF_Function::get_date_time_difference( $start, $end );
						$hour_dif                             = array_key_exists( 'hour', $differ ) ? $differ['hour'] : 0;
						$min_dif                              = array_key_exists( 'min', $differ ) ? $differ['min'] : 0;
						$dif                                  = $min_dif > 0 ? $hour_dif + 1 : $hour_dif;
						$abprf_infos['date_info']['dif']      = $dif;
						$abprf_infos['date_info']['dif_text'] = array_key_exists( 'text', $differ ) ? $differ['text'] : '';
					}
					$abprf_infos['date_info']['start_time'] = $start;
					$abprf_infos['date_info']['end_time']   = $end;
					$date_infos                             = [];
					$properties                             = ABPRF_Query::get_property( [ 'post_id' => $post_id, 'rent_continue' => 'on', 'rent_rule' => $rent_rule, 'status' => 'publish' ] );
					ob_start();
					if ( ! empty( $post_id ) && $post_id > 0 ) {
						$date_infos = json_decode( get_transient( 'abprf_date_infos_' . $post_id ), true );
                        if(empty($date_infos)){
	                        $all_date_time_info = ABPRF_Function::get_all_date_time_info( $rent_rule, $post_id );
	                        $date_infos       = is_array( $all_date_time_info ) && array_key_exists( 'php_info', $all_date_time_info ) ? $all_date_time_info['php_info'] : [];
	                        set_transient( 'abprf_date_infos_' . $post_id, json_encode( $date_infos ), HOUR_IN_SECONDS );
                        }
					}
					//echo '<pre>';print_r( $date_infos);					echo '</pre>';
					$exit_property = 0;
					if ( ! empty( $properties ) && is_array( $properties ) && sizeof( $properties ) > 0 && is_array( $date_infos ) && sizeof( $date_infos ) > 0 && $dif > 0 ) {
						?>
                        <div class="property_item_area">
                        <input type="hidden" name="start_time" value="<?php echo esc_attr( $start ); ?> "/>
                        <input type="hidden" name="end_time" value="<?php echo esc_attr( $end ); ?> "/>
                        <input type="hidden" name="post_id" value="<?php echo esc_attr( $post_id ); ?> "/>
                        <input type="hidden" name="rent_rule" value="<?php echo esc_attr( $rent_rule ); ?>"/>
						<?php
						foreach ( $properties as $property ) {
							$post_id_property = array_key_exists( 'post_id', $property ) ? $property['post_id'] : '';
							$post_data        = array_key_exists( $post_id_property, $date_infos ) ? $date_infos[ $post_id_property ] : $date_infos['global'];
							$date_list        = is_array( $post_data ) && array_key_exists( 'date', $post_data ) ? $post_data['date'] : '';
							$date_list        = explode( ',', $date_list );
							if ( in_array( $rent_start_date, $date_list ) ) {
								if ( $rent_rule == 'hourly' && isset( $start_time ) && isset( $end_time ) ) {
									$time_list = is_array( $post_data ) && array_key_exists( 'time', $post_data ) ? $post_data['time'] : [];
									if ( is_array( $time_list ) && sizeof( $time_list ) > 0 ) {
										$day_name = strtolower( gmdate( 'l', strtotime( $rent_start_date ) ) );
										if ( array_key_exists( $rent_start_date, $time_list ) ) {
											$time_slots = $time_list[ $rent_start_date ];
										} elseif ( array_key_exists( $day_name, $time_list ) ) {
											$time_slots = $time_list[ $day_name ];
										} else {
											$time_slots = array_key_exists( 'slot', $time_list ) ? $time_list['slot'] : '';
										}
										if ( ! empty( $time_slots ) && ABPRF_Function::check_time_slot_exit( $time_slots, $start_time . '-' . $end_time ) ) {
											do_action( 'abprf_property_item', $abprf_infos, $property );
											$exit_property ++;
										}
									}
								}
							}
						}
						?></div><?php
						if ( ! empty( $post_id ) && $post_id > 0 ) {
							?>
                            <div class="property_others">
								<?php
									do_action( 'abprf_additional', $post_id, $abprf_infos );
									do_action( 'abprf_client_form', $post_id, $abprf_infos );
									do_action( 'abprf_total_price', $abprf_infos );
								?>
                            </div>
							<?php
						}
					}
					if ( $exit_property == 0 ) {
						ABPRF_Layout::layout_warning_info( 'no_property_found' );
					}
					$property_info = ob_get_clean();
					ob_start();
					do_action( 'abprf_rental_duration', $abprf_infos['date_info'] );
					$date_details = ob_get_clean();
					wp_send_json_success( [ 'property_info' => $property_info, 'date_details' => $date_details ] );
				}
				wp_die();
			}

			//=============================//
			public function abprf_book_continue() {
				if ( isset( $_POST['nonce'] ) && wp_verify_nonce( sanitize_text_field( wp_unslash( $_POST['nonce'] ) ), 'abprf_ajax_nonce' ) ) {
					global $woocommerce;
					if ( isset( $_POST['form_data'] ) && is_array( $_POST['form_data'] ) ) {
						// phpcs:ignore WordPress.Security.NonceVerification.Missing, WordPress.Security.ValidatedSanitizedInput.InputNotSanitized
						$raw_data  = wp_unslash( $_POST['form_data'] );
						$form_data = array();
						foreach ( $raw_data as $field ) {
							if ( ! isset( $field['name'], $field['value'] ) ) {
								continue;
							}
							$name               = sanitize_key( $field['name'] );
							$value              = sanitize_text_field( $field['value'] );
							$form_data[ $name ] = $value;
						}
						foreach ( $form_data as $key => $value ) {
							$_POST[ $key ] = $value;
						}
						$_POST['form_data'] = '';
						$link_id            = isset( $_POST['wc_link_id'] ) ? sanitize_text_field( wp_unslash( $_POST['wc_link_id'] ) ) : '';
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
						$product_id = apply_filters( 'woocommerce_add_to_cart_product_id', $link_id );
						$quantity   = 1;
						// phpcs:ignore WordPress.NamingConventions.PrefixAllGlobals.NonPrefixedHooknameFound
						$passed_validation = apply_filters( 'woocommerce_add_to_cart_validation', true, $product_id, $quantity );
						$product_status    = get_post_status( $product_id );
						if ( $passed_validation && $woocommerce->cart->add_to_cart( $product_id ) && 'publish' === $product_status ) {
							$checkout_system = ABPRF_Function::get_options( 'abprf_configuration', 'checkout_system', 'default' );
							if ( $checkout_system == 'checkout' ) {
								printf( '%s', esc_url( wc_get_checkout_url() ) );
							} elseif ( $checkout_system == 'cart' ) {
								printf( '%s', esc_url( wc_get_cart_url() ) );
							}
						}
					}
				}
				wp_die();
			}
		}
		new ABPRF_Ajax();
	}