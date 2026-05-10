<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPRF_ADMIN' ) ) {
		class ABPRF_ADMIN {
			public function __construct() {
				add_action( 'admin_menu', array( $this, 'admin_menu' ) );
				add_action( 'abprf_load_global', array( $this, 'load_global' ) );
			}

			public function admin_menu(): void {
				$abprf_configuration = ABPRF_Function::get_option( 'abprf_configuration' );
				$label               = isset( $abprf_configuration['label'] ) && $abprf_configuration['label'] ? $abprf_configuration['label'] : __( 'RentalForge', 'abprf-rental-forge' );
				$slug                = isset( $abprf_configuration['slug'] ) && $abprf_configuration['slug'] ? $abprf_configuration['slug'] : 'rental-forge';
				add_menu_page( $label, $label, 'manage_options', $slug, array( $this, 'load_main_page' ), 'dashicons-hammer', 6 );
			}

			public function load_main_page(): void {
				$abprf_info     = ABPRF_Query::get_info();
				$brand_label    = isset( $abprf_info['label'] ) && $abprf_info['label'] ? $abprf_info['label'] : __( 'RentalForge', 'abprf-rental-forge' );
				$category_label = isset( $abprf_info['category_label'] ) && $abprf_info['category_label'] ? $abprf_info['category_label'] : __( 'Category', 'abprf-rental-forge' );
				$brand_icon     = isset( $abprf_info['brand_icon'] ) && $abprf_info['brand_icon'] ? $abprf_info['brand_icon'] : 'fas fa-hammer';
				$total_post     = isset( $abprf_info['total_post'] ) && $abprf_info['total_post'] ? $abprf_info['total_post'] : 0;
				$total_property = isset( $abprf_info['total_property'] ) && $abprf_info['total_property'] ? $abprf_info['total_property'] : 0;
				$total_order    = isset( $abprf_info['total_order'] ) && $abprf_info['total_order'] ? $abprf_info['total_order'] : 0;
				$new_post_url   = isset( $abprf_info['new_post_url'] ) && $abprf_info['new_post_url'] ? $abprf_info['new_post_url'] : '';
				$active_tab     = filter_input( INPUT_GET, 'rf_tab', FILTER_SANITIZE_SPECIAL_CHARS );
				$active_tab     = $active_tab ?? 'dashboard';
				?>
                <div class="abprf_area  abprf_admin">
                    <div class="admin_head _fj_between">
                        <div class="head_brand _d_flex">
                            <div class="brand_icon _all_center_mar_r_xs"><?php ABPRF_Layout::image_icon( $brand_icon ); ?></div>
                            <div class="_fd_column">
                                <h4 class="_abprf"><?php echo esc_html( $brand_label ); ?></h4>
                                <span class="brand_version"><?php echo esc_html( get_plugin_data( ABPRF_PLUGIN_FILE ) ['Version'] ); ?></span>
                            </div>
                        </div>
                        <div class="_group_content">
                            <a class="_btn_default_mar_r" href="<?php echo esc_url( $new_post_url ); ?>"><span class="_mar_r_xs">➕</span><?php esc_html_e( 'Add New Post', 'abprf-rental-forge' ); ?></a>
                            <button type="button" class="_btn_default_mar_r" data-target-popup="#abprf_property_popup"><span class="_mar_r_xs">➕</span><?php esc_html_e( 'Add New Property', 'abprf-rental-forge' ); ?></button>
                            <button type="button" class="_btn_default" data-target-popup="#abprf_category_popup"><span class="_mar_r_xs">➕</span><?php echo esc_html__( 'Add New', 'abprf-rental-forge' ) . ' ' . esc_html( $category_label ); ?></button>
                        </div>
                    </div>
                    <div class="admin_menu _bg_info">
                        <div class="menu_list _d_flex">
                            <a href="<?php echo esc_url( add_query_arg( 'rf_tab', 'dashboard' ) ); ?>" class="_btn_light_info <?php echo esc_attr( $active_tab == 'dashboard' ? 'rf_active' : '' ); ?>"><span class="_mar_r_xs">📊</span><?php esc_html_e( 'Dashboard', 'abprf-rental-forge' ); ?></a>
                            <a href="<?php echo esc_url( add_query_arg( 'rf_tab', 'posts' ) ); ?>" class="_btn_light_info post_tab <?php echo esc_attr( $active_tab == 'posts' ? 'rf_active' : '' ); ?>"><?php ABPRF_Layout::image_icon( $brand_icon, '_mar_r_xs' ); ?><?php esc_html_e( 'Post Lists', 'abprf-rental-forge' ); ?><sup class="_mar_l_xs_circle_icon_xs"><?php echo esc_html( $total_post ); ?></sup></a>
                            <a href="<?php echo esc_url( add_query_arg( 'rf_tab', 'properties' ) ); ?>" class="_btn_light_info properties_tab <?php echo esc_attr( $active_tab == 'properties' ? 'rf_active' : '' ); ?>"><span class="_mar_r_xxs">🏠</span><?php esc_html_e( 'Properties', 'abprf-rental-forge' ); ?><sup class="_mar_l_xs_circle_icon_xs"><?php echo esc_html( $total_property ); ?></sup></a>
                            <a href="<?php echo esc_url( add_query_arg( 'rf_tab', 'orders' ) ); ?>" class="_btn_light_info <?php echo esc_attr( $active_tab == 'orders' ? 'rf_active' : '' ); ?>"><span class="_mar_r_xxs">📋</span><?php esc_html_e( 'Orders', 'abprf-rental-forge' ); ?><sup class="_mar_l_xs_circle_icon_xs"><?php echo esc_html( $total_order ); ?></sup></a>
                            <a href="<?php echo esc_url( add_query_arg( 'rf_tab', 'global' ) ); ?>" class="_btn_light_info <?php echo esc_attr( $active_tab == 'global' ? 'rf_active' : '' ); ?>"><span class="_mar_r_xxs">🌐</span><?php esc_html_e( 'Global Data', 'abprf-rental-forge' ); ?></a>
                            <a href="<?php echo esc_url( add_query_arg( 'rf_tab', 'configuration' ) ); ?>" class="_btn_light_info <?php echo esc_attr( $active_tab == 'configuration' ? 'rf_active' : '' ); ?>"><span class="_mar_r_xxs">⚙️</span><?php esc_html_e( 'Configuration', 'abprf-rental-forge' ); ?></a>
                            <a href="<?php echo esc_url( add_query_arg( 'rf_tab', 'status' ) ); ?>" class="_btn_light_info <?php echo esc_attr( $active_tab == 'status' ? 'rf_active' : '' ); ?>"><span class="_mar_r_xxs">🛡️</span><?php esc_html_e( 'Status', 'abprf-rental-forge' ); ?></a>
                            <?php do_action('abprf_add_admin_menu_tab',$active_tab); ?>
                        </div>
                    </div>
                    <div class="dashboard_content">
						<?php do_action( 'abprf_load_' . $active_tab, $abprf_info ); ?>
                    </div>
					<?php ABPRF_Layout::load_admin_globally( ); ?>
                </div>
				<?php
			}

			public function load_global($abprf_info): void {
				$category_label = isset( $abprf_info['category_label'] ) && $abprf_info['category_label'] ? $abprf_info['category_label'] : __( 'Category', 'abprf-rental-forge' );
				?>
                <div class="_reflex_6_abp_panel_max_1200_mar_auto">
                    <div class="abprf_tabs tab_top">
                        <ul class="_abprf tab_lists">
                            <li data-tabs-target="#abprf_global_dates"><span class="_mar_r_xxs">🗓️</span> <?php esc_html_e( 'Dates', 'abprf-rental-forge' ); ?></li>
                            <li data-tabs-target="#abprf_global_additional_service"><span class="_mar_r_xxs">💰</span> <?php esc_html_e( 'Additional services', 'abprf-rental-forge' ); ?></li>
                            <li data-tabs-target="#abprf_global_client_form"><span class="_mar_r_xxs">📋</span> <?php esc_html_e( 'Client Form', 'abprf-rental-forge' ); ?></li>
                            <li data-tabs-target="#abprf_global_tc"><span class="_mar_r_xxs">🤝</span> <?php esc_html_e( 'Term & Conditions', 'abprf-rental-forge' ); ?></li>
                            <li data-tabs-target="#abprf_global_faq"><span class="_mar_r_xxs">❓</span> <?php esc_html_e( 'FAQ', 'abprf-rental-forge' ); ?></li>
                            <li data-tabs-target="#abprf_global_category"><span class="_mar_r_xxs">🏘️</span><?php echo esc_html( $category_label ); ?></li>
                            <li data-tabs-target="#abprf_global_location"><span class="_mar_r_xxs">📍</span><?php esc_html_e( 'Location', 'abprf-rental-forge' ); ?></li>
                            <li data-tabs-target="#abprf_global_feature"><span class="_mar_r_xxs">🔗</span><?php esc_html_e( 'Feature', 'abprf-rental-forge' ); ?></li>
                            <li data-tabs-target="#abprf_global_brand"><span class="_mar_r_xxs">🏷️</span><?php esc_html_e( 'Brand', 'abprf-rental-forge' ); ?></li>
                        </ul>
                        <div class="tab_content _bg_white">
							<?php
								do_action( 'abprf_global_dates' );
								do_action( 'abprf_global_additional_service' );
								do_action( 'abprf_global_client_form' );
								do_action( 'abprf_global_tc' );
								do_action( 'abprf_global_faq' );
								do_action( 'abprf_global_category' ,$abprf_info);
								do_action( 'abprf_global_location');
								do_action( 'abprf_global_feature');
								do_action( 'abprf_global_brand');
							?>
                        </div>
                    </div>
                </div>
				<?php
			}
		}
		new ABPRF_ADMIN();
	}