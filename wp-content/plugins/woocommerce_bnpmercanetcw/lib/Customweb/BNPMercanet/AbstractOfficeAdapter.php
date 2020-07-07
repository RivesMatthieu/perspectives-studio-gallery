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

require_once 'Customweb/I18n/Translation.php';
require_once 'Customweb/BNPMercanet/AbstractAdapter.php';
require_once 'Customweb/Core/Http/Client/Factory.php';
require_once 'Customweb/BNPMercanet/Util.php';
require_once 'Customweb/Core/Http/Request.php';



/**
 * Abstract implementation of an adapter to execute a operation on the
 * office interface.
 *
 * @author Thomas Hunziker
 *
 */
abstract class Customweb_BNPMercanet_AbstractOfficeAdapter extends Customweb_BNPMercanet_AbstractAdapter {

	/**
	 * This method returns the endpoint URL of the backend service.
	 *
	 * @return string
	 */
	abstract protected function getEndpoint();

	/**
	 * This method sends the given parameters to the endpoint and evaluates the result.
	 *
	 * @param array $parameters
	 * @return array Result parameters
	 * @throws Exception In case the response is not valid or the server was unable to response.
	 */
	protected function processRequest(array $parameters){
		$parameters['seal'] = $this->calculateSeal($parameters);
		
		$json = json_encode($parameters);
		
		$request = new Customweb_Core_Http_Request($this->getEndpoint());
		$request->setMethod('POST')->appendHeader('content-type:application/json')->appendHeader('accept:application/json')->setBody($json);
		$client = Customweb_Core_Http_Client_Factory::createClient();
		$response = $client->send($request);
		
		if ($response->getStatusCode() != '200') {
			throw new Exception(
					Customweb_I18n_Translation::__(
							"Failed to send request to remote server. Expect to receive response code 200, receive code !code. Status Message: !message", 
							array(
								'!code' => $response->getStatusCode(),
								'!message' => $response->getStatusMessage() 
							)));
		}
		$result = json_decode($response->getBody(), true);
		
		if ($result === null) {
			throw new Exception(Customweb_I18n_Translation::__("Unable to decode the server response."));
		}
		
		return $result;
	}

	protected function processWithResponseValidation(array $parameters){
		$response = $this->processWithSealValidation($parameters);
		
		if (isset($response['responseCode']) && $response['responseCode'] != '00') {
			throw new Exception(Customweb_BNPMercanet_Util::getErrorMessageByResponseCode($response['responseCode']));
		}
		
		return $response;
	}

	protected function processWithSealValidation(array $parameters){
		$response = $this->processRequest($parameters);
		
		if (!isset($response['seal'])) {
			if (isset($response['responseCode']) && $response['responseCode'] != '00') {
				throw new Exception("Transaction failed because the server responded with:".Customweb_BNPMercanet_Util::getErrorMessageByResponseCode($response['responseCode']));
			}
			
			throw new Exception("No seal was provided in the server response.");
		}
		
		$calculatedSeal = strtolower($this->calculateSeal($response));
		if ($calculatedSeal !== strtolower($response['seal'])) {
			throw new Exception(Customweb_I18n_Translation::__("The calculated and the returned seal do not match."));
		}
		
		return $response;
	}

	protected function calculateSeal($parameters){
		ksort($parameters);
		$data = '';
		
		if (isset($parameters['keyVersion'])) {
			unset($parameters['keyVersion']);
		}
		
		if (isset($parameters['seal'])) {
			unset($parameters['seal']);
		}
		foreach ($parameters as $value) {
			if(is_array($value)){
				ksort($value);
				foreach ($value as $valueSub) {
					$data .= $valueSub;
				}
			}else{
				$data .= $value;
			}
			
		}
		
		return hash_hmac('sha256', $data, $this->getConfiguration()->getSecretKey());
	}

	protected function checkLiveAvailability(){
		$url = $this->getEndpoint();
		$replaced = str_replace($this->getConfiguration()->getBaseOfficeEndPointUrlTest(), $this->getConfiguration()->getBaseOfficeEndPointUrlLive(), 
				$url);
		$request = new Customweb_Core_Http_Request($replaced);
		$request->setMethod('POST')->appendHeader('content-type:application/json')->appendHeader('accept:application/json');
		$client = Customweb_Core_Http_Client_Factory::createClient();
		try {
			$client->send($request);
		}
		catch (Exception $e) {
			throw new Exception(
					Customweb_I18n_Translation::__('Error connecting to BNP Paribas Mercanet. Reason: !message', 
							array(
								'!message' => $e->getMessage() 
							)));
		}
	}
}