<?php
	ViewWpf::display('woofiltersEditTabCommonTitle');
?>
<div class="row-settings-block">
	<div class="settings-block-label settings-w100 col-xs-4 col-sm-3">
		<?php esc_html_e('Show on frontend as', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Depending on whether you need one or several categories to be available at the same time, you may show your categories list as checkbox or dropdown.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values settings-w100 col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php 
				HtmlWpf::selectbox('f_frontend_type', array(
					'options' => array('list' => 'Checkbox list', 'dropdown' => 'Dropdown'),
					'attrs' => 'class="woobewoo-flat-input"'
				));
				?>
		</div>
	</div>
</div>
<div class="row-settings-block wpfTypeSwitchable" data-type="dropdown">
	<div class="settings-block-label settings-w100 col-xs-4 col-sm-3">
		<?php esc_html_e('Dropdown label', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Dropdown first option text.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values settings-w100 col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php 
				HtmlWpf::text('f_dropdown_first_option_text', array(
					'placeholder' => esc_attr__('Select all', 'woo-product-filter'),
					'attrs' => 'class="woobewoo-flat-input"'
				));
				?>
		</div>
	</div>
</div>
<div class="row-settings-block wpfAutomaticOrByHand" data-value="authomatic">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Set range automatically', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('If this option is enabled, you may set the price range settings automatically.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_range_automatic', array('checked' => 1)); ?>
		</div>
		<div class="settings-value settings-w100" data-parent="f_range_automatic">
			<div class="settings-value-label">
				<?php esc_html_e('Step', 'woo-product-filter'); ?>
				<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Here you may set the value of prise increase step. The default value is set to 20. All the steps are equal. When setting the step, please note that the number of elements in the list should not exceed 100, otherwise the step setting will be reset and automatically calculated.', 'woo-product-filter'); ?>"></i>
			</div>
			<?php 
				HtmlWpf::text('f_step', array(
					'value' => 20,
					'attrs' => 'class="woobewoo-flat-input woobewoo-number woobewoo-width60"'
				));
				?>
		</div>
	</div>
</div>
<div class="row-settings-block wpfAutomaticOrByHand" data-value="hand">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Set range manually', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('If this option is enabled, you may press the "Setup" button and customize your price range settings. You may increase or decrease the number of steps and set different values for each step.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_range_by_hands', array('attrs' => 'data-preselect-flag="1"')); ?>
		</div>
		<div class="settings-value settings-w100" data-parent="f_range_by_hands">
			<button class="button button-mini wpfRangeByHandSetup"><?php echo esc_html__('Setup', 'woo-product-filter'); ?></button>
		</div>
	</div>
</div>
<?php 
if ($isPro) {
	DispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersPriceRange');
} else {
	?>
	<div class="row-settings-block">
		<div class="settings-block-label col-xs-4 col-sm-3">
			<?php esc_html_e('Use Under/Over values', 'woo-product-filter'); ?>
			<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Use Under/Over label instead of minimum and maximum values.', 'woo-product-filter'); ?>"></i>
		</div>
		<div class="settings-block-values col-xs-6 col-sm-7 col-xl-8">
			<span class="settings-value wpfProLabel"><?php esc_html_e('PRO option', 'woo-product-filter'); ?></span>
		</div>
	</div>
	<div class="row-settings-block wpfTypeSwitchable" data-type="list">
		<div class="settings-block-label col-xs-4 col-sm-3">
			<?php esc_html_e('Show price input fields', 'woo-product-filter'); ?>
			<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Add fields for manually entering a price range.', 'woo-product-filter'); ?>"></i>
		</div>
		<div class="settings-block-values col-xs-6 col-sm-7 col-xl-8">
			<span class="settings-value wpfProLabel"><?php esc_html_e('PRO option', 'woo-product-filter'); ?></span>
		</div>
	</div>
	<div class="row-settings-block" data-value="decimals">
		<div class="settings-block-label col-xs-4 col-sm-3">
			<?php esc_html_e('Use custom number of decimals', 'woo-product-filter'); ?>
			<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('This sets the number of decimal points shown in displayed prices.', 'woo-product-filter'); ?>"></i>
		</div>
		<div class="settings-block-values col-xs-6 col-sm-7 col-xl-8">
			<span class="settings-value wpfProLabel"><?php esc_html_e('PRO option', 'woo-product-filter'); ?></span>
		</div>
	</div>
<?php } ?>
<div class="row-settings-block wpfTypeSwitchable" data-type="list">
	<div class="settings-block-label settings-w100 col-xs-4 col-sm-3">
		<?php esc_html_e('Layout', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Select a vertical or horizontal layout and set the count of columns.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values settings-w100 col-xs-8 col-sm-9">
		<div class="settings-value settings-w50">
			<?php 
				HtmlWpf::selectbox('f_layout', array(
					'options' => array('ver' => esc_attr__('Vertical', 'woo-product-filter'), 'hor' => esc_attr__('Horizontal', 'woo-product-filter')),
					'attrs' => 'class="woobewoo-flat-input"'
				));
				?>
		</div>
		<div class="settings-value settings-w50" data-select="f_layout" data-select-value="ver">
			<div class="settings-value-label">
				<?php esc_html_e('Columns', 'woo-product-filter'); ?>
			</div>
			<?php HtmlWpf::text('f_ver_columns', array('value' => 1, 'attrs' => 'class="woobewoo-flat-input woobewoo-number woobewoo-width40"')); ?>
		</div>
	</div>
</div>
<div class="row-settings-block wpfTypeSwitchable" data-type="list">
	<div class="settings-block-label settings-w100 col-xs-4 col-sm-3">
		<?php esc_html_e('Maximum height in frontend', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Set maximum displayed height in frontend.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values settings-w100 col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::text('f_max_height', array('value'=>'200', 'attrs' => 'class="woobewoo-flat-input woobewoo-number woobewoo-width60"')); ?> px
		</div>
	</div>
</div>
<?php HtmlWpf::hidden('f_range_by_hands_values', array()); ?>
<?php HtmlWpf::hidden('f_range_by_hands_default', array()); ?>
<?php HtmlWpf::hidden('f_preselect', array()); ?>
