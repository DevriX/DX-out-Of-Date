<div class="wrap">
	<div id="icon-edit" class="icon32 icon32-base-template"><br></div>
	<h2><?php _e( "Out Of Date", 'ood' ); ?></h2>

	<form id="dx-ood-form" action="options.php" method="POST">
			<?php settings_fields( 'ood_setting' ) ?>
			
			<?php do_settings_sections( 'dx-ood' ) ?>
			
			<?php submit_button(); ?>
	</form> 
	
	<div class="page-footer">
		<?php _e( 'Plugin created by', 'ood' ); ?>
		<a href="http://devwp.eu/" target="_blank">nofearinc</a>.
		<?php _e( 'More plugins available here:', 'ood' ); ?>
		<a href="http://profiles.wordpress.org/nofearinc/" target="_blank">http://profiles.wordpress.org/nofearinc/</a>
	</div>
</div>