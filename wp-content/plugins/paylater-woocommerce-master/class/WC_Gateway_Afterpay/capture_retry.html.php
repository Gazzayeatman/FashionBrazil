<?php
/**
* Capture Idempotency Display Page
* @var WC_Gateway_Afterpay $this
*/

# get the directory for the loader image
$loader_url = plugins_url("images/ajax-loader.gif", dirname(dirname(__FILE__)));
?>

<img class="afterpay-logo" src="https://static.afterpay.com/integration/checkout/logo-afterpay-colour-86x18.png" srcset="https://static.afterpay.com/integration/checkout/logo-afterpay-colour-86x18.png 1x, https://static.afterpay.com/integration/checkout/logo-afterpay-colour-86x18@2x.png 2x, https://static.afterpay.com/integration/checkout/logo-afterpay-colour-86x18@3x.png 3x" width="86" height="18" alt="Afterpay" />
<div class="afterpay-loader img-wrapper"><img src="<?php echo $loader_url; ?>" alt="Loading..." /></div>

<p class="result-message">Please wait while the transaction is being processed</p>
<a class="checkout-link" style="display:none" href="<?php echo wc_get_checkout_url(); ?>">Go back to Checkout</a>

<script>
    jQuery(document).ready(function($) {

        var count = 0;

        setInterval(function() { 
            
            // get the variable from the URL
            var quote_id = findGetParameter("quote_id");
            var token = findGetParameter("token");
            var nonce = findGetParameter("nonce");
            
            
            var base_url = window.location.protocol + "//" + window.location.host + "/" + window.location.pathname.split('/')[1];
            
            var target_url = base_url; 
            var target_url = target_url + "?afterpay_capture=" + true; 
            var target_url = target_url + "&quote_id=" + quote_id; 
  

            count++;
                    
            $.ajax({
                type: "POST",
                url: target_url,
                data: { 
                        "quote_id"  : quote_id,
                        "nonce"     : nonce
                    },
                success: function(data) {
                    if (typeof data !== "undefined" && data.length) {
                        data = $.parseJSON(data);

                        // if the capture is successful, redirect the Customer to Success Page
                        if (data.success) {                    
                            $(".afterpay-loader").hide();
            
                            if (data.redirect)
                                window.location.href = data.redirect;
                        }
                    }
                }
            });
            
            if (count > 4) {
                $(".result-message").text("Afterpay transaction details cannot be retrieved. Please check your email for Afterpay notifications before reattempting transaction.");
                $(".checkout-link").show();
            }

        }, 3000);//time in milliseconds 

        function findGetParameter(parameterName) {
            var result = null,
                tmp = [];
            location.search
                .substr(1)
                .split("&")
                .forEach(function (item) {
                  tmp = item.split("=");
                  if (tmp[0] === parameterName) result = decodeURIComponent(tmp[1]);
                });
            return result;
        }
    
    });
</script>
<style>
    .post-navigation { display: none; }
</style>