<?php

if( !defined( 'ABSPATH' ) ) exit;

class W2MLAYBUY_Gateway extends WC_Payment_Gateway {

    public function __construct() {
        $this->id = 'laybuy';
        $this->icon = apply_filters( 'w2mlaybuy_gateway_icon', plugin_dir_url(__FILE__) . 'images/laybuy-button.png' );
        $this->has_fields   = false;
        $this->method_title = 'Laybuy';

        $this->init_form_fields();
        $this->init_settings();

        $this->title        = $this->get_option( 'title' );
        $this->description  = $this->get_option( 'description' );
        add_action( 'woocommerce_update_options_payment_gateways_' . $this->id, array( $this, 'process_admin_options' ) );
        add_action( 'woocommerce_thankyou_' . $this->id, array( $this, 'thankyou_page' ) );

    }

    /**
     * Initialize Gateway Settings Form Fields
     */
    public function init_form_fields() {
        $this->form_fields = array(
            'enabled' => array(
                'title'   => __( 'Enable/Disable', 'w2mlaybuy' ),
                'type'    => 'checkbox',
                'label'   => __( 'Enable Laybuy', 'w2mlaybuy' ),
                'default' => 'no'
            ),
            'title' => array(
                'title'       => __( 'Title', 'w2mlaybuy' ),
                'type'        => 'text',
                'description' => __( 'This is the title for this payment method. The customer will see this during checkout.', 'w2mlaybuy' ),
                'default'     => __( 'Laybuy', 'w2mlaybuy' ),
                'desc_tip'    => true,
            ),
            'description' => array(
                'title'       => __( 'Description', 'w2mlaybuy' ),
                'type'        => 'textarea',
                'description' => __( 'This is the description for this payment method. The customer will see this during checkout.', 'w2mlaybuy' ),
                'default'     => __( 'Receive your order now but split your payments over 6 weeks', 'w2mlaybuy' ),
                'desc_tip'    => true,
            ),		
            'price_breakdown_option_product_page' => array(
                'title'       => __( 'Price breakdown on products', 'w2mlaybuy' ),
                'type'        => 'select',
                'description' => __( 'Select how you want to display the price breakdown on each product page.', 'w2mlaybuy' ),
                'default'     => 'disable',
                'options'     => array(
                    'disable' => __( 'Disable', 'w2mlaybuy' ),
                    'text_only' => __( 'Text Only', 'w2mlaybuy' ),
                    'text_and_table' => __( 'Text and Table', 'w2mlaybuy' ),
                )
            ),
            'price_breakdown_option_checkout_page' => array(
                'title'       => __( 'Price breakdown in checkout', 'w2mlaybuy' ),
                'type'        => 'select',
                'description' => __( 'Select how you want to display the price breakdown in the checkout page.', 'w2mlaybuy' ),
                'default'     => 'disable',
                'options'     => array(
                    'disable' => __( 'Disable', 'w2mlaybuy' ),
                    'text_only' => __( 'Text Only', 'w2mlaybuy' ),
                    'text_and_table' => __( 'Text and Table', 'w2mlaybuy' ),
                )
            ),
            'environment' => array(
                'title'       => __( 'Environment', 'w2mlaybuy' ),
                'type'        => 'select',
                'description' => __( 'Select the sandbox environment for testing purposes only.', 'w2mlaybuy' ),
                'default'     => 'production',
                'options'     => array(
                    'sandbox' => __( 'Sandbox', 'w2mlaybuy' ),
                    'production' => __( 'Production', 'w2mlaybuy' )
                ),
                'desc_tip'    => true,
            ),
            'merchant_id' => array(
                'title'       => __( 'Merchant ID', 'w2mlaybuy' ),
                'type'        => 'text',
                'description' => __( 'This will be supplied by laybuy.com', 'w2mlaybuy' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
            'api_key' => array(
                'title'       => __( 'API Key', 'w2mlaybuy' ),
                'type'        => 'text',
                'description' => __( 'This will be supplied by laybuy.com', 'w2mlaybuy' ),
                'default'     => '',
                'desc_tip'    => true,
            ),
        );
    }

    public function payment_fields() {
        if ( $description = $this->get_description() ) {
            $description = apply_filters( 'laybuy_modify_payment_description', $description, $this->get_order_total() );
            echo wpautop( wptexturize( $description ) );
        }
    }

    /**
     * This is the method called when they select this gateway as mode of payment.
     */
    public function process_payment( $order_id ) {

        error_log($order_id);
        if( get_post_meta( $order_id, '_laybuy_token', true ) ) {


            if( $this->is_sandbox_enabled() ) {
                $redirect = SANDBOX_PAY_API_ENDPOINT . 'pay/' . get_post_meta( $order_id, '_laybuy_token', true );
            } else {
                $redirect = PRODUCTION_PAY_API_ENDPOINT . 'pay/' . get_post_meta( $order_id, '_laybuy_token', true );
            }

            return array(
                'result'   => 'success',
                'redirect' => $redirect,
            );
        }

        $order = wc_get_order( $order_id );

        /**
         * We need to make our own return url in order to handle the cancel state correctly
         * If the user wants to cancel the order explicitly(intended) then we need to handle this
         */
        $request_data = array(
            'amount'    => WC()->cart->total,
            'currency'  => $order->get_currency(),
            'returnUrl' => $this->get_custom_return_url( $order ),
            'merchantReference' => '#' . uniqid() . $order_id . time(),
            'customer' => array(
                'firstName' => $order->get_billing_first_name(),
                'lastName' => $order->get_billing_last_name(),
                'email' => $order->get_billing_email(),
                'phone' => $order->get_billing_phone()
            ),
            'billingAddress' => array(
                "address1" => $order->get_billing_address_1(),
                "city" => $order->get_billing_city(),
                "postcode" => $order->get_billing_postcode(),
                "country" => $order->get_billing_country(),
            ),
            'items' => array()
        );

        // $request_data['items'][] = array(
        //     'id' => 'order_total',
        //     'description' => 'Total amount of order as stated on cart and checkout page',
        //     'quantity' => 1,
        //     'price' => $this->get_order_total()
        // );

        foreach ( WC()->cart->get_cart() as $cart_item_key => $cart_item ) {
            $_product = $cart_item['data'];
            $_product_id = $cart_item['product_id'];

            if ( $_product && $_product->exists() && $cart_item['quantity'] > 0 && apply_filters( 'woocommerce_cart_item_visible', true, $cart_item, $cart_item_key ) ) {

                $request_data['items'][] = array(
                    'id' => $_product_id,
                    'description' => $_product->get_name(),
                    'quantity' => $cart_item['quantity'],
                    'price' => w2mlaybuy_get_product_price( $_product )
                );
            }
        }

        if( 0 < w2mlaybuy_get_cart_shipping_total() ) {
            $request_data['items'][] = array(
                'id' => 'shipping_fee',
                'description' => 'Shipping Fee',
                'quantity' => 1,
                'price' => w2mlaybuy_get_cart_shipping_total()
            );
        }

        if( 0 < WC()->cart->get_taxes_total() && 'excl' == WC()->cart->tax_display_cart ) {
            $request_data['items'][] = array(
                'id' => 'tax_total',
                'description' => 'Tax',
                'quantity' => 1,
                'price' => WC()->cart->get_taxes_total()
            );
        }

        foreach ( WC()->cart->get_coupons() as $code => $coupon ) {

            if( 0 < w2mlaybuy_get_coupon_amount( $coupon ) ) {
                $request_data['items'][] = array(
                    'id' => 'coupon_' . $code,
                    'description' => 'Coupon: ' . $coupon . ' applied.',
                    'quantity' => 1,
                    'price' => -1 * abs( w2mlaybuy_get_coupon_amount( $coupon ) )
                );
            }

        }

        if( $this->is_sandbox_enabled() ) {
            $create_order_api_endpoint = SANDBOX_API_ENDPOINT . CREATE_ORDER_SUFFIX;
        } else {
            $create_order_api_endpoint = PRODUCTION_API_ENDPOINT . CREATE_ORDER_SUFFIX;
        }

        $request = wp_remote_post(
            $create_order_api_endpoint,
            array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode( $this->get_merchant_id() . ':' . $this->get_api_key() )
                ),
                'body' => json_encode( $request_data )
            )
        );

        if( is_wp_error( $request ) ) {
            $order->update_status( 'failed', __( $request->get_error_message(), 'w2mlaybuy' ) );
            wc_add_notice( __( 'Please try to place the order again, Error message: ', 'w2mlaybuy' ) . $request->get_error_message(), 'error' );
            return;
        }

        $response = json_decode( $request['body'] );

        if( 'error' == strtolower( $response->result ) ) {
            $order->update_status( 'failed', __( $response->error, 'w2mlaybuy' ) );
            wc_add_notice( __( 'Payment error with Laybuy system: ', 'w2mlaybuy' ) . $response->error, 'error' );
            return;
        } else {
            return array(
                'result'   => 'success',
                'redirect' => $response->paymentUrl,
            );
        }

    }

