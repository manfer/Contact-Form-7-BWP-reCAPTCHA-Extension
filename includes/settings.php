<?php

	if ( defined('ALLOW_CF7_BWP_CAPT_INCLUDE') === false )
		die( 'no direct access' );

	function show_donation() {
?>
		<div id="donate-button">
				<div id="donate-text">If you find this useful please consider buying me a coffee. Thanks.</div>
				<div id="donate-form">
					<form action="https://www.paypal.com/cgi-bin/webscr" method="post">
						<input type="hidden" name="cmd" value="_s-xclick">
						<input type="hidden" name="encrypted" value="-----BEGIN PKCS7-----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-----END PKCS7-----">
						<input type="image" src="https://www.paypalobjects.com/es_ES/ES/i/btn/btn_donateCC_LG.gif" border="0" name="submit" alt="PayPal - The safer, easier way to pay online!">
						<img alt="" border="0" src="https://www.paypalobjects.com/es_ES/i/scr/pixel.gif" width="1" height="1">
					</form>
				</div>
			</div>

<?php
	}
?>

	<?php show_donation(); ?>
	<div class="wrap">
		<a name="cf7_bwp_capt"></a>

		<h2><?php _e( 'CF7 BWP reCAPTCHA Extension Options', 'cf7_bwp_capt' ); ?></h2>

		<?php settings_errors(); ?>

		<p><?php _e( 'Contact form 7 better wordpress reCAPTCHA extension let\'s you add a reCAPTCHA to your contact form. Just configure here the look&feel you want for the reCAPTCHA and go to your Contact form 7 configuration page to add a reCAPTCHA tag to your form.', 'cf7_bwp_capt' ); ?></p>

		<form method="post" action="options.php">

			<?php settings_fields( $this->options_name . '_group' ); ?>

			<?php do_settings_sections( $this->options_name . '_page' ); ?>

			<p class="submit"><input type="submit" class="button-primary" title="<?php _e( 'Save Options' ) ?>" value="<?php _e( 'Save Changes' ) ?> &raquo;" /></p>
		</form>
	</div>