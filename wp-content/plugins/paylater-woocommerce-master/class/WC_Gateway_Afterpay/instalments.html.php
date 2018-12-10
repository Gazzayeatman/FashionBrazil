<?php
/**
 * Afterpay Checkout Instalments Display
 * @var WC_Gateway_Afterpay $this
 */

if ($this->settings['testmode'] != 'production') {
    ?><p class="afterpay-test-mode-warning-text"><?php _e( 'TEST MODE ENABLED', 'woo_afterpay' ); ?></p><?php
}

$order_button_text = !empty($this->order_button_text) ? $this->order_button_text : 'Place order';

?>
<ul class="form-list">
    <li class="form-alt">
        <div class="instalment-info-container" id="afterpay-checkout-instalment-info-container">
            <p class="header-text">
                <?php _e( 'Your payment schedule. Four interest-free payments totalling', 'woo_afterpay' ); ?>
                <strong><?php echo $this->display_price_html($order_total); ?></strong>
            </p>
            <div class="instalment-wrapper">
                <div class="instalment">
                    <p class="instalment-header-text"><?php echo $this->display_price_html($instalments[0]); ?></p>
                    <div class="img-wrapper"><img src="<?php echo "{$this->plugin_url}/images/checkout/circle_1@2x.png"; ?>" alt="" /></div>
                    <p class="instalment-footer-text"><?php _e( 'First instalment', 'woo_afterpay' ); ?></p>
                </div>
                <div class="instalment">
                    <p class="instalment-header-text"><?php echo $this->display_price_html($instalments[1]); ?></p>
                    <div class="img-wrapper"><img src="<?php echo "{$this->plugin_url}/images/checkout/circle_2@2x.png"; ?>" alt="" /></div>
                    <p class="instalment-footer-text"><?php _e( '2 weeks later', 'woo_afterpay' ); ?></p>
                </div>
                <div class="instalment">
                    <p class="instalment-header-text"><?php echo $this->display_price_html($instalments[2]); ?></p>
                    <div class="img-wrapper"><img src="<?php echo "{$this->plugin_url}/images/checkout/circle_3@2x.png"; ?>" alt="" /></div>
                    <p class="instalment-footer-text"><?php _e( '4 weeks later', 'woo_afterpay' ); ?></p>
                </div>
                <div class="instalment">
                    <p class="instalment-header-text"><?php echo $this->display_price_html($instalments[3]); ?></p>
                    <div class="img-wrapper"><img src="<?php echo "{$this->plugin_url}/images/checkout/circle_4@2x.png"; ?>" alt="" /></div>
                    <p class="instalment-footer-text"><?php _e( '6 weeks later', 'woo_afterpay' ); ?></p>
                </div>
            </div>
        </div>
        <p class="footer-text">
            <?php _e( "You&rsquo;ll be redirected to the Afterpay website when you click \"{$order_button_text}\".", 'woo_afterpay' ); ?>
            <br />
            <a href="https://www.afterpay.com/terms" target="_blank"><?php _e( 'Terms &amp; Conditions', 'woo_afterpay' ); ?></a>
        </p>
    </li>
</ul>
<div class="what-is-afterpay-container">
    <a id="checkout-what-is-afterpay-link" href="#" target="_blank"><?php _e( 'What is Afterpay?', 'woo_afterpay' ); ?></a>
    <a href="#afterpay-what-is-modal"></a>
 </div>