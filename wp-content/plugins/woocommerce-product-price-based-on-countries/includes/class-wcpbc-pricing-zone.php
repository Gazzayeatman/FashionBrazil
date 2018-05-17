<?php

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Represents a single pricing zone
 *
 * @class 		WCPBC_Pricing_Zone
 * @since		1.7.0
 * @version		1.7.4
 * @category	Class
 * @author 		oscargare
 */
class WCPBC_Pricing_Zone {

	/**
	 * Zone Data
	 * @var array
	 */
	protected $data = array();
	
	/**
	 * Constructor for zones.
	 *
	 * @param array $data 
	 */
	public function __construct( $data = null ) {	

		$this->data = wp_parse_args( $data, array(
			'zone_id'		=> '',
			'name'        	=> '',
			'countries'     => array(),
			'currency'   	=> get_option('woocommerce_currency'),	
			'exchange_rate' => '1'
		) );
	}

	/**
	 * Get zone data.
	 *	 
	 * @return array
	 */
	public function get_data() {
		return $this->data;
	}

	/**
	 * Get zone id.
	 *	 
	 * @return string
	 */
	public function get_zone_id() {
		return $this->data['zone_id'];
	}

	/**
	 * Get zone name.
	 *	 
	 * @return string
	 */
	public function get_name() {
		return $this->data['name'];
	}

	/**
	 * Get countries.
	 *	 
	 * @return array
	 */
	public function get_countries() {
		return $this->data['countries'];
	}

	/**
	 * Get zone currency.
	 *	 
	 * @return string
	 */
	public function get_currency() {
		return $this->data['currency'];
	}

	/**
	 * Get exchange rate.
	 *	 
	 * @return float
	 */
	public function get_exchange_rate() {
		return floatval( $this->data['exchange_rate'] );
	}	

	/**
	 * Get a meta key based on zone ID
	 *	 	 
	 * @param string $meta_key
	 * @return string
	 */	
	public function get_postmetakey( $meta_key = '' ) {
		return '_' . $this->get_zone_id() . $meta_key;
	}

	/**
	 * Get a meta value based on zone ID
	 *	 
	 * @param int  	 $post_id	
	 * @param string $meta_key
	 * @return mixed
	 */	
	public function get_postmeta( $post_id, $meta_key ) {
		return get_post_meta( $post_id, $this->get_postmetakey( $meta_key ), true );
	}

	/**
	 * Update meta value based on zone ID
	 *	 
	 * @param int  	 $post_id	
	 * @param string $meta_key	 
	 */	
	private function set_postmeta( $post_id, $meta_key, $meta_value ) {
		return update_post_meta( $post_id, $this->get_postmetakey( $meta_key), $meta_value );
	}

	/**
	 * Product price by exchange rate?
	 *
	 * @param int  $post_id	
	 * @return bool
	 */
	public function is_exchange_rate( $post_id ) {
		return 'manual' !== $this->get_postmeta( $post_id, '_price_method' );
	}

	/**
	 * Return a price calculate by exchange rate
	 *
	 * @param float $price
	 * @param bool $round
	 * @return float
	 */
	public function get_exchange_rate_price( $price, $round = true ) {
		if ( empty( $price ) ) {			
			$value = $price;
		} else {			
			$value = floatval( $price ) * $this->get_exchange_rate();	
			if ( $round ) {
				$value = $this->round( $value );
			}
		}
		
		return $value;
	}	

	/**
	 * Return product price calculate by exchange rate
	 *
	 * @param int $price
	 * @param string $meta_key
	 * @param bool $round
	 * @return float
	 */
	public function get_exchange_rate_price_by_post( $post_id, $meta_key, $round = true ) {
		$base_price = get_post_meta( $post_id, $meta_key, true );		
		return $this->get_exchange_rate_price( $base_price, $round );
	}

	/**
	 * Get product price
	 *	 
	 * @param int  	 $post_id	
	 * @param string $meta_key	 
	 * @return mixed
	 */	
	public function get_postprice( $post_id, $meta_key ) {
		$zone_price = $this->get_postmeta( $post_id, $meta_key );		

		if ( $this->is_exchange_rate( $post_id ) ) {
			
			$_price = strval( $this->get_exchange_rate_price_by_post( $post_id, $meta_key, false ) );			

			if ( $_price !== $zone_price ) {				
				$zone_price = $_price;				
				$this->set_postmeta( $post_id, $meta_key, $_price );				
			}
			$zone_price = $this->round( $zone_price );			
		}

		return $zone_price;
	}	

	/**
	 * Round a price
	 *
	 * @param float $price
	 * @param float $num_decimal
	 */
	private function round( $price, $num_decimals = '' ) {		
		if ( wcpbc_empty_nozero( $num_decimals) ) {
			$num_decimals = wc_get_price_decimals();
		} 

		$value = $price;

		if ( ! empty( $value ) ) {
			$value = round( $value, $num_decimals );
		}
		return $value;
	}	

	/**
	 * Return an amount in the shop base currency
	 *
	 * @since 1.7.4
	 *
	 * @param float $amount	 
	 */
	public function get_base_currency_amount( $amount ) {				
		$amount = floatval( $amount );		
		return ( $amount / $this->get_exchange_rate() );
	}

	/**
	 * Getter implementation by __call magic method
	 *
	 * @since 1.7.0
	 *
	 * @param $method
	 * @param $parameters
	 *
	 * @return mixed
	 */
	public function __call( $method, $parameters ) {
		$prop = str_replace( 'get_', '', $method );
		if ( 'get_' === substr( $method, 0, 4 ) &&  array_key_exists ( $prop, $this->data ) && empty( $parameters ) ) {
			return $this->data[ $prop ];
		}
	}	
}