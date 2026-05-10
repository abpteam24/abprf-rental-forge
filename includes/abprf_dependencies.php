<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	if ( ! class_exists( 'ABPRF_Dependencies' ) ) {
		class ABPRF_Dependencies {
			public function __construct() {
				add_action( 'admin_enqueue_scripts', array( $this, 'admin_enqueue' ), 90 );
				add_action( 'wp_enqueue_scripts', array( $this, 'frontend_enqueue' ), 90 );
				$this->load_file();
				add_action( 'init', [ $this, 'register_cpt' ] );
				add_filter( 'use_block_editor_for_post_type', [ $this, 'disable_gutenberg' ], 10, 2 );
				add_filter( 'plugin_action_links', array( $this, 'plugin_settings_link' ), 10, 2 );
				add_action( 'upgrader_process_complete', [ $this, 'flush_rewrite' ] );
				add_action( 'activated_plugin', array( $this, 'activation_redirect' ) );
			}

			public function admin_enqueue(): void {
				$configuration = ABPRF_Function::get_option( 'abprf_configuration' );
				$label         = isset( $configuration['label'] ) && $configuration['label'] ? $configuration['label'] : __( 'RentalForge', 'abprf-rental-forge' );
				$this->lib_enqueue();
				wp_enqueue_editor();
				wp_enqueue_media();
				//admin script
				wp_enqueue_script( 'jquery-ui-sortable' );
				wp_enqueue_style( 'wp-color-picker' );
				wp_enqueue_script( 'wp-color-picker' );
				wp_enqueue_style( 'wp-codemirror' );
				wp_enqueue_script( 'wp-codemirror' );
				//=============================//
				wp_enqueue_script( 'abprf_admin', ABPRF_URL . '/assets/js/abprf_admin.js', array( 'jquery' ), time(), true );
				wp_localize_script( 'abprf_admin', 'abprf_admin_data', [
					'ajax_url' => admin_url( 'admin-ajax.php' ),
					'nonce' => wp_create_nonce( 'abprf_admin_ajax_nonce' ),
					'icon_url' => ABPRF_URL . '/assets/js/abprf_icons.json',
					'msg' => [
						'confirm_delete' => __( 'Are you sure you want to delete this item?', 'abprf-rental-forge' ),
						'confirm_ok' => __( '1. Ok : To Remove Item .', 'abprf-rental-forge' ),
						'confirm_cancel' => __( '2. Cancel : To Cancel .', 'abprf-rental-forge' ),
						'saving' => __( 'Saving.............!', 'abprf-rental-forge' ),
						'saved' => __( 'Saved...............!', 'abprf-rental-forge' ),
						'importing' => __( 'Importing........', 'abprf-rental-forge' ),
						'imported' => __( 'Imported Successfully............. !', 'abprf-rental-forge' ),
						'loading' => __( 'Loading........', 'abprf-rental-forge' ),
						'loaded' => __( 'Loaded Successfully............. !', 'abprf-rental-forge' ),
						'order_loading' => __( 'Order Loading........ !', 'abprf-rental-forge' ),
						'error' => __( 'An error occurred. Please try again.', 'abprf-rental-forge' ),
						'deleting' => __( 'Deleting.............', 'abprf-rental-forge' ),
						'delete_success' => __( 'Delete Successfully.............', 'abprf-rental-forge' ),
						'property_loading' => __( 'Property List Loading.............', 'abprf-rental-forge' ),
						'property_loading_success' => __( 'Property List already Loaded !', 'abprf-rental-forge' ),
						'post_loading' => __( 'Post List Loading.............', 'abprf-rental-forge' ),
						'post_loading_success' => __( 'Post List already Loaded !', 'abprf-rental-forge' ),
						'post_deleting' => __( 'Post Permanent Deleting.........!', 'abprf-rental-forge' ),
						'post_delete_success' => __( 'Post Delete successfully!', 'abprf-rental-forge' ),
						'post_trashing' => __( 'Post move to Trashing.........!', 'abprf-rental-forge' ),
						'post_trash_success' => __( 'Post move to trashed successfully!', 'abprf-rental-forge' ),
						'post_restoring' => __( 'Post Restoring.........!', 'abprf-rental-forge' ),
						'post_restored' => __( 'Post Restored successfully!', 'abprf-rental-forge' ),
						'wc_install' => __( 'Woocommerce Downloading And Installing.........!', 'abprf-rental-forge' ),
						'wc_installing' => __( 'Woocommerce  Installing.........!', 'abprf-rental-forge' ),
						'create_post_page' => $label . ' ' . __( 'Post Page Creating ........!', 'abprf-rental-forge' ),
						'create_property_page' => $label . ' ' . __( 'Property Page Creating ........!', 'abprf-rental-forge' ),
					],
				] );
				wp_enqueue_style( 'abprf_admin', ABPRF_URL . '/assets/css/abprf_admin.css', array(), time() );
				//=============================//
				$this->global_enqueue();
				do_action( 'abprf_admin_enqueue' );
			}

			public function frontend_enqueue(): void {
				if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
					wp_enqueue_script( 'wc-checkout' );
					wp_enqueue_style( 'select2' );
					wp_enqueue_script( 'select2' );
				}
				$this->lib_enqueue();
				$this->global_enqueue();
				do_action( 'abprf_frontend_enqueue' );
			}

			public function lib_enqueue(): void {
				wp_enqueue_script( 'jquery' );
				wp_enqueue_script( 'jquery-ui-core' );
				wp_enqueue_script( 'jquery-ui-datepicker' );
				wp_enqueue_style( 'abprf_jquery_ui', ABPRF_URL . '/assets/css/jquery-ui.min.css', array(), '1.13.2' );
				wp_enqueue_style( 'abprf_font_awesome', ABPRF_URL . '/assets/css/font_awesome.min.css', array(), '5.15.4' );
				wp_enqueue_style( 'abprf_lib', ABPRF_URL . '/assets/css/abprf_lib.css', array(), time() );
				wp_enqueue_script( 'abprf_lib', ABPRF_URL . '/assets/js/abprf_lib.js', array( 'jquery' ), time(), true );
				if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
					wp_localize_script( 'abprf_lib', 'abprf_var', [
						'currency_symbol' => get_woocommerce_currency_symbol(),
						'currency_position' => get_option( 'woocommerce_currency_pos' ),
						'currency_decimal' => wc_get_price_decimal_separator(),
						'thousands_separator' => wc_get_price_thousand_separator(),
						'decimal_num' => ABPRF_Function::get_option( 'woocommerce_price_num_decimals', 2 ),
						'currency_suffix' => ABPRF_Function::get_option( 'woocommerce_price_display_suffix', '' ),
						'blank_image' => ABPRF_BLANK_IMG_URL,
						'date_format' => ABPRF_Function::get_options( 'abprf_configuration', 'date_format', 'D d M , yy' ),
					] );
				} else {
					wp_localize_script( 'abprf_lib', 'abprf_var', [
						'currency_symbol' => '',
						'currency_position' => '',
						'currency_decimal' => '',
						'thousands_separator' => '',
						'decimal_num' => '',
						'wc_suffix' => '',
						'blank_image' => ABPRF_BLANK_IMG_URL,
						'date_format' => ABPRF_Function::get_options( 'abprf_configuration', 'date_format', 'D d M , yy' ),
					] );
				}
			}

			public function global_enqueue(): void {
				wp_enqueue_style( 'abprf', ABPRF_URL . '/assets/css/abprf.css', array(), time() );
				do_action( 'abprf_global_script' );
				$abprf_css_var   = ABPRF_Function::get_option( 'abprf_css_var' );
				$default_color   = isset( $abprf_css_var['color_default'] ) && $abprf_css_var['color_default'] ? $abprf_css_var['color_default'] : '#303030';
				$color_theme     = isset( $abprf_css_var['color_theme'] ) && $abprf_css_var['color_theme'] ? $abprf_css_var['color_theme'] : '#95951c';
				$color_theme_ee  = $color_theme . 'ee';
				$color_theme_cc  = $color_theme . 'cc';
				$color_theme_aa  = $color_theme . 'aa';
				$color_theme_88  = $color_theme . '88';
				$color_theme_77  = $color_theme . '77';
				$alternate_color = isset( $abprf_css_var['color_theme_alternate'] ) && $abprf_css_var['color_theme_alternate'] ? $abprf_css_var['color_theme_alternate'] : '#fff';
				$color_warning   = isset( $abprf_css_var['color_warning'] ) && $abprf_css_var['color_warning'] ? $abprf_css_var['color_warning'] : '#E67C30';
				$bg_section      = isset( $abprf_css_var['bg_section'] ) && $abprf_css_var['bg_section'] ? $abprf_css_var['bg_section'] : '#FAFCFE';
				$default_br      = isset( $abprf_css_var['br_default'] ) && $abprf_css_var['br_default'] ? $abprf_css_var['br_default'] . 'px' : '0px';
				$fs_h1           = isset( $abprf_css_var['fs_h1'] ) && $abprf_css_var['fs_h1'] ? $abprf_css_var['fs_h1'] . 'px' : '35px';
				$fs_h2           = isset( $abprf_css_var['fs_h2'] ) && $abprf_css_var['fs_h2'] ? $abprf_css_var['fs_h2'] . 'px' : '30px';
				$fs_h3           = isset( $abprf_css_var['fs_h3'] ) && $abprf_css_var['fs_h3'] ? $abprf_css_var['fs_h3'] . 'px' : '25px';
				$fs_h4           = isset( $abprf_css_var['fs_h4'] ) && $abprf_css_var['fs_h4'] ? $abprf_css_var['fs_h4'] . 'px' : '20px';
				$fs_h5           = isset( $abprf_css_var['fs_h5'] ) && $abprf_css_var['fs_h5'] ? $abprf_css_var['fs_h5'] . 'px' : '17px';
				$fs_h6           = isset( $abprf_css_var['fs_h6'] ) && $abprf_css_var['fs_h6'] ? $abprf_css_var['fs_h6'] . 'px' : '15px';
				$fs_label        = isset( $abprf_css_var['fs_label'] ) && $abprf_css_var['fs_label'] ? $abprf_css_var['fs_label'] . 'px' : '14px';
				$default_fs      = isset( $abprf_css_var['fs_default'] ) && $abprf_css_var['fs_default'] ? $abprf_css_var['fs_default'] . 'px' : '12px';
				$button_fs       = isset( $abprf_css_var['fs_button'] ) && $abprf_css_var['fs_button'] ? $abprf_css_var['fs_button'] . 'px' : '14px';
				$bg_button       = isset( $abprf_css_var['bg_button'] ) && $abprf_css_var['bg_button'] ? $abprf_css_var['bg_button'] : '#007CBA';
				$color_button    = isset( $abprf_css_var['color_button'] ) && $abprf_css_var['color_button'] ? $abprf_css_var['color_button'] : $alternate_color;
				$off             = __( 'OFF', 'abprf-rental-forge' );
				$on              = __( 'ON', 'abprf-rental-forge' );
				$abprf_var       =
					":root {
						--rf_br: {$default_br};						
						--rf_text_off:'{$off}';
						--rf_text_on: '{$on}';
						--rf_fs: {$default_fs};						
						--rf_fs_small: 11px;
						--rf_fs_label: {$fs_label};
						--rf_fs_h6: {$fs_h6};
						--rf_fs_h5: {$fs_h5};
						--rf_fs_h4: {$fs_h4};
						--rf_fs_h3: {$fs_h3};
						--rf_fs_h2: {$fs_h2};
						--rf_fs_h1: {$fs_h1};						
						--rf_button_bg: {$bg_button};
						--rf_button_color: {$color_button};
						--rf_button_fs: {$button_fs};
						--rf_button_height: 40px;
						--rf_button_height_xs: 30px;
						--rf_button_width: 120px;
						--rf_color_default: {$default_color};
						--rf_color_border: #DDD;
						--rf_color_active: #0E6BB7;
						--rf_color_section: {$bg_section};
						--rf_color_theme: {$color_theme};
						--rf_color_theme_ee: {$color_theme_ee};
						--rf_color_theme_cc: {$color_theme_cc};
						--rf_color_theme_aa: {$color_theme_aa};
						--rf_color_theme_88: {$color_theme_88};
						--rf_color_theme_77: {$color_theme_77};
						--rf_color_theme_alter: {$alternate_color};
						--rf_color_warning:{$color_warning};						
					}";
				wp_add_inline_style( 'abprf', $abprf_var );
			}

			private function load_file(): void {
				require_once ABPRF_DIR . '/includes/abprf_function.php';
				require_once ABPRF_DIR . '/includes/abprf_query.php';
				require_once ABPRF_DIR . '/includes/abprf_layout.php';
				if ( is_admin() ) {
					require_once ABPRF_DIR . '/admin/abprf_admin.php';
					require_once ABPRF_DIR . '/admin/abprf_post.php';
					require_once ABPRF_DIR . '/admin/abprf_dashboard.php';
					require_once ABPRF_DIR . '/admin/abprf_properties.php';
					require_once ABPRF_DIR . '/admin/abprf_orders.php';
					require_once ABPRF_DIR . '/admin/abprf_dates.php';
					require_once ABPRF_DIR . '/admin/abprf_additional.php';
					require_once ABPRF_DIR . '/admin/abprf_form.php';
					require_once ABPRF_DIR . '/admin/abprf_faq_tc.php';
					require_once ABPRF_DIR . '/admin/abprf_configuration.php';
					require_once ABPRF_DIR . '/admin/abprf_status.php';
					require_once ABPRF_DIR . '/admin/abprf_category.php';
					require_once ABPRF_DIR . '/admin/abprf_location.php';
					require_once ABPRF_DIR . '/admin/abprf_brand.php';
					require_once ABPRF_DIR . '/admin/abprf_feature.php';
				}
				if ( in_array( 'woocommerce/woocommerce.php', get_option( 'active_plugins' ) ) ) {
					require_once ABPRF_DIR . '/includes/abprf_hooks.php';
					require_once ABPRF_DIR . '/includes/abprf_ajax.php';
					require_once ABPRF_DIR . '/includes/abprf_frontend.php';
					require_once ABPRF_DIR . '/includes/abprf_shortcodes.php';
					require_once ABPRF_DIR . '/includes/abprf_woocommerce.php';
					require_once ABPRF_DIR . '/admin/abprf_hidden_post.php';
				}
			}

			public function register_cpt(): void {
				$configuration = ABPRF_Function::get_option( 'abprf_configuration' );
				$cpt           = ABPRF_Function::get_cpt();
				$label         = isset( $configuration['label'] ) && $configuration['label'] ? $configuration['label'] : __( 'RentalForge', 'abprf-rental-forge' );
				$slug          = isset( $configuration['slug'] ) && $configuration['slug'] ? $configuration['slug'] : 'rental-forge';
				$icon          = isset( $configuration['icon'] ) && $configuration['icon'] ? $configuration['icon'] : 'dashicons-hammer';
				$labels        = [
					'name' => esc_html( $label ),
					'singular_name' => esc_html( $label ),
					'menu_name' => esc_html( $label ),
					'name_admin_bar' => esc_html( $label ),
					'archives' => __( 'Post List', 'abprf-rental-forge' ),
					'attributes' => __( 'Post List', 'abprf-rental-forge' ),
					'parent_item_colon' => __( 'Post Item:', 'abprf-rental-forge' ),
					'all_items' => __( 'Post', 'abprf-rental-forge' ),
					'add_new_item' => __( 'Add Post', 'abprf-rental-forge' ),
					'add_new' => __( 'Add Post', 'abprf-rental-forge' ),
					'new_item' => __( 'Add Post', 'abprf-rental-forge' ),
					'edit_item' => __( 'Edit Post', 'abprf-rental-forge' ),
					'update_item' => __( 'Update Post', 'abprf-rental-forge' ),
					'view_item' => __( 'View Post', 'abprf-rental-forge' ),
					'view_items' => __( 'View Post', 'abprf-rental-forge' ),
					'search_items' => __( 'Search Post', 'abprf-rental-forge' ),
					'not_found' => __( 'Post Not Found', 'abprf-rental-forge' ),
					'not_found_in_trash' => __( 'Post Not found in Trash', 'abprf-rental-forge' ),
					'featured_image' => __( 'Post Image', 'abprf-rental-forge' ),
					'set_featured_image' => __( 'Post Image', 'abprf-rental-forge' ),
					'remove_featured_image' => __( 'Remove Post Image', 'abprf-rental-forge' ),
					'use_featured_image' => __( 'Use image Post as featured image', 'abprf-rental-forge' ),
					'insert_into_item' => __( 'Insert  Post', 'abprf-rental-forge' ),
					'uploaded_to_this_item' => __( 'Uploaded  Post', 'abprf-rental-forge' ),
					'items_list' => __( 'Post List', 'abprf-rental-forge' ),
					'items_list_navigation' => __( 'Category list navigation', 'abprf-rental-forge' ),
					'filter_items_list' => __( 'Filter Post List', 'abprf-rental-forge' )
				];
				$args          = [
					'public' => true,
					'labels' => $labels,
					'menu_icon' => esc_html( $icon ),
					'supports' => [ 'title', 'editor', 'thumbnail' ],
					'rewrite' => [ 'slug' => esc_html( $slug ), 'with_front' => true, 'pages' => true, 'feeds' => true, ],
					'show_in_rest' => true,
					'rest_base' => 'abprf_post',
					'capability_type' => 'post',
					'publicly_queryable' => true,  // you should be able to query it
					'show_ui' => true,  // you should be able to edit it in wp-admin
					'show_in_menu' => false,
					'exclude_from_search' => true,  // you should exclude it from search results
					'show_in_nav_menus' => true,  // you should be able to add it to menus
					'has_archive' => true,  // it should have archive page
				];
				register_post_type( $cpt, $args );
				$category_label = isset( $configuration['category_label'] ) && $configuration['category_label'] ? $configuration['category_label'] : __( 'Category', 'abprf-rental-forge' );
				$category_slug  = isset( $configuration['cat_slug'] ) && $configuration['cat_slug'] ? $configuration['cat_slug'] : 'cat_rent';
				$full_text      = $label . ' ' . $category_label;
				$label_category = array(
					'name' => $full_text,
					'singular_name' => $full_text,
				);
				$args           = [
					'hierarchical' => true,
					"public" => true,
					'labels' => $label_category,
					'show_ui' => true,
					'show_admin_column' => false,
					'show_in_menu' => false,
					'query_var' => true,
					'rewrite' => [ 'slug' => $category_slug ],
					'show_in_rest' => true,
					'rest_base' => 'abprf_category',
					'meta_box_cb' => false,
				];
				register_taxonomy( 'abprf_category', $cpt, $args );
				$full_text      = $label . ' ' . __( 'Locations', 'abprf-rental-forge' );
				$label_location = array(
					'name' => $full_text,
					'singular_name' => $full_text,
				);
				$args           = [
					'hierarchical' => true,
					"public" => true,
					'labels' => $label_location,
					'show_ui' => true,
					'show_admin_column' => false,
					'show_in_menu' => false,
					'query_var' => true,
					'rewrite' => [ 'slug' => 'loc_rent' ],
					'show_in_rest' => true,
					'rest_base' => 'abprf_location',
					'meta_box_cb' => false,
				];
				register_taxonomy( 'abprf_location', $cpt, $args );
				$full_text      = $label . ' ' . __( 'Features', 'abprf-rental-forge' );
				$label_brand = array(
					'name' => $full_text,
					'singular_name' => $full_text,
				);
				$args           = [
					'hierarchical' => true,
					"public" => true,
					'labels' => $label_brand,
					'show_ui' => true,
					'show_admin_column' => false,
					'show_in_menu' => false,
					'query_var' => true,
					'rewrite' => [ 'slug' => 'fec_rent' ],
					'show_in_rest' => true,
					'rest_base' => 'abprf_feature',
					'meta_box_cb' => false,
				];
				register_taxonomy( 'abprf_brand', $cpt, $args );
				flush_rewrite_rules();
			}

			public static function activation() {
				self::create_table();
				flush_rewrite_rules();
			}

			public static function deactivate() {
				flush_rewrite_rules();
			}

			public static function create_table() {
				global $wpdb;
				$order_table      = $wpdb->prefix . 'abprf_orders';
				$order_table_old  = $wpdb->prefix . 'abprf_orders_old';
				$property_table   = $wpdb->prefix . 'abprf_property';
				$collate          = $wpdb->has_cap( 'collation' ) ? $wpdb->get_charset_collate() : '';
				$abprf_orders     = "CREATE TABLE IF NOT EXISTS $order_table (
																	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
																	order_id BIGINT UNSIGNED NOT NULL ,
																	item_id BIGINT UNSIGNED NOT NULL,
									                                 post_id BIGINT UNSIGNED NOT NULL,
									                                 user_id BIGINT UNSIGNED NOT NULL,  
									                                 property_id JSON NOT NULL,
									                                 ex_id JSON NOT NULL,
																    pick_up varchar(100)  NULL,
									                                start_time TIMESTAMP NULL,
																    drop_off varchar(100)  NULL,
									                                end_time TIMESTAMP NULL,
     																book_from TIMESTAMP  NULL,							    
																    book_to TIMESTAMP NULL,
    																category varchar(20)  NULL,
    																location varchar(20)  NULL,
    																brand varchar(20)  NULL,
    																price_info JSON NOT NULL,
																    property_info JSON NOT NULL,
																    ex_info JSON NOT NULL,
																    pass_info JSON NOT NULL,
																    delivery_option varchar(20) NULL,
																    book_status varchar(20) NOT NULL,
																    order_status varchar(20) NOT NULL,
																    payment_method varchar(100)  NULL,
    									                        	billing_name varchar(100)  NULL,
																    billing_email varchar(100)  NULL,																    
																    billing_phone varchar(20)  NULL,
																    billing_address varchar(255)  NULL,
																    others JSON NULL,
																     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
																    updated_at TIMESTAMP  NULL,
																     PRIMARY KEY  (id),
																    KEY order_id (order_id),
																    KEY user_id (user_id),
									                                KEY item_id (item_id)
							    )$collate;";
				$abprf_orders_old = "CREATE TABLE IF NOT EXISTS $order_table_old (
																	id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
																	order_id BIGINT UNSIGNED NOT NULL ,
																	item_id BIGINT UNSIGNED NOT NULL,
									                                 post_id BIGINT UNSIGNED NOT NULL,
									                                 user_id BIGINT UNSIGNED NOT NULL,  
									                                 property_id JSON NOT NULL,
									                                 ex_id JSON NOT NULL,
																    pick_up varchar(100)  NULL,
									                                start_time TIMESTAMP NULL,
																    drop_off varchar(100)  NULL,
									                                end_time TIMESTAMP NULL,
     																book_from TIMESTAMP  NULL,							    
																    book_to TIMESTAMP NULL,
    																category varchar(20)  NULL,
    																location varchar(20)  NULL,
    																brand varchar(20)  NULL,
    																price_info JSON NOT NULL,
																    property_info JSON NOT NULL,
																    ex_info JSON NOT NULL,
																    pass_info JSON NOT NULL,
																    delivery_option varchar(20) NULL,
																    book_status varchar(20) NOT NULL,
																    order_status varchar(20) NOT NULL,
																    payment_method varchar(100)  NULL,
    									                        	billing_name varchar(100)  NULL,
																    billing_email varchar(100)  NULL,																    
																    billing_phone varchar(20)  NULL,
																    billing_address varchar(255)  NULL,
																    others JSON NULL,
																     created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
																    updated_at TIMESTAMP  NULL,
																     PRIMARY KEY  (id),
																    KEY order_id (order_id),
																    KEY user_id (user_id),
									                                KEY item_id (item_id)
							    )$collate;";
				$abprf_property   = "CREATE TABLE IF NOT EXISTS $property_table (
												             id BIGINT UNSIGNED NOT NULL AUTO_INCREMENT,
												             post_id BIGINT UNSIGNED NOT NULL,
												             rent_continue varchar(20) NOT NULL DEFAULT 'on',
    														name varchar(100) NOT NULL,    														
												             brand varchar(20) NULL,
 															category varchar(200) NULL,
 															location varchar(200) NULL,
 															features varchar(200) NULL,
												             rent_rule varchar(20) NULL,  
    														price_qty_info JSON NOT NULL,		
												             gallery varchar(100) NULL,
												             status varchar(20) NULL,												                                     
												             others JSON NULL,
												             created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP NOT NULL,
												             updated_at TIMESTAMP NULL,
												             PRIMARY KEY (id),
												             KEY post_id (post_id)
												) $collate AUTO_INCREMENT = 100;";
				if ( ! function_exists( 'dbDelta' ) ) {
					require_once ABSPATH . 'wp-admin/includes/upgrade.php';
				}
				dbDelta( $abprf_orders );
				dbDelta( $abprf_orders_old );
				dbDelta( $abprf_property );
			}

			public function plugin_settings_link( $links_array, $plugin_file_name ) {
				if ( strpos( $plugin_file_name, ABPRF_BASE ) ) {
					array_unshift( $links_array, '<a class="_abprf" href="' . esc_url( admin_url() ) . 'admin.php?page=rental-forge&rf_tab=configuration">' . __( 'Configuration', 'abprf-rental-forge' ) . '</a>' );
				}

				return $links_array;
			}

			public function flush_rewrite(): void {
				flush_rewrite_rules();
			}

			public function disable_gutenberg( $current_status, $post_type ) {
				if ( $post_type === ABPRF_Function::get_cpt() ) {
					return false;
				}

				return $current_status;
			}

			public function activation_redirect( $plugin ): void {
				if ( $plugin == plugin_basename( __FILE__ ) ) {
					if ( ABPRF_Function::check_wc() < 2 ) {
						wp_safe_redirect( esc_url( admin_url() ) . 'admin.php?page=rental-forge&rf_tab=configuration' );
						exit;
					}
				}
			}
		}
		new ABPRF_Dependencies();
	}