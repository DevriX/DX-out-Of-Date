<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-base-template"><br></div>
	<h2><?php esc_html_e( 'Out Of Date', 'dx-out-of-date' ); ?></h2>

	<form id="dx-ood-form" action="options.php" method="POST">
			<?php settings_fields( 'ood_setting' ); ?>

			<?php do_settings_sections( 'dx-ood' ); ?>

			<?php submit_button(); ?>
	</form>

	<div class="page-footer">
		<?php esc_html_e( 'Plugin created by', 'dx-out-of-date' ); ?>
		<a href="http://devwp.eu/" target="_blank">nofearinc</a>.
		<?php esc_html_e( 'More plugins available here:', 'dx-out-of-date' ); ?>
		<a href="http://profiles.wordpress.org/nofearinc/" target="_blank">http://profiles.wordpress.org/nofearinc/</a>
	</div>
</div>
