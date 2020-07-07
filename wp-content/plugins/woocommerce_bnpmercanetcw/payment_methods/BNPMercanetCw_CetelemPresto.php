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

class BNPMercanetCw_CetelemPresto extends BNPMercanetCw_PaymentMethod
{
	public $machineName = 'cetelempresto';
	public $admin_title = 'Cetelem Presto';
	public $title = 'Cetelem Presto';
	
	protected function getMethodSettings(){
		return array(
			'cetelem_type' => array(
				'title' => __("Financial Product", 'woocommerce_bnpmercanetcw'),
 				'default' => 'CLA',
 				'description' => __("Select your financial product your account is configured for.", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'CLA' => __("Classic Credit Product (CLA) only", 'woocommerce_bnpmercanetcw'),
 					'CCH' => __("Classic Credit Product (CLA) and Renewable Account Opening with Card (CCH)", 'woocommerce_bnpmercanetcw'),
 				),
 			),
 			'cetelem_product' => array(
				'title' => __("Product Type", 'woocommerce_bnpmercanetcw'),
 				'default' => '320',
 				'description' => __("Here you can specify the main product your shop is selling", 'woocommerce_bnpmercanetcw'),
 				'cwType' => 'select',
 				'type' => 'select',
 				'options' => array(
					'320' => __("Household other", 'woocommerce_bnpmercanetcw'),
 					'322' => __("Fridge/Freezer", 'woocommerce_bnpmercanetcw'),
 					'323' => __("Dishwasher", 'woocommerce_bnpmercanetcw'),
 					'324' => __("Washing machine", 'woocommerce_bnpmercanetcw'),
 					'325' => __("Household group", 'woocommerce_bnpmercanetcw'),
 					'326' => __("Fridge", 'woocommerce_bnpmercanetcw'),
 					'327' => __("Freezer", 'woocommerce_bnpmercanetcw'),
 					'328' => __("Stove/Cooking plate", 'woocommerce_bnpmercanetcw'),
 					'329' => __("Tumble drier", 'woocommerce_bnpmercanetcw'),
 					'330' => __("Furniture other", 'woocommerce_bnpmercanetcw'),
 					'331' => __("Living room", 'woocommerce_bnpmercanetcw'),
 					'332' => __("Dining room", 'woocommerce_bnpmercanetcw'),
 					'333' => __("Bedroom", 'woocommerce_bnpmercanetcw'),
 					'334' => __("Couch", 'woocommerce_bnpmercanetcw'),
 					'335' => __("Furniture group", 'woocommerce_bnpmercanetcw'),
 					'336' => __("Armchair", 'woocommerce_bnpmercanetcw'),
 					'337' => __("Bookshelf/Wardrobe", 'woocommerce_bnpmercanetcw'),
 					'338' => __("Bedding", 'woocommerce_bnpmercanetcw'),
 					'339' => __("Bedroom", 'woocommerce_bnpmercanetcw'),
 					'340' => __("Furnishing textiles", 'woocommerce_bnpmercanetcw'),
 					'341' => __("Office furniture", 'woocommerce_bnpmercanetcw'),
 					'342' => __("Bathroom furniture", 'woocommerce_bnpmercanetcw'),
 					'343' => __("Kitchen furniture", 'woocommerce_bnpmercanetcw'),
 					'610' => __("Video/Audio/IT", 'woocommerce_bnpmercanetcw'),
 					'611' => __("Video recorder/DVD", 'woocommerce_bnpmercanetcw'),
 					'613' => __("Audio equipment", 'woocommerce_bnpmercanetcw'),
 					'615' => __("Television", 'woocommerce_bnpmercanetcw'),
 					'616' => __("IT", 'woocommerce_bnpmercanetcw'),
 					'619' => __("Group purchase TV/Audio", 'woocommerce_bnpmercanetcw'),
 					'620' => __("Photo equipment", 'woocommerce_bnpmercanetcw'),
 					'621' => __("Phone", 'woocommerce_bnpmercanetcw'),
 					'622' => __("Home Cinema", 'woocommerce_bnpmercanetcw'),
 					'623' => __("LCD/Plasma", 'woocommerce_bnpmercanetcw'),
 					'624' => __("Video camera", 'woocommerce_bnpmercanetcw'),
 					'625' => __("Computer", 'woocommerce_bnpmercanetcw'),
 					'626' => __("Printer/Scanner", 'woocommerce_bnpmercanetcw'),
 					'631' => __("Travel holiday", 'woocommerce_bnpmercanetcw'),
 					'640' => __("Clothing", 'woocommerce_bnpmercanetcw'),
 					'650' => __("Books", 'woocommerce_bnpmercanetcw'),
 					'660' => __("Leisure other", 'woocommerce_bnpmercanetcw'),
 					'663' => __("Crafts, Gardening", 'woocommerce_bnpmercanetcw'),
 					'730' => __("Jewelry", 'woocommerce_bnpmercanetcw'),
 					'737' => __("Shutter", 'woocommerce_bnpmercanetcw'),
 					'738' => __("Mower", 'woocommerce_bnpmercanetcw'),
 					'739' => __("Tiller", 'woocommerce_bnpmercanetcw'),
 					'740' => __("Chainsaw", 'woocommerce_bnpmercanetcw'),
 					'741' => __("Brushcutter", 'woocommerce_bnpmercanetcw'),
 					'742' => __("Quad bike", 'woocommerce_bnpmercanetcw'),
 					'743' => __("Garden furniture", 'woocommerce_bnpmercanetcw'),
 					'744' => __("Barbecue", 'woocommerce_bnpmercanetcw'),
 					'855' => __("Piano", 'woocommerce_bnpmercanetcw'),
 					'857' => __("Organ", 'woocommerce_bnpmercanetcw'),
 					'858' => __("Music other", 'woocommerce_bnpmercanetcw'),
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
 		); 
	}
	
	public function __construct() {
		$this->icon = apply_filters(
			'woocommerce_bnpmercanetcw_cetelempresto_icon', 
			BNPMercanetCw_Util::getResourcesUrl('icons/cetelempresto.png')
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