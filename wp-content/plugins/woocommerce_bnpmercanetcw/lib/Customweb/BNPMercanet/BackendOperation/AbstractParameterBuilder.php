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

require_once 'Customweb/Util/Currency.php';
require_once 'Customweb/BNPMercanet/AbstractParameterBuilder.php';
require_once 'Customweb/BNPMercanet/Util.php';

class Customweb_BNPMercanet_BackendOperation_AbstractParameterBuilder extends Customweb_BNPMercanet_AbstractParameterBuilder {
	private $amount;
	
	public function __construct(Customweb_DependencyInjection_IContainer $container, Customweb_BNPMercanet_Authorization_Transaction $transaction, $amount){
		parent::__construct($container, $transaction);
		$this->amount = $amount;
	}

	protected function getOperationOriginFields(){
		return array(
			'operationOrigin' => 'WEBSHOP' 
		);
	}

	protected function getTransactionReferenceFields(){
		$reference = $this->getTransaction()->getTransactionReference();
		if($reference === null) {
			$reference = $this->getHelper()->formatTransactionReference($this->getTransaction()->getExternalTransactionId());
		}
		return array(
			'transactionReference' =>  $reference
		);
	}

	public function build(){
		return array_merge(
				array(
					'operationAmount' => Customweb_BNPMercanet_Util::formatCurrencyAmount($this->getAmount(), $this->getTransaction()->getCurrencyCode()),
					'currencyCode' => Customweb_Util_Currency::getNumericCode($this->getTransaction()->getCurrencyCode()) 
				), $this->getBaseFields(), $this->getOperationOriginFields(), $this->getInterfaceVersionFields(), 
				$this->getTransactionReferenceFields());
	}
	
	protected function getAmount(){
		return $this->amount;
	}
}