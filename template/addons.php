<h2>List Add-Ons</h2>
<div id="poststuff">

	<div class="postbox-container">
		<?php foreach ( Legull_Conf::retrieve()->addons as $addon ) : ?>
			<div class="postbox addon">
				<h3 class="hndle">
					<span><?php echo $addon['name']; ?></span>
				</h3>

				<div class="inside">
					<p><?php echo $addon['description']; ?></p>
					<a href="<?php echo $addon['remote_url']; ?>" target="_blank" class="button"><?php _e( 'More Info' ); ?></a>
				</div>
			</div>
		<?php endforeach; ?>
	</div>
</div>