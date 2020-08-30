<?php
	ViewWpf::display('woofiltersEditTabCommonTitle');
?>
<div class="row-settings-block">
	<div class="settings-block-label settings-w100 col-xs-4 col-sm-3">
		<?php esc_html_e('Show on frontend as', 'woo-product-filter'); ?>
	</div>
	<div class="settings-block-values settings-w100 col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php 
				HtmlWpf::selectbox('f_frontend_type', array(
					'options' => array('dropdown' => 'Dropdown', 'list' => 'Checkboxes', 'switch' => 'Toggle Switch' . $labelPro),
					'attrs' => 'class="woobewoo-flat-input"'
				));
				?>
		</div>
	</div>
</div>
<?php
if ($isPro) {
	DispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersSwitchType');
}
?>
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
<div class="row-settings-block">
	<div class="settings-block-label settings-w100 col-xs-4 col-sm-3">
		<?php esc_html_e('Stock status', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Here you may select the sorting options available for your site users (min two options).', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="sub-block-values settings-w100 col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php 
			$options = array();
			$labels = $this->getModel('woofilters')->getFilterLabels('InStock');
			foreach ($labels as $key => $value) {
				$options[] = array(
					'id' => 'f_stock_' . $key,
					'value' => $key, 
					'checked' => 1,
					'text' => $value,
				);
			}
			HtmlWpf::checkboxlist('f_options', array('options' => $options), '</div><div class="settings-value">');
			?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Change status names', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Here you may change stock status names.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="sub-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_status_names', array()); ?>
		</div>
		<div class="settings-value" data-parent="f_status_names">
			<?php HtmlWpf::text('f_stock_statuses[in]', array('placeholder' => esc_attr($labels['instock']), 'attrs' => 'class="woobewoo-flat-input"')); ?>
		</div>
		<div class="settings-value" data-parent="f_status_names">
			<?php HtmlWpf::text('f_stock_statuses[out]', array('placeholder' => esc_attr($labels['outofstock']), 'attrs' => 'class="woobewoo-flat-input"')); ?>
		</div>
		<div class="settings-value" data-parent="f_status_names">
			<?php HtmlWpf::text('f_stock_statuses[on]', array('placeholder' => esc_attr($labels['onbackorder']), 'attrs' => 'class="woobewoo-flat-input"')); ?>
		</div>
	</div>
</div>
<?php
if ($isPro) {
	DispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersStock');
}
?>
