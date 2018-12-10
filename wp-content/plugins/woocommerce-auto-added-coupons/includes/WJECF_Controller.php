<?php

defined('ABSPATH') or die();

/**
 * The main controller for WooCommerce Extended Coupon Features
 */
class WJECF_Controller {

    // Coupon message codes
    //NOTE: I use prefix 79 for this plugin; there's no guarantee that other plugins don't use the same values!
    const E_WC_COUPON_MIN_MATCHING_SUBTOTAL_NOT_MET  = 79100;
    const E_WC_COUPON_MAX_MATCHING_SUBTOTAL_NOT_MET  = 79101;
    const E_WC_COUPON_MIN_MATCHING_QUANTITY_NOT_MET  = 79102;
    const E_WC_COUPON_MAX_MATCHING_QUANTITY_NOT_MET  = 79103;
    const E_WC_COUPON_SHIPPING_METHOD_NOT_MET        = 79104;
    const E_WC_COUPON_PAYMENT_METHOD_NOT_MET         = 79105;
    const E_WC_COUPON_NOT_FOR_THIS_USER              = 79106;
    const E_WC_COUPON_FIRST_PURCHASE_ONLY            = 79107;

    private $options = null;
    private $_user_emails = null;    

    /**
     * Singleton Instance
     *
     * @static
     * @return Singleton Instance
     */
    public static function instance() {
        if ( is_null( self::$_instance ) ) {
            self::$_instance = new self();
        }
        return self::$_instance;
    }
    protected static $_instance = null;

    
    public function __construct() {
        $this->options = new WJECF_Options( 
            'wjecf_options',
            array(
                'db_version' => 0, // integer
                'debug_mode' => false, // true or false
                'disabled_plugins' => array(), // e.g. [ 'WJECF_AutoCoupon' ]
                'autocoupon_allow_remove' => false
            )
        );
    }

    public function start() {
        add_action('init', array( $this, 'init_hook' ));
    }

    public function init_hook() {
        if ( ! class_exists('WC_Coupon') ) {
            add_action( 'admin_notices', array( $this, 'admin_notice_woocommerce_not_found' ) );
            return;
        }

        $this->controller_init();
        
        /**
         * Fires before the WJECF plugins are initialised.
         * 
         * Perfect hook for themes or plugins to load custom WJECF plugins.
         * 
         * @since 2.3.7
         **/
        do_action( 'wjecf_init_plugins');

        //Start the plugins
        foreach ( WJECF()->get_plugins() as $name => $plugin ) {
            if ( $plugin->plugin_is_enabled() ) {

                foreach( $plugin->get_plugin_dependencies() as $dependency_name ) {
                    $dependency = $this->get_plugin( $dependency_name );
                    if ( ! $dependency || ! $dependency->plugin_is_enabled() ) {
                        $this->log( 'error', 'Unable to initialize ' . $name . ' because it requires ' . $dependency_name );
                        continue;
                    }
                }

                $plugin->init_hook();
                if ( is_admin() ) {
                    $plugin->init_admin_hook();
                }
            }
        }
    }

    public function controller_init() {
        
        $this->log( 'debug', "INIT " . ( is_ajax() ? "AJAX" : is_admin() ? "ADMIN" : "FRONTEND" ) . "  " . $_SERVER['REQUEST_URI'] );
        
        //Frontend hooks

        //assert_coupon_is_valid (which raises exception on invalid coupon) can only be used on WC 2.3.0 and up
        if ( WJECF_WC()->check_woocommerce_version('2.3.0') ) {
            add_filter('woocommerce_coupon_is_valid', array( $this, 'assert_coupon_is_valid' ), 10, 3 ); //Since WC3.2 WC_Discounts is passed as a 3rd argument
        } else {
            add_filter('woocommerce_coupon_is_valid', array( $this, 'coupon_is_valid' ), 10, 2 );
        }

        //Last check for coupons with restricted_emails (moved from WJECF_AutoCoupon since 2.5.6)        
        add_action( 'woocommerce_checkout_update_order_review', array( $this, 'fetch_billing_email' ), 10 ); // AJAX One page checkout 
        add_action( 'woocommerce_after_checkout_validation', array( $this, 'fetch_billing_email' ), 10 ); // Checkout posted

        add_filter('woocommerce_coupon_error', array( $this, 'woocommerce_coupon_error' ), 10, 3 );
    }

    protected $plugins = array();

