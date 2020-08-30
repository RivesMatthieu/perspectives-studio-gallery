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
					'options' => array(
						'list' => 'Radiobuttons list (single select)',
						'dropdown' => 'Dropdown (single select)',
						'mul_dropdown' => 'Multiple Dropdown' . $labelPro,
						'multi' => 'Checkbox list (multiple select)' . $labelPro,
						'buttons' => 'Buttons' . $labelPro,
						'text' => 'Text' . $labelPro
					),
					'attrs' => 'class="woobewoo-flat-input"'
				));
				?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label col-xs-4 col-sm-3 wpfTypeSwitchable" data-not-type="buttons">
		<?php esc_html_e('Show hierarchical', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Show paternal and subsidiary categories (for checkbox list). If you display only some categories, make sure that the parent categories are selected.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100 wpfTypeSwitchable" data-not-type="buttons">
			<?php HtmlWpf::checkboxToggle('f_show_hierarchical', array()); ?>
		</div>
		<div class="settings-value settings-w100 wpfTypeSwitchable" data-not-type="buttons" data-parent-switch="f_show_hierarchical">
			<div class="settings-value-label">
				<?php esc_html_e('Hide categories parent', 'woo-product-filter'); ?>
				<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Show only categories children.', 'woo-product-filter'); ?>"></i>
			</div>
			<?php HtmlWpf::checkboxToggle('f_hide_parent', array()); ?>
		</div>
		<?php
		if ($isPro) {
			DispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersHierarchicalOption');
		} else {
			?>
			<div class="settings-value wpfTypeSwitchable" data-type="multi list" data-parent-switch="f_show_hierarchical">
				<div class="settings-value-label">
					<span class="settings-value"><?php esc_html_e('Collapsible', 'woo-product-filter'); ?></span>
					<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('If enabled, then show only parent elements, if there are children, they are minimized.', 'woo-product-filter'); ?>"></i>
					<span class="settings-value wpfProLabel"><?php esc_html_e('PRO option', 'woo-product-filter'); ?></span>
				</div>
			</div>
			<?php
		}
		?>
		<div class="settings-value settings-w100 wpfTypeSwitchable" data-not-type="buttons list dropdown" data-parent-switch="f_show_hierarchical">
			<div class="settings-value-label">
				<?php esc_html_e('Product selection hierarchically', 'woo-product-filter'); ?>
				<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('You can restrict filtering result only to child categories or only to parent if both of them selected in the same time.', 'woo-product-filter'); ?>"></i>
			</div>
			<?php
				HtmlWpf::selectbox('f_multi_logic_hierarchical', array(
					'options' => array(
						'any'    => esc_attr__('Could be in both, child or parent', 'woo-product-filter'),
						'child'  => esc_attr__('Only in child', 'woo-product-filter'),
						'parent' => esc_attr__('Only in parent', 'woo-product-filter'),
					),
					'attrs' => 'class="woobewoo-flat-input"'
				));
				?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-value wpfTypeSwitchable" data-type="list dropdown">
		<div class="settings-block-label col-xs-4 col-sm-3 wpfTypeSwitchable" data-not-type="buttons">
			<?php esc_html_e('Filter with children', 'woo-product-filter'); ?>
			<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('If parent filter category was selected then extend filter result to child categories.', 'woo-product-filter'); ?>"></i>
		</div>
		<div class="settings-block-values col-xs-8 col-sm-9">
			<?php HtmlWpf::checkboxToggle('f_extend_parent_filtering', array('checked' => 1)); ?>
		</div>
	</div>
</div>
<?php 
if ($isPro) {
	DispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersButtonsType');
	DispatcherWpf::doAction('addEditTabFilters', 'partEditTabFiltersCategoryMulti');
}

ViewWpf::display('woofiltersEditTabCustomTags');
?>
<div class="row-settings-block wpfTypeSwitchable" data-type="dropdown mul_dropdown">
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
		<?php esc_html_e('Sort by', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Here you may set categories sorting by ascendance or descendance.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values settings-w100 col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php 
				HtmlWpf::selectbox('f_sort_by', array(
					'options' => array('asc' => 'ASC', 'desc' => 'DESC', 'default' => 'Default' . $labelPro),
					'attrs' => 'class="woobewoo-flat-input' . ( $isPro ? '' : ' wpfWithProAd' ) . '"'
				));
				?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Order by custom', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Categories are displayed according to the order of their selection in the input fields.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_order_custom', array()); ?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Show count', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Show count display the number of products that have the appropriate parameter (attribute, category, tag).', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_show_count', array()); ?>
		</div>
		<div class="settings-value settings-w100" data-parent="f_show_count">
			<div class="settings-value-label">
				<?php esc_html_e('Count parents with children', 'woo-product-filter'); ?>
				<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Count for parent category also her children count. <strong>Warning!</strong> If filtering will slow do uncheck this option', 'woo-product-filter'); ?>"></i>
			</div>
			<?php HtmlWpf::checkboxToggle('f_show_count_parent_with_children', array()); ?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Hide categories without products', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Hide categories without products', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_hide_empty', array()); ?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label settings-w100 col-xs-4 col-sm-3">
		<?php esc_html_e('Product categories', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Here you may select product categories to be displayed on your site from the list. If you want to select several categories, hold the "Shift" button and click on category names. Or you can hold "Ctrl" and click on category names. Press "Ctrl" + "a" for checking all categories.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values settings-w100 col-xs-8 col-sm-9">
		<div class="settings-value woobewoo-width-full">
			<?php 
				HtmlWpf::selectlist('f_mlist', array(
					'options' => $categoryDisplay,
					'data-parents' => json_encode($parentCategories, JSON_HEX_QUOT | JSON_HEX_TAG)
				));
				?>
		</div>
		<div class="settings-value settings-w100">
			<div class="settings-value-label">
				<?php esc_html_e('Include children', 'woo-product-filter'); ?>
			</div>
			<?php HtmlWpf::checkboxToggle('f_mlist_with_children', array()); ?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Make selected categories as default', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Selected categories will be marked as default and hidden on frontend.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_hidden_categories', array('attrs' => 'data-preselect-flag="1"')); ?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Restrict filtering results only to selected categories', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('When the filter is clear, he will be restrict filtered results only by selected items. Be careful when using with other filters in block!', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_filtered_by_selected', array()); ?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label settings-w100 col-xs-4 col-sm-3">
		<?php esc_html_e('Exclude terms ids', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Here you may exclude category terms from filter by ids. Example input: 1,2,3 ', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values settings-w100 col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::text('f_exclude_terms', array('attrs' => 'class="woobewoo-flat-input"')); ?>
		</div>
	</div>
</div>
<div class="row-settings-block wpfTypeSwitchable" data-type="list">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Show search', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Show search display the bar for searching by category name in the filter', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_show_search_input', array()); ?>
		</div>
		<div class="settings-value settings-w100" data-parent="f_show_search_input">
			<?php
			$labels = $this->getModel('woofilters')->getFilterLabels('Category');
			HtmlWpf::text('f_search_label', array('placeholder' => esc_html($labels['search']), 'attrs' => 'class="woobewoo-flat-input"'));
			?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Hide child', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('Hide child taxonomy', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_hide_taxonomy', array()); ?>
		</div>
	</div>
</div>
<div class="row-settings-block">
	<div class="settings-block-label col-xs-4 col-sm-3">
		<?php esc_html_e('Always display all categories', 'woo-product-filter'); ?>
		<i class="fa fa-question woobewoo-tooltip no-tooltip" title="<?php echo esc_attr__('If checked, the entire list of categories will always be visible, otherwise only available for filtered items.', 'woo-product-filter'); ?>"></i>
	</div>
	<div class="settings-block-values col-xs-8 col-sm-9">
		<div class="settings-value settings-w100">
			<?php HtmlWpf::checkboxToggle('f_show_all_categories', array()); ?>
		</div>
	</div>
</div>
<div class="row-settings-block wpfTypeSwitchable" data-not-type="dropdown mul_dropdown">
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
<div class="row-settings-block wpfTypeSwitchable" data-not-type="dropdown mul_dropdown">
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
