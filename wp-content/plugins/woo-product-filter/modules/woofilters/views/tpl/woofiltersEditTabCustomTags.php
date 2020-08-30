<div class="row-settings-block">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Use Custom tags', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Choose tags for filter titles. Any settings you leave blank will default.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="sub-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_custom_tags'); ?>
		</div>
		<div class="settings-value" data-parent="f_custom_tags">
			<div class="settings-block-label woobewoo-width120">
				<?php esc_html_e('Filter header', 'woo-product-filter'); ?>
			</div>
			<?php
			HtmlWpf::selectbox('f_custom_tags_settings[header]', array(
				'options' => $this->getModule()->getFilterTagsList(),
				'attrs' => 'class="woobewoo-flat-input"'
			));
			?>
		</div>
		<div class="settings-value" data-parent="f_custom_tags">
			<div class="settings-block-label woobewoo-width120">
				<?php esc_html_e('1-st level title', 'woo-product-filter'); ?>
			</div>
			<?php
			HtmlWpf::selectbox('f_custom_tags_settings[title_1]', array(
				'options' => $this->getModule()->getFilterTagsList(),
				'attrs' => 'class="woobewoo-flat-input"'
			));
			?>
		</div>
		<div class="settings-value" data-parent="f_custom_tags">
			<div class="settings-block-label woobewoo-width120">
				<?php esc_html_e('2-nd level title', 'woo-product-filter'); ?>
			</div>
			<?php
			HtmlWpf::selectbox('f_custom_tags_settings[title_2]', array(
				'options' => $this->getModule()->getFilterTagsList(),
				'attrs' => 'class="woobewoo-flat-input"'
			));
			?>
		</div>
		<div class="settings-value" data-parent="f_custom_tags">
			<div class="settings-block-label woobewoo-width120">
				<?php esc_html_e('3-rd level title', 'woo-product-filter'); ?>
			</div>
			<?php
			HtmlWpf::selectbox('f_custom_tags_settings[title_3]', array(
				'options' => $this->getModule()->getFilterTagsList(),
				'attrs' => 'class="woobewoo-flat-input"'
			));
			?>
		</div>
	</div>
</div>
