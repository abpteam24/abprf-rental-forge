<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPRF_Properties' ) ) {
		class ABPRF_Properties {
			public function __construct() {
				add_action( 'abprf_load_properties', array( $this, 'load_properties' ) );
				add_action( 'abprf_post_content', [ $this, 'tab_content' ] );
				add_action( 'wp_ajax_abprf_save_property', array( $this, 'save_property' ) );
				add_action( 'wp_ajax_abprf_property_add_edit', array( $this, 'property_add_edit' ) );
				add_action( 'wp_ajax_abprf_reload_property_list', array( $this, 'reload_property_list' ) );
				add_action( 'wp_ajax_abprf_property_delete', array( $this, 'property_delete' ) );
			}

			public function load_properties( $abprf_info ): void {
				//$total_property = isset( $abprf_info['total_property'] ) && $abprf_info['total_property'] ? $abprf_info['total_property'] : 0;
				$post_ids               = isset( $abprf_info['post_ids'] ) && $abprf_info['post_ids'] ? $abprf_info['post_ids'] : [];
				$filter_args['post_id'] = 'all';
				?>
                <div class="abprf_properties">
                    <div class="_fj_between_f_wrap">
                        <h4 class="_abprf_color_theme"><span class="_mar_r_xxs">🏠</span> <?php esc_html_e( 'Properties', 'abprf-rental-forge' ); ?></h4>
                        <div class="abp_dropdown _max_400">
                            <label class="_abprf_all_center">
                                <input type="hidden" name="select_property_hidden" value=""/>
                                <input type="text" class="_form_control_text_center validation_name" name="select_property" placeholder="<?php esc_attr_e( 'Search  Post', 'abprf-rental-forge' ); ?>" value=""/>
                            </label>
                            <div class="dropdown_list">
                                <ul class="_abprf">
                                    <li data-value="all" data-text="<?php esc_attr_e( 'All Post', 'abprf-rental-forge' ); ?>"><?php esc_html_e( 'All Post', 'abprf-rental-forge' ); ?></li>
                                    <li data-value="on" data-text="<?php esc_attr_e( 'Rent Active', 'abprf-rental-forge' ); ?>"><?php esc_html_e( 'Rent Active', 'abprf-rental-forge' ); ?></li>
                                    <li data-value="off" data-text="<?php esc_attr_e( 'Rent De-active', 'abprf-rental-forge' ); ?>"><?php esc_html_e( 'Rent De-active', 'abprf-rental-forge' ); ?></li>
									<?php if ( ! empty( $post_ids ) && is_array( $post_ids ) && sizeof( $post_ids ) > 0 ) { ?>
										<?php foreach ( $post_ids as $post_id ) { ?>
                                            <li data-value="<?php echo esc_attr( $post_id ); ?>" data-text="<?php echo esc_attr( get_the_title( $post_id ) ); ?>"><?php echo esc_html( get_the_title( $post_id ) ); ?></li>
										<?php } ?>
									<?php } ?>
                                </ul>
                            </div>
                        </div>
                        <button type="button" class="_btn_default" data-property_id="" data-target-popup="#abprf_property_popup"><span class="_mar_r_xs">➕</span><?php esc_html_e( 'Add New Property', 'abprf-rental-forge' ); ?></button>
                    </div>
                    <div class="_divider_xs"></div>
                </div>
                <div class="_section_xs properties_list">
					<?php $this->properties_table( $filter_args ); ?>
                </div>
				<?php
			}

			public function tab_content( $abprf_infos ): void {
				$copy_post_id                = array_key_exists( 'copy_post_id', $abprf_infos ) ? $abprf_infos['copy_post_id'] : '';
				$rent_rule                  = array_key_exists( 'rent_rule', $abprf_infos ) ? $abprf_infos['rent_rule'] : 'hourly';
				$day_time_start              = array_key_exists( 'day_time_start', $abprf_infos ) ? $abprf_infos['day_time_start'] : '';
				$day_time_end                = array_key_exists( 'day_time_end', $abprf_infos ) ? $abprf_infos['day_time_end'] : '';
				$hour_threshold              = array_key_exists( 'hour_threshold', $abprf_infos ) ? $abprf_infos['hour_threshold'] : 24;
				$cut_off_date                = array_key_exists( 'cut_off_date', $abprf_infos ) ? $abprf_infos['cut_off_date'] : 1;
				$day_threshold               = array_key_exists( 'day_threshold', $abprf_infos ) ? $abprf_infos['day_threshold'] : 30;
				$filter_args['copy_post_id'] = $copy_post_id;
				$filter_args['post_id']      = array_key_exists( 'post_id', $abprf_infos ) ? $abprf_infos['post_id'] : '';
				$rent_rules                  = ABPRF_Layout::rent_rules();
				?>
                <div class="tab_item abprf_equipment_price" data-tabs="#abprf_equipment_price">
                    <h4 class="_abprf_color_theme"><span class="_mar_r_xxs">🏠</span> <?php esc_html_e( 'Properties and Price Configuration', 'abprf-rental-forge' ); ?></h4>
                    <div class="_divider_xs"></div>
                    <div class="_setting_item">
                        <div class="custom_radio _fj_between">
                            <h5 class="_abprf_color_theme"><?php esc_html_e( 'Rent Date & Time Rule', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></h5>
                            <input type="hidden" class="_form_control" name="rent_rule" value="<?php echo esc_attr( $rent_rule ); ?>"/>
                            <div class="_f_wrap">
								<?php foreach ( $rent_rules as $key => $rule ) { ?>
                                    <div class="radio_item">
                                        <button type="button" class="_btn_white_xs <?php echo esc_attr( $rent_rule == $key ? 'rf_active' : '' ); ?>" data-close-target="#<?php echo esc_attr( $key ); ?>" data-radio="<?php echo esc_attr( $key ); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                            <i class="_abprf_fs_h4"><span data-icon class="_mar_r_xs <?php echo esc_attr( $rent_rule == $key ? 'far fa-check-circle' : 'far fa-circle' ); ?>"></span></i><span class="_text_left_fs_label"><?php echo esc_html( $rule ); ?></span>
                                        </button>
                                    </div>
								<?php } ?>
                            </div>
                        </div>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'rent_rule' ); ?>
                    </div>
                    <div class="<?php echo esc_attr( $rent_rule == 'daily' ? 'rf_active' : '' ); ?>" data-close="#daily">
                        <div class="group_setting">
                            <div class="_setting_item">
                                <div class="_f_wrap_fj_between_fa_center">
                                    <span class="_mar_r_xs_fs_label"><?php esc_html_e( 'Day time Start-End', 'abprf-rental-forge' ); ?></span>
                                    <div class="_group_content">
										<?php ABPRF_Layout::input_time( 'day_time_start', $day_time_start );
											ABPRF_Layout::input_time( 'day_time_end', $day_time_end ); ?>
                                    </div>
                                </div>
                                <div class="_divider_xs"></div>
								<?php ABPRF_Layout::info_text( 'day_time_start_end' ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="<?php echo esc_attr( $rent_rule == 'multi_day' ? 'rf_active' : '' ); ?>" data-close="#multi_day">
                        <div class="group_setting">
                            <div class="_setting_item">
                                <label class="_f_equal_f_wrap">
                                    <span class="_mar_r_xs"><?php esc_html_e( 'Hour Threshold', 'abprf-rental-forge' ); ?></span>
                                    <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="hour_threshold" placeholder="Ex:30" value="<?php echo esc_attr( $hour_threshold ); ?>"/>
                                </label>
                                <div class="_divider_xs"></div>
								<?php ABPRF_Layout::info_text( 'hour_threshold' ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="<?php echo esc_attr( $rent_rule == 'monthly' ? 'rf_active' : '' ); ?>" data-close="#monthly">
                        <div class="group_setting">
                            <div class="_setting_item">
                                <label class="_f_equal_f_wrap">
                                    <span class="_mar_r_xs"><?php esc_html_e( 'Month Cut-Off Date', 'abprf-rental-forge' ); ?></span>
                                    <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="cut_off_date" placeholder="Ex:10" value="<?php echo esc_attr( $cut_off_date ); ?>"/>
                                </label>
                                <div class="_divider_xs"></div>
								<?php ABPRF_Layout::info_text( 'cut_off_date' ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="<?php echo esc_attr( $rent_rule == 'multi_month' ? 'rf_active' : '' ); ?>" data-close="#multi_month">
                        <div class="group_setting">
                            <div class="_setting_item">
                                <label class="_f_equal_f_wrap">
                                    <span class="_mar_r_xs"><?php esc_html_e( 'Day Threshold', 'abprf-rental-forge' ); ?></span>
                                    <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="day_threshold" placeholder="Ex:10" value="<?php echo esc_attr( $day_threshold ); ?>"/>
                                </label>
                                <div class="_divider_xs"></div>
								<?php ABPRF_Layout::info_text( 'day_threshold' ); ?>
                            </div>
                        </div>
                    </div>
                    <div class="properties_list">
						<?php $this->properties_table( $filter_args ); ?>
                    </div>
					<?php if ( empty( $copy_post_id ) ) { ?>
                        <div class="_divider_xs"></div>
                        <button type="button" class="_btn_default" data-property_id="" data-target-popup="#abprf_property_popup"><span class="_mar_r_xs">➕</span><?php esc_html_e( 'Add New Property', 'abprf-rental-forge' ); ?></button>
					<?php } ?>
                </div>
				<?php
			}

			public function save_property() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$post_id     = isset( $_POST['post_id'] ) ? sanitize_text_field( wp_unslash( $_POST['post_id'] ) ) : '';
					$property_id = isset( $_POST['property_id'] ) ? sanitize_text_field( wp_unslash( $_POST['property_id'] ) ) : '';
					$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
					$qty         = isset( $_POST['qty'] ) ? sanitize_text_field( wp_unslash( $_POST['qty'] ) ) : '';
					$rent_rule  = isset( $_POST['rent_rule'] ) ? sanitize_text_field( wp_unslash( $_POST['rent_rule'] ) ) : '';
					//echo '<pre>'; print_r($_POST); echo '</pre>';die();
					if ( $post_id && $name && $qty > 0 && $rent_rule ) {
						$rent_continue       = isset( $_POST['rent_continue'] ) ? sanitize_text_field( wp_unslash( $_POST['rent_continue'] ) ) : 'on';
						$qty_info['qty']     = intval( $qty );
						$qty_info['reserve'] = intval( isset( $_POST['qty_reserve'] ) ? sanitize_text_field( wp_unslash( $_POST['qty_reserve'] ) ) : 0 );
						$qty_info['min']     = isset( $_POST['qty_min'] ) ? sanitize_text_field( wp_unslash( $_POST['qty_min'] ) ) : '';
						$qty_info['max']     = isset( $_POST['qty_max'] ) ? sanitize_text_field( wp_unslash( $_POST['qty_max'] ) ) : '';


							$price_info[ $rent_rule ]['price'] = isset( $_POST[ 'price_' . $rent_rule ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'price_' . $rent_rule ] ) ) : '';
							$price_info[ $rent_rule ]['min']   = isset( $_POST[ 'min_' . $rent_rule ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'min_' . $rent_rule ] ) ) : '';
							$price_info[ $rent_rule ]['max']   = isset( $_POST[ 'max_' . $rent_rule ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'max_' . $rent_rule ] ) ) : '';
							if ( $rent_rule == 'multi_day' ) {
								$price_info[ $rent_rule ]['price_hour'] = isset( $_POST[ 'price_' . $rent_rule . '_hour' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'price_' . $rent_rule . '_hour' ] ) ) : '';
							}
							if ( $rent_rule == 'multi_month' ) {
								$price_info[ $rent_rule ]['price_day'] = isset( $_POST[ 'price_' . $rent_rule . '_day' ] ) ? sanitize_text_field( wp_unslash( $_POST[ 'price_' . $rent_rule . '_day' ] ) ) : '';
							}

						$price_info['deposit']['type']  = isset( $_POST['deposit_type'] ) ? sanitize_text_field( wp_unslash( $_POST['deposit_type'] ) ) : '';
						$price_info['deposit']['value'] = isset( $_POST['deposit_value'] ) ? sanitize_text_field( wp_unslash( $_POST['deposit_value'] ) ) : '';
						$others                         = [];
						$features                       = [];
						$feature_names                  = isset( $_POST['feature_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['feature_name'] ) ) : [];
						$feature_values                 = isset( $_POST['feature_value'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['feature_value'] ) ) : [];
						$feature_icon                   = isset( $_POST['feature_icon'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['feature_icon'] ) ) : [];
						if ( sizeof( $feature_names ) > 0 && sizeof( $feature_values ) > 0 ) {
							foreach ( $feature_names as $key => $feature_name ) {
								if ( $feature_name && $feature_values[ $key ] ) {
									$features[ $key ]['label'] = $feature_name;
									$features[ $key ]['value'] = $feature_values[ $key ];
									$features[ $key ]['icon']  = $feature_icon[ $key ];
								}
							}
						}
						$category = ABPRF_Function::get_post_info( $post_id, 'category' );
						$location = ABPRF_Function::get_post_info( $post_id, 'location' );
						$data     = [
							'post_id' => intval( $post_id ),
							'rent_continue' => $rent_continue,
							'name' => sanitize_text_field( $name ),
							'icon' => isset( $_POST['icon'] ) ? sanitize_text_field( wp_unslash( $_POST['icon'] ) ) : '',
							'qty_info' => json_encode( $qty_info ),
							'brand' => isset( $_POST['brand'] ) ? sanitize_text_field( wp_unslash( $_POST['brand'] ) ) : '',
							'category' => $category,
							'location' => $location,
							'description' => isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '',
							'rent_rule' => sanitize_text_field( $rent_rule ),
							'price_info' => json_encode( $price_info ),
							'features' => json_encode( $features ),
							'gallery' => isset( $_POST['abprf_sliders'] ) ? sanitize_text_field( wp_unslash( $_POST['abprf_sliders'] ) ) : '',
							'status' => get_post_status( $post_id ),
							'others' => json_encode( $others ),
							'updated_at' => current_time( 'Y-m-d H:i' )
						];
						global $wpdb;
						$table_name = $wpdb->prefix . 'abprf_property';
						if ( $property_id ) {
							$where = [ 'id' => $property_id ];
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->update( $table_name, $data, $where, [ '%s', '%s', '%s' ], [ '%d' ] );
							wp_send_json_success( esc_html__( 'Property Updated Successfully ! ', 'abprf-rental-forge' ) );
						} else {
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->insert( $table_name, $data );
							wp_send_json_success( esc_html__( 'Property Saved Successfully ! ', 'abprf-rental-forge' ) );
						}
					} else {
						wp_send_json_success( esc_html__( 'Property not Saved !', 'abprf-rental-forge' ) );
					}
				} else {
					wp_send_json_success( esc_html__( 'Property not Saved ! Authentication Error', 'abprf-rental-forge' ) );
				}
				wp_die();
			}

			public function property_add_edit() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$property_id   = isset( $_POST['property_id'] ) ? sanitize_text_field( wp_unslash( $_POST['property_id'] ) ) : '';
					$property_copy = isset( $_POST['property_copy'] ) ? sanitize_text_field( wp_unslash( $_POST['property_copy'] ) ) : 0;
					$this->add_property( $property_id, $property_copy );
				}
				wp_die();
			}

			public function reload_property_list() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$filter_args            = isset( $_POST['filter_args'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['filter_args'] ) ) : [];
					$post_id                = array_key_exists( 'post_id', $filter_args ) && $filter_args['post_id'] != '' ? $filter_args['post_id'] : 'all';
					$filter_args['post_id'] = $post_id;
					$this->properties_table( $filter_args );
				}
				wp_die();
			}

			public function property_delete() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$property_id = isset( $_POST['property_id'] ) ? sanitize_text_field( wp_unslash( $_POST['property_id'] ) ) : '';
					if ( ! empty( $property_id ) && $property_id > 0 ) {
						$properties = ABPRF_Query::get_property( [ 'property_id' => $property_id ] );
						if ( ! empty( $properties ) && sizeof( $properties ) > 0 ) {
							global $wpdb;
							$table_name = $wpdb->prefix . 'abprf_property';
							// phpcs:ignore WordPress.DB.DirectDatabaseQuery.DirectQuery, WordPress.DB.DirectDatabaseQuery.NoCaching
							$wpdb->delete( $table_name, array( 'id' => $property_id ), array( '%d' ) );
						}
					}
					ob_start();
					$filter_args            = isset( $_POST['filter_args'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['filter_args'] ) ) : [];
					$post_id                = array_key_exists( 'post_id', $filter_args ) && $filter_args['post_id'] != '' ? $filter_args['post_id'] : 'all';
					$filter_args['post_id'] = $post_id;
					$this->properties_table( $filter_args );
					$html = ob_get_clean();
					wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Deleted Successfully............. ! ', 'abprf-rental-forge' ) ] );
				} else {
					wp_send_json_success( [ 'html' => esc_html__( 'Something Error Occur !', 'abprf-rental-forge' ), 'msg' => esc_html__( 'Something Error Occur !', 'abprf-rental-forge' ) ] );
				}
				wp_die();
			}

			public function properties_table( $filter_args ): void {
				$total_property = ABPRF_Query::get_property( $filter_args, true );
				// echo '<pre>';print_r($filter_args);echo '</pre>';
				$page_number           = array_key_exists( 'page_number', $filter_args ) && is_numeric( $filter_args['page_number'] ) ? (int) $filter_args['page_number'] : 1;
				$limit                 = array_key_exists( 'page_item', $filter_args ) && is_numeric( $filter_args['page_item'] ) ? (int) $filter_args['page_item'] : ABPRF_Function::get_option( 'abprf_per_page_item', 20 );
				$count                 = ( $page_number - 1 ) * $limit + 1;
				$filter_args['limit']  = $limit;
				$filter_args['offset'] = $count - 1;
				$properties            = ABPRF_Query::get_property( $filter_args );
				if ( ! empty( $properties ) && is_array( $properties ) && sizeof( $properties ) > 0 ) {
					$filter_post_id = array_key_exists( 'post_id', $filter_args ) ? $filter_args['post_id'] : '';
					$copy_post_id   = array_key_exists( 'copy_post_id', $filter_args ) ? $filter_args['copy_post_id'] : '';
					$rent_rules     = ABPRF_Layout::rent_rules();
					?>
                    <table class="_abprf">
                        <thead>
                        <tr>
                            <th class="_w_50"><?php esc_html_e( 'SI', 'abprf-rental-forge' ); ?></th>
                            <th><?php esc_html_e( 'Image/icon', 'abprf-rental-forge' ); ?></th>
                            <th><?php esc_html_e( 'Property', 'abprf-rental-forge' ); ?></th>
							<?php if ( ( empty( $filter_post_id ) || is_string( $filter_post_id ) ) && empty( $copy_post_id ) ) { ?>
                                <th><?php esc_html_e( 'Post', 'abprf-rental-forge' ); ?></th>
							<?php } ?>
                            <th><?php esc_html_e( 'Shortcode', 'abprf-rental-forge' ); ?></th>
							<?php foreach ( $rent_rules as $rule ) { ?>
                                <th><?php echo esc_html( $rule ); ?></th>
							<?php } ?>
                            <th><?php esc_html_e( 'Stock', 'abprf-rental-forge' ); ?></th>
                            <th><?php esc_html_e( 'Actions', 'abprf-rental-forge' ); ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $properties as $property ) {
							$icon               = array_key_exists( 'icon', $property ) ? $property['icon'] : '';
							$name               = array_key_exists( 'name', $property ) ? $property['name'] : '';
							$property_id        = array_key_exists( 'id', $property ) ? $property['id'] : '';
							$post_id            = array_key_exists( 'post_id', $property ) ? $property['post_id'] : '';
							$status             = array_key_exists( 'status', $property ) ? $property['status'] : '';
							$rent_continue      = array_key_exists( 'rent_continue', $property ) ? $property['rent_continue'] : '';
							$qty_info           = array_key_exists( 'qty_info', $property ) ? $property['qty_info'] : '';
							$qty_info           = ! empty( $qty_info ) ? json_decode( $qty_info, true ) : [];
							$qty                = array_key_exists( 'qty', $qty_info ) ? $qty_info['qty'] : '';
							$post_rent_continue = ABPRF_Function::get_post_info( $post_id, 'rent_continue', 'on' );
							$post_status        = get_post_status( $post_id );
							$rent_rule         = array_key_exists( 'rent_rule', $property ) ? $property['rent_rule'] : '';
							$price_info         = array_key_exists( 'price_info', $property ) ? $property['price_info'] : '';
							$price_info         = ! empty( $price_info ) ? json_decode( $price_info, true ) : [];
							?>
                            <tr class="delete_area">
                                <th><?php echo esc_html( $count ); ?>.</th>
                                <th class="_fs_h2"><?php ABPRF_Layout::image_icon( $icon ); ?></th>
                                <td>
                                    <div class="_fd_column">
                                        <h5 class="_abprf_color_theme"><?php echo esc_html( $name ); ?></h5>
                                        <div class="_d_flex">
											<?php if ( ! empty( $copy_post_id ) ) { ?>
                                                <input type="hidden" name="copy_property_id[]" value="<?php echo esc_attr( $property_id ); ?>"/>
											<?php } else { ?>
                                                <span class="_mar_r_xxs publish"><?php echo esc_html( __( 'Property Id : ', 'abprf-rental-forge' ) . ' ' . $property_id ); ?></span>
											<?php } ?>
                                            <span class="_mar_r_xxs <?php echo esc_attr( $rent_continue == 'on' ? 'publish' : 'trash' ); ?>"><?php echo esc_html( $rent_continue == 'on' ? __( 'Rent On', 'abprf-rental-forge' ) : __( 'Rent Off', 'abprf-rental-forge' ) ); ?></span>
                                            <span class="_mar_r_xxs <?php echo esc_attr( $status ); ?>"><?php echo esc_html( $status ); ?></span>
                                        </div>
                                    </div>
                                </td>
								<?php if ( ( empty( $filter_post_id ) || is_string( $filter_post_id ) ) && empty( $copy_post_id ) ) { ?>
                                    <td>
                                        <a href="<?php echo esc_url( get_edit_post_link( $post_id ) ); ?>" class="_abprf_fs_h5 _color_theme"><?php echo esc_html( get_the_title( $post_id ) ); ?></a>
                                        <div class="_d_flex">
                                            <span class="_mar_r_xxs publish"><?php echo esc_html( __( 'Post Id : ', 'abprf-rental-forge' ) . ' ' . $post_id ); ?></span>
                                            <span class="_mar_r_xxs <?php echo esc_attr( $post_rent_continue == 'on' ? 'publish' : 'trash' ); ?>"><?php echo esc_html( $post_rent_continue == 'on' ? __( 'Rent On', 'abprf-rental-forge' ) : __( 'Rent Off', 'abprf-rental-forge' ) ); ?></span>
                                            <span class="_mar_r_xxs <?php echo esc_attr( $post_status ); ?>"><?php echo esc_html( $post_status ); ?></span>
                                        </div>
                                    </td>
								<?php } ?>
                                <th><code> [abprf-property id="<?php echo esc_attr( $property_id ); ?>"]</code></th>
								<?php foreach ( $rent_rules as $key => $rule ) { ?>
                                    <th><?php
											$prices = array_key_exists( $key, $price_info ) ? $price_info[ $key ] : [];
											$price  = is_array( $prices ) && array_key_exists( 'price', $prices ) ? $prices['price'] : '';
											if ( $price ) {
												echo wp_kses_post( wc_price( $price ) );
											} else {
												echo esc_html( '❌' );
											}
										?></th>
								<?php } ?>
                                <th><?php echo esc_html( $qty ); ?></th>
                                <th>
									<?php if ( empty( $copy_post_id ) ) { ?>
                                        <div class="_f_wrap">
                                            <button type="button" class="_btn_light_yellow_mar_r_xxs" data-property_id="<?php echo esc_attr( $property_id ); ?>" data-target-popup="#abprf_property_popup" title="<?php echo esc_html__( 'Edit : ', 'abprf-rental-forge' ) . ' ' . esc_html( $name ); ?>">✍️</button>
                                            <button type="button" class="_btn_light_navy_blue _mar_r_xxs property_copy" data-property_id="<?php echo esc_attr( $property_id ); ?>" data-target-popup="#abprf_property_popup" title="<?php echo esc_html__( 'Copy/Clone : ', 'abprf-rental-forge' ) . ' ' . esc_html( $name ); ?>">🔁</button>
                                            <button type="button" class="_btn_light_danger_xxs abprf_property_delete" data-property_id="<?php echo esc_attr( $property_id ); ?>" title="<?php echo esc_html__( 'Trash : ', 'abprf-rental-forge' ) . ' ' . esc_html( $name ); ?>">❌</button>
                                        </div>
									<?php } else { ?>
										<?php ABPRF_Layout::button_delete(); ?>
									<?php } ?>
                                </th>
                            </tr>
							<?php
							$count ++;
						} ?>
                        </tbody>
                    </table>
					<?php
					do_action( 'abprf_pagination', [ 'page_item' => $limit, 'page_number' => $page_number, 'total' => $total_property, 'style' => 'ajax' ] );
				} else {
					ABPRF_Layout::layout_warning_info( 'not_property_found' );
				}
				//echo '<pre>';				print_r( $properties );				echo '</pre>';
			}

			public function add_property( $property_id = '', $property_copy = 0 ): void {
				$cpt      = ABPRF_Function::get_cpt();
				$post_ids = ABPRF_Query::get_post_id( [ 'status' => [ 'publish', 'draft', 'private', 'trash' ] ] );
				if ( ! empty( $post_ids ) && sizeof( $post_ids ) > 0 ) {
					$save_text = __( 'Save Property Configuration', 'abprf-rental-forge' );
					$property  = [];
					if ( $property_id ) {
						$properties = ABPRF_Query::get_property( [ 'property_id' => $property_id ] );
						if ( ! empty( $properties ) && is_array( $properties ) && sizeof( $properties ) > 0 ) {
							$property  = current( $properties );
							$save_text = __( 'Update Property Configuration', 'abprf-rental-forge' );
						}
						if ( $property_copy > 0 ) {
							$property_id = '';
							$save_text   = __( 'Copy Property Configuration', 'abprf-rental-forge' );
						}
					}
					?>
                    <div class="data_property">
                        <input type="hidden" name="property_id" value="<?php echo esc_attr( $property_id ); ?>">
                        <h5 class="_abprf_color_theme"><?php esc_html_e( 'Property General Configuration', 'abprf-rental-forge' ); ?></h5>
                        <div class="_divider_xs"></div>
						<?php
							$this->post_rent_continue( $property, $post_ids );
							$this->name_icon_brand_dec( $property );
							$this->property_price_qty( $property );
							$this->deposit( $property );
							$this->features( $property );
							$this->gallery( $property );
						?>
                        <div class="_divider_xs"></div>
                        <button type="button" class="_btn_theme save_property"><span class="_mar_r_xxs">💾</span><?php echo esc_html( $save_text ); ?></button>
                    </div>
					<?php
				} else {
					ABPRF_Layout::layout_warning_info( 'not_post_found' );
					?>
                    <div class="_divider_xs"></div>
                    <a class="_btn_theme" href="<?php echo esc_url( admin_url( 'post-new.php?post_type=' . $cpt ) ); ?>"><span class="_mar_r_xs">➕</span><?php esc_html_e( 'Add New Post', 'abprf-rental-forge' ); ?></a>
					<?php
				}
			}

			public function post_rent_continue( $property = [], $post_ids = [] ): void {
				$current_post_id = array_key_exists( 'post_id', $property ) ? $property['post_id'] : '';
				$rent_continue   = array_key_exists( 'rent_continue', $property ) ? $property['rent_continue'] : 'on';
				?>
                <div class="group_setting">
                    <div class="_setting_item">
                        <label class="_f_equal_f_wrap">
                            <span class="_mar_r_xs"><?php esc_html_e( 'Select Post', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></span>
                            <select class="_form_control " name="post_id" required>
                                <option disabled selected><?php esc_html_e( 'Please Select', 'abprf-rental-forge' ); ?></option>
								<?php foreach ( $post_ids as $post_id ) { ?>
                                    <option value="<?php echo esc_attr( $post_id ); ?>" <?php echo esc_attr( $post_id == $current_post_id ? 'selected' : '' ); ?>><?php echo esc_html( get_the_title( $post_id ) ); ?></option>
								<?php } ?>
                            </select>
                        </label>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'post_id' ); ?>
                    </div>
                    <div class="_setting_item">
                        <div class="_fa_center">
							<?php ABPRF_Layout::switch_checkbox( 'rent_continue', $rent_continue ); ?>
                            <span class="_fs_label_mar_lr_xs"><?php esc_html_e( 'Rent continue?', 'abprf-rental-forge' ); ?></span>
                        </div>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'rent_continue' ); ?>
                    </div>
                </div>
				<?php
			}

			public function name_icon_brand_dec( $property = [] ) {
				$icon_image  = array_key_exists( 'icon', $property ) ? $property['icon'] : '';
				$name        = array_key_exists( 'name', $property ) ? $property['name'] : '';
				$brand       = array_key_exists( 'brand', $property ) ? $property['brand'] : '';
				$description = array_key_exists( 'description', $property ) ? $property['description'] : '';
				?>
                <div class=" _ov_auto_setting_item">
                    <table class="_abprf_fixed">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Icon/Image', 'abprf-rental-forge' ); ?></th>
                            <th colspan="2"><?php esc_html_e( 'Property Name', 'abprf-rental-forge' ); ?></th>
                            <th colspan="2"><?php esc_html_e( 'Property Brand', 'abprf-rental-forge' ); ?></th>
                            <th colspan="2"><?php esc_html_e( 'Short Description', 'abprf-rental-forge' ); ?></th>
                        </tr>
                        </thead>
                        <tbody class="_bg_white">
                        <tr>
                            <th>
                                <div class="_fj_center"><?php do_action( 'abprf_add_image_icon', 'icon', $icon_image ); ?></div>
                                <div class="_divider_xxs"></div>
								<?php ABPRF_Layout::info_text( 'icon' ); ?>
                            </th>
                            <th colspan="2">
                                <label>
                                    <input type="text" class="_form_control_w_full validation_name" name="name" placeholder="<?php esc_attr_e( 'EX: Bike', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $name ); ?>" required/>
                                </label>
                                <div class="_divider_xxs"></div>
								<?php ABPRF_Layout::info_text( 'name' ); ?>
                            </th>
                            <th colspan="2">
                                <label>
                                    <input type="text" class="_form_control_w_full validation_name" name="brand" placeholder="<?php esc_attr_e( 'EX: Yamaha R15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $brand ); ?>"/>
                                </label>
                                <div class="_divider_xxs"></div>
								<?php ABPRF_Layout::info_text( 'brand' ); ?>
                            </th>
                            <th colspan="2">
                                <label>
                                    <textarea class="_form_control_w_full" name="description" placeholder="<?php esc_attr_e( 'EX: Description', 'abprf-rental-forge' ); ?>"><?php echo esc_html( $description ); ?></textarea>
                                </label>
                                <div class="_divider_xxs"></div>
								<?php ABPRF_Layout::info_text( 'description' ); ?>
                            </th>
                        </tr>
                        </tbody>
                    </table>
                </div>
				<?php
			}

			public function property_price_qty( $property = [] ): void {
				$qty_info    = array_key_exists( 'qty_info', $property ) ? $property['qty_info'] : '';
				$qty_info    = ! empty( $qty_info ) ? json_decode( $qty_info, true ) : [];
				$qty         = array_key_exists( 'qty', $qty_info ) ? $qty_info['qty'] : '';
				$qty_reserve = array_key_exists( 'reserve', $qty_info ) ? $qty_info['reserve'] : '';
				$qty_min     = array_key_exists( 'min', $qty_info ) ? $qty_info['min'] : '';
				$qty_max     = array_key_exists( 'max', $qty_info ) ? $qty_info['max'] : '';
				/**************************/
				$rent_rule  = array_key_exists( 'rent_rule', $property ) ? $property['rent_rule'] : 'multi_day';
				$price_info = array_key_exists( 'price_info', $property ) ? $property['price_info'] : '';
				$price_info = ! empty( $price_info ) ? json_decode( $price_info, true ) : [];
				/**************************/
				$hourly_info  = array_key_exists( 'hourly', $price_info ) ? $price_info['hourly'] : [];
				$price_hourly = is_array( $hourly_info ) && array_key_exists( 'price', $hourly_info ) ? $hourly_info['price'] : '';
				$min_hourly   = is_array( $hourly_info ) && array_key_exists( 'min', $hourly_info ) ? $hourly_info['min'] : '';
				$max_hourly   = is_array( $hourly_info ) && array_key_exists( 'max', $hourly_info ) ? $hourly_info['max'] : '';
				/**************************/
				$daily_info  = array_key_exists( 'daily', $price_info ) ? $price_info['daily'] : [];
				$price_daily = is_array( $daily_info ) && array_key_exists( 'price', $daily_info ) ? $daily_info['price'] : '';
				$min_daily   = is_array( $daily_info ) && array_key_exists( 'min', $daily_info ) ? $daily_info['min'] : '';
				$max_daily   = is_array( $daily_info ) && array_key_exists( 'max', $daily_info ) ? $daily_info['max'] : '';
				/**************************/
				$multi_day_info       = array_key_exists( 'multi_day', $price_info ) ? $price_info['multi_day'] : [];
				$price_multi_day      = is_array( $multi_day_info ) && array_key_exists( 'price', $multi_day_info ) ? $multi_day_info['price'] : '';
				$price_multi_day_hour = is_array( $multi_day_info ) && array_key_exists( 'price_hour', $multi_day_info ) ? $multi_day_info['price_hour'] : '';
				$min_multi_day        = is_array( $multi_day_info ) && array_key_exists( 'min', $multi_day_info ) ? $multi_day_info['min'] : '';
				$max_multi_day        = is_array( $multi_day_info ) && array_key_exists( 'max', $multi_day_info ) ? $multi_day_info['max'] : '';
				/**************************/
				$monthly_info  = array_key_exists( 'monthly', $price_info ) ? $price_info['monthly'] : [];
				$price_monthly = is_array( $monthly_info ) && array_key_exists( 'price', $monthly_info ) ? $monthly_info['price'] : '';
				$min_monthly   = is_array( $monthly_info ) && array_key_exists( 'min', $monthly_info ) ? $monthly_info['min'] : '';
				$max_monthly   = is_array( $monthly_info ) && array_key_exists( 'max', $monthly_info ) ? $monthly_info['max'] : '';
				/**************************/
				$multi_month_info      = array_key_exists( 'monthly', $price_info ) ? $price_info['monthly'] : [];
				$price_multi_month_day = is_array( $multi_month_info ) && array_key_exists( 'price_day', $multi_month_info ) ? $multi_month_info['price_day'] : '';
				$price_multi_month     = is_array( $multi_month_info ) && array_key_exists( 'price', $multi_month_info ) ? $multi_month_info['price'] : '';
				$min_multi_month       = is_array( $multi_month_info ) && array_key_exists( 'min', $multi_month_info ) ? $multi_month_info['min'] : '';
				$max_multi_month       = is_array( $multi_month_info ) && array_key_exists( 'max', $multi_month_info ) ? $multi_month_info['max'] : '';
				/**************************/
				$rent_rules = ABPRF_Layout::rent_rules();
				?>
                <div class="_divider_xs"></div>
                <div class="custom_radio _fj_between">
                    <h5 class="_abprf_color_theme"><?php esc_html_e( 'Pricing and Quantity Configuration', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></h5>
                    <input type="hidden" class="_form_control" name="rent_rule" value="<?php echo esc_attr( $rent_rule ); ?>"/>
                    <div class="_f_wrap">
						<?php foreach ( $rent_rules as $key => $rule_label ) { ?>
                            <div class="radio_item">
                                <button type="button" class="_btn_white_xs <?php echo esc_attr( $rent_rule == $key ? 'rf_active' : '' ); ?>" data-close-target="#<?php echo esc_attr( $key ); ?>" data-radio="<?php echo esc_attr( $key ); ?>" data-open-icon="far fa-check-circle" data-close-icon="far fa-circle">
                                    <i class="_abprf_fs_h4"><span data-icon class="_mar_r_xs <?php echo esc_attr( $rent_rule == $key ? 'far fa-check-circle' : 'far fa-circle' ); ?>"></span></i><span class="_text_left_fs_label"><?php echo esc_html( $rule_label ); ?></span>
                                </button>
                            </div>
						<?php } ?>
                    </div>
                </div>
				<?php ABPRF_Layout::info_text( 'price_rule' ); ?>
                <div class="_divider_xs"></div>
                <div class=" _ov_auto_setting_item">
                    <table class="_abprf">
                        <thead>
                        <tr>
                            <th>
                                <div class="_f_equal _fj_center">
                                    <span><?php esc_html_e( 'Available Qty', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Reserve Qty', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Min Qty', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Max Qty', 'abprf-rental-forge' ); ?></span>
                                </div>
                            </th>
                            <th data-close="#hourly" class=" <?php echo esc_attr( $rent_rule == 'hourly' ? 'rf_active' : '' ); ?>">
                                <div class="_f_equal _fj_center">
                                    <span><?php esc_html_e( 'Hourly Rate', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Min Hours ', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Max Hours', 'abprf-rental-forge' ); ?></span>
                                </div>
                            </th>
                            <th data-close="#daily" class=" <?php echo esc_attr( $rent_rule == 'daily' ? 'rf_active' : '' ); ?>">
                                <div class="_f_equal _fj_center">
                                    <span><?php esc_html_e( 'Daily Rate', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Min Days ', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Max Days', 'abprf-rental-forge' ); ?></span>
                                </div>
                            </th>
                            <th data-close="#multi_day" class=" <?php echo esc_attr( $rent_rule == 'multi_day' ? 'rf_active' : '' ); ?>">
                                <div class="_f_equal _fj_center">
                                    <span><?php esc_html_e( 'Daily Rate', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Hourly Rate', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Min Days ', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Max Days', 'abprf-rental-forge' ); ?></span>
                                </div>
                            </th>
                            <th data-close="#monthly" class="<?php echo esc_attr( $rent_rule == 'monthly' ? 'rf_active' : '' ); ?>">
                                <div class="_f_equal _fj_center">
                                    <span><?php esc_html_e( 'Monthly Rate', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Min Months ', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Max Months', 'abprf-rental-forge' ); ?></span>
                                </div>
                            </th>
                            <th data-close="#multi_month" class="<?php echo esc_attr( $rent_rule == 'multi_month' ? 'rf_active' : '' ); ?>">
                                <div class="_f_equal _fj_center">
                                    <span><?php esc_html_e( 'Monthly Rate', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Daily Rate', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Min Months ', 'abprf-rental-forge' ); ?></span>
                                    <span><?php esc_html_e( 'Max Months', 'abprf-rental-forge' ); ?></span>
                                </div>
                            </th>
                        </tr>
                        </thead>
                        <tbody class="_bg_white">
                        <tr>
                            <th>
                                <div class="_group_content">
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="qty" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $qty ); ?>" required/>
                                    </label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="qty_reserve" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $qty_reserve ); ?>"/>
                                    </label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="qty_min" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $qty_min ); ?>" required/>
                                    </label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="qty_max" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $qty_max ); ?>"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
								<?php ABPRF_Layout::info_text( 'qty_reserve_min_max' ); ?>
                            </th>
                            <th data-close="#hourly" class=" <?php echo esc_attr( $rent_rule == 'hourly' ? 'rf_active' : '' ); ?>">
                                <div class="_group_content">
                                    <label><input type="text" class="_form_control validation_price" name="price_hourly" placeholder="Ex: 10" value="<?php echo esc_attr( $price_hourly ); ?>"/></label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="min_hourly" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $min_hourly ); ?>"/>
                                    </label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="max_hourly" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $max_hourly ); ?>"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
								<?php ABPRF_Layout::info_text( 'hourly_min_max' ); ?>
                            </th>
                            <th data-close="#daily" class=" <?php echo esc_attr( $rent_rule == 'daily' ? 'rf_active' : '' ); ?>">
                                <div class="_group_content">
                                    <label><input type="text" class="_form_control validation_price" name="price_daily" placeholder="Ex: 10" value="<?php echo esc_attr( $price_daily ); ?>"/></label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="min_daily" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $min_daily ); ?>"/>
                                    </label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="max_daily" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $max_daily ); ?>"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
								<?php ABPRF_Layout::info_text( 'daily_min_max' ); ?>
                            </th>
                            <th data-close="#multi_day" class=" <?php echo esc_attr( $rent_rule == 'multi_day' ? 'rf_active' : '' ); ?>">
                                <div class="_group_content">
                                    <label><input type="text" class="_form_control validation_price" name="price_multi_day" placeholder="Ex: 10" value="<?php echo esc_attr( $price_multi_day ); ?>"/></label>
                                    <label><input type="text" class="_form_control validation_price" name="price_multi_day_hour" placeholder="Ex: 10" value="<?php echo esc_attr( $price_multi_day_hour ); ?>"/></label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="min_multi_day" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $min_multi_day ); ?>"/>
                                    </label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="max_multi_day" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $max_multi_day ); ?>"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
								<?php ABPRF_Layout::info_text( 'daily_min_max' ); ?>
                            </th>
                            <th data-close="#monthly" class=" <?php echo esc_attr( $rent_rule == 'monthly' ? 'rf_active' : '' ); ?>">
                                <div class="_group_content">
                                    <label><input type="text" class="_form_control validation_price" name="price_monthly" placeholder="Ex: 10" value="<?php echo esc_attr( $price_monthly ); ?>"/></label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="min_monthly" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $min_monthly ); ?>"/>
                                    </label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="max_monthly" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $max_monthly ); ?>"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
								<?php ABPRF_Layout::info_text( 'monthly_min_max' ); ?>
                            </th>
                            <th data-close="#multi_month" class=" <?php echo esc_attr( $rent_rule == 'multi_month' ? 'rf_active' : '' ); ?>">
                                <div class="_group_content">
                                    <label><input type="text" class="_form_control validation_price" name="price_multi_month" placeholder="Ex: 10" value="<?php echo esc_attr( $price_multi_month ); ?>"/></label>
                                    <label><input type="text" class="_form_control validation_price" name="price_multi_month_day" placeholder="Ex: 10" value="<?php echo esc_attr( $price_multi_month_day ); ?>"/></label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="min_multi_month" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $min_multi_month ); ?>"/>
                                    </label>
                                    <label>
                                        <input type="number" pattern="[0-9]*" step="1" class="_form_control validation_number" name="max_multi_month" placeholder="<?php esc_attr_e( 'EX: 15', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $max_multi_month ); ?>"/>
                                    </label>
                                </div>
                                <div class="_divider_xxs"></div>
								<?php ABPRF_Layout::info_text( 'monthly_min_max' ); ?>
                            </th>
                        </tr>
                        </tbody>
                    </table>
                </div>
				<?php
			}

			public function deposit( $property = [] ): void {
				$price_info     = array_key_exists( 'price_info', $property ) ? $property['price_info'] : '';
				$price_info     = ! empty( $price_info ) ? json_decode( $price_info, true ) : [];
				$deposit_info   = array_key_exists( 'deposit', $price_info ) ? $price_info['deposit'] : [];
				$deposit_type   = is_array( $deposit_info ) && array_key_exists( 'type', $deposit_info ) ? $deposit_info['type'] : '';
				$deposit_value  = is_array( $deposit_info ) && array_key_exists( 'value', $deposit_info ) ? $deposit_info['value'] : '';
				$active_deposit = $deposit_type && $deposit_value ? 'on' : 'off';
				?>
                <div class="_setting_item">
                    <div class="_fa_center">
						<?php ABPRF_Layout::switch_checkbox( 'active_deposit', $active_deposit ); ?>
                        <span class="_fs_label_mar_lr_xs"><?php esc_html_e( 'Active Deposit?', 'abprf-rental-forge' ); ?></span>
                    </div>
                    <div class="_divider_xs"></div>
					<?php ABPRF_Layout::info_text( 'active_deposit' ); ?>
                </div>
                <div data-collapse="#active_deposit" class=" <?php echo esc_attr( $active_deposit == 'on' ? 'rf_active' : '' ); ?>">
                    <div class="group_setting">
                        <div class="_setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="_mar_r_xs"><?php esc_html_e( 'Select Deposit Type', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></span>
                                <select class="_form_control " name="deposit_type">
                                    <option disabled selected><?php esc_html_e( 'Please Select Deposit Type', 'abprf-rental-forge' ); ?></option>
                                    <option value="fixed" <?php echo esc_attr( $deposit_type == 'fixed' ? 'selected' : '' ); ?>><?php esc_html_e( 'Fixed Amount', 'abprf-rental-forge' ); ?></option>
                                    <option value="percent" <?php echo esc_attr( $deposit_type == 'percent' ? 'selected' : '' ); ?>><?php esc_html_e( 'Percentage(%) of Total Price', 'abprf-rental-forge' ); ?></option>
                                    <option value="qty" <?php echo esc_attr( $deposit_type == 'qty' ? 'selected' : '' ); ?>><?php esc_html_e( 'Fixed Amount per Quantity', 'abprf-rental-forge' ); ?></option>
                                </select>
                            </label>
                            <div class="_divider_xs"></div>
							<?php ABPRF_Layout::info_text( 'deposit_type' ); ?>
                        </div>
                        <div class="_setting_item">
                            <label class="_f_equal_f_wrap">
                                <span class="_mar_r_xs"><?php esc_html_e( 'Deposit Value', 'abprf-rental-forge' ); ?></span>
                                <input type="text" class="_form_control validation_price" name="deposit_value" placeholder="Ex: 10" value="<?php echo esc_attr( $deposit_value ); ?>"/>
                            </label>
                            <div class="_divider_xs"></div>
							<?php ABPRF_Layout::info_text( 'deposit_value' ); ?>
                        </div>
                    </div>
                </div>
				<?php
			}

			public function features( $property = [] ): void {
				$features = array_key_exists( 'features', $property ) ? $property['features'] : '';
				$features = ! empty( $features ) ? json_decode( $features, true ) : [];
				?>
                <div class="_divider_xs"></div>
                <h5 class="_abprf_color_theme"><?php esc_html_e( 'Feature Configuration', 'abprf-rental-forge' ); ?></h5>
				<?php ABPRF_Layout::info_text( 'property_feature' ); ?>
                <div class="_divider_xs"></div>
                <div class="configuration_content">
                    <table class="_abprf">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'Icon', 'abprf-rental-forge' ); ?></th>
                            <th><?php esc_html_e( 'Label', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></th>
                            <th><?php esc_html_e( 'Value', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></th>
                            <th class="_w_10"><?php esc_html_e( 'Action', 'abprf-rental-forge' ); ?></th>
                        </tr>
                        </thead>
                        <tbody class="insertable_area sortable_area">
						<?php
							if ( is_array( $features ) && sizeof( $features ) > 0 ) {
								foreach ( $features as $feature ) {
									$this->feature_item( $feature );
								}
							}
						?>
                        </tbody>
                    </table>
                    <div class="_divider_xs"></div>
					<?php ABPRF_Layout::button_add( __( 'Add New Feature', 'abprf-rental-forge' ) ); ?>
                    <div class="abprf_d_none">
                        <table class="_abprf">
                            <tbody class="hidden_content">
							<?php $this->feature_item(); ?>
                            </tbody>
                        </table>
                    </div>
                </div>
				<?php
			}

			public function gallery( $property = [] ): void {
				$sliders = array_key_exists( 'gallery', $property ) ? $property['gallery'] : '';
				?>
                <div class="_divider_xs"></div>
                <h5 class="_abprf_color_theme"><?php esc_html_e( 'Gallery Configuration', 'abprf-rental-forge' ); ?></h5>
				<?php ABPRF_Layout::info_text( 'abprf_sliders' ); ?>
                <div class="_divider_xs"></div>
                <div class="_setting_item">
					<?php do_action( 'abprf_add_image_multiple', 'abprf_sliders', $sliders ); ?>
                </div>
				<?php
			}

			public function feature_item( $feature = [] ): void {
				$label = is_array( $feature ) && array_key_exists( 'label', $feature ) ? $feature['label'] : '';
				$value = is_array( $feature ) && array_key_exists( 'value', $feature ) ? $feature['value'] : '';
				$icon  = array_key_exists( 'icon', $feature ) ? $feature['icon'] : '';
				?>
                <tr class="delete_area">
                    <th><?php do_action( 'abprf_add_icon', 'feature_icon[]', $icon ); ?></th>
                    <th>
                        <label>
                            <input type="text" class="_form_control validation_name" name="feature_name[]" placeholder="<?php esc_attr_e( 'EX: Model', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $label ); ?>"/>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="text" class="_form_control validation_name" name="feature_value[]" placeholder="<?php esc_attr_e( 'EX: 2005', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $value ); ?>"/>
                        </label>
                    </th>
                    <td><?php ABPRF_Layout::button_delete_sort(); ?></td>
                </tr>
				<?php
			}
		}
		new ABPRF_Properties();
	}