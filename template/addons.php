<div id="poststuff">
	list of addons | upload addon
	<div class="postbox-container">
	<?php foreach( Legull_Conf::retrieve()->addons as $addon ) : ?>
		<div class="postbox addon">
			<h3 class="hndle">
				<span><?php echo $addon['name']; ?></span>
			</h3>
			<div class="inside">
				<h4>Description:</h4>
				<p><?php echo $addon['description']; ?></p>
				<a href="<?php echo $addon['remote_url']; ?>" target="_blank" class="button">Download</a>
			</div>
		</div>
	<?php endforeach; ?>
	</div>
</div>