    /**
     * Load a WJECF Plugin (class name)
     * @param string $class_name The class name of the plugin
     * @return bool True if succeeded, otherwise false
     */
    public function add_plugin( $class_name ) {
        if ( isset( $this->plugins[ $class_name ] ) ) {
            return false; //Already loaded
        }
        
        if ( ! class_exists( $class_name ) ) {
            $this->log( 'warning', 'Unknown plugin: ' . $class_name );
            return false; //Not found
        }

        $plugin = new $class_name();
        foreach( $plugin->get_plugin_dependencies() as $dependency ) {
            if ( ! class_exists( $dependency ) ) {
                $this->log( 'warning', 'Unknown dependency: ' . $dependency . ' for plugin ' . $class_name );
                return false;
            }

            if ( isset( $this->plugins[ $class_name ] ) ) {
                continue; //dependency is al geladen
            }

            $this->add_plugin( $dependency );            
        }

        //Assert dependencies
        try {
            $plugin->assert_dependencies();
        } catch (Exception $ex) {
            $msg = sprintf('Failed loading %s. %s', $class_name, $ex->getMessage() );

            $this->log( 'error', $msg );

            if ( $wjecf_admin = WJECF()->get_plugin('WJECF_Admin') ) {
                $wjecf_admin->enqueue_notice( $msg, 'error' );
            }
            return false;
        }


        $this->plugins[ $class_name ] = $plugin;
        $this->log( 'debug', 'Loaded plugin: ' . $class_name );

        return true;
    }


    /**
     * Description
     * @param type $plugin 
     * @return bool true if succesful
     */
    private function load_dependencies( $plugin ) {

        return true;
    }

    public function get_plugins() {
        return $this->plugins;
    }
    
    /**
     * Retrieves the WJECF Plugin
     * @param string $class_name 
     * @return object|bool The plugin if found, otherwise returns false
     */
    public function get_plugin( $class_name ) {
        if ( isset( $this->plugins[ $class_name ] ) ) {
            return $this->plugins[ $class_name ];
        } else {
            return false;
        }
    }

/* OPTIONS */

    public function get_options() {
        return $this->options->get();
    }

    public function get_option( $key, $default = null ) {
        return $this->options->get( $key, $default );
    }

    public function set_option( $key, $value ) {
        $this->options->set( $key, $value );
    }

    public function save_options() {
        if ( ! is_admin() ) {
            $this->log( 'error', 'WJECF Options must only be saved from admin.' );
            return;
        }
        $this->options->save();
    }

    public function sanitizer() {
        return WJECF_Sanitizer::instance();
    }

    /**
     * Same as WordPress add_action(), but prevents the callback to be recursively called
     * 
     * @param string $tag 
     * @param callable $function_to_add 
     * @param int $priority 
     * @param int $accepted_args 
     */
    public function safe_add_action( $tag, $function_to_add, $priority = 10, $accepted_args = 1 ) {
        $_RECURSION_LIMIT = 5;
        WJECF_Action_Or_Filter::action( $tag, $function_to_add, $priority, $accepted_args, $_RECURSION_LIMIT );
    }

/* FRONTEND HOOKS */

    /**
     * Notifies that WooCommerce has not been detected.
     * @return void
     */
    public function admin_notice_woocommerce_not_found() {
        $msg = __( 'WooCommerce Extended Coupon Features is disabled because WooCommerce could not be detected.', 'woocommerce-jos-autocoupon' );
        echo '<div class="error"><p>' . $msg . '</p></div>';
    }

