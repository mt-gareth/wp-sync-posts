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
<table class="wp-list-table widefat fixed striped table-view-list options">
	<thead>
	<tr>
		<th scope="col" id="option_id" class="manage-column column-option_id column-primary ">
			<span>ID</span>
		</th>
		<th scope="col" id="option_name" class="manage-column column-option_name  ">
			<span>Name</span>
		</th>
		<th scope="col" id="option_url" class="manage-column column-option_url">
			<span>URL</span>
		</th>
		<th scope="col" id="option_options" class="manage-column column-options">
			<span>Options</span>
		</th>
	</tr>
	</thead>

	<tbody id="the-list" data-wp-lists="list:option">
	<?php foreach ( $options[ 'wpsp_connections' ] as $connection ) : ?>
		<tr>
			<td class="option_id column-option_id has-row-actions column-primary" data-colname="ID"><?php echo $connection->ID; ?></td>
			<td class="option_name column-option_name" data-colname="Name"><?php echo $connection->name; ?></td>
			<td class="option_value column-option_value" data-colname="Value"><?php echo $connection->url; ?></td>
			<td class="autoload column-autoload" data-colname="Options">
				<span class="connection-edit" data-connection="<?php echo htmlspecialchars( json_encode( $connection ), ENT_QUOTES, 'UTF-8' ); ?>">Edit</span> /
				<span class="connection-delete" data-connection="<?php echo $connection->ID; ?>">Delete</span></td>
		</tr>
	<?php endforeach; ?>

	</tbody>

</table>
