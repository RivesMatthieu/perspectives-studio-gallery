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

class BNPMercanetCw_Sofortueberweisung extends BNPMercanetCw_PaymentMethod
{
	public $machineName = 'sofortueberweisung';
	public $admin_title = 'Sofortüberweisung';
	public $title = 'Sofortüberweisung';
	
	protected function getMethodSettings(){
		return array(
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
			'woocommerce_bnpmercanetcw_sofortueberweisung_icon', 
			BNPMercanetCw_Util::getResourcesUrl('icons/sofortueberweisung.png')
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