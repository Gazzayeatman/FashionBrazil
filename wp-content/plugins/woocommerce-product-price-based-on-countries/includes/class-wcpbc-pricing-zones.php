<?php
/**
 * Handles storage and retrieval of pricing zones
 * 
 * @author  Oscar Gare
 * @version 1.0.0
 * @since   1.7.4
 */

if ( ! defined( 'ABSPATH' ) ) {
	exit;
}

/**
 * Pricing zones class.
 */
class WCPBC_Pricing_Zones {

	/**
	 * Return the pricing zone class
	 *
	 * @return string
	 */
	private static function get_pricing_zone_class_name() {
		$classname = 'WCPBC_Pricing_Zone';
		if ( wcpbc_is_pro() && class_exists( 'WCPBC_Pricing_Zone_Pro' ) ) {
			$classname = 'WCPBC_Pricing_Zone_Pro';
		}

		return $classname;
	}

	/**
	 * Get pricing zones 
	 *	 
	 * @return array Array of  WCPBC_Pricing_Zone.
	 */
	public static function get_zones() {
		$classname 	= self::get_pricing_zone_class_name();
		$zones   	= array();

		foreach ( (array) get_option( 'wc_price_based_country_regions', array() ) as $id => $data ) {
			$zones[ $id ] = new $classname( array_merge( $data, array( 'zone_id' => $id ) ) );
		}

		return $zones;
	}

	/**
	 * Get a zone
	 *
	 * @param WCPBC_Pricing_Zone|string $the_zone
	 * @param string 					$currency
	 * @param float 					$exchange_rate
	 * @return WCPBC_Pricing_Zone
	 */
	public static function get_zone( $the_zone, $currency = '', $exchange_rate = '' ) {
		$zone 		= false;
		$classname 	= self::get_pricing_zone_class_name();

		if ( is_object( $the_zone ) && in_array( get_class( $zone ), array( 'WCPBC_Pricing_Zone', 'WCPBC_Pricing_Zone_Pro' ) ) ) {

			$zone = $the_zone;

		} elseif ( is_array( $the_zone ) ) {
			
			$zone = new $classname( $the_zone );

		} elseif ( ! empty( $currency ) && ! empty( $exchange_rate ) ) {
			
			$zone = new $classname( array(
				'zone_id' 		 => $the_zone,
				'currency'  	 => $currency,
				'exchange_rate'  => $exchange_rate
			) );
		}

		return $zone;
	}

	/**
	 * Get pricing zone by an ID.
	 *
	 * @param string $zone_id .
	 * @return WCPBC_Pricing_Zone
	 */
	public static function get_zone_by_id( $id ) {
		$zone 	 	= null;			
		$zones   	= (array) get_option( 'wc_price_based_country_regions', array() );
		$classname 	= self::get_pricing_zone_class_name();

		if ( ! empty( $zones[ $id ] ) ) {
			$zone = new $classname( array_merge( $zones[ $id ], array( 'zone_id' => $id ) ) );
		}

		return $zone;
	}

	/**
	 * Get pricing zone by country
	 *	 
	 * @param string $country
	 * @return WCPBC_Pricing_Zone
	 */
	public static function get_zone_by_country( $country ) {
		
		$zone 	 	= null;			
		$zones   	= (array) get_option( 'wc_price_based_country_regions', array() );
		$classname 	= self::get_pricing_zone_class_name();

		foreach ( $zones as $key => $zone_data ) {				
			if ( in_array( $country, $zone_data['countries'] ) ) {
				$zone = new $classname( array_merge( $zone_data, array( 'zone_id' => $key ) ) );
				break;
			}								
		}		
	
		return $zone;
	}

	/**
 	 * Return a zone from an order
 	 *
 	 * @param int $order
 	 * @return WCPBC_Pricing_Zone 	 
 	 */
 	public static function get_zone_from_order( $order ){
 		$zone = false;

 		if ( $order = wc_get_order( $order ) ) {

 			$order_id 	=  version_compare( WC_VERSION, '3.0', '<' ) ? $order->id : $order->get_id(); 			
 			$zone_data 	= get_post_meta( $order_id, '_wcpbc_pricing_zone', true );

 			if ( ! empty( $zone_data ) && is_array( $zone_data ) ) {

 				$zone = self::get_zone( $zone_data );
 				
 			} else {

 				// Get zone by order country
 				$address  = $order->get_address( get_option('wc_price_based_country_based_on', 'billing') );			

				if ( ! $address['country'] ) {
					$address = $order->get_address( 'billing' );
				}	

				$zone = self::get_zone_by_country( $address['country'] );
 			} 			
 		}  		
		
		return $zone;
 	}

}
