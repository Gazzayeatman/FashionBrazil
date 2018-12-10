<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit; // Exit if accessed directly
}

/**
 * WCPBC_Frontend_Pricing class.
 *
 * @class 		WCPBC_Frontend_Pricing
 * @version		1.7.4
 * @author 		oscargare
 */
class WCPBC_Frontend_Pricing {

	/**
	 * @var string
	 */
	private static $_zone;

	/**	 
	 * Init the class	
	 * 
	 * @param WCPBC_Pricing_Zone/string $the_zone
	 * @param string $currency
	 * @param float $exchange_rate
	 */
	public static function init( $the_zone = '', $currency = '', $exchange_rate = '' ) { 
		
		if ( $zone = WCPBC_Pricing_Zones::get_zone( $the_zone, $currency, $exchange_rate ) ) {
			self::$_zone = $zone;			
		} else {
			self::$_zone = WCPBC()->current_zone;
		}

		self::init_hooks();
		
		do_action( 'wc_price_based_country_frontend_princing_init' );
	}

	/**
	 * Hook actions and filters
	 *
	 * @since 1.7.0
	 */
	private static function init_hooks(){
		add_filter( 'get_post_metadata', array( __CLASS__, 'get_price_metadata' ), 10, 4 );
		add_filter( 'woocommerce_currency',  array( __CLASS__ , 'get_currency' ), 100 );
		add_filter( 'woocommerce_get_variation_prices_hash', array( __CLASS__ , 'get_variation_prices_hash' ), 10, 3 );		
		add_filter( 'woocommerce_get_catalog_ordering_args', array( __CLASS__ , 'get_catalog_ordering_args' ) );		
		add_filter( 'woocommerce_product_query_meta_query', array( __CLASS__ , 'product_query_meta_query' ),10, 2 );
		add_filter( 'woocommerce_price_filter_meta_keys', array( __CLASS__ , 'price_filter_meta_keys' ) );
		add_filter( 'pre_transient_wc_products_onsale', array( __CLASS__ , 'product_ids_on_sale' ), 10, 2 );
		add_filter( 'woocommerce_package_rates', array( __CLASS__ , 'package_rates' ), 10, 2 );
		add_filter( 'woocommerce_shipping_zone_shipping_methods', array( __CLASS__ , 'shipping_zone_shipping_methods' ), 10, 4 );			
		add_action( 'woocommerce_coupon_loaded', array( __CLASS__ , 'coupon_loaded' ) );
		add_action( 'woocommerce_checkout_update_order_meta', array( __CLASS__, 'update_order_meta'), 10, 2 );	
	}

	/**
	 * Return price meta data value
	 *	
	 * @param null|array|string $value     The value get_metadata() should return - a single metadata value or an array of values.
     * @param int               $object_id Object ID.
     * @param string            $meta_key  Meta key.
     * @param bool              $single    Whether to return only the first value of the specified $meta_key.
	 */
	public static function get_price_metadata( $meta_value, $object_id, $meta_key, $single ) {
		
		if ( $single && in_array( $meta_key, wcpbc_get_overwrite_meta_keys() ) ) {						

			// Remove filter to not going into a endless loop			
			remove_filter( 'get_post_metadata', array( __CLASS__, 'get_price_metadata'), 10, 4 );
			
			if ( in_array( $meta_key, wcpbc_get_date_on_sale_meta_keys() ) && 'manual' == self::$_zone->get_postmeta( $object_id, '_sale_price_dates' ) ) {
							
				$meta_value = self::$_zone->get_postmeta( $object_id, $meta_key );			
				
			} elseif ( in_array( $meta_key, wcpbc_get_price_meta_keys() ) ) {		

				$meta_value = self::$_zone->get_postprice( $object_id, $meta_key );

			} elseif ( ! in_array( $meta_key, wcpbc_get_date_on_sale_meta_keys() ) ) {

				$meta_value = self::$_zone->get_postmeta( $object_id, $meta_key );
			}		

			// Add filter			 
			add_filter( 'get_post_metadata', array( __CLASS__, 'get_price_metadata'), 10, 4 );
		}
		
		return $meta_value;
	}		

