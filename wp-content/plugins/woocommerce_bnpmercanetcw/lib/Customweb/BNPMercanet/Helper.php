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

require_once 'Customweb/Util/Rand.php';
require_once 'Customweb/Payment/Util.php';



/**
 *
 * @author Thomas Hunziker
 * @Bean
 */
class Customweb_BNPMercanet_Helper {
	
	private $configuration;

	public function __construct(Customweb_BNPMercanet_Configuration $configuration){
		$this->configuration = $configuration;
	}

	public function calculateSeal($data){
		return hash('sha256', $data . $this->getConfiguration()->getSecretKey());
	}

	public function getConfiguration(){
		return $this->configuration;
	}

	public function formatTransactionReference($transactionId){
		$schema = $this->getConfiguration()->getTransactionReferenceSchema();
		
		$transactionId = preg_replace('/[^0-9A-Za-z]+/i', '', $transactionId);
		
		// We add a random part to ensure, that the reference will be always unique. In 
		// test mode all merchant use the same account, hence the transaction reference is
		// hard to get unique, without randomness.
		if ($this->getConfiguration()->isTestMode()) {
			$schema = Customweb_Util_Rand::getRandomString(10) . $schema;
		}
		
		$transactionId = Customweb_Payment_Util::applyOrderSchema($schema, $transactionId, 35);
		
		// We filter the transaction ID again in case the merchant adds a invalid char over the schema.
		$transactionId = preg_replace('/[^0-9A-Za-z]+/i', '', $transactionId);
		
		return $transactionId;
	}

	public function formatSchemaForOrderId($transactionId){
		$schema = $this->getConfiguration()->getTransactionReferenceSchema();
		
		$transactionId = preg_replace('/[^0-9A-Za-z]+/i', '', $transactionId);
		
		// We add a random part to ensure, that the reference will be always unique. In
		// test mode all merchant use the same account, hence the transaction reference is
		// hard to get unique, without randomness.
		if ($this->getConfiguration()->isTestMode()) {
			$schema = Customweb_Util_Rand::getRandomString(10) . $schema;
		}
		
		$transactionId = Customweb_Payment_Util::applyOrderSchema($schema, $transactionId, 30);
		
		// We filter the transaction ID again in case the merchant adds a invalid char over the schema.
		$transactionId = preg_replace('/[^0-9A-Za-z]+/i', '', $transactionId);
		
		return $transactionId;
	}
}