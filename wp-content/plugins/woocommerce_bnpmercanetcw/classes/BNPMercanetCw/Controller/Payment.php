<?php

/**
 *  * You are allowed to use this API in your web application.
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
require_once 'BNPMercanetCw/Util.php';
require_once 'BNPMercanetCw/ContextRequest.php';
require_once 'BNPMercanetCw/Controller/Abstract.php';



/**
 *
 * @author Nico Eigenmann
 *
 */
class BNPMercanetCw_Controller_Payment extends BNPMercanetCw_Controller_Abstract {

	public function indexAction(){
		$parameters = BNPMercanetCw_ContextRequest::getInstance()->getParameters();
		$aliasTransactionId = null;
		try {
			$order = $this->loadOrder($parameters);
		}
		catch (Exception $e) {
			return $this->formatErrorMessage($e->getMessage());
		}
		
		$orderPostId= $order->get_id();
		
		if (!isset($parameters['cwpmc'])) {
			return $this->formatErrorMessage(__('Missing payment method.', 'woocommerce_bnpmercanetcw'));
		}
		$paymentMethodClass = $parameters['cwpmc'];
			
		if (isset($parameters['cwalias'])) {
			$aliasTransactionId = $parameters['cwalias'];
		}
		
		$paymentMethod = BNPMercanetCw_Util::getPaymentMehtodInstance(strip_tags($paymentMethodClass));
		
		$response = $paymentMethod->processTransaction($orderPostId, $aliasTransactionId);
		
		if (is_array($response) && isset($response['redirect'])) {
			header('Location: ' . $response['redirect']);
			die();
		}
		
		return $response;
	}
}