	/**	 
	 * Get currency code.
	 *
	 * @param string $currency_code
	 * @return string
	 */	
	public static function get_currency( $currency_code ) {
		return self::$_zone->get_currency();
	}

	/**
	 * Returns unique cache key to store variation child prices
	 * @param array $hash
	 * @param WC_Product $product
	 * @param bool $display
	 * @return array
	 */
	public static function get_variation_prices_hash( $price_hash, $product, $display ) {
		$price_hash[] = self::$_zone->get_postmetakey() . self::$_zone->get_currency() . self::$_zone->get_exchange_rate();
		return $price_hash;
	}
	
	/**
	 * Override _price metakey in array of arguments for ordering products based on the selected values.
	 * @param array $args	 
	 * @return array
	 */
	public static function get_catalog_ordering_args( $args ) {
		if ( isset( $args['meta_key'] ) && $args['meta_key'] == '_price' ) {
			$args['meta_key'] = self::$_zone->get_postmetakey( '_price' );
		}		
		return $args;
	}
	
	/**
	 * Override _price metakey in meta query for filtering by price.
	 * @param array $args	 
	 * @return array
	 */
	public static function product_query_meta_query( $meta_query, $q ) {
		if ( isset( $meta_query['price_filter']['key'] ) && $meta_query['price_filter']['key'] == '_price' ) {
			$meta_query['price_filter']['key'] = self::$_zone->get_postmetakey( '_price' );
		}		
		return $meta_query;
	}
	
	/**
	 * Override _price metakey for get filtered min and max price for current products.
	 * @param array $args	 
	 * @return array
	 */
	public static function price_filter_meta_keys( $meta_keys ) {
		return array( self::$_zone->get_postmetakey( '_price' ) );
	}
	
