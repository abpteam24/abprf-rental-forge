<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPRF_Brand' ) ) {
		class ABPRF_Brand {
			public function __construct() {
				add_action( 'abprf_global_brand', array( $this, 'global_brand' ) );
				add_action( 'wp_ajax_abprf_save_brand', array( $this, 'save_brand' ) );
				add_action( 'wp_ajax_abprf_brand_delete', array( $this, 'brand_delete' ) );
				add_action( 'wp_ajax_abprf_brand_add_edit', array( $this, 'brand_add_edit' ) );
			}

			public function global_brand() {
				?>
				<div class="tab_item" data-tabs="#abprf_global_brand">
					<div class="brand_list _ov_auto">
						<?php $this->load_brand_list(); ?>
					</div>
					<div class="_divider_xs"></div>
					<button type="button" class="_btn_default" data-target-popup="#abprf_brand_popup"><span class="_mar_r_xs">➕</span><?php esc_html_e( 'Add New Brand', 'abprf-rental-forge' ); ?></button>
				</div>
				<?php
			}

			public function save_brand() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$cat_term_id = isset( $_POST['cat_term_id'] ) ? sanitize_text_field( wp_unslash( $_POST['cat_term_id'] ) ) : '';
					$name        = isset( $_POST['name'] ) ? sanitize_text_field( wp_unslash( $_POST['name'] ) ) : '';
					$slug        = isset( $_POST['slug'] ) ? sanitize_text_field( wp_unslash( $_POST['slug'] ) ) : '';
					$description = isset( $_POST['description'] ) ? sanitize_text_field( wp_unslash( $_POST['description'] ) ) : '';
					$page_type   = isset( $_POST['page_type'] ) ? sanitize_text_field( wp_unslash( $_POST['page_type'] ) ) : 0;
					if ( ! empty( $name ) ) {
						if ( ! empty( $cat_term_id ) ) {
							$result = wp_update_term( $cat_term_id, 'abprf_brand', array(
								'name' => $name,
								'slug' => $slug,
								'description' => $description,
							) );
						} else {
							$result = wp_insert_term(
								$name,
								'abprf_brand',
								array(
									'slug' => $slug,
									'description' => $description,
								)
							);
						}
						$this->update_brand();
						ob_start();
						if ( $page_type == 'post' ) {
							self::brand_selection();
						} elseif ( $page_type == 'list' ) {
							$this->load_brand_list();
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

			public function brand_delete() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$brand_id = isset( $_POST['brand_id'] ) ? sanitize_text_field( wp_unslash( $_POST['brand_id'] ) ) : '';
					$result = wp_delete_term( $brand_id, 'abprf_brand' );
					$this->update_brand();
					ob_start();
					$this->load_brand_list();
					$html = ob_get_clean();
					if ( is_wp_error( $result ) ) {
						wp_send_json_success( [ 'html' => $html, 'msg' => $result->get_error_message() ] );
					} else {
						wp_send_json_success( [ 'html' => $html, 'msg' => esc_html__( 'Brand Delete Successfully !', 'abprf-rental-forge' ) ] );
					}
				}
				wp_die();
			}

			public function update_brand(): void {
				$taxonomies = ABPRF_Function::get_taxonomy( 'abprf_brand' );
				$category   = [];
				if ( ! empty( $taxonomies ) && is_array( $taxonomies ) && sizeof( $taxonomies ) > 0 ) {
					foreach ( $taxonomies as $taxonomy ) {
						$category[ $taxonomy->term_id ]['name']        = $taxonomy->name;
						$category[ $taxonomy->term_id ]['description'] = $taxonomy->description;
					}
				}
				ksort( $category );
				update_option( 'abprf_brand', $category );
			}

			public function load_brand_list(): void {
				$abprf_brands = ABPRF_Function::get_option( 'abprf_brand' );
				$count          = 1;
				if ( ! empty( $abprf_brands ) && is_array( $abprf_brands ) && sizeof( $abprf_brands ) > 0 ) { ?>
					<table class="_abprf">
						<thead>
						<tr>
							<th><?php esc_html_e( 'SI', 'abprf-rental-forge' ) ?></th>
							<th class="_min_200"><?php esc_html_e( 'Brand Title', 'abprf-rental-forge' ) ?></th>
							<th><?php esc_html_e( 'ID', 'abprf-rental-forge' ) ?></th>
							<th class="_min_150"><?php esc_html_e( 'Description', 'abprf-rental-forge' ) ?></th>
							<th class="_w_250"><?php esc_html_e( 'Shortcode Property', 'abprf-rental-forge' ) ?></th>
							<th class="_w_100"><?php esc_html_e( 'Action', 'abprf-rental-forge' ) ?></th>
						</tr>
						</thead>
						<tbody>
						<?php foreach ( $abprf_brands as $term_id => $brand) {
							$name        = is_array( $brand ) && array_key_exists( 'name', $brand ) ? $brand['name'] : '';
							$description = is_array( $brand ) && array_key_exists( 'description', $brand ) ? $brand['description'] : '';
							?>
							<tr>
								<th><?php echo esc_html( $count ); ?>.</th>
								<th class="_text_left"><a href="<?php echo esc_url( get_term_link( $term_id ) ); ?>" target="_blank" class="_abprf_fs_h5 _color_theme"><?php echo esc_html( $name ); ?></a></th>
								<th><?php echo esc_html( $term_id ); ?></th>
								<td><?php echo esc_html( $description ); ?></td>
								<th><code> [abprf-property brand_id="<?php echo esc_attr( $term_id ); ?>"]</code></th>
								<th>
									<div class="_f_wrap">
										<button type="button" class="_btn_light_yellow_mar_r_xxs" data-brand_id="<?php echo esc_attr( $term_id ); ?>" data-target-popup="#abprf_brand_popup" title="<?php echo esc_attr__( 'Edit : ', 'abprf-rental-forge' ) . ' ' . esc_attr( $name ); ?>">✍️</button>
										<button type="button" class="_btn_light_danger_xxs abprf_brand_delete" data-brand_id="<?php echo esc_attr( $term_id ); ?>" title="<?php echo esc_attr__( 'Trash : ', 'abprf-rental-forge' ) . ' ' . esc_attr( $name ); ?>">❌</button>
									</div>
								</th>
							</tr>
							<?php $count ++;
						} ?>
						</tbody>
					</table>
				<?php } else {
					ABPRF_Layout::layout_warning_info( 'no_brand' );
				}
			}

			public function brand_add_edit() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					$brand_id    = isset( $_POST['brand_id'] ) ? sanitize_text_field( wp_unslash( $_POST['brand_id'] ) ) : '';
					$page_type = isset( $_POST['page_type'] ) ? sanitize_text_field( wp_unslash( $_POST['page_type'] ) ) : '';
					$this->form( $brand_id, $page_type );
				}
				wp_die();
			}

			public function form( $term_id = '', $page_type = '' ) {
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
				<form class="save_brand" method="post" action="">
					<input type="hidden" name="cat_term_id" value="<?php echo esc_attr( $term_id ); ?>"/>
					<input type="hidden" name="page_type" value="<?php echo esc_attr( $page_type ); ?>"/>
					<div class="_setting_item">
						<label class="_f_equal_f_wrap">
							<span class="_mar_r_xs"><?php esc_html_e( 'Brand Title', 'abprf-rental-forge' ); ?><sup class="_color_required">*</sup></span>
							<input class="_form_control" name="name" value="<?php echo esc_attr( $name ); ?>" placeholder="<?php esc_attr_e( 'Name', 'abprf-rental-forge' ); ?>" required/>
						</label>
						<div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'cat_name' ); ?>
					</div>
					<div class="_setting_item">
						<label class="_f_equal_f_wrap">
							<span class="_mar_r_xs"><?php esc_html_e( 'Brand Slug (Optional)', 'abprf-rental-forge' ); ?></span>
							<input class="_form_control" name="slug" value="<?php echo esc_attr( $slug ); ?>" placeholder="<?php esc_attr_e( 'Slug', 'abprf-rental-forge' ); ?>"/>
						</label>
						<div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'cat_slug' ); ?>
					</div>
					<div class="_setting_item">
						<label class="_f_equal_f_wrap">
							<span class="_mar_r_xs"><?php  esc_html_e( 'Description', 'abprf-rental-forge' ); ?></span>
							<textarea class="_form_control" name="description" placeholder="<?php esc_attr_e( 'Description', 'abprf-rental-forge' ); ?>"><?php echo esc_html( $des ); ?></textarea>
						</label>
						<div class="_divider_xs"></div>
						<?php ABPRF_Layout::info_text( 'cat_des' ); ?>
					</div>
					<div class="_divider_xs"></div>
					<button type="submit" class="_btn_theme"><span class="_mar_r_xxs">💾</span><?php echo ( ! empty( $term_id ) ? esc_html__( 'Update Brand', 'abprf-rental-forge' ) : esc_html__( 'Save Brand', 'abprf-rental-forge' ) ); ?></button>
				</form>
				<?php
			}

			public static function brand_selection( $_category = '' ): void {
				$category_array = ! empty( $_category ) ? explode( ',', $_category ) : [];
				$all_categories = ABPRF_Function::get_option( 'abprf_brand' );
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
					<button type="button" class="_btn_default" data-target-popup="#abprf_brand_popup"><span class="_mar_r_xs">➕</span><?php echo esc_html__( 'Add New', 'abprf-rental-forge' ) . ' ' . esc_html( ABPRF_Function::get_options( 'abprf_configuration', 'category_label', __( 'Category', 'abprf-rental-forge' ) ) ); ?></button>
					<?php
				}
			}
		}
		new ABPRF_Brand();
	}