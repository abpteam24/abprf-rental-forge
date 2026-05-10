<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPRF_Category' ) ) {
		class ABPRF_Category {
			public function __construct() {
				add_action( 'abprf_global_category', array( $this, 'global_category' ) );
				add_action( 'wp_ajax_abprf_save_category', array( $this, 'save_category' ) );
				add_action( 'wp_ajax_abprf_cat_delete', array( $this, 'cat_delete' ) );
				add_action( 'wp_ajax_abprf_cat_add_edit', array( $this, 'cat_add_edit' ) );
			}

			public function global_category( $abprf_info ) {
				$category_label = isset( $abprf_info['category_label'] ) && $abprf_info['category_label'] ? $abprf_info['category_label'] : __( 'Category', 'abprf-rental-forge' );
				?>
                <div class="tab_item" data-tabs="#abprf_global_category">
                    <div class="category_list _ov_auto">
						<?php $this->load_cat_list(); ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <button type="button" class="_btn_default" data-target-popup="#abprf_category_popup"><span class="_mar_r_xs">➕</span><?php echo esc_html__( 'Add New', 'abprf-rental-forge' ) . ' ' . esc_html( $category_label ); ?></button>
                </div>
				<?php
			}

			public function save_category() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$cat_term_id = isset( $_POST['cat_term_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cat_term_id'] ) ) : '';
					$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
					$slug        = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
					$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
					$page_type   = isset( $_POST['page_type'] ) ? sanitize_text_field( wp_unslash( $_POST['page_type'] ) ) : 0;
					if ( ! empty( $name ) ) {
						if ( ! empty( $cat_term_id ) ) {
							$result = wp_update_term( $cat_term_id, 'abprf_category', array(
								'name' => $name,
								'slug' => $slug,
								'description' => $description,
							) );
						} else {
							$result = wp_insert_term(
								$name,
								'abprf_category',
								array(
									'slug' => $slug,
									'description' => $description,
								)
							);
						}
						$this->update_category();
						ob_start();
						if ( $page_type == 'post' ) {
							self::category_selection();
						} elseif ( $page_type == 'list' ) {
							$this->load_cat_list();
						}
						$html = ob_get_clean();
						if ( is_wp_error( $result ) ) {
							wp_send_json_success( [ 'html' => $html, 'msg' => $result->get_error_message() ] );
						} else {
							wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Category Saved Successfully !', 'abprf-rental-forge' ) ] );
						}
					}
				} else {
					wp_send_json_success( esc_html__( 'not Saved !', 'abprf-rental-forge' ) );
				}
				wp_die();
			}

			public function cat_delete() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$cat_id = isset( $_POST['cat_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cat_id'] ) ) : '';
					$result = wp_delete_term( $cat_id, 'abprf_category' );
					$this->update_category();
					ob_start();
					$this->load_cat_list();
					$html = ob_get_clean();
					if ( is_wp_error( $result ) ) {
						wp_send_json_success( [ 'html' => $html, 'msg' => $result->get_error_message() ] );
					} else {
						wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Category Delete Successfully !', 'abprf-rental-forge' ) ] );
					}
				}
				wp_die();
			}

			public function update_category(): void {
				$taxonomies = ABPRF_Function::get_taxonomy( 'abprf_category' );
				$category   = [];
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$category[ $taxonomy->term_id ]['name']        = $taxonomy->name;
						$category[ $taxonomy->term_id ]['description'] = $taxonomy->description;
					}
				}
				ksort( $category );
				update_option( 'abprf_category', $category );
			}

			public function load_cat_list(): void {
				$all_categories = ABPRF_Function::get_option( 'abprf_category' );
				$count          = 1;
				if ( ! empty( $all_categories ) && is_array( $all_categories ) && sizeof( $all_categories ) > 0 ) { ?>
                    <table class="_abprf">
                        <thead>
                        <tr>
                            <th><?php esc_html_e( 'SI', 'abprf-rental-forge' ) ?></th>
                            <th class="_min_200"><?php esc_html_e( 'Category Title', 'abprf-rental-forge' ) ?></th>
                            <th><?php esc_html_e( 'ID', 'abprf-rental-forge' ) ?></th>
                            <th class="_min_150"><?php esc_html_e( 'Description', 'abprf-rental-forge' ) ?></th>
                            <th class="_w_250"><?php esc_html_e( 'Shortcode Post', 'abprf-rental-forge' ) ?></th>
                            <th class="_w_250"><?php esc_html_e( 'Shortcode Property', 'abprf-rental-forge' ) ?></th>
                            <th class="_w_100"><?php esc_html_e( 'Action', 'abprf-rental-forge' ) ?></th>
                        </tr>
                        </thead>
                        <tbody>
						<?php foreach ( $all_categories as $term_id => $category ) {
							$name        = is_array( $category ) && array_key_exists( 'name', $category ) ? $category['name'] : '';
							$description = is_array( $category ) && array_key_exists( 'description', $category ) ? $category['description'] : '';
							?>
                            <tr>
                                <th><?php echo esc_html( $count ); ?>.</th>
                                <th class="_text_left"><a href="<?php echo esc_url( get_term_link( $term_id ) ); ?>" target="_blank" class="_abprf_fs_h5 _color_theme"><?php echo esc_html( $name ); ?></a></th>
                                <th><?php echo esc_html( $term_id ); ?></th>
                                <td><?php echo esc_html( $description ); ?></td>
                                <th><code> [abprf-post cat_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
                                <th><code> [abprf-property cat_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
                                <th>
                                    <div class="_f_wrap">
                                        <button type="button" class="_btn_light_yellow_mar_r_xxs" data-cat_id="<?php echo esc_attr( $term_id ); ?>" data-target-popup="#abprf_category_popup" title="<?php echo esc_attr__( 'Edit : ', 'abprf-rental-forge' ) . ' ' . esc_attr( $name ); ?>">✍️</button>
                                        <button type="button" class="_btn_light_danger_xxs abprf_cat_delete" data-cat_id="<?php echo esc_attr( $term_id ); ?>" title="<?php echo esc_attr__( 'Trash : ', 'abprf-rental-forge' ) . ' ' . esc_attr( $name ); ?>">❌</button>
                                    </div>
                                </th>
                            </tr>
							<?php $count ++;
						} ?>
                        </tbody>
                    </table>
				<?php } else {
					ABPRF_Layout::layout_warning_info( 'no_category' );
				}
			}

			public function cat_add_edit() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$cat_id    = isset( $_POST['cat_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cat_id'] ) ) : '';
					$page_type = isset( $_POST['page_type'] ) ? sanitize_text_field( wp_unslash( $_POST['page_type'] ) ) : '';
					$this->cat_form( $cat_id, $page_type );
				}
				wp_die();
			}

			public function cat_form( $term_id = '', $page_type = '' ) {
				$name           = $slug = $des = '';
				$category_label = ABPRF_Function::get_options( 'abprf_configuration', 'category_label', __( 'Category', 'abprf-rental-forge' ) );
				if ( ! empty( $term_id ) ) {
					$term = get_term( $term_id );
					if ( ! empty( $term ) ) {
						$name = $term->name;
						$slug = $term->slug;
						$des  = $term->description;
					}
				}
				?>
                <form class="save_category" method="post" action="">
                    <input type="hidden" name="cat_term_id" value="<?php echo esc_attr( $term_id ); ?>"/>
                    <input type="hidden" name="page_type" value="<?php echo esc_attr( $page_type ); ?>"/>
                    <div class="_setting_item">
                        <label class="_f_equal_f_wrap">
                            <span class="_mar_r_xs"><?php echo esc_html( $category_label ) . ' ' . esc_html__( 'Name', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></span>
                            <input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abprf-rental-forge' ); ?>" required/>
                        </label>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'cat_name' ); ?>
                    </div>
                    <div class="_setting_item">
                        <label class="_f_equal_f_wrap">
                            <span class="_mar_r_xs"><?php echo esc_html( $category_label ) . ' ' . esc_html__( 'Slug (Optional)', 'abprf-rental-forge' ); ?></span>
                            <input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abprf-rental-forge' ); ?>"/>
                        </label>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'cat_slug' ); ?>
                    </div>
                    <div class="_setting_item">
                        <label class="_f_equal_f_wrap">
                            <span class="_mar_r_xs"><?php echo esc_html( $category_label ) . ' ' . esc_html__( 'Description', 'abprf-rental-forge' ); ?></span>
                            <textarea class="_form_control" name="description" placeholder="<?php esc_attr_e( 'Description', 'abprf-rental-forge' ); ?>"><?php echo esc_html( $des ); ?></textarea>
                        </label>
                        <div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'cat_des' ); ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <button type="submit" class="_btn_theme"><span class="_mar_r_xxs">💾</span><?php echo ( ! empty( $term_id ) ? esc_html__( 'Update', 'abprf-rental-forge' ) : esc_html__( 'Save', 'abprf-rental-forge' ) ) . ' ' . esc_html( $category_label ); ?></button>
                </form>
				<?php
			}

			public static function category_selection( $_category = '' ): void {
				$category_array = ! empty( $_category ) ? explode( ',', $_category ) : [];
				$all_categories = ABPRF_Function::get_option( 'abprf_category' );
				if ( ! empty( $all_categories ) && is_array( $all_categories ) && sizeof( $all_categories ) > 0 ) { ?>
                    <div class="custom_checkbox">
                        <input type="hidden" name="category" value="<?php echo esc_attr( $_category ); ?>"/>
						<?php foreach ( $all_categories as $key => $category ) {
							$name = is_array( $category ) && array_key_exists( 'name', $category ) ? $category['name'] : ''; ?>
                            <div class="checkbox_item _min_100">
                                <button type="button" class="_btn_white_xs <?php echo esc_attr( in_array( $key, $category_array ) ? 'rf_active' : '' ); ?>" data-checked="<?php echo esc_attr( $key ); ?>" data-open-icon="fa-check-square" data-close-icon="fa-square">
                                    <span data-icon class="_mar_r_xs far <?php echo esc_attr( in_array( $key, $category_array ) ? 'far fa-check-square' : 'fa-square' ); ?>"></span><?php echo esc_html( $name ); ?>
                                </button>
                            </div>
						<?php } ?>
                    </div>
				<?php } else { ?>
                    <p><?php echo esc_html( ABPRF_Layout::array_info( 'no_category' ) ); ?></p>
                    <button type="button" class="_btn_default" data-target-popup="#abprf_category_popup"><span class="_mar_r_xs">➕</span><?php echo esc_html__( 'Add New', 'abprf-rental-forge' ) . ' ' . esc_html( ABPRF_Function::get_options( 'abprf_configuration', 'category_label', __( 'Category', 'abprf-rental-forge' ) ) ); ?></button>
					<?php
				}
			}
		}
		new ABPRF_Category();
	}