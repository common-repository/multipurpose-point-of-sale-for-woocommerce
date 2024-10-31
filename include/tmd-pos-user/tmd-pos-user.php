<?php
/**
 * tmd pos user 
 * 
 * @package pos admin user
 */
defined("ABSPATH") || exit;
$args  = array('role'=>'tmd_pos_user', 'orderby'=>'ID', 'order' => 'DESC');
$users = get_users( $args );
?>
<div class="wrap">
	<h2><?php echo esc_html( $GLOBALS['title'] ); ?></h2>
	<div id="tmd-pos-user-container">
		<table class="wp-list-table widefat fixed striped table-view-list users tmd-pos-user">
			<thead>
				<tr>
					<th><?php esc_html_e( 'User ID', 'tmdpos' ); ?></th>
					<th><?php esc_html_e( 'Username', 'tmdpos' ); ?></th>
					<th><?php esc_html_e( 'Email', 'tmdpos' ); ?></th>
					<th><?php esc_html_e( 'Full Name', 'tmdpos' ); ?></th>
					<th class="column-posts"><?php esc_html_e( 'Action', 'tmdpos'); ?> </th>
				</tr>
			</thead>
			<tbody>
				<?php
					if (!empty( $users ) && is_array($users) ) {
						foreach ($users as $user) {
							?>
								<tr>
									<th># <?php echo esc_html( $user->ID ); ?></th>
									<td><?php echo esc_html( $user->user_login ); ?></td>
									<td><?php echo esc_html( $user->user_email ); ?></td>
									<td><?php echo esc_html( $user->display_name ); ?></td>
									<td><a class="button-primary" target="_blank" href="user-edit.php?user_id=<?php echo esc_html( $user->ID ); ?>"><?php esc_html_e( 'View', 'tmdpos'); ?></a></td>
								</tr>
							<?php
						}
					}
				?>
			</tbody>
			<tfoot>
				<tr>
					<th><?php esc_html_e( 'User ID', 'tmdpos' ); ?></th>
					<th><?php esc_html_e( 'Username', 'tmdpos' ); ?></th>
					<th><?php esc_html_e( 'Email', 'tmdpos' ); ?></th>
					<th><?php esc_html_e( 'Full Name', 'tmdpos' ); ?></th>
					<th class="column-posts"><?php esc_html_e( 'Action', 'tmdpos'); ?> </th>
				</tr>
			</tfoot>
		</table>
	</div>
</div>