    /**
     * Overwrite coupon error message, if $err_code is an error code of this plugin
     * @param string $err Original error message
     * @param int $err_code Error code
     * @param WC_Coupon $coupon The coupon
     * @return string Overwritten error message
     */
    public function woocommerce_coupon_error( $err, $err_code, $coupon ) {
        switch ( $err_code ) {
            case self::E_WC_COUPON_MIN_MATCHING_SUBTOTAL_NOT_MET:
                $min_price = wc_price( WJECF_Wrap( $coupon )->get_meta( '_wjecf_min_matching_product_subtotal' ) );
                $err = sprintf( __( 'The minimum subtotal of the matching products for this coupon is %s.', 'woocommerce-jos-autocoupon' ), $min_price );
            break;
            case self::E_WC_COUPON_MAX_MATCHING_SUBTOTAL_NOT_MET:
                $max_price = wc_price( WJECF_Wrap( $coupon )->get_meta( '_wjecf_max_matching_product_subtotal' ) );
                $err = sprintf( __( 'The maximum subtotal of the matching products for this coupon is %s.', 'woocommerce-jos-autocoupon' ), $max_price );
            break;
            case self::E_WC_COUPON_MIN_MATCHING_QUANTITY_NOT_MET:
                $min_matching_product_qty = intval( WJECF_Wrap( $coupon )->get_meta( '_wjecf_min_matching_product_qty' ) );
                $err = sprintf( __( 'The minimum quantity of matching products for this coupon is %s.', 'woocommerce-jos-autocoupon' ), $min_matching_product_qty );
            break;
            case self::E_WC_COUPON_MAX_MATCHING_QUANTITY_NOT_MET:
                $max_matching_product_qty = intval( WJECF_Wrap( $coupon )->get_meta( '_wjecf_min_matching_product_qty' ) );
                $err = sprintf( __( 'The maximum quantity of matching products for this coupon is %s.', 'woocommerce-jos-autocoupon' ), $max_matching_product_qty );
            break;
            case self::E_WC_COUPON_SHIPPING_METHOD_NOT_MET:
                $err = __( 'The coupon is not valid for the currently selected shipping method.', 'woocommerce-jos-autocoupon' );
            break;
            case self::E_WC_COUPON_PAYMENT_METHOD_NOT_MET:
                $err = __( 'The coupon is not valid for the currently selected payment method.', 'woocommerce-jos-autocoupon' );
            break;
            case self::E_WC_COUPON_NOT_FOR_THIS_USER:
                $err = sprintf( __( 'Sorry, it seems the coupon "%s" is not yours.', 'woocommerce-jos-autocoupon' ), WJECF_Wrap( $coupon )->get_code() );
            break;
            case self::E_WC_COUPON_FIRST_PURCHASE_ONLY:
                $err = sprintf( __( 'Sorry, coupon "%s" is only valid on your first purchase.', 'woocommerce-jos-autocoupon' ), WJECF_Wrap( $coupon )->get_code() );
            break;
            default:
                //Do nothing
            break;
        }
        return $err;
    }

    /**
     * Extra validation rules for coupons.
     * @param bool $valid 
     * @param WC_Coupon $coupon 
     * @param WC_Discounts $discounts
     * @return bool True if valid; False if not valid.
     */
    public function coupon_is_valid( $valid, $coupon, $wc_discounts = null ) {
        try {
            return $this->assert_coupon_is_valid( $valid, $coupon, $wc_discounts );
        } catch ( Exception $e ) {
            return false;
        }
    }  

