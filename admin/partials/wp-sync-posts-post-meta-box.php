<div class="wpsp-post-sync-form" id="wpsp-post-sync-form">
	<div class="wpsp-form">
		<div class="input-group">
			<p>
				<label class="post-attributes-label" for="wpsp-connection">Connection</label>
			</p>
			<select name="wpsp_connection" id="wpsp-connection">
				<?php foreach ( $options[ 'wpsp_connections' ] as $connection ) : ?>
					<option value="<?php echo $connection->ID; ?>"><?php echo $connection->name; ?></option>
				<?php endforeach; ?>
			</select>
		</div>

		<div class="input-group">
			<p>
				<label class="post-attributes-label" for="wpsp-post-selection">Remote Post Selection</label>
			</p>
			<select name="wpsp_post_selection" id="wpsp-post-selection">
				<option value="find">Find Remote By Slug</option>
				<option value="new">Create A New Post</option>
				<option value="manual">Set Post Manually</option>
			</select>
		</div>

		<div class="input-group hidden">
			<p>
				<label class="post-attributes-label" for="wpsp-new-post">Manual Post ID</label>
			</p>
			<input type="text" name="wpsp_remote_post_id" id="wpsp-new-post">
		</div>
		<input type="hidden" name="wpsp_local_post_id" id="wpsp-local-post-id" value="<?php the_ID(); ?>">
		<div class="button-group">
			<button class="button-primary" id="wpsp-submit-push">Push To</button>
			<button class="button-primary" id="wpsp-submit-pull">Pull From</button>
			<input type="hidden" id="wpsp-sync-nonce" value="<?php echo wp_create_nonce( 'wpsp-sync' ); ?>">
		</div>
		<div class="wpsp-notices">
			<p class="wpsp-notice wpsp-notice-error hidden" id="wpsp-error-message">There was an error</p>
			<p class="wpsp-notice wpsp-notice-success hidden" id="wpsp-success-message">Your post has been pushed successfully</p>
		</div>
	</div>

	<div class="wpsp-confirmation hidden">
		<p class="wpsp-confirmation-message-pull hidden">Please confirm you want to replace the content of this post with the content of the post from the remote site.</p>
		<p class="wpsp-confirmation-message-push hidden">Please confirm you want to replace the content on the remote site with the content of this post.</p>
		<button class="button-primary" id="wpsp-submit-confirm">Confirm</button>
		<button class="button-secondary" id="wpsp-submit-cancel">Cancel</button>
	</div>
</div>