	/**
	 * Returns an array containing the IDs of the products that are on sale. Filter through get_transient
	 * @return array
	 */
	public static function product_ids_on_sale( $value, $transient = false ) {
		global $wpdb;
		
		$cache_key = 'wcpbc_products_onsale_' . self::$_zone->get_zone_id();
			
		// Load from cache
		$product_ids_on_sale = get_transient( $cache_key );

		// Valid cache found
		if ( false !== $product_ids_on_sale ) {			
			return $product_ids_on_sale;
		}
		
		$decimals = absint( wc_get_price_decimals() );

		$on_sale_posts = $wpdb->get_results( $wpdb->prepare( "
			SELECT post.ID, post.post_parent FROM `{$wpdb->posts}` AS post
			LEFT JOIN `{$wpdb->postmeta}` AS meta ON post.ID = meta.post_id
			LEFT JOIN `{$wpdb->postmeta}` AS meta2 ON post.ID = meta2.post_id
			WHERE post.post_type IN ( 'product', 'product_variation' )
				AND post.post_status = 'publish'
				AND meta.meta_key = %s
				AND meta2.meta_key = %s
				AND CAST( meta.meta_value AS DECIMAL ) >= 0
				AND CAST( meta.meta_value AS CHAR ) != ''
				AND CAST( meta.meta_value AS DECIMAL( 10, {$decimals} ) ) = CAST( meta2.meta_value AS DECIMAL( 10, {$decimals} ) )
			GROUP BY post.ID
		", self::$_zone->get_postmetakey( '_sale_price' ), self::$_zone->get_postmetakey( '_price' ) ) );

		$product_ids_on_sale = array_unique( array_map( 'absint', array_merge( wp_list_pluck( $on_sale_posts, 'ID' ), array_diff( wp_list_pluck( $on_sale_posts, 'post_parent' ), array( 0 ) ) ) ) );

		set_transient( $cache_key, $product_ids_on_sale, DAY_IN_SECONDS * 30 );

		return $product_ids_on_sale;
	}
	
	/**
     * Apply exchange rate to shipping cost
     * @param array $rates
     * @param array $package cart items
     * @return float
     */
    public static function package_rates( $rates, $package ) {		
		
		if ( get_option( 'wc_price_based_country_shipping_exchange_rate', 'no') == 'yes' ) {
			
			foreach ( $rates as $rate ) {				
				$change = false;
			
				if ( ! isset( $rate->wcpbc_data ) ) {
					
					$rate->wcpbc_data = array(
						'exchange_rate' => self::$_zone->get_exchange_rate(),
						'orig_cost'		=> $rate->cost,
						'orig_taxes'	=> $rate->taxes
					);															
					$change = true;
					
				} elseif ( $rate->wcpbc_data['exchange_rate'] !== self::$_zone->get_exchange_rate() ) {				
					
					$rate->wcpbc_data['exchange_rate'] = self::$_zone->get_exchange_rate();				
					$change = true;
					
				}	
				
				if ( $change ) {
					//Apply exchange rate					

					if ( ! wc_prices_include_tax() ) {
						$rate->cost = self::$_zone->get_exchange_rate_price( $rate->cost );						
					} else {
						$rate->cost = self::$_zone->get_exchange_rate_price( $rate->cost, false );						
					}								

					//recalculate taxes
					$rate_taxes = $rate->taxes;
					foreach ( $rate->wcpbc_data['orig_taxes'] as $i => $tax ){
						$rate_taxes[$i] = ( $tax/$rate->wcpbc_data['orig_cost'] ) * $rate->cost;
					}
					$rate->taxes = $rate_taxes;					
				}												
			}			
		}	

		return $rates;				
	}
	
	/**
      * Apply exchange rate to free shipping min amount
	  * @param array $methods
	  * @param array $raw_methods
	  * @param array $allowed_classes
	  * @param WC_Shipping_Zone $shipping
	  */
    public static function shipping_zone_shipping_methods( $methods, $raw_methods, $allowed_classes, $shipping ) {
    	
    	if ( get_option( 'wc_price_based_country_shipping_exchange_rate', 'no') == 'yes' ) {

    		foreach ( $methods as $instance_id => $method ) {
				if ( $method->id === 'free_shipping' ) {
					$method->min_amount = self::$_zone->get_exchange_rate_price( $method->min_amount );
				}
			}
    	}		
		
		return $methods;
	}

	/**
     * Apply exchange rate to coupon
     *
     * @param WC_Coupon $coupon          
     */
    public static function coupon_loaded( $coupon ) {
		$_back = version_compare( WC_VERSION, '3.0', '<' );

		$coupon_id 		= $_back ? $coupon->id : $coupon->get_id();
		$coupon_amount 	= $_back ? $coupon->coupon_amount : $coupon->get_amount();
		$minimum_amount = $_back ? $coupon->minimum_amount : $coupon->get_minimum_amount();
		$maximum_amount = $_back ? $coupon->maximum_amount : $coupon->get_maximum_amount();

		if ( 'exchange_rate' == get_post_meta( $coupon_id, 'zone_pricing_type', true ) ) {
			$amount = self::$_zone->get_exchange_rate_price( $coupon_amount );
			self::set_coupon_prop( $coupon, 'coupon_amount', $amount, $_back );			
		}
				
		if ( $minimum_amount ) {
			$amount = self::$_zone->get_exchange_rate_price( $minimum_amount );
			self::set_coupon_prop( $coupon, 'minimum_amount', $amount, $_back );											
			
		}
		if ( $maximum_amount ) {
			$amount = self::$_zone->get_exchange_rate_price( $maximum_amount );
			self::set_coupon_prop( $coupon, 'maximum_amount', $amount, $_back );																		
		}
	}

	/**
	 * Set a coupon property value
	 *
	 * @since 1.7
	 * @param WC_Coupon $coupon
	 * @param string 	$prop
	 * @param mixed 	$value
	 * @param boolean 	$wc_old
	 */
	private static function set_coupon_prop( $coupon, $prop, $value, $wc_old ){
		if ( $wc_old ) {
			$coupon->{$prop} = $value;
		} else {
			$setter = 'coupon_amount' == $prop ? 'set_amount' : 'set_' . $prop;			
			$coupon->{$setter}( $value );
		}
	}

	/**
	 * Add zone data to order meta
	 *
	 * @since 1.7.4
	 * @param int $order_id
	 * @param array $data
	 */
	public static function update_order_meta( $order_id, $data ) {
		
		update_post_meta( $order_id, '_wcpbc_base_exchange_rate', self::$_zone->get_base_currency_amount( 1 ) );
		update_post_meta( $order_id, '_wcpbc_pricing_zone', self::$_zone->get_data() );
	}
}