    /**
     * Extra validation rules for coupons. Throw an exception when not valid.
     * @param bool $valid 
     * @param WC_Coupon $coupon 
     * @param WC_Discounts $discounts
     * @return bool True if valid; False if already invalid on function call. In any other case an Exception will be thrown.
     */
    public function assert_coupon_is_valid( $valid, $coupon, $wc_discounts = null ) {

        //Not valid? Then it will never validate, so get out of here
        if ( ! $valid ) {
            return false;
        }

        $wrap_coupon = WJECF_Wrap( $coupon );
        $items = WJECF_WC()->get_discount_items( $wc_discounts );

        //$this->log( 'debug', 'Asserting validity of coupon: ' . $wrap_coupon->get_code() );

        //============================
        //Test if ALL products are in the cart (if AND-operator selected instead of the default OR)
        $products_and = $wrap_coupon->get_meta( '_wjecf_products_and' ) == 'yes';
        if ( $products_and && sizeof( $wrap_coupon->get_product_ids() ) > 1 ) { // We use > 1, because if size == 1, 'AND' makes no difference        
            //Get array of all cart product and variation ids
            $item_ids = array();

            foreach( $items as $item_key => $item ) {
                if ( $item->product === false ) continue;
                $wrap_product = WJECF_Wrap( $item->product );

                $item_ids[] = $wrap_product->get_id();
                if (  $item->product->is_type( 'variation' ) ) $item_ids[] = $wrap_product->get_variable_product_id();
            }
            //Filter used by WJECF_WPML hook
            $item_ids = apply_filters( 'wjecf_get_product_ids', array_unique( $item_ids ) );

            //check if every single product is in the cart
            foreach( apply_filters( 'wjecf_get_product_ids', $wrap_coupon->get_product_ids() ) as $product_id ) {
                if ( ! in_array( $product_id, $item_ids ) ) {
                    throw new Exception( WC_Coupon::E_WC_COUPON_NOT_APPLICABLE );
                }
            }        
        }

        //============================
        //Test if products form ALL categories are in the cart (if AND-operator selected instead of the default OR)
        $categories_and = $wrap_coupon->get_meta( '_wjecf_categories_and' ) == 'yes';
        if ( $categories_and && sizeof( $wrap_coupon->get_product_categories() ) > 1 ) { // We use > 1, because if size == 1, 'AND' makes no difference        
            //Get array of all cart product and variation ids
            $product_cats = array();

            foreach( $items as $item_key => $item ) {
                if ( $item->product === false ) continue;

                $product_id = WJECF_Wrap( $item->product )->get_id();
                if ('product_variation' == get_post_type( $product_id )) $product_id = WJECF_Wrap( $item->product )->get_variable_product_id();
                $product_cats = array_merge( $product_cats,  wp_get_post_terms( $product_id, 'product_cat', array( "fields" => "ids" ) ) );
            }
            //Filter used by WJECF_WPML hook
            $product_cats = apply_filters( 'wjecf_get_product_cat_ids', $product_cats );
            //check if every single category is in the cart
            foreach( apply_filters( 'wjecf_get_product_cat_ids', $wrap_coupon->get_product_categories() ) as $cat_id ) {
                if ( ! in_array( $cat_id, $product_cats ) ) {
                    $this->log( 'debug', $cat_id . " is not in " . implode( ',', $product_cats ));
                    throw new Exception( WC_Coupon::E_WC_COUPON_NOT_APPLICABLE );
                }
            }
        }
        
        //============================
        //Test min/max quantity of matching products
        //
        //For all items in the cart:
        //  If coupon contains both a product AND category inclusion filter: the item is counted if it matches either one of them
        //  If coupon contains either a product OR category exclusion filter: the item will NOT be counted if it matches either one of them
        //  If sale items are excluded by the coupon: the item will NOT be counted if it is a sale item
        //  If no filter exist, all items will be counted

        $multiplier = null; //null = not initialized

        //Validate quantity
        $min_matching_product_qty = intval( $wrap_coupon->get_meta( '_wjecf_min_matching_product_qty' ) );
        $max_matching_product_qty = intval( $wrap_coupon->get_meta( '_wjecf_max_matching_product_qty' ) );
        if ( $min_matching_product_qty > 0 || $max_matching_product_qty > 0 ) {
            //Count the products
            $qty = $this->get_quantity_of_matching_products( $coupon, $wc_discounts );
            //$this->log( 'debug', 'Quantity of matching products: ' . $qty );
            if ( $min_matching_product_qty > 0 && $qty < $min_matching_product_qty ) throw new Exception( self::E_WC_COUPON_MIN_MATCHING_QUANTITY_NOT_MET );
            if ( $max_matching_product_qty > 0 && $qty > $max_matching_product_qty ) throw new Exception( self::E_WC_COUPON_MAX_MATCHING_QUANTITY_NOT_MET );
            
            if ( $min_matching_product_qty > 0 ) {
                $multiplier = self::min_value( floor( $qty / $min_matching_product_qty ), $multiplier );
            }
        }    

        //Validate subtotal (2.2.2)
        $min_matching_product_subtotal = floatval( $wrap_coupon->get_meta( '_wjecf_min_matching_product_subtotal' ) );
        $max_matching_product_subtotal = floatval( $wrap_coupon->get_meta( '_wjecf_max_matching_product_subtotal' ) );
        if ( $min_matching_product_subtotal > 0 || $max_matching_product_subtotal > 0 ) { 
            $subtotal = $this->get_subtotal_of_matching_products( $coupon, $wc_discounts );
            //$this->log( 'debug', 'Subtotal of matching products: ' . $subtotal );
            if ( $min_matching_product_subtotal > 0 && $subtotal < $min_matching_product_subtotal ) throw new Exception( self::E_WC_COUPON_MIN_MATCHING_SUBTOTAL_NOT_MET );
            if ( $max_matching_product_subtotal > 0 && $subtotal > $max_matching_product_subtotal ) throw new Exception( self::E_WC_COUPON_MAX_MATCHING_SUBTOTAL_NOT_MET );

            if ( $min_matching_product_subtotal > 0 ) {
                $multiplier = self::min_value( floor( $subtotal / $min_matching_product_subtotal ), $multiplier );
            }
        }

        //============================
        //Test restricted shipping methods
        $shipping_method_ids = $this->get_coupon_shipping_method_ids( $coupon );
        if ( sizeof( $shipping_method_ids ) > 0 ) {
            $chosen_shipping_methods = WC()->session->get( 'chosen_shipping_methods' );
            $chosen_shipping = empty( $chosen_shipping_methods ) ? '' : $chosen_shipping_methods[0]; 
            //$this->log('debug', 'Current shipping method: ' . ( $chosen_shipping === '' ? 'none' : $chosen_shipping ));
            $chosen_shipping = explode( ':', $chosen_shipping); //UPS and USPS stores extra data, seperated by colon
            $chosen_shipping = $chosen_shipping[0];
            
            if ( ! in_array( $chosen_shipping, $shipping_method_ids ) ) {
                throw new Exception( self::E_WC_COUPON_SHIPPING_METHOD_NOT_MET );
            }
        }
        
        //============================
        //Test restricted payment methods
        $payment_method_ids = $this->get_coupon_payment_method_ids( $coupon );
        if ( sizeof( $payment_method_ids ) > 0 ) {            
            $chosen_payment_method = isset( WC()->session->chosen_payment_method ) ? WC()->session->chosen_payment_method : array();    
            
            if ( ! in_array( $chosen_payment_method, $payment_method_ids ) ) {
                throw new Exception( self::E_WC_COUPON_PAYMENT_METHOD_NOT_MET );
            }
        }            


        //============================
        //Test restricted user ids and roles
        //NOTE: If both customer id and role restrictions are provided, the coupon matches if either the id or the role matches
        $coupon_customer_ids = $this->get_coupon_customer_ids( $coupon );
        $coupon_customer_roles = $this->get_coupon_customer_roles( $coupon );
        if ( sizeof( $coupon_customer_ids ) > 0 || sizeof( $coupon_customer_roles ) > 0 ) {        
            $user = wp_get_current_user();

            //If both fail we invalidate. Otherwise it's ok
            if ( ! in_array( $user->ID, $coupon_customer_ids ) && ! array_intersect( $user->roles, $coupon_customer_roles ) ) {
                throw new Exception( self::E_WC_COUPON_NOT_FOR_THIS_USER );
            }
        }
        
        //============================
        //Test excluded user roles
        $coupon_excluded_customer_roles = $this->get_coupon_excluded_customer_roles( $coupon );
        if ( sizeof( $coupon_excluded_customer_roles ) > 0 ) {        
            $user = wp_get_current_user();

            //Excluded customer roles
            if ( array_intersect( $user->roles, $coupon_excluded_customer_roles ) ) {
                throw new Exception( self::E_WC_COUPON_NOT_FOR_THIS_USER );
            }
        }

        //We use our own filter (instead of woocommerce_coupon_is_valid) for easier compatibility management
        //e.g. WC prior to 2.3.0 can't handle Exceptions; while 2.3.0 and above require exceptions
        do_action( 'wjecf_assert_coupon_is_valid', $coupon, $wc_discounts );

        if ( $wrap_coupon->get_minimum_amount() ) {
             $multiplier = self::min_value( floor( WC()->cart->subtotal / $wrap_coupon->get_minimum_amount() ), $multiplier );
        }


        $this->coupon_multiplier_values[ $wrap_coupon->get_code() ] = $multiplier;

        //$this->log( 'debug', 'Coupon ' . $wrap_coupon->get_code() . ' is valid. Multiplyer value: ' . $multiplier );

        return true;    // VALID!        
    }

