<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPRF_Location' ) ) {
		class ABPRF_Location {
			public function __construct() {
				add_action( 'abprf_global_location', array( $this, 'global_location' ) );
				add_action( 'wp_ajax_abprf_save_location', array( $this, 'save_location' ) );
				add_action( 'wp_ajax_abprf_loc_delete', array( $this, 'loc_delete' ) );
				add_action( 'wp_ajax_abprf_loc_add_edit', array( $this, 'loc_add_edit' ) );
			}

			public function global_location(): void {
				?>
                <div class="tab_item" data-tabs="#abprf_global_location">
                    <div class="location_list _ov_auto">
						<?php $this->load_loc_list(); ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <button type="button" class="_btn_default" data-target-popup="#abprf_location_popup"><span class="_mar_r_xs">➕</span><?php echo esc_html__( 'Add New Location', 'abprf-rental-forge' ); ?></button>
                </div>
				<?php
			}

			public function save_location() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$cat_term_id = isset( $_POST['loc_term_id'] ) ? sanitize_text_field( wp_unslash( $_POST['loc_term_id'] ) ) : '';
					$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
					$slug        = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
					$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
					$page_type   = isset( $_POST['page_type'] ) ? sanitize_text_field( wp_unslash( $_POST['page_type'] ) ) : 0;
					if ( ! empty( $name ) ) {
						if ( ! empty( $cat_term_id ) ) {
							$result = wp_update_term( $cat_term_id, 'abprf_location', array(
								'name' => $name,
								'slug' => $slug,
								'description' => $description,
							) );
						} else {
							$result = wp_insert_term(
								$name,
								'abprf_location',
								array(
									'slug' => $slug,
									'description' => $description,
								)
							);
						}
						$this->update_location();
						ob_start();
						if ( $page_type == 'post' ) {
							self::location_selection();
						} elseif ( $page_type == 'list' ) {
							$this->load_loc_list();
						}
						$html = ob_get_clean();
						if ( is_wp_error( $result ) ) {
							wp_send_json_success( [ 'html' => $html, 'msg' => $result->get_error_message() ] );
						} else {
							wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Location Saved Successfully !', 'abprf-rental-forge' ) ] );
						}
					}
				} else {
					wp_send_json_success( esc_html__( 'not Saved !', 'abprf-rental-forge' ) );
				}
				wp_die();
			}

			public function loc_delete() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$loc_id = isset( $_POST['loc_id'] ) ? sanitize_text_field( wp_unslash( $_POST['loc_id'] ) ) : '';
					$result = wp_delete_term( $loc_id, 'abprf_location' );
					$this->update_location();
					ob_start();
					$this->load_loc_list();
					$html = ob_get_clean();
					if ( is_wp_error( $result ) ) {
						wp_send_json_success( [ 'html' => $html, 'msg' => $result->get_error_message() ] );
					} else {
						wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Location Delete Successfully !', 'abprf-rental-forge' ) ] );
					}
				}
				wp_die();
			}

			public function update_location(): void {
				$taxonomies = ABPRF_Function::get_taxonomy( 'abprf_location' );
				$location   = [];
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$location[ $taxonomy->term_id ]['name']        = $taxonomy->name;
						$location[ $taxonomy->term_id ]['description'] = $taxonomy->description;
					}
				}
				ksort( $location );
				update_option( 'abprf_location', $location );
			}

			public function load_loc_list(): void {
				$all_locations = ABPRF_Function::get_option( 'abprf_location' );
				$count      = 1;
				if ( ! empty( $all_locations ) && is_array( $all_locations ) && sizeof( $all_locations ) > 0 ) { ?>
                    <table class="_abprf">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'SI', 'abprf-rental-forge' ) ?></th>
                            <th class="_min_200"><?php esc_html_e( 'Location Title', 'abprf-rental-forge' ) ?></th>
                            <th><?php esc_html_e( 'ID', 'abprf-rental-forge' ) ?></th>
                            <th class="_min_150"><?php esc_html_e( 'Location Full Address', 'abprf-rental-forge' ) ?></th>
                            <th class="_w_250"><?php esc_html_e( 'Shortcode Post', 'abprf-rental-forge' ) ?></th>
                            <th class="_w_250"><?php esc_html_e( 'Shortcode Property', 'abprf-rental-forge' ) ?></th>
                            <th class="_w_100"><?php esc_html_e( 'Action', 'abprf-rental-forge' ) ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $all_locations as $term_id => $location ) {
							$name        = is_array( $location ) && array_key_exists( 'name', $location ) ? $location['name'] : '';
							$description = is_array( $location ) && array_key_exists( 'description', $location ) ? $location['description'] : '';
							?>
                            <tr>
                                <th><?php echo esc_html( $count ); ?>.</th>
                                <th class="_text_left"><a href="<?php echo esc_url( get_term_link( $term_id ) ); ?>" target="_blank" class="_abprf_fs_h5 _color_theme"><?php echo esc_html( $name ); ?></a></th>
                                <th><?php echo esc_html( $term_id ); ?></th>
                                <td><?php echo esc_html( $description ); ?></td>
                                <th><code> [abprf-post loc_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
                                <th><code> [abprf-property loc_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
                                <td>
                                    <div class="_f_wrap">
                                        <button type="button" class="_btn_light_yellow_mar_r_xxs" data-loc_id="<?php echo esc_attr( $term_id ); ?>" data-target-popup="#abprf_location_popup" title="<?php echo esc_attr__( 'Edit : ', 'abprf-rental-forge' ) . ' ' . esc_attr( $name ); ?>">✍️</button>
                                        <button type="button" class="_btn_light_danger_xxs abprf_loc_delete" data-loc_id="<?php echo esc_attr( $term_id ); ?>" title="<?php echo esc_attr__( 'Trash : ', 'abprf-rental-forge' ) . ' ' . esc_attr( $name ); ?>">❌</button>
                                    </div>
                                </td>
                            </tr>
							<?php $count ++;
						} ?>
                        </tbody>
                    </table>
				<?php } else {
					ABPRF_Layout::layout_warning_info( 'no_location' );
				}
			}

			public function loc_add_edit() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$loc_id    = isset( $_POST['loc_id'] ) ? sanitize_text_field( wp_unslash( $_POST['loc_id'] ) ) : '';
					$page_type = isset( $_POST['page_type'] ) ? sanitize_text_field( wp_unslash( $_POST['page_type'] ) ) : '';
					$this->loc_form( $loc_id, $page_type );
				}
				wp_die();
			}

			public function loc_form( $term_id = '', $page_type = '' ) {
				$name           = $slug = $des = '';
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
                    <input type="hidden" name="loc_term_id" value="<?php echo esc_attr( $term_id ); ?>"/>
                    <input type="hidden" name="page_type" value="<?php echo esc_attr( $page_type ); ?>"/>
                    <div class="_setting_item">
                        <label class="_f_equal_f_wrap">
                            <span class="_mar_r_xs"><?php  esc_html_e( 'Location Name', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></span>
                            <input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abprf-rental-forge' ); ?>" required/>
                        </label>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'loc_name' ); ?>
                    </div>
                    <div class="_setting_item">
                        <label class="_f_equal_f_wrap">
                            <span class="_mar_r_xs"><?php  esc_html_e( 'Location Slug (Optional)', 'abprf-rental-forge' ); ?></span>
                            <input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abprf-rental-forge' ); ?>"/>
                        </label>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'loc_slug' ); ?>
                    </div>
                    <div class="_setting_item">
                        <label class="_f_equal_f_wrap">
                            <span class="_mar_r_xs"><?php  esc_html_e( 'Location Full Address', 'abprf-rental-forge' ); ?></span>
                            <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e( 'Address', 'abprf-rental-forge' ); ?>"><?php echo esc_html( $des ); ?></textarea>
                        </label>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'loc_des' ); ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <button type="submit" class="_btn_theme"><span class="_mar_r_xxs">💾</span><?php echo ( ! empty( $term_id ) ? esc_html__( 'Update Location', 'abprf-rental-forge' ) : esc_html__( 'Save Location', 'abprf-rental-forge' ) ); ?></button>
                </form>
				<?php
			}
			public static function location_selection( $_location = '' ): void {
				$all_location = ABPRF_Function::get_option( 'abprf_location' );
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
		new ABPRF_Location();
	}