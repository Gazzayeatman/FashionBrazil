<?php dpsp_admin_header(); ?>

<div class="dpsp-page-wrapper dpsp-page-extensions dpsp-sub-page-opt-in-hound wrap">

	<div id="opt-in-hound-promo-box">
		<div id="opt-in-hound-promo-box-banner">
			<img src="<?php echo DPSP_PLUGIN_DIR_URL . '/assets/img/opt-in-hound-banner.png'; ?>" />
		</div>

		<div id="opt-in-hound-promo-box-inner">

			<!-- Title and Sub-title -->
			<br /><h1 class="opt-in-hound-promo-box-title"><?php echo __( 'Increase your Email Subscribers', 'social-pug' ); ?><br /><span style="display: block; margin-top: 10px;"><?php echo __( 'in a beautiful, simple and easy way', 'social-pug' ); ?></span></h1>

			<hr />

			<h2 class="opt-in-hound-promo-box-sub-title"><?php echo __( 'Many of you wanted email marketing tools built in <strong>Social Pug</strong>.', 'social-pug' ); ?></h2>

			<h2 class="opt-in-hound-promo-box-sub-title"><?php echo __( 'Because we wanted to bring these awesome features to you, yet maintain Social Pug\'s simplicity, we have decided to build another great little plugin for you, named <strong>Opt-In Hound</strong>.', 'social-pug' ); ?></h2>

			<!-- Call to Action -->
			<div class="opt-in-hound-promo-box-cta">
				<a class="button-primary" href="<?php echo admin_url( 'plugin-install.php?s=opt-in-hound&tab=search&type=term' ); ?>"><?php echo __( 'Try it Now. It\'s Free.', 'social-pug' ); ?></a>
			</div>

		</div>
	</div>

</div>