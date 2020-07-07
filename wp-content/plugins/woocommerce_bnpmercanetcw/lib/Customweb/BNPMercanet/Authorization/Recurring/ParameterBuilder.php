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

require_once 'Customweb/BNPMercanet/Authorization/Transaction.php';
require_once 'Customweb/Core/Exception/CastException.php';
require_once 'Customweb/BNPMercanet/Authorization/AbstractParameterBuilder.php';

class Customweb_BNPMercanet_Authorization_Recurring_ParameterBuilder extends Customweb_BNPMercanet_Authorization_AbstractParameterBuilder {

	public function build(){
		$fields = array_merge($this->getInterfaceVersionFields(), $this->getTransactionReferenceFields(), $this->getAmountFields(), 
				$this->getReferenceFields(), $this->getPaymentMethod()->getCaptureModeFields($this->getTransaction()), $this->getBaseFields());
		
		$fields['orderChannel'] = 'INTERNET';
		$fields['returnContext'] = 'ReturnContext';
		
		return $fields;
	}

	protected function getReferenceFields(){
		return array(
			'fromMerchantId' => $this->getConfiguration()->getMerchantId(),
			'fromTransactionReference' => $this->getInitialTransaction()->getTransactionReference() 
		);
	}

	protected function getInitialTransaction(){
		$initialTransaction = $this->getTransaction()->getTransactionContext()->getInitialTransaction();
		
		if (!($initialTransaction instanceof Customweb_BNPMercanet_Authorization_Transaction)) {
			throw new Customweb_Core_Exception_CastException('Customweb_BNPMercanet_Authorization_Transaction');
		}
		
		return $initialTransaction;
	}
}