    /**
     * Output for the order received page.
     */
    public function thankyou_page( $order_id ) {
        $order = wc_get_order( $order_id );

        $confirm_order_request = $this->confirm_order( $order_id );
        if( $confirm_order_request['result'] ) {
            $order->update_status( 'processing' );
        } else {
            $order->update_status( 'failed', $confirm_order_request['error'] );
        }
    }

    public function confirm_order( $order_id ) {

        if( $this->is_sandbox_enabled() ) {
            $endpoint = SANDBOX_API_ENDPOINT;
        } else {
            $endpoint = PRODUCTION_API_ENDPOINT;
        }

        $endpoint .= CONFIRM_ORDER_SUFFIX;

        if( !get_post_meta( $order_id, '_laybuy_token', true ) ) {
            return array(
                'result' => false,
                'error'  => 'Token is not saved in database'
            );
        }

        $request_data = array(
            'token' => get_post_meta( $order_id, '_laybuy_token', true )
        );

        $request = wp_remote_post( $endpoint,
            array(
                'headers' => array(
                    'Authorization' => 'Basic ' . base64_encode( $this->get_merchant_id() . ':' . $this->get_api_key() )
                ),
                'body' => json_encode( $request_data )
            )
        );

        $response = json_decode( $request['body'] );

        if( 'error' == strtolower( $response->result ) ) {
            return array(
                'result' => false,
                'error'  => $result->error
            );
        } else {
            update_post_meta( $order_id, '_laybuy_order_id', $response->orderId );
            return array(
                'result' => true,
                'message'  => 'Order is confirmed'
            );
        }
    }

    public function get_merchant_id()  {
        return $this->get_option( 'merchant_id' );
    }

    public function get_api_key()  {
        return $this->get_option( 'api_key' );
    }

    public function is_sandbox_enabled() {
        return 'sandbox' == $this->get_option( 'environment' );
    }

    /**
     * Since Laybuy only accepts a return url we need to somehow create our own to accommodate cancelling.
     */
    public function get_custom_return_url($order) {
        // when creating the cancel or success form see this file WC_Form_Handler line 30

        $custom_return_url = add_query_arg( array(
            'gateway_id'      => 'w2mlaybuy',
            'order'        => $order->get_order_key(),
            'order_id'     => $order->get_id(),
        ), get_home_url() );

        return $custom_return_url;
    }


    public function get_active_price( $product_id ) {
        $product = wc_get_product( $product_id );

        if( 'yes' == get_option( 'woocommerce_prices_include_tax' ) ) {
            return wc_get_price_including_tax( $product );
        } else {
            return $product->get_price();
        }
    }

}