    /**
     * Return the lowest multiplier value
     */
    private static function min_value( $value, $current_multiplier_value = null ) {
        return ( $current_multiplier_value === null || $value < $current_multiplier_value ) ? $value : $current_multiplier_value;
    }

    /**
     * The amount of times the minimum spend / quantity / subtotal values are reached
     * @return int 1 or more if coupon is valid, otherwise 0
     */
    public function get_coupon_multiplier_value( $coupon ) {
        $coupon = WJECF_WC()->get_coupon( $coupon );

        //If coupon validation was not executed, the value is unknown
        if ( ! isset( $this->coupon_multiplier_values[ WJECF_Wrap( $coupon )->get_code() ] ) ) {
            if ( ! $this->coupon_is_valid( true, $coupon ) ) {
                return 0;
            }
        }
        $multiplier = $this->coupon_multiplier_values[ WJECF_Wrap( $coupon )->get_code() ];

        //error_log("get multiplier " . WJECF_Wrap( $coupon )->get_code() . " = " . $multiplier );
        return $multiplier;
    }

    //Temporary storage
    private $coupon_multiplier_values = array();

    
    /**
     * (API FUNCTION)
     * The total amount of the products in the cart that match the coupon restrictions
     * since 2.2.2-b3
     */
    public function get_quantity_of_matching_products( $coupon, $wc_discounts = null ) {
        $coupon = WJECF_WC()->get_coupon( $coupon );
        $items = WJECF_WC()->get_discount_items( $wc_discounts );

        $qty = 0;
        foreach( $items as $item_key => $item ) {
            if ( $item->product === false ) continue;

            if ($this->coupon_is_valid_for_product( $coupon, $item->product, $item->object ) ) {
                $qty += $item->quantity;
            }
        }
        return $qty;
    }

    /**
     * (API FUNCTION)
     * The total value of the products in the cart that match the coupon restrictions
     * since 2.2.2-b3
     */
    public function get_subtotal_of_matching_products( $coupon, $wc_discounts = null ) {
        $coupon = WJECF_WC()->get_coupon( $coupon );
        $items = WJECF_WC()->get_discount_items( $wc_discounts );

        $subtotal_precise = 0;
        foreach( $items as $item_key => $item ) {
            if ( $item->product === false ) continue;

            if ($this->coupon_is_valid_for_product( $coupon, $item->product, $item->object ) ) {
                $subtotal_precise += $item->price;
            }
        }

        $subtotal = WJECF_WC()->wc_remove_number_precision( $subtotal_precise );
        return $subtotal;
    }

