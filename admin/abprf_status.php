<?php
	if ( ! defined( 'ABSPATH' ) ) {
		die;
	} // Cannot access pages directly.
	if ( ! class_exists( 'ABPRF_Status' ) ) {
		class ABPRF_Status {
			public function __construct() {
				add_action( 'abprf_load_status', array( $this, 'load_status' ) );
				//=============================//
				add_action( 'wp_ajax_abprf_install_and_active_wc', array( $this, 'install_and_active_wc' ) );
				add_action( 'wp_ajax_abprf_active_wc', array( $this, 'active_wc' ) );
				//=============================//
				add_action( 'wp_ajax_abprf_create_post_list_page', array( $this, 'create_post_list_page' ) );
				add_action( 'wp_ajax_abprf_create_property_list_page', array( $this, 'create_property_list_page' ) );
				//=============================//
				add_action( 'wp_ajax_abprf_import_dummy', array( $this, 'import_dummy' ) );
			}

			public function load_status($abprf_info): void {
				?>
                <div class="_reflex_6_abp_panel_max_1200_mar_auto abprf_status">
                    <div class="_panel_head">
                        <h4 class="_abprf"><span class="_mar_r_xxs">🛡️</span> <?php esc_html_e( 'Status  & Information', 'abprf-rental-forge' ); ?></h4>
                    </div>
                    <div class="_panel_body">
						<?php $this->version(); ?>
						<?php $this->wordpress(); ?>
						<?php $this->php(); ?>
						<?php $this->wc(); ?>
						<?php do_action( 'abprf_add_tools' ); ?>
						<?php $this->page_create($abprf_info); ?>
						<?php $this->dummy_import($abprf_info); ?>
                    </div>
                </div>
				<?php
			}

			//=============================//
			public function version(): void {
				?>
                <div class="_section_xs_mar_t_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abprf"> <?php esc_html_e( 'RentalForge Version', 'abprf-rental-forge' ) ?> </h6>
                        <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php echo esc_html( get_plugin_data( ABPRF_PLUGIN_FILE ) ['Version'] ); ?></button>
                    </div>
                </div>
				<?php
			}

			public function wordpress(): void {
				$version = get_bloginfo( 'version' );
				?>
                <div class="_section_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abprf"> <?php esc_html_e( 'WordPress Version', 'abprf-rental-forge' ); ?> </h6>
						<?php if ( $version > 5.5 ) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php echo esc_html( $version ); ?></button>
						<?php } else { ?>
                            <button class="_btn_light_warning_xs_min_125" type="button"><span class="fas fa-exclamation-triangle _mar_r_xxs"></span><?php echo esc_html( $version ); ?></button>
						<?php } ?>
                    </div>
                </div>
				<?php
			}

			public function php(): void {
				$version = phpversion();
				?>
                <div class="_section_xs">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abprf"> <?php esc_html_e( 'Php Version', 'abprf-rental-forge' ); ?> </h6>
						<?php if ( $version > 7.4 ) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php echo esc_html( $version ); ?></button>
						<?php } else { ?>
                            <button class="_btn_light_warning_xs_min_125" type="button"><span class="fas fa-exclamation-triangle _mar_r_xxs"></span><?php echo esc_html( $version ); ?></button>
						<?php } ?>
                    </div>
                </div>
				<?php
			}

			//=============================//
			public function wc(): void {
				$wc_status = ABPRF_Function::check_wc();
				$title     = $wc_status == 2 ? __( 'Woocommerce Plugin', 'abprf-rental-forge' ) : __( 'Woocommerce need to install and active', 'abprf-rental-forge' );
				$title     = $wc_status == 1 ? __( 'Woocommerce already installed but  not  activated', 'abprf-rental-forge' ) : $title;
				$name      = get_option( 'woocommerce_email_from_name' );
				$email     = get_option( 'woocommerce_email_from_address' );
				?>
                <form class="_section_xs" method="post" action="">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abprf"> <?php echo esc_html( $title ); ?></h6>
						<?php if ( $wc_status == 2 ) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php esc_html_e( 'Activated', 'abprf-rental-forge' ); ?></button>
						<?php } elseif ( $wc_status == 1 ) { ?>
                            <button class="_btn_theme_xs_min_125 active_wc" type="button"><span class="fas fa-tasks _mar_r_xxs"></span><?php esc_html_e( 'Active Now', 'abprf-rental-forge' ); ?></button>
						<?php } else { ?>
                            <button class="_btn_warning_xs_min_125 install_and_active_wc" type="button"><span class="fas fa-file-download _mar_r_xxs"></span><?php esc_html_e( 'Install & Active Now', 'abprf-rental-forge' ); ?></button>
						<?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
					<?php if ( $wc_status == 2 && defined( 'WC_VERSION' ) ) { ?>
                        <div class="_fa_center_fj_between">
                            <h6 class="_abprf"><?php esc_html_e( 'Woocommerce Version', 'abprf-rental-forge' ); ?></h6>
							<?php if ( version_compare( WC_VERSION, '8.0', '>' ) ) { ?>
                                <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php echo esc_html( WC_VERSION ); ?></button>
							<?php } else { ?>
                                <button class="_btn_light_warning_xs_min_125" type="button"><span class="fas fa-exclamation-triangle _mar_r_xxs"></span><?php echo esc_html( WC_VERSION ); ?></button>
							<?php } ?>
                        </div>
						<?php if ( ! empty( $name ) ) { ?>
                            <div class="_divider_xs"></div>
                            <div class="_fa_center_fj_between">
                                <h6 class="_abprf"><?php esc_html_e( 'Name', 'abprf-rental-forge' ); ?></h6>
                                <button class="_btn_light_success_xs_min_125" type="button"><?php echo esc_html( $name ); ?></button>
                            </div>
						<?php } ?>
						<?php if ( ! empty( $email ) ) { ?>
                            <div class="_divider_xs"></div>
                            <div class="_fa_center_fj_between">
                                <h6 class="_abprf"><?php esc_html_e( 'Email Address', 'abprf-rental-forge' ); ?></h6>
                                <button class="_btn_light_success_xs_min_125_text_inherit" type="button"><?php echo esc_html( $email ); ?></button>
                            </div>
						<?php } ?>
					<?php } else { ?>
                        <div class="_color_warning"><span class=" _abprf_mar_r_xxs  fas fa-exclamation-triangle"></span><?php esc_html_e( 'RentalForge is entirely dependent on the WooCommerce plugin. Please install and activate the WooCommerce plugin otherwise the plugin will not work. Installing this tool may take some time', 'abprf-rental-forge' ); ?></div>
					<?php } ?>
                </form>
				<?php
			}

			public function install_and_active_wc() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					include_once( ABSPATH . 'wp-admin/includes/plugin-install.php' );
					include_once( ABSPATH . 'wp-admin/includes/file.php' );
					include_once( ABSPATH . 'wp-admin/includes/misc.php' );
					include_once( ABSPATH . 'wp-admin/includes/class-wp-upgrader.php' );
					$plugin             = 'woocommerce';
					$api                = plugins_api( 'plugin_information', array(
						'slug' => $plugin,
						'fields' => array(
							'short_description' => false,
							'sections' => false,
							'requires' => false,
							'rating' => false,
							'ratings' => false,
							'downloaded' => false,
							'last_updated' => false,
							'added' => false,
							'tags' => false,
							'compatibility' => false,
							'homepage' => false,
							'donate_link' => false,
						),
					) );
					$title              = 'title';
					$url                = 'url';
					$nonce              = 'nonce';
					$woocommerce_plugin = new Plugin_Upgrader( new Plugin_Installer_Skin( compact( 'title', 'url', 'nonce', 'plugin', 'api' ) ) );
					$woocommerce_plugin->install( $api->download_link );
					activate_plugin( 'woocommerce/woocommerce.php' );
                    esc_html_e('Woocommerce DownloadedAnd Installed successfully.....','abprf-rental-forge');
				}
				wp_die();
			}

			public function active_wc() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					if ( is_dir( ABSPATH . 'wp-content/plugins/woocommerce' ) ) {
						activate_plugin( 'woocommerce/woocommerce.php' );
						esc_html_e('Woocommerce Installed successfully.....','abprf-rental-forge');
					}
				}
				wp_die();
			}

			//=============================//
			public function page_create($abprf_info): void {
				$label         = isset( $abprf_info['label'] ) && $abprf_info['label'] ? $abprf_info['label'] : __( 'RentalForge', 'abprf-rental-forge' );
				?>
                <form class="_section_xs" method="post" action="">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abprf"><?php echo esc_html($label).' '.esc_html_e( 'List Page', 'abprf-rental-forge' ); ?></h6>
						<?php if ( ABPRF_Function::get_page_by_slug( 'rf_post_list' ) ) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php esc_html_e( 'Activated', 'abprf-rental-forge' ); ?></button>
						<?php } else { ?>
                            <button class="_btn_warning_xs_min_125 create_post_list_page" type="button"><span class="fas fa-plus _mar_r_xxs"></span><?php esc_html_e( 'Add RentalForge List Page', 'abprf-rental-forge' ); ?></button>
						<?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fa_center_fj_between">
                        <h6 class="_abprf"><?php esc_html_e( 'Property List Page', 'abprf-rental-forge' ); ?></h6>
						<?php if ( ABPRF_Function::get_page_by_slug( 'rf_property_list' ) ) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php esc_html_e( 'Activated', 'abprf-rental-forge' ); ?></button>
						<?php } else { ?>
                            <button class="_btn_warning_xs_min_125 create_property_list_page" type="button"><span class="fas fa-plus _mar_r_xxs"></span><?php esc_html_e( 'Add Property List Page', 'abprf-rental-forge' ); ?></button>
						<?php } ?>
                    </div>
					<?php do_action( 'abprf_page_create' ); ?>
                </form>
				<?php
			}

			public function create_post_list_page() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					if ( ! ABPRF_Function::get_page_by_slug( 'rf_post_list' ) ) {
						$configuration                = ABPRF_Function::get_option( 'abprf_configuration' );
						$label=isset( $configuration['label'] ) && $configuration['label'] ? $configuration['label'] : __( 'RentalForge', 'abprf-rental-forge' );
						$page = array(
							'post_type' => 'page',
							'post_name' => 'rf_post_list',
							'post_title' => $label.' '.__( 'List', 'abprf-rental-forge' ),
							'post_content' => '[abprf-post]',
							'post_status' => 'publish',
						);
						wp_insert_post( $page );
						flush_rewrite_rules();
						echo esc_html($label).' '.esc_html_e('Page Created successfully.....','abprf-rental-forge');
					}
				}
				wp_die();
			}

			public function create_property_list_page() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					if ( ! ABPRF_Function::get_page_by_slug( 'rf_property_list' ) ) {
						$configuration                = ABPRF_Function::get_option( 'abprf_configuration' );
						$label=isset( $configuration['label'] ) && $configuration['label'] ? $configuration['label'] : __( 'RentalForge', 'abprf-rental-forge' );
						$page = array(
							'post_type' => 'page',
							'post_name' => 'rf_property_list',
							'post_title' => $label.' '.__( 'Property List', 'abprf-rental-forge' ),
							'post_content' => '[abprf-property]',
							'post_status' => 'publish',
						);
						wp_insert_post( $page );
						flush_rewrite_rules();
                        echo esc_html($label).' '.esc_html_e('Property Page Created successfully.....','abprf-rental-forge');
					}
				}
				wp_die();
			}

			//=============================//
			public function dummy_import($abprf_info): void {
				$total = sizeof( isset( $abprf_info['post_ids'] ) && $abprf_info['post_ids'] ? $abprf_info['post_ids'] :ABPRF_Query::get_post_id() );
				?>
                <form class="_section_xs" method="post" action="">
                    <div class="_fa_center_fj_between">
                        <h6 class="_abprf"> <?php esc_html_e( 'Number of Post', 'abprf-rental-forge' ); ?> </h6>
						<?php if ( $total > 0 ) { ?>
                            <button class="_btn_light_success_xs_min_125" type="button"><span class="fas fa-check _mar_r_xxs"></span><?php echo esc_html( $total ); ?></button>
						<?php } else { ?>
                            <button class="_btn_light_warning_xs_min_125" type="button"><span class="fas fa-exclamation-triangle _mar_r_xxs"></span><?php esc_html_e( 'Can Not Find Post', 'abprf-rental-forge' ); ?></button>
						<?php } ?>
                    </div>
                    <div class="_divider_xs"></div>
                    <div class="_fa_center_fj_between">
                        <h6 class="_abprf"> <?php esc_html_e( 'Dummy Import', 'abprf-rental-forge' ); ?> </h6>
                        <button class="<?php echo esc_attr( $total > 0 ? '_btn_light_success_xs' : '_btn_warning_xs' ); ?>_btn_theme_min_125 import_dummy" type="button"><span class="fas fa-plus _mar_r_xxs"></span><?php esc_html_e( 'Add New Dummy Post', 'abprf-rental-forge' ); ?></button>
                    </div>
                </form>
				<?php
			}

			public function import_dummy() {
				if ( is_admin() && check_ajax_referer( 'abprf_admin_ajax_nonce', 'nonce' ) && current_user_can( 'manage_options' ) ) {
					//$this->add_data( $this->dummy_data() );
					flush_rewrite_rules();
				}
				wp_die();
			}

			public static function add_data( $dummy_infos ): void {
				if ( array_key_exists( 'taxonomy', $dummy_infos ) ) {
					foreach ( $dummy_infos['taxonomy'] as $taxonomy => $taxonomy_option ) {
						if ( taxonomy_exists( $taxonomy ) ) {
							$check_terms = get_terms( array( 'taxonomy' => $taxonomy, 'hide_empty' => false ) );
							if ( is_string( $check_terms ) || sizeof( $check_terms ) == 0 ) {
								foreach ( $taxonomy_option as $taxonomy_data ) {
									unset( $term );
									$term = wp_insert_term( $taxonomy_data['name'], $taxonomy );
								}
							}
						}
					}
				}
				if ( array_key_exists( 'options', $dummy_infos ) ) {
					foreach ( $dummy_infos['options'] as $option => $dummy_option ) {
						$option_data = get_option( $option );
						if ( ! $option_data || sizeof( $option_data ) == 0 ) {
							update_option( $option, $dummy_option );
						}
					}
				}
				if ( array_key_exists( 'custom_post', $dummy_infos ) ) {
					foreach ( $dummy_infos['custom_post'] as $custom_post => $dummy_post ) {
						foreach ( $dummy_post as $dummy_data ) {
							$args = array();
							if ( isset( $dummy_data['name'] ) ) {
								$args['post_title'] = $dummy_data['name'];
							}
							$args['post_status'] = 'publish';
							$args['post_type']   = $custom_post;
							$post_id             = wp_insert_post( $args );
							if ( array_key_exists( 'post_data', $dummy_data ) ) {
								foreach ( $dummy_data['post_data'] as $meta_key => $data ) {
									update_post_meta( $post_id, $meta_key, $data );
								}
							}
						}
					}
				}
			}

			public function dummy_data(): array {
				return [
					'taxonomy' => [
						'abprf_category' => [
							0 => [ 'name' => 'AC' ],
							1 => [ 'name' => 'Non AC' ],
							2 => [ 'name' => 'AC Sleeper' ],
						],
					],
					'options' => [
						'abprf_additional' => ABPRF_Layout::static_additional(),
						'abprf_traveller_pattern' => ABPRF_Layout::static_form(),
					],
					'custom_post' => [
						'abprf_post' => [
							0 => [
								'name' => 'Bucharest-Izmail',
								'post_data' => [
									//General
									'display_equipment_id' => 'on',
									'equipment_id' => wp_rand( 100, 999 ),
									'display_category' => 'on',
									'category' => 'AC',
									'rent_continue' => 'on',
									//Date_settings
									'date_type' => 'periodic_date',
									'specific_dates' => [],
									'periodic_start_date' => gmdate( 'Y-m-d', strtotime( ' +1 day' ) ),
									'periodic_end_date' => '',
									'periodic_after' => 1,
									'advance_date_number' => 15,
									'weekend' => 'sunday',
									'specific_off_dates' => [
										gmdate( 'Y-m-d', strtotime( ' +15 day' ) ),
									],
									'off_date_range' => [
										0 => [
											'from' => gmmktime( 'Y-m-d', strtotime( ' +25 day' ) ),
											'to' => gmdate( 'Y-m-d', strtotime( ' +28 day' ) ),
										],
									],
									//seat_settings
									'display_ticket_type' => '',
									'ticket_type' => '',
									'seat_type' => 'seat_plan',
									'ld_rows' => '12',
									'ld_columns' => '5',
									'display_ud' => '',
									'ud_infos' => [],
									'ud_rows' => '',
									'ud_columns' => '',
									'total_seat' => '40',
									//Route_settings
									'routing_infos' => [
										0 => [ 'stop' => 'A', 'type' => 'bp', 'time' => '08:00' ],
										1 => [ 'stop' => 'B', 'type' => 'bp', 'time' => '09:00' ],
										2 => [ 'stop' => 'C', 'type' => 'bp', 'time' => '11:00' ],
										3 => [ 'stop' => 'D ', 'type' => 'both', 'time' => '12:00' ],
										4 => [ 'stop' => 'E', 'type' => 'both', 'time' => '14:00' ],
										5 => [ 'stop' => 'F', 'type' => 'dp', 'time' => '15:45' ],
										6 => [ 'stop' => 'G', 'type' => 'dp', 'time' => '17:00' ],
									],
									'route_direction' => [ 'A', 'B', 'C', 'D', 'E', 'F', 'G' ],
									//price_settings
									'price_infos' => [
										0 => [ 'bp' => 'A', 'dp' => 'D ', 'price' => '750', 'adult' => '', 'child' => '', 'infant' => '' ],
										1 => [ 'bp' => 'A', 'dp' => 'E', 'price' => '850', 'adult' => '', 'child' => '', 'infant' => '' ],
										2 => [ 'bp' => 'A', 'dp' => 'F', 'price' => '1000', 'adult' => '', 'child' => '', 'infant' => '' ],
										3 => [ 'bp' => 'A', 'dp' => 'G', 'price' => '1200', 'adult' => '', 'child' => '', 'infant' => '' ],
										4 => [ 'bp' => 'B', 'dp' => 'D ', 'price' => '1100', 'adult' => '', 'child' => '', 'infant' => '' ],
										5 => [ 'bp' => 'B', 'dp' => 'E', 'price' => '900', 'adult' => '', 'child' => '', 'infant' => '' ],
										6 => [ 'bp' => 'B', 'dp' => 'F', 'price' => '800', 'adult' => '', 'child' => '', 'infant' => '' ],
										7 => [ 'bp' => 'B', 'dp' => 'G', 'price' => '700', 'adult' => '', 'child' => '', 'infant' => '' ],
										8 => [ 'bp' => 'C', 'dp' => 'D ', 'price' => '1000', 'adult' => '', 'child' => '', 'infant' => '' ],
										9 => [ 'bp' => 'C', 'dp' => 'E', 'price' => '900', 'adult' => '', 'child' => '', 'infant' => '' ],
										10 => [ 'bp' => 'C', 'dp' => 'F', 'price' => '800', 'adult' => '', 'child' => '', 'infant' => '' ],
										11 => [ 'bp' => 'C', 'dp' => 'G', 'price' => '700', 'adult' => '', 'child' => '', 'infant' => '' ],
										12 => [ 'bp' => 'D ', 'dp' => 'E', 'price' => '800', 'adult' => '', 'child' => '', 'infant' => '' ],
										13 => [ 'bp' => 'D ', 'dp' => 'F', 'price' => '600', 'adult' => '', 'child' => '', 'infant' => '' ],
										14 => [ 'bp' => 'D ', 'dp' => 'G', 'price' => '300', 'adult' => '', 'child' => '', 'infant' => '' ],
										15 => [ 'bp' => 'E ', 'dp' => 'F', 'price' => '400', 'adult' => '', 'child' => '', 'infant' => '' ],
										16 => [ 'bp' => 'E ', 'dp' => 'G', 'price' => '300', 'adult' => '', 'child' => '', 'infant' => '' ],
									],
									//Reg form
									'display_client_form' => 'on',
									'display_single_form' => 'on',
									'abprf_forms' => ABPRF_Layout::static_form(),
									//additional service
									'display_additional_services' => 'on',
									'additional_services' => ABPRF_Layout::static_additional(),
									//slider_settings
									'display_slider' => 'on',
									'abprf_sliders' => [ 200, 300, 400, 500, 600, 700, 800, 900, 1000 ],
								]
							],
						]
					]
				];
			}
		}
		new ABPRF_Status();
	}