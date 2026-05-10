<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abprf_registration_template', function ( $abprf_infos = [] ) {
		$rent_rule  = array_key_exists( 'rent_rule', $abprf_infos ) ? $abprf_infos['rent_rule'] : 'hourly';
		$post_id    = array_key_exists( 'post_id', $abprf_infos ) ? $abprf_infos['post_id'] : '';
		$properties = ABPRF_Query::get_property( [ 'post_id' => $post_id, 'rent_continue' => 'on', 'rent_rule' => $rent_rule, 'status' => 'publish' ] );
		?>
        <div class="abprf_booking">
            <form class="" action="" method="post">
                <h2 class="_abprf_mar_b"><?php esc_html_e( 'Available Property', 'abprf-rental-forge' ); ?></h2>
				<?php wp_nonce_field( 'abprf_registration_nonce' );
					do_action( 'abprf_admin_order', $post_id ); ?>
                <div class="property_registration">
                    <div class="property_item_area">
						<?php
							if ( ! empty( $properties ) && is_array( $properties ) && sizeof( $properties ) > 0 ) {
								foreach ( $properties as $property ) {
									do_action( 'abprf_property_item', $abprf_infos, $property );
								}
							} else {
								ABPRF_Layout::layout_warning_info( 'no_property_found' );
							} ?>
                    </div>
                </div>
            </form>
        </div>
		<?php
	}, 10, 2 );
