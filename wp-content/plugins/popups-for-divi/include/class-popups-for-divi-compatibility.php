<?php
/**
 * Makes sure, that our plugin integrates nicely with other plugins.
 *
 * @package Popups_For_Divi
 */

/**
 * Compatibility module.
 *
 * @since 1.4.5
 */
class Popups_For_Divi_Compatibility extends Popups_For_Divi_Component {
	/**
	 * Called during the "plugins_loaded" action. Hook up all actions/filters.
	 *
	 * @since  1.4.5
	 * @return void
	 */
	public function setup() {
		// SG Optimizer.
		add_action(
			'sgo_javascript_combine_excluded_inline_content',
			[ $this, 'sg_optimizer_exclude_inline_content' ]
		);
	}

	/**
	 * Instructs SG Optimizer to NOT combine our loader script. Combined scripts are
	 * moved to end of the document, which counteracts the entire purpose of the
	 * loader...
	 *
	 * @since 1.4.5
	 * @param array $exclude_list Default exclude list.
	 * @return array Extended exclude list.
	 */
	public function sg_optimizer_exclude_inline_content( $exclude_list ) {
		$exclude_list[] = 'window.DiviPopupData=window.DiviAreaConfig=';

		return $exclude_list;
	}
}
