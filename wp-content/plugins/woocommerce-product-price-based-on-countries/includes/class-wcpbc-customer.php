<?php

if ( ! defined( 'ABSPATH' ) ) exit; // Exit if accessed directly

if ( ! class_exists( 'WCPBC_Customer' ) ) :

/**
 * WCPBC_Customer Legacy
 *
 * Store WCPBC frontend data Handler 
 *
 * @class 		WCPBC_Customer
 * @version		1.7.0
 * @category	Class
 * @author 		oscargare
 */
class WCPBC_Customer {

	/** Stores customer price based on country data as an array */
	protected $_data;	

	/**
	 * Constructor for the wcpbc_customer class loads the data.
	 *
	 * @access public
	 */
	public function __construct( $data ) {		
		$this->_data = $data;
	}	

	/**
	 * __get function.
	 *
	 * @access public
	 * @param string $property
	 * @return string
	 */
	public function __get( $property ) {

		$value = isset( $this->_data[ $property ] ) ? $this->_data[ $property ] : '';

		if ( $property === 'countries' && ! $value) {
			$value = array();			
		}

		return $value;
	}

}
endif;
