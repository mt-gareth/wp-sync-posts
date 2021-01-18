<?php
/**
 * Provide a admin area view for the plugin
 *
 * This file is used to markup the admin-facing aspects of the plugin.
 *
 * @link       motiontactic.com
 * @since      1.0.0
 *
 * @package    Wp_Sync_Posts
 * @subpackage Wp_Sync_Posts/admin/partials
 */
?>
<div class="wrap wpsp wpsp-admin-settings">
	<div class="heading">
		<h1>WP Sync Posts</h1>
	</div>
	<div class="content">
		<div class="tabs">
			<div class="tab" data-tab-target="connections">Connections</div>
			<div class="tab" data-tab-target="settings">Settings</div>
		</div>
		<div class="tabbed-content">
			<div class="content-section active" data-tab="connections">
				<?php if ( count( $options[ 'wpsp_connections' ] ) > 0 ) : ?>
					<h2>Existing Connections</h2>
					<?php include 'wp-sync-posts-admin-display-table.php'; ?>
				<?php endif; //existing connections check?>
				<h2 class="connection-form-title" data-new="Create New Connection" data-update="Update Connection">Create New Connection</h2>
				<form class="add-update-connection" id="add-update-connection">
					<div class="input-group">
						<label>
							<span>Name</span>
							<input type="text" name="name">
						</label>
					</div>
					<div class="input-group">
						<label>
							<span>URL</span>
							<input type="text" name="url">
						</label>
					</div>
					<div class="input-group">
						<label>
							<span>Key</span>
							<input type="text" name="key">
						</label>
					</div>
					<div class="input-group find-replace-group">
						<h4>Find and Replace</h4>
						<div id="add-connection-find-replace" data-count="0">
							<div class="items" data-group="find-replace">
								<div class="item-content">
									<label>
										<span>Find</span>
										<input type="text" id="inputFind" placeholder="Find" data-name="find">
									</label>
									<label>
										<span>Replace</span>
										<input type="text" id="inputReplace" placeholder="Replace" data-name="replace">
									</label>
								</div>
								<div class="pull-right repeater-remove-btn">
									<button class="btn btn-danger remove-btn">Remove</button>
								</div>
							</div>
							<div class="inputs"></div>
							<button class="repeater-add-btn">Add</button>
						</div>
					</div>

					<input type="hidden" name="connection-id" value="">

					<button class="connection-clear hidden" id="connection-clear">Cancel</button>
					<input type="hidden" id="wpsp-connection-nonce" value="<?php echo wp_create_nonce( 'wpsp-connection' ); ?>">
					<span id="error-message">Invalid Connection</span>
					<input type="submit" value="Save" id="add-update-connection-submit" disabled>
				</form>
			</div>
			<div class="content-section" data-tab="settings">
				<div class="input-group">
					<label>
						<input type="checkbox" name="wpsp-allow-push" id="wpsp-allow-push" <?php echo $options[ 'wpsp_allow_push' ] === 'true' ? 'checked' : ''; ?>>
						<span>Allow Push</span>
					</label>
				</div>
				<div class="input-group">
					<label>
						<input type="checkbox" name="wpsp-allow-pull" id="wpsp-allow-pull" <?php echo $options[ 'wpsp_allow_pull' ] === 'true' ? 'checked' : ''; ?>>
						<span>Allow Pull</span>
					</label>
				</div>
				<div class="input-group">
					<label>
						<span>Key</span>
						<input type="text" name="wpsp-key" value="<?php echo $options[ 'wpsp_key' ]; ?>">
					</label>
				</div>
				<button class="reset-key" id="reset-key">Reset Key</button>
				<input type="hidden" id="wpsp-reset-key-nonce" value="<?php echo wp_create_nonce( 'wpsp-settings-reset-key' ); ?>">
				<input type="hidden" id="wpsp-update-settings" value="<?php echo wp_create_nonce( 'wpsp-update-settings' ); ?>">
			</div>
		</div>
	</div>

</div>