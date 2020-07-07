<?php
if ( ! defined( 'ABSPATH' ) ) {
	exit;
}
?>

<h2 class="woo-conditional-shipping-heading">
	<?php _e( 'Conditions', 'woo-conditional-shipping' ); ?>
	<a href="<?php echo admin_url( 'admin.php?page=wc-settings&tab=shipping&section=woo_conditional_shipping&ruleset_id=new' ); ?>" class="page-title-action"><?php esc_html_e( 'Add ruleset', 'woo-conditional-shipping' ); ?></a>
</h2>

<p>
  <?php _e( 'Conditions are used to modify shipping method availability. Each ruleset contains conditions and actions which are applied to shipping method(s).', 'woo-conditional-shipping' ); ?>
</p>
<table class="woo-conditional-shipping-rulesets widefat">
	<thead>
		<tr>
			<th class="woo-conditional-shipping-ruleset-status"></th>
			<th class="woo-conditional-shipping-ruleset-name"><?php esc_html_e( 'Ruleset', 'woo-conditional-shipping' ); ?></th>
		</tr>
	</thead>
	<tbody class="woo-conditional-shipping-ruleset-rows">
		<?php foreach ( $rulesets as $ruleset ) { ?>
			<tr>
				<td class="woo-conditional-shipping-ruleset-status">
					<?php if ( $ruleset->get_enabled() ) { ?>
						<span class="status-enabled tips" data-tip="<?php _e( 'Enabled', 'woo-conditional-shipping' ); ?>"><?php _e( 'Enabled', 'woo-conditional-shipping' ); ?></span>
					<?php } else { ?>
						<span class="status-disabled tips" data-tip="<?php _e( 'Disabled', 'woo-conditional-shipping' ); ?>"><?php _e( 'Disabled', 'woo-conditional-shipping' ); ?></span>
					<?php } ?>
				</td>
				<td class="woo-conditional-shipping-ruleset-name">
					<a href="<?php echo $ruleset->get_admin_edit_url(); ?>">
						<?php echo $ruleset->get_title(); ?>
					</a>
					<div class="row-actions">
						<a href="<?php echo $ruleset->get_admin_edit_url(); ?>"><?php _e( 'Edit', 'woo-conditional-shipping' ); ?></a> | <a href="<?php echo $ruleset->get_admin_delete_url(); ?>" class="woo-conditional-shipping-ruleset-delete"><?php _e( 'Delete', 'woo-conditional-shipping' ); ?></a>
					</div>
				</td>
			</tr>
		<?php } ?>
		<?php if ( empty( $rulesets ) ) { ?>
			<tr>
				<td colspan="2" class="woo-conditional-shipping-ruleset-name">
					<?php _e( 'No rulesets defined yet.', 'woo-conditional-shipping' ); ?>
				</td>
			</tr>
		<?php } ?>
  </tbody>
</table>
