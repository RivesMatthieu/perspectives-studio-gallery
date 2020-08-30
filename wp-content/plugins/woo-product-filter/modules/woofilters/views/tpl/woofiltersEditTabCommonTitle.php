<div class="row-settings-block">
	<div class="settings-block-label settings-w100 col-xs-4 col-sm-3">
		<?php esc_html_e('Show title label', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Show title label with open/close filter functionality', 'woo-product-filter'); ?>"></i>
	</div>
	
	<div class="settings-block-values settings-w100 col-xs-8 col-sm-9">
		<div class="settings-value">
			<div class="settings-value-label woobewoo-width60">
				<?php esc_html_e('desktop', 'woo-product-filter'); ?>
			</div>
			<?php 
				HtmlWpf::selectbox('f_enable_title', array(
					'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
					'attrs' => 'class="woobewoo-flat-input"'
				));
				?>
		</div>
		<div class="settings-value settings-w100">
			<div class="settings-value-label woobewoo-width60">
				<?php esc_html_e('mobile', 'woo-product-filter'); ?>
			</div>
			<?php 
				HtmlWpf::selectbox('f_enable_title_mobile', array(
					'options' => array('no' => 'No', 'yes_close' => 'Yes, show as close', 'yes_open' => 'Yes, show as opened'),
					'attrs' => 'class="woobewoo-flat-input"'
				));
				?>
		</div>
	</div>
</div>
