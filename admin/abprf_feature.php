<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPRF_Feature' ) ) {
		class ABPRF_Feature {
			public function __construct() {
				add_action( 'abprf_global_feature', array( $this, 'global_feature' ) );
				add_action( 'wp_ajax_abprf_save_feature', array( $this, 'save_feature' ) );
				add_action( 'wp_ajax_abprf_feature_delete', array( $this, 'feature_delete' ) );
				add_action( 'wp_ajax_abprf_feature_edit', array( $this, 'feature_edit' ) );
			}

			public function global_feature(): void {
				?>
                <div class="tab_item feature_area" data-tabs="#abprf_global_feature">
                    <div class="feature_list _group_list">
						<?php $this->load_feature_list(); ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="configuration_content">
                        <form class="save_feature" method="post" action="">
                            <div class="hide_on_load">
                                <table class="_abprf ">
                                    <thead>
                                    <tr>
                                        <th><?php esc_html_e( 'Icon', 'abprf-rental-forge' ); ?></th>
                                        <th><?php esc_html_e( 'Label', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></th>
                                        <th><?php esc_html_e( 'Value', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></th>
                                        <th class="_w_10"><?php esc_html_e( 'Action', 'abprf-rental-forge' ); ?></th>
                                    </tr>
                                    </thead>
                                    <tbody class="insertable_area sortable_area">
                                    </tbody>
                                </table>
                                <div class="_divider_xs"></div>
                            </div>
                            <div class="_fj_between">
								<?php ABPRF_Layout::button_add( __( 'Add New Feature', 'abprf-rental-forge' ) ); ?>
                                <button type="submit" class="_btn_theme hide_on_load "><span class="_mar_r_xxs">💾</span><?php esc_html_e( 'Save Feature', 'abprf-rental-forge' ); ?></button>
                            </div>
                        </form>
                        <div class="abprf_d_none">
                            <table class="_abprf">
                                <tbody class="hidden_content">
								<?php $this->feature_item(); ?>
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
				<?php
			}

			public function feature_item( $feature = [], $id = '' ): void {
				$label = is_array( $feature ) && array_key_exists( 'label', $feature ) ? $feature['label'] : '';
				$value = is_array( $feature ) && array_key_exists( 'value', $feature ) ? $feature['value'] : '';
				$icon  = array_key_exists( 'icon', $feature ) ? $feature['icon'] : '';
				?>
                <tr class="delete_area">
                    <th><?php do_action( 'abprf_add_icon', 'feature_icon[]', $icon ); ?></th>
                    <th>
                        <label>
                            <input type="hidden" name="feature_id[]" value="<?php echo esc_attr( $id ); ?>"/>
                            <input type="text" class="_form_control validation_name" name="feature_name[]" placeholder="<?php esc_attr_e( 'EX: Model', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $label ); ?>" required/>
                        </label>
                    </th>
                    <th>
                        <label>
                            <input type="text" class="_form_control validation_name" name="feature_value[]" placeholder="<?php esc_attr_e( 'EX: 2005', 'abprf-rental-forge' ); ?>" value="<?php echo esc_attr( $value ); ?>" required/>
                        </label>
                    </th>
                    <td><?php ABPRF_Layout::button_delete_sort(); ?></td>
                </tr>
				<?php
			}

			public function save_feature() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$old_features   = ABPRF_Function::get_option( 'abprf_feature' );
					$id             = ! empty( $old_features ) && sizeof( $old_features ) > 0 ? array_key_last( $old_features ) : 'fec_id_1';
					$feature_ids    = isset( $_POST['feature_id'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['feature_id'] ) ) : [];
					$feature_names  = isset( $_POST['feature_name'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['feature_name'] ) ) : [];
					$feature_values = isset( $_POST['feature_value'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['feature_value'] ) ) : [];
					$feature_icon   = isset( $_POST['feature_icon'] ) ? array_map( 'sanitize_text_field', wp_unslash( $_POST['feature_icon'] ) ) : [];
					if ( sizeof( $feature_names ) > 0 && sizeof( $feature_values ) > 0 ) {
						foreach ( $feature_names as $key => $feature_name ) {
							if ( $feature_name && $feature_values[ $key ] ) {
								$old_id = array_key_exists( $key, $feature_ids ) ? $feature_ids[ $key ] : '';
								if ( ! empty( $old_id ) && array_key_exists( $old_id, $old_features ) ) {
									$id = $old_id;
								} else {
									if ( array_key_exists( $id, $old_features ) ) {
										$number    = (int) str_replace( 'fec_id_', '', $id );
										$new_count = $number + 1;
										$id        = 'fec_id_' . $new_count;
										while ( array_key_exists( $id, $old_features ) ) {
											$number    = (int) str_replace( 'fec_id_', '', $id );
											$new_count = $number + 1;
											$id        = 'fec_id_' . $new_count;
										}
									}
								}
								$old_features[ $id ]['label'] = $feature_name;
								$old_features[ $id ]['value'] = $feature_values[ $key ];
								$old_features[ $id ]['icon']  = $feature_icon[ $key ];
							}
						}
					}
					update_option( 'abprf_feature', $old_features );
					ob_start();
					$this->load_feature_list();
					$html = ob_get_clean();
					wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Feature Saved Successfully !', 'abprf-rental-forge' ) ] );
				} else {
					wp_send_json_success( esc_html__( 'Feature not Saved !', 'abprf-rental-forge' ) );
				}
				wp_die();
			}

			public function feature_delete() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$fec_id   = isset( $_POST['fec_id'] ) ? sanitize_text_field( wp_unslash( $_POST['fec_id'] ) ) : '';
					$features = ABPRF_Function::get_option( 'abprf_feature' );
					unset( $features[ $fec_id ] );
					update_option( 'abprf_feature', $features );
					ob_start();
					$this->load_feature_list();
					$html = ob_get_clean();
					wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Feature Delete Successfully !', 'abprf-rental-forge' ) ] );
				} else {
					ob_start();
					$this->load_feature_list();
					$html = ob_get_clean();
					wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Feature not Delete. Something Error Found !', 'abprf-rental-forge' ) ] );
				}
				wp_die();
			}

			public function feature_edit() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$fec_id   = isset( $_POST['fec_id'] ) ? sanitize_text_field( wp_unslash( $_POST['fec_id'] ) ) : '';
					$features = ABPRF_Function::get_option( 'abprf_feature' );
					$feature  = array_key_exists( $fec_id, $features ) ? $features[ $fec_id ] : [];
					ob_start();
					$this->feature_item( $feature, $fec_id );
					$html = ob_get_clean();
					wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Feature Delete Successfully !', 'abprf-rental-forge' ) ] );
				}
				wp_die();
			}

			public function load_feature_list(): void {
				$features = ABPRF_Function::get_option( 'abprf_feature' );
				//echo '<pre>';				print_r( $features );				echo '</pre>';
				if ( ! empty( $features ) && is_array( $features ) && sizeof( $features ) > 0 ) {
					foreach ( $features as $key => $feature ) {
						$label = is_array( $feature ) && array_key_exists( 'label', $feature ) ? $feature['label'] : '';
						$value = is_array( $feature ) && array_key_exists( 'value', $feature ) ? $feature['value'] : '';
						$icon  = array_key_exists( 'icon', $feature ) ? $feature['icon'] : '';
						?>
                        <div class="_list_item">
                            <h6 class="_abprf_color_theme"> <?php ABPRF_Layout::image_icon( $icon ); ?> <?php echo esc_html( $label ); ?> - <?php echo esc_html( $value ); ?></h6>
                            <div class="_f_wrap">
                                <button type="button" class="_btn_light_yellow_mar_r_xxs abprf_feature_edit" data-fec_id="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr__( 'Edit : ', 'abprf-rental-forge' ) . ' ' . esc_attr( $label ); ?>">✍️</button>
                                <button type="button" class="_btn_light_danger_xxs abprf_feature_delete" data-fec_id="<?php echo esc_attr( $key ); ?>" title="<?php echo esc_attr__( 'Trash : ', 'abprf-rental-forge' ) . ' ' . esc_attr( $label ); ?>">❌</button>
                            </div>
                        </div>
						<?php
					}
				}
			}

			public function form( $term_id = '', $page_type = '' ): void {
				$name = $slug = $des = '';
				if ( ! empty( $term_id ) ) {
					$term = get_term( $term_id );
					if ( ! empty( $term ) ) {
						$name = $term->name;
						$slug = $term->slug;
						$des  = $term->description;
					}
				}
				?>
                <form class="save_location" method="post" action="">
                    <input type="hidden" name="term_id" value="<?php echo esc_attr( $term_id ); ?>"/>
                    <input type="hidden" name="page_type" value="<?php echo esc_attr( $page_type ); ?>"/>
                    <div class="_setting_item">
                        <label class="_f_equal_f_wrap">
                            <span class="_mar_r_xs"><?php esc_html_e( 'Feature Label', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></span>
                            <input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abprf-rental-forge' ); ?>" required/>
                        </label>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'feature_name' ); ?>
                    </div>
                    <div class="_setting_item">
                        <label class="_f_equal_f_wrap">
                            <span class="_mar_r_xs"><?php esc_html_e( 'Feature Slug (Optional)', 'abprf-rental-forge' ); ?></span>
                            <input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abprf-rental-forge' ); ?>"/>
                        </label>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'feature_slug' ); ?>
                    </div>
                    <div class="_setting_item">
                        <label class="_f_equal_f_wrap">
                            <span class="_mar_r_xs"><?php esc_html_e( 'Feature Value', 'abprf-rental-forge' ); ?></span>
                            <input class="_form_control" name="description" value="<?php echo esc_attr( $des ); ?>" placeholder="<?php esc_attr_e( '25 Sqt', 'abprf-rental-forge' ); ?>" required/>
                        </label>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'feature_des' ); ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <button type="submit" class="_btn_theme"><span class="_mar_r_xxs">💾</span><?php echo( ! empty( $term_id ) ? esc_html__( 'Update Feature', 'abprf-rental-forge' ) : esc_html__( 'Save Feature', 'abprf-rental-forge' ) ); ?></button>
                </form>
				<?php
			}

			public static function feature_selection( $_location = '' ): void {
				$all_location   = ABPRF_Function::get_option( 'abprf_location' );
				$location_array = ! empty( $_location ) ? explode( ',', $_location ) : [];
				if ( ! empty( $all_location ) && is_array( $all_location ) && sizeof( $all_location ) > 0 ) { ?>
                    <div class="custom_checkbox">
                        <input type="hidden" name="location" value="<?php echo esc_attr( $_location ); ?>"/>
						<?php foreach ( $all_location as $key => $location ) {
							$name = is_array( $location ) && array_key_exists( 'name', $location ) ? $location['name'] : ''; ?>
                            <div class="checkbox_item _min_100">
                                <button type="button" class="_btn_white_xs <?php echo esc_attr( in_array( $key, $location_array ) ? 'rf_active' : '' ); ?>" data-checked="<?php echo esc_attr( $key ); ?>" data-open-icon="fa-check-square" data-close-icon="fa-square">
                                    <span data-icon class="_mar_r_xs far <?php echo esc_attr( in_array( $key, $location_array ) ? 'far fa-check-square' : 'fa-square' ); ?>"></span><?php echo esc_html( $name ); ?>
                                </button>
                            </div>
						<?php } ?>
                    </div>
				<?php } else { ?>
                    <p><?php echo esc_html( ABPRF_Layout::array_info( 'no_location' ) ); ?></p>
                    <button type="button" class="_btn_default" data-target-popup="#abprf_location_popup"><span class="_mar_r_xs">➕</span><?php echo esc_html__( 'Add New Location', 'abprf-rental-forge' ); ?></button>
					<?php
				}
			}
		}
		new ABPRF_Feature();
	}