    /**
     * (API FUNCTION)
     * Test if coupon is valid for the product 
     * (this function is used to count the quantity of matching products)
     */
    public function coupon_is_valid_for_product( $coupon, $product, $values = array() ) {
        //Do not count the free products
        if ( isset( $values['_wjecf_free_product_coupon'] ) ) {
            return false;
        }

        //Get the original coupon, without values overwritten by WJECF
        $duplicate_coupon = $this->get_original_coupon( $coupon );

        //$coupon->is_valid_for_product() only works for fixed_product or percent_product discounts
        if ( ! $duplicate_coupon->is_type( WJECF_WC()->wc_get_product_coupon_types() ) ) {
            WJECF_Wrap( $duplicate_coupon )->set_discount_type( 'fixed_product' );
        }

        $valid = $duplicate_coupon->is_valid_for_product( $product, $values );
        //$this->log( sprintf("%s valid for %s? %s", WJECF_Wrap( $coupon )->get_code(), WJECF_Wrap( $product )->get_name(), $valid ? 'yes':'no' ) );
        return $valid;
    }

    
    // =====================

    /**
     * Get array of the selected shipping methods ids.
     * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
     * @return array Id's of the shipping methods or an empty array.
     */    
    public function get_coupon_shipping_method_ids( $coupon ) {
        $v = WJECF_Wrap( $coupon )->get_meta( '_wjecf_shipping_methods' );
        return is_array( $v ) ? $v : array();
    }

/**
 * Get array of the selected payment method ids.
 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
 * @return array  Id's of the payment methods or an empty array.
 */    
    public function get_coupon_payment_method_ids( $coupon ) {
        $v = WJECF_Wrap( $coupon )->get_meta( '_wjecf_payment_methods' );
        return is_array( $v ) ? $v : array();
    }
    
/**
 * Get array of the selected customer ids.
 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
 * @return array  Id's of the customers (users) or an empty array.
 */    
    public function get_coupon_customer_ids( $coupon ) {
        $v = WJECF_Wrap( $coupon )->get_meta( '_wjecf_customer_ids' );
        return WJECF()->sanitizer()->sanitize( $v, 'int[]' );
    }
    
/**
 * Get array of the selected customer role ids.
 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
 * @return array  Id's (string) of the customer roles or an empty array.
 */    
    public function get_coupon_customer_roles( $coupon ) {
        $v = WJECF_Wrap( $coupon )->get_meta( '_wjecf_customer_roles' );
        return is_array( $v ) ? $v : array();
    }    

/**
 * Get array of the excluded customer role ids.
 * @param  WC_Coupon|string $coupon The coupon code or a WC_Coupon object
 * @return array  Id's (string) of the excluded customer roles or an empty array.
 */    
    public function get_coupon_excluded_customer_roles( $coupon ) {
        $v = WJECF_Wrap( $coupon )->get_meta( '_wjecf_excluded_customer_roles' );
        return is_array( $v ) ? $v : array();
    }


// ===========================================================================
// User identification
// ===========================================================================

    /**
     * Get a list of the users' known email addresses
     * 
     * NOTE: Also called in AutoCoupon
     * 
     * @since 2.5.6 (Moved from WJECF_AutoCoupon)
     */
    public function get_user_emails() {
        if ( ! is_array($this->_user_emails) ) {
            $this->_user_emails = array();
            //Email of the logged in user
            if ( is_user_logged_in() ) {
                $current_user   = wp_get_current_user();
                $this->_user_emails[] = $current_user->user_email;
            }
        }
        $user_emails = $this->_user_emails;

        $billing_email = $this->get_session('billing_email', '');
        if ( is_email( $billing_email ) ) $user_emails[] = $billing_email;

        $user_emails = array_map( 'strtolower', $user_emails );
        $user_emails = array_map( 'sanitize_email', $user_emails );
        $user_emails = array_filter( $user_emails, 'is_email' );
        return array_unique( $user_emails );
    }

    /**
     * Called on action: woocommerce_checkout_update_order_review
     * 
     * Collects billing email address from the checkout-form
     */
    public function fetch_billing_email( $post_data ) {
        //post_data can be an array, or a query=string&like=this
        if ( ! is_array( $post_data ) ) {
            parse_str( $post_data, $posted );
        } else {
            $posted = $post_data;
        }

        if ( isset( $posted['billing_email'] ) ) {
            //$this->log('debug', 'billing:' . $posted['billing_email']);
            WJECF()->set_session( 'billing_email', $posted['billing_email'] );
        }
    }

