<?php
	if ( ! defined( 'ABSPATH' ) ) {
		exit; // Exit if accessed directly
	}
	add_action( 'abprf_property_item_template', function ( $abprf_infos, $property = [] ) {
		if ( is_array( $property ) && sizeof( $property ) > 0 ) {
			$date_time_info = array_key_exists( 'date_info', $abprf_infos ) ? $abprf_infos['date_info'] : [];
			$dif            = array_key_exists( 'dif', $date_time_info ) ? $date_time_info['dif'] : 0;
			$dif_text       = array_key_exists( 'dif_text', $date_time_info ) ? $date_time_info['dif_text'] : '';
			$dif_exit       = 0;
			$post_id        = array_key_exists( 'post_id', $property ) ? $property['post_id'] : '';
			$property_id    = array_key_exists( 'id', $property ) ? $property['id'] : '';
			$rent_rule    = array_key_exists( 'rent_rule', $property ) ? $property['rent_rule'] : '';
			$price_info     = array_key_exists( 'price_info', $property ) ? $property['price_info'] : '';
			$price_info     = ! empty( $price_info ) ? json_decode( $price_info, true ) : [];
			if ( !empty($rent_rule) ) {
				$icon_image         = array_key_exists( 'icon', $property ) ? $property['icon'] : '';
				$name               = array_key_exists( 'name', $property ) ? $property['name'] : '';
				$brand              = array_key_exists( 'brand', $property ) ? $property['brand'] : '';
				$total_price        = 0;
				$property_condition = '';
				?>
                <div class="property_item _box_1">
                    <div class="item_head">
                        <div class="item_img _all_center">
							<?php ABPRF_Layout::image_icon( $icon_image ); ?>
                        </div>
                        <h4 class="_abprf"><?php echo esc_html( $name ); ?></h4>
						<?php if ( ! empty( $brand ) ) { ?>
                            <p class="_abprf"><?php echo esc_html( $brand ); ?></p>
						<?php } ?>
                    </div>
                    <div class="item_body">
						<?php
							$features = array_key_exists( 'features', $property ) ? $property['features'] : '';
							$features = ! empty( $features ) ? json_decode( $features, true ) : [];
							if ( ! empty( $features ) && is_array( $features ) && sizeof( $features ) > 0 ) { ?>
                                <div class="item_spec">
									<?php foreach ( $features as $feature ) {
										$value = is_array( $feature ) && array_key_exists( 'value', $feature ) ? $feature['value'] : '';
										$icon  = is_array( $feature ) && array_key_exists( 'icon', $feature ) ? $feature['icon'] : '';
										if ( $value ) { ?>
                                            <span class="spec_badge"><?php ABPRF_Layout::image_icon( $icon ); ?><?php echo esc_html( $value ); ?></span>
										<?php }
									} ?>
                                </div>
							<?php } ?>
                        <div class="pricing_box">
							<?php
								$deposit_info   = array_key_exists( 'deposit', $price_info ) ? $price_info['deposit'] : [];
								$deposit_type   = is_array( $deposit_info ) && array_key_exists( 'type', $deposit_info ) ? $deposit_info['type'] : '';
								$deposit_value  = is_array( $deposit_info ) && array_key_exists( 'value', $deposit_info ) ? $deposit_info['value'] : '';
								$active_deposit = $deposit_type && $deposit_value ? 'on' : 'off';
								$hourly_info    =  array_key_exists( 'hourly', $price_info ) ? $price_info['hourly'] : [];
								$price_hourly   = is_array( $hourly_info ) && array_key_exists( 'price', $hourly_info ) ? $hourly_info['price'] : '';
								if ( $rent_rule == 'hourly' && ! empty( $price_hourly ) ) {
									$min_hour = array_key_exists( 'min', $hourly_info ) ? $hourly_info['min'] : 1;
									$max_hour = array_key_exists( 'max', $hourly_info ) ? $hourly_info['max'] : '';
									if ( ! empty( $max_hour ) ) {
										$dif_exit = $min_hour <= $dif && $max_hour >= $dif ? 1 : 0;
									} else {
										$dif_exit = $min_hour <= $dif ? 1 : 0;
									}
									$price       = apply_filters( 'abprf_filter_hourly_price', $price_hourly, $property );
									$price       = ABPRF_Function::tax_with_price( $post_id, $price );
									$total_price = $price * $dif;
									?>
                                    <div class="price_row">
                                        <div class="_fd_column">
                                            <span class="price_label"><?php esc_html_e( 'Hourly Rate', 'abprf-rental-forge' ); ?></span>
                                            <span class="item_condition">
                                                <?php
	                                                $property_condition = ABPRF_Layout::property_condition( $rent_rule, $min_hour, $max_hour );
	                                                echo esc_html( $property_condition );
                                                ?>
                                            </span>
                                        </div>
                                        <span class="price_value"><?php echo wp_kses_post( wc_price( $price ) ) . esc_html__( '/hr', 'abprf-rental-forge' ); ?></span>
                                    </div>
								<?php } ?>
							<?php
								$daily_info  = array_key_exists( 'daily', $price_info ) ? $price_info['daily'] : [];
								$price_daily = is_array( $daily_info ) && array_key_exists( 'price', $daily_info ) ? $daily_info['price'] : '';
								if ( $rent_rule == 'daily' && ! empty( $price_daily ) ) {
									$min_day = is_array( $daily_info ) && array_key_exists( 'min', $daily_info ) ? $daily_info['min'] : 1;
									$price   = apply_filters( 'abprf_filter_daily_price', $price_daily, $property );
									$price   = ABPRF_Function::tax_with_price( $post_id, $price ); ?>
                                    <div class="price_row">
                                        <div class="_fd_column">
                                            <span class="price_label"><?php esc_html_e( 'Daily Rate', 'abprf-rental-forge' ); ?></span>
                                            <span class="item_condition"><?php echo esc_html( sprintf(
												/* translators: %s = minimum number of days */
													_n( 'Min. %s Day', 'Min. %s Days', $min_day, 'abprf-rental-forge' ), $min_day ) ); ?></span>
                                        </div>
                                        <span class="price_value"><?php echo wp_kses_post( wc_price( $price ) ) . esc_html__( '/Day', 'abprf-rental-forge' ); ?></span>
                                    </div>
								<?php } ?>
							<?php
								$monthly_info  = array_key_exists( 'monthly', $price_info ) ? $price_info['monthly'] : [];
								$price_monthly = is_array( $monthly_info ) && array_key_exists( 'price', $monthly_info ) ? $monthly_info['price'] : '';
								if ( $rent_rule == 'monthly' && ! empty( $price_monthly ) ) {
									$min_month = is_array( $monthly_info ) && array_key_exists( 'min', $monthly_info ) ? $monthly_info['min'] : 1;
									$price     = apply_filters( 'abprf_filter_monthly_price', $price_monthly, $property );
									$price     = ABPRF_Function::tax_with_price( $post_id, $price ); ?>
                                    <div class="price_row">
                                        <div class="_fd_column">
                                            <span class="price_label"><?php esc_html_e( 'Monthly Rate', 'abprf-rental-forge' ); ?></span>
                                            <span class="item_condition"><?php echo esc_html( sprintf(
												/* translators: %s = minimum number of month */
													_n( 'Min. %s Month', 'Min. %s Months', $min_month, 'abprf-rental-forge' ), $min_month ) ); ?></span>
                                        </div>
                                        <span class="price_value"><?php echo wp_kses_post( wc_price( $price ) ) . esc_html__( '/Month', 'abprf-rental-forge' ); ?></span>
                                    </div>
								<?php } ?>
							<?php if ( $active_deposit == 'on' ) { ?>
                                <div class="item_condition">
									<?php if ( $deposit_type == 'fixed' ) {
										echo wp_kses_post( sprintf(
										/* translators: %s = deposit label' */
											_x( '• Deposit: %s Fixed', 'deposit label', 'abprf-rental-forge' ), wc_price( $deposit_value ) ) );
									} elseif ( $deposit_type == 'percent' ) {
										echo esc_html( sprintf(
										/* translators: %s = deposit label' */
											_x( '• Deposit: %s of Total Price', 'deposit label', 'abprf-rental-forge' ), esc_html( $deposit_value . '%' ) ) );
									} else {
										echo wp_kses_post( sprintf(
										/* translators: %s = deposit label' */
											_x( '• Deposit: %s Per Item', 'deposit label', 'abprf-rental-forge' ), wc_price( $deposit_value ) ) );
									}
									?>
                                </div>
							<?php } ?>
							<?php if ( is_array( $date_time_info ) && sizeof( $date_time_info ) > 0 ) { ?>
                                <div class="calculated_cost">
                                    <div class="cost_label"><?php echo esc_html__( 'Total for ', 'abprf-rental-forge' ) . ' ' . esc_html( $dif_text ); ?></div>
									<?php if ( $dif_exit > 0 ) { ?>
                                        <div class="cost_value">
											<?php echo $total_price > 0 ? wp_kses_post( wc_price( $total_price ) ) : esc_html__( 'Free ', 'abprf-rental-forge' ); ?>
                                        </div>
									<?php } else { ?>
                                        <div class="cost_condition">
											<?php echo esc_html( $property_condition ); ?>
                                        </div>
									<?php } ?>
                                </div>
							<?php } ?>
                        </div>
						<?php if ( is_array( $date_time_info ) && sizeof( $date_time_info ) > 0 && $dif_exit > 0 ) {
							//echo '<pre>';print_r( $date_time_info);					echo '</pre>';
							$qty_info      = array_key_exists( 'qty_info', $property ) ? $property['qty_info'] : '';
							$qty_info      = ! empty( $qty_info ) ? json_decode( $qty_info, true ) : [];
							$total_qty     = array_key_exists( 'qty', $qty_info ) ? $qty_info['qty'] : 0;
							$reserve_qty   = array_key_exists( 'reserve', $qty_info ) ? $qty_info['reserve'] : 0;
							$min_qty       = array_key_exists( 'min', $qty_info ) ? $qty_info['min'] : 1;
							$max_qty       = array_key_exists( 'max', $qty_info ) ? $qty_info['max'] : '';
							$available_qty = $total_qty - $reserve_qty;
							$max_qty       = ! empty( $max_qty ) && $max_qty <= $available_qty ? $max_qty : $available_qty;
							$min_qty       = max( $min_qty, 1 );
							if ( $max_qty >= $min_qty ) {
								$collapse_id = '#' . $post_id . '_' . $property_id;
								?>
                                <div class="select_property">
                                    <input type="hidden" name="property_id[]" value="<?php echo esc_attr( $property_id ); ?>"/>
                                    <input type="hidden" name="deposit_type[]" value="<?php echo esc_attr( $deposit_type ); ?>"/>
                                    <input type="hidden" name="deposit_value[]" value="<?php echo esc_attr( $deposit_value ); ?>"/>
                                    <div class="custom_checkbox">
                                        <input type="hidden" name="property_check[]" value="" data-id="<?php echo esc_attr( $collapse_id ); ?>"/>
                                        <div class="checkbox_item _fa_center _fs_label" data-checked="1" data-open-icon="far fa-check-square" data-close-icon="far fa-square">
                                            <h3 class="_abprf"><span data-icon class="_mar_r_xs far fa-square"></span></h3><?php echo esc_html__( 'Select ', 'abprf-rental-forge' ) . ' ' . esc_html( $name ); ?>
                                        </div>
                                    </div>
									<?php
										if ( $max_qty > $min_qty ) {
											$input_info['name']        = 'property_qty[]';
											$input_info['price']       = $total_price;
											$input_info['available']   = $available_qty;
											$input_info['min_qty']     = $min_qty;
											$input_info['max_qty']     = $max_qty;
											$input_info['collapse_id'] = $collapse_id;
											ABPRF_Layout::quantity_input( $input_info );
										} else { ?>
                                            <input type="hidden" name="property_qty[]" value="<?php echo esc_attr( $min_qty ); ?>" data-price="<?php echo esc_attr( $total_price ); ?>"/>
										<?php } ?>
                                </div>
							<?php } else {
								ABPRF_Layout::layout_warning_info( 'property_not_available' );
							}
						} ?>
                    </div>
                </div>
				<?php
			}
		}
	}, 10, 2 );