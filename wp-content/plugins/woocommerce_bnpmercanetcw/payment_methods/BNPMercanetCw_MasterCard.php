<?php

/**
 * You are allowed to use this API in your web application.
 *
 * Copyright (C) 2018 by customweb GmbH
 *
 * This program is licenced under the customweb software licence. With the
 * purchase or the installation of the software in your application you
 * accept the licence agreement. The allowed usage is outlined in the
 * customweb software licence which can be found under
 * http://www.sellxed.com/en/software-license-agreement
 *
 * Any modification or distribution is strictly forbidden. The license
 * grants you the installation in one application. For multiuse you will need
 * to purchase further licences at http://www.sellxed.com/shop.
 *
 * See the customweb software licence agreement for more details.
 *
*/

require_once dirname(dirname(__FILE__)) . '/classes/BNPMercanetCw/PaymentMethod.php'; 

class BNPMercanetCw_MasterCard extends BNPMercanetCw_PaymentMethod
{
	public $machineName = 'mastercard';
	public $admin_title = 'MasterCard';
	public $title = 'MasterCard';
	
	protected function getMethodSettings(){
		return array(
			'capturing' => array(
				'title' => __("Capturing Delay", 'woocommerce_bnpmercanetcw'),
 				'default' => '0',
 				'description' => __("This option defines how many days the transaction remains open after authorization. A value of 0 means that the authorization is captured as soon as possible. We recommend you to define a cron job in your shop especially if the capture delay is greater than 0 in order to automatically update the transaction status. How this can be done is described in the latest version of the integration manual. Please check with BNP Paribas Mercanet which delay is possible and what implication a delay may have. If the value is set to Validation, the transaction will only be authorized. You have to capture it manually, otherwise the funds are not transfered to your account. The timeframe to capture the transaction depends on the card used, 3d-Secure behaviour and other factors.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'disabled' => __("Validation", 'woocommerce_bnpmercanetcw'),
 					'0' => __("0", 'woocommerce_bnpmercanetcw'),
 					'1' => __("1", 'woocommerce_bnpmercanetcw'),
 					'2' => __("2", 'woocommerce_bnpmercanetcw'),
 					'3' => __("3", 'woocommerce_bnpmercanetcw'),
 					'4' => __("4", 'woocommerce_bnpmercanetcw'),
 					'5' => __("5", 'woocommerce_bnpmercanetcw'),
 					'6' => __("6", 'woocommerce_bnpmercanetcw'),
 					'7' => __("7", 'woocommerce_bnpmercanetcw'),
 					'8' => __("8", 'woocommerce_bnpmercanetcw'),
 					'9' => __("9", 'woocommerce_bnpmercanetcw'),
 					'10' => __("10", 'woocommerce_bnpmercanetcw'),
 					'11' => __("11", 'woocommerce_bnpmercanetcw'),
 					'12' => __("12", 'woocommerce_bnpmercanetcw'),
 					'13' => __("13", 'woocommerce_bnpmercanetcw'),
 					'14' => __("14", 'woocommerce_bnpmercanetcw'),
 					'15' => __("15", 'woocommerce_bnpmercanetcw'),
 					'16' => __("16", 'woocommerce_bnpmercanetcw'),
 					'17' => __("17", 'woocommerce_bnpmercanetcw'),
 					'18' => __("18", 'woocommerce_bnpmercanetcw'),
 					'19' => __("19", 'woocommerce_bnpmercanetcw'),
 					'20' => __("20", 'woocommerce_bnpmercanetcw'),
 					'21' => __("21", 'woocommerce_bnpmercanetcw'),
 					'22' => __("22", 'woocommerce_bnpmercanetcw'),
 					'23' => __("23", 'woocommerce_bnpmercanetcw'),
 					'24' => __("24", 'woocommerce_bnpmercanetcw'),
 					'25' => __("25", 'woocommerce_bnpmercanetcw'),
 					'26' => __("26", 'woocommerce_bnpmercanetcw'),
 					'27' => __("27", 'woocommerce_bnpmercanetcw'),
 					'28' => __("28", 'woocommerce_bnpmercanetcw'),
 					'29' => __("29", 'woocommerce_bnpmercanetcw'),
 					'30' => __("30", 'woocommerce_bnpmercanetcw'),
 					'31' => __("31", 'woocommerce_bnpmercanetcw'),
 					'32' => __("32", 'woocommerce_bnpmercanetcw'),
 					'33' => __("33", 'woocommerce_bnpmercanetcw'),
 					'34' => __("34", 'woocommerce_bnpmercanetcw'),
 					'35' => __("35", 'woocommerce_bnpmercanetcw'),
 					'36' => __("36", 'woocommerce_bnpmercanetcw'),
 					'37' => __("37", 'woocommerce_bnpmercanetcw'),
 					'38' => __("38", 'woocommerce_bnpmercanetcw'),
 					'39' => __("39", 'woocommerce_bnpmercanetcw'),
 					'40' => __("40", 'woocommerce_bnpmercanetcw'),
 					'41' => __("41", 'woocommerce_bnpmercanetcw'),
 					'42' => __("42", 'woocommerce_bnpmercanetcw'),
 					'43' => __("43", 'woocommerce_bnpmercanetcw'),
 					'44' => __("44", 'woocommerce_bnpmercanetcw'),
 					'45' => __("45", 'woocommerce_bnpmercanetcw'),
 					'46' => __("46", 'woocommerce_bnpmercanetcw'),
 					'47' => __("47", 'woocommerce_bnpmercanetcw'),
 					'48' => __("48", 'woocommerce_bnpmercanetcw'),
 					'49' => __("49", 'woocommerce_bnpmercanetcw'),
 					'50' => __("50", 'woocommerce_bnpmercanetcw'),
 					'51' => __("51", 'woocommerce_bnpmercanetcw'),
 					'52' => __("52", 'woocommerce_bnpmercanetcw'),
 					'53' => __("53", 'woocommerce_bnpmercanetcw'),
 					'54' => __("54", 'woocommerce_bnpmercanetcw'),
 					'55' => __("55", 'woocommerce_bnpmercanetcw'),
 					'56' => __("56", 'woocommerce_bnpmercanetcw'),
 					'57' => __("57", 'woocommerce_bnpmercanetcw'),
 					'58' => __("58", 'woocommerce_bnpmercanetcw'),
 					'59' => __("59", 'woocommerce_bnpmercanetcw'),
 					'60' => __("60", 'woocommerce_bnpmercanetcw'),
 					'61' => __("61", 'woocommerce_bnpmercanetcw'),
 					'62' => __("62", 'woocommerce_bnpmercanetcw'),
 					'63' => __("63", 'woocommerce_bnpmercanetcw'),
 					'64' => __("64", 'woocommerce_bnpmercanetcw'),
 					'65' => __("65", 'woocommerce_bnpmercanetcw'),
 					'66' => __("66", 'woocommerce_bnpmercanetcw'),
 					'67' => __("67", 'woocommerce_bnpmercanetcw'),
 					'68' => __("68", 'woocommerce_bnpmercanetcw'),
 					'69' => __("69", 'woocommerce_bnpmercanetcw'),
 					'70' => __("70", 'woocommerce_bnpmercanetcw'),
 					'71' => __("71", 'woocommerce_bnpmercanetcw'),
 					'72' => __("72", 'woocommerce_bnpmercanetcw'),
 					'73' => __("73", 'woocommerce_bnpmercanetcw'),
 					'74' => __("74", 'woocommerce_bnpmercanetcw'),
 					'75' => __("75", 'woocommerce_bnpmercanetcw'),
 					'76' => __("76", 'woocommerce_bnpmercanetcw'),
 					'77' => __("77", 'woocommerce_bnpmercanetcw'),
 					'78' => __("78", 'woocommerce_bnpmercanetcw'),
 					'79' => __("79", 'woocommerce_bnpmercanetcw'),
 					'80' => __("80", 'woocommerce_bnpmercanetcw'),
 					'81' => __("81", 'woocommerce_bnpmercanetcw'),
 					'82' => __("82", 'woocommerce_bnpmercanetcw'),
 					'83' => __("83", 'woocommerce_bnpmercanetcw'),
 					'84' => __("84", 'woocommerce_bnpmercanetcw'),
 					'85' => __("85", 'woocommerce_bnpmercanetcw'),
 					'86' => __("86", 'woocommerce_bnpmercanetcw'),
 					'87' => __("87", 'woocommerce_bnpmercanetcw'),
 					'88' => __("88", 'woocommerce_bnpmercanetcw'),
 					'89' => __("89", 'woocommerce_bnpmercanetcw'),
 					'90' => __("90", 'woocommerce_bnpmercanetcw'),
 					'91' => __("91", 'woocommerce_bnpmercanetcw'),
 					'92' => __("92", 'woocommerce_bnpmercanetcw'),
 					'93' => __("93", 'woocommerce_bnpmercanetcw'),
 					'94' => __("94", 'woocommerce_bnpmercanetcw'),
 					'95' => __("95", 'woocommerce_bnpmercanetcw'),
 					'96' => __("96", 'woocommerce_bnpmercanetcw'),
 					'97' => __("97", 'woocommerce_bnpmercanetcw'),
 					'98' => __("98", 'woocommerce_bnpmercanetcw'),
 					'99' => __("99", 'woocommerce_bnpmercanetcw'),
 				),
 			),
 			'status_authorized' => array(
				'title' => __("Authorized Status", 'woocommerce_bnpmercanetcw'),
 				'default' => 'wc-processing',
 				'description' => __("This status is set, when the payment was successfull and it is authorized.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'use-default' => __("Use WooCommerce rules", 'woocommerce_bnpmercanetcw'),
 				),
 				'is_order_status' => true,
 			),
 			'status_uncertain' => array(
				'title' => __("Uncertain Status", 'woocommerce_bnpmercanetcw'),
 				'default' => 'wc-on-hold',
 				'description' => __("You can specify the order status for new orders that have an uncertain authorisation status.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
				),
 				'is_order_status' => true,
 			),
 			'status_cancelled' => array(
				'title' => __("Cancelled Status", 'woocommerce_bnpmercanetcw'),
 				'default' => 'wc-cancelled',
 				'description' => __("You can specify the order status when an order is cancelled.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'no_status_change' => __("Don't change order status", 'woocommerce_bnpmercanetcw'),
 				),
 				'is_order_status' => true,
 			),
 			'status_captured' => array(
				'title' => __("Captured Status", 'woocommerce_bnpmercanetcw'),
 				'default' => 'no_status_change',
 				'description' => __("You can specify the order status for orders that are captured either directly after the order or manually in the backend.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'no_status_change' => __("Don't change order status", 'woocommerce_bnpmercanetcw'),
 				),
 				'is_order_status' => true,
 			),
 			'threed_secure_min_amount' => array(
				'title' => __("3D Secure Minimum amount", 'woocommerce_bnpmercanetcw'),
 				'default' => '0',
 				'description' => __("If an order total is below the specified amount, it will be processed without 3d Secure. Note Non-3D Secure Transactions will cause that the merchant is liable in case of a charge back. As a merchant you take the risk of not being paid for this transaction in case the client challenges the transaction.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'textfield',
 				'type' => 'text',
 			),
 			'nfois' => array(
				'title' => __("Payment Plans", 'woocommerce_bnpmercanetcw'),
 				'default' => 'inactive',
 				'description' => __("Enable the configured payment plans for this method.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'active' => __("Active", 'woocommerce_bnpmercanetcw'),
 					'inactive' => __("Inactive", 'woocommerce_bnpmercanetcw'),
 				),
 			),
 			'status_success_after_uncertain' => array(
				'title' => __("Successful Payments after uncertain", 'woocommerce_bnpmercanetcw'),
 				'default' => 'no_status_change',
 				'description' => __("You can specify the order status for orders that are successful after being in a uncertain state.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'no_status_change' => __("Don't change order status", 'woocommerce_bnpmercanetcw'),
 				),
 				'is_order_status' => true,
 			),
 			'status_refused_after_uncertain' => array(
				'title' => __("Refused Payments after uncertain", 'woocommerce_bnpmercanetcw'),
 				'default' => 'no_status_change',
 				'description' => __("You can specify the order status for orders that are refused after being in a uncertain state.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'orderstatusselect',
 				'type' => 'select',
 				'options' => array(
					'no_status_change' => __("Don't change order status", 'woocommerce_bnpmercanetcw'),
 				),
 				'is_order_status' => true,
 			),
 			'authorizationMethod' => array(
				'title' => __("Authorization Method", 'woocommerce_bnpmercanetcw'),
 				'default' => 'PaymentPage',
 				'description' => __("Select the authorization method to use for processing this payment method.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'PaymentPage' => __("Payment Page", 'woocommerce_bnpmercanetcw'),
 					'IframeAuthorization' => __("IFrame Authorization", 'woocommerce_bnpmercanetcw'),
 				),
 			),
 			'alias_manager' => array(
				'title' => __("Alias Manager", 'woocommerce_bnpmercanetcw'),
 				'default' => 'inactive',
 				'description' => __("The alias manager allows the customer to select from a credit card previously stored. The sensitive data is stored by BNP Paribas Mercanet.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'active' => __("Active", 'woocommerce_bnpmercanetcw'),
 					'inactive' => __("Inactive", 'woocommerce_bnpmercanetcw'),
 				),
 			),
 		); 
	}
	
	public function __construct() {
		$this->icon = apply_filters(
			'woocommerce_bnpmercanetcw_mastercard_icon', 
			BNPMercanetCw_Util::getResourcesUrl('icons/mastercard.png')
		);
		parent::__construct();
	}
	
	public function createMethodFormFields() {
		$formFields = parent::createMethodFormFields();
		
		return array_merge(
			$formFields,
			$this->getMethodSettings()
		);
	}

}