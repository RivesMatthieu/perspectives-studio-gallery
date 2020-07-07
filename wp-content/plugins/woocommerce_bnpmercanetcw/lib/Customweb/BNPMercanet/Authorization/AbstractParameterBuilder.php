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

require_once 'Customweb/Util/Currency.php';
require_once 'Customweb/BNPMercanet/AbstractParameterBuilder.php';
require_once 'Customweb/BNPMercanet/Util.php';


class Customweb_BNPMercanet_Authorization_AbstractParameterBuilder extends Customweb_BNPMercanet_AbstractParameterBuilder{
	

	protected function getAmountFields() {
		return array(
			'amount' => Customweb_BNPMercanet_Util::formatCurrencyAmount($this->getTransaction()->getAuthorizationAmount(), $this->getTransaction()->getCurrencyCode()),
			'currencyCode' => Customweb_Util_Currency::getNumericCode($this->getTransaction()->getCurrencyCode()),
		);
	}
	
	protected function getTransactionReferenceFields() {
		return array(
			'transactionReference' => $this->getHelper()->formatTransactionReference($this->getTransaction()->getExternalTransactionId()),
			'statementReference' => $this->getHelper()->formatTransactionReference($this->getTransaction()->getExternalTransactionId()),
			'orderId' => $this->getHelper()->formatSchemaForOrderId($this->getTransaction()->getExternalTransactionId())
		);
	}
	
	protected function getPaymentMethod() {
		$paymentMethod = $this->getTransaction()->getTransactionContext()->getOrderContext()->getPaymentMethod();
		return $this->getMethodFactory()->getPaymentMethod($paymentMethod, $this->getTransaction()->getAuthorizationMethod());
	}
	
	/**
	 * @return Customweb_BNPMercanet_Method_Factory
	 */
	protected function getMethodFactory() {
		return $this->getContainer()->getBean('Customweb_BNPMercanet_Method_Factory');
	}
	

	
}