    public function is_pro() {
        return $this instanceof WJECF_Pro_Controller;
    }

// ===========================================================================
// START - OVERWRITE INFO MESSAGES
// ===========================================================================

    /**
     * 2.3.4
     * If a 'Coupon applied' message is displayed by WooCommerce, replace it by another message (or no message)
     * @param WC_Coupon $coupon The coupon to replace the message for
     * @param string $new_message The new message. Set to empty string if no message must be displayed
     */
    public function start_overwrite_success_message( $coupon, $new_message = '' ) {
        $this->overwrite_coupon_message[ WJECF_Wrap( $coupon )->get_code() ] = array( $coupon->get_coupon_message( WC_Coupon::WC_COUPON_SUCCESS ) => $new_message );
        add_filter( 'woocommerce_coupon_message', array( $this, 'filter_woocommerce_coupon_message' ), 10, 3 );
    }

    /**
     * 2.3.4
     * Stop overwriting messages
     */
    public function stop_overwrite_success_message() {
        remove_filter( 'woocommerce_coupon_message', array( $this, 'filter_woocommerce_coupon_message' ), 10 );
        $this->overwrite_coupon_message = array();
    }

    private $overwrite_coupon_message = array(); /* [ 'coupon_code' => [ old_message' => 'new_message' ] ] */

    function filter_woocommerce_coupon_message( $msg, $msg_code, $coupon ) {
        if ( isset( $this->overwrite_coupon_message[ WJECF_Wrap( $coupon )->get_code() ][ $msg ] ) ) {
            $msg = $this->overwrite_coupon_message[ WJECF_Wrap( $coupon )->get_code() ][ $msg ];
        }
        return $msg;
    }

// ===========================================================================
// END - OVERWRITE INFO MESSAGES
// ===========================================================================

    /**
     * @since 2.4.4
     * 
     * Get a coupon, but inhibit the woocommerce_coupon_loaded to overwrite values.
     * @param WC_Coupon|string $coupon The coupon code or a WC_Coupon object
     * @return WC_Coupon The coupon object
     */
    public function get_original_coupon( $coupon ) {
        //Prevent returning the same instance
        if ( $coupon instanceof WC_Coupon ) {
            $coupon = WJECF_Wrap( $coupon )->get_code();
        }
        $this->inhibit_overwrite++;
        $coupon = WJECF_WC()->get_coupon( $coupon );
        $this->inhibit_overwrite--;
        return $coupon;
    }

    private $inhibit_overwrite = 0; 

    /**
     * @since 2.4.4
     * 
     * May coupon values be overwritten by this plugin upon load?
     * @return bool
     */    
    public function allow_overwrite_coupon_values() {
        return ( $this->inhibit_overwrite == 0 ) && ! is_admin();
    }

//============

    private $_session_data = null;
    /**
     * Read something from the session.
     * 
     * If key is omitted; all the session data will be returned as an array
     * 
     * @param string $key The key for identification
     * @param any $default The default value (Default: false)
     * 
     * @return The saved value if found, otherwise the default value
     */
    public function get_session( $key = null, $default = false ) {
        if ( $this->_session_data == null ) {
            if ( WC()->session == null ) {
                $this->log( 'error', 'Trying to access WC()->session while it was not yet initialized.' );
                return null;
            }
            $this->_session_data = WC()->session->get( '_wjecf_session_data', array() );
        }

        if ( ! isset( $key ) ) return $this->_session_data;
        if ( ! isset( $this->_session_data[ $key ] ) ) return $default;
        return $this->_session_data[ $key ];
    }

    /**
     * Save something in the session
     * 
     * @param string $key The key for identification
     * @param anything $value The value to store. Use 'null' to remove the value
     */
    public function set_session( $key, $value ) {
        if ( $this->_session_data == null) {
            if ( WC()->session == null ) {
                $this->log( 'error', 'Trying to access WC()->session while it was not yet initialized.' );
                return null;
            }
            $this->_session_data = WC()->session->get( '_wjecf_session_data', array() );
        }
        if ( is_null( $value ) ) {
            unset( $this->_session_data[ $key ] );
        } else {
            $this->_session_data[ $key ] = $value;
        }

        WC()->session->set( '_wjecf_session_data', $this->_session_data );
    }

    /**
     * (Copied from class-woocommerce.php) What type of request is this?
     *
     * @since 2.6.2
     * @param  string $type admin, ajax, cron or frontend.
     * @return bool
     */
    public function is_request( $type ) {
        switch ( $type ) {
            case 'admin':
                return is_admin();
            case 'ajax':
                return defined( 'DOING_AJAX' );
            case 'cron':
                return defined( 'DOING_CRON' );
            case 'frontend':
                return ( ! is_admin() || defined( 'DOING_AJAX' ) ) && ! defined( 'DOING_CRON' );
        }
    }
    
