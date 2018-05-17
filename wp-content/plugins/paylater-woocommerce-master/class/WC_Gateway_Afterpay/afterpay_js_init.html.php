<?php
/**
* Afterpay Javascript initialisation code
*/
?>
<script type="text/javascript" src="<?php echo $this->get_web_url() . 'afterpay.js'; ?>"></script>
<script type="text/javascript">
	(function(token) {
		var afterpay_js_interval = setInterval(function() {
			if (typeof AfterPay != 'undefined') {
				clearInterval(afterpay_js_interval);

				var api_version = "<?php echo $this->settings['api-version']; ?>";

				if (typeof AfterPay.initialize === "function" && api_version == "v1" ) { 
				    // safe to use the function
				    AfterPay.initialize(<?php if (!is_null($init_object)): echo json_encode($init_object); endif; ?>);
				}
				else {
					AfterPay.init(<?php if (!is_null($init_object)): echo json_encode($init_object); endif; ?>);
				}
				
				AfterPay.<?php echo $lightbox_launch_method; ?>({
					token: token
				});
			}
		}, 200);
	})(<?php echo json_encode($token); ?>);
</script>