    /**
     * Get overwritable template filename
     * 
     * Template can be overwritten in wp-content/themes/YOUR_THEME/woocommerce-auto-added-coupons/
     * @param string $template_name 
     * @return string Template filename
     */
    public function get_template_filename( $template_name ) {
        $template_path = 'woocommerce-auto-added-coupons';

        //Get template overwritten file
        $template = locate_template( trailingslashit( $template_path ) . $template_name );

        // Get default template
        if ( ! $template ) {
            $plugin_template_path = plugin_dir_path( dirname(__FILE__) ) . 'templates/';
            $template = $plugin_template_path . $template_name;
        }

        return $template;
    }

    /**
     * Include a template file, either from this plugins directory or overwritten in the themes directory
     * @param type $template_name 
     * @return type
     */
    public function include_template( $template_name, $variables = array() ) {
        extract( $variables );
        include( $this->get_template_filename( $template_name ) );
    }

// ========================
// INFO ABOUT WJECF PLUGIN
// ========================

    /**
     * Filename of this plugin including the containing directory.
     * @return string
     */
    public function plugin_file() {
        $filename = $this->is_pro() ? "woocommerce-jos-autocoupon-pro.php" : "woocommerce-jos-autocoupon.php" ;
        return trailingslashit( basename( dirname( dirname( __FILE__ ) ) ) ) . $filename;
    }

    public function plugin_basename() {
        return plugin_basename( $this->plugin_file() );
    }


    /**
     * url to the base directory of this plugin (wp-content/woocommerce-jos-autocoupon/) with trailing slash
     * @return string
     */
    public function plugin_url( $suffix = '' ) {
        return plugins_url( '/', dirname( __FILE__ ) ) . $suffix;
    } 

    public function plugin_version() {
        return WJECF_VERSION;
    }

// ========================
// LOGGING
// ========================


    /**
     * Log a message for debugging.
     * 
     * If debug_mode is false; messages with level 'debug' will be ignored.
     * 
     * @param string $level The level of the message. e.g. 'debug' or 'warning'
     * @param string $string The message to log
     * @param int $skip_backtrace Defaults to 0, amount of items to skip in backtrace to fetch class and method name
     */
    public function log( $level, $message = null, $skip_backtrace = 0) {
        if ( ! $this->debugger ) {
            $this->debugger = $this->get_plugin( 'WJECF_Debug' );
        }
        
        if ( $this->debugger ) {
            $this->debugger->log( $level, $message, $skip_backtrace + 1 );
        }
    }
    private $debugger = null;
    
}

/**
 * Class that prevents an action or filter to be recursively called
 */
class WJECF_Action_Or_Filter {

    private $tag; 
    private $function_to_add; 
    private $priority; 
    private $accepted_args; 
    private $limit_calls;

    /**
     *  
     * @param string $tag name of the action or filter
     * @param callable $function_to_add 
     * @param int $priority 
     * @param int $accepted_args 
     * @param int $limit_calls When > 0 the calls will be limited to this amount (prevents recursive calls)
     */
    private function __construct( $tag, $function_to_add, $priority, $accepted_args, $limit_calls = 0 ) {
        $this->tag = $tag; 
        $this->function_to_add = $function_to_add; 
        $this->priority = $priority; 
        $this->accepted_args = $accepted_args; 

        $this->limit_calls = $limit_calls;
    }

    private $inhibit = false;
    private $counter = 0;

    //Must be public for WC
    public function execute() {
        if ($this->inhibit) return;

        $this->counter++;

        $this->inhibit = true;
        $func_args = func_get_args(); // $func_args variable required for PHP5.2
        $retval = call_user_func_array( $this->function_to_add, $func_args );
        $this->inhibit = false;

        if ($this->limit_calls > 0 && $this->counter >= $this->limit_calls) {
            remove_action( $this->tag, array( $this, 'execute' ), $this->priority ); //unhook the action
        }

        return $retval;
    }

    /**
     * Same as WordPress add_action(), but prevents the callback to be recursively called
     * 
     * @param string $tag 
     * @param callable $function_to_add 
     * @param int $priority 
     * @param int $accepted_args 
     */
    public static function action( $tag, $function_to_add, $priority = 10, $accepted_args = 1, $limit_calls ) {
        $me = new WJECF_Action_Or_Filter( $tag, $function_to_add, $priority, $accepted_args, $limit_calls );
        add_action( $tag, array( $me, 'execute' ), $priority, $accepted_args );
    }
}
