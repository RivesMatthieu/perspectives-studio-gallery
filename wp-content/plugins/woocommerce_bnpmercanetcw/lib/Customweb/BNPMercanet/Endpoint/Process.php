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

require_once 'Customweb/BNPMercanet/Authorization/RedirectParameterBuilder.php';
require_once 'Customweb/I18n/Translation.php';
require_once 'Customweb/Payment/ITransactionHandler.php';
require_once 'Customweb/Payment/Endpoint/Controller/Process.php';
require_once 'Customweb/Util/Html.php';
require_once 'Customweb/Core/Http/Response.php';



/**
 *
 * @author Mathis Kappeler
 * @Controller("process")
 *
 */
class Customweb_BNPMercanet_Endpoint_Process extends Customweb_Payment_Endpoint_Controller_Process {

	/**
	 * @Action("redirect")
	 */
	public function redirect(Customweb_Core_Http_IRequest $request){
		$transactionHandler = $this->getContainer()->getBean('Customweb_Payment_ITransactionHandler');
		if (!$transactionHandler instanceof Customweb_Payment_ITransactionHandler) {
			throw new Exception('TransactionHandler is not an instance of Customweb_Payment_ITransactionHandler');
		}
		
		$idMap = $this->getTransactionId($request);
		$transaction = $transactionHandler->findTransactionByTransactionExternalId($idMap['id']);
		
		$parameters = $request->getParameters();
		$data = null;
		if (isset($parameters['Data'])) {
			$data = $parameters['Data'];
		}
		else if (isset($parameters['base64Response'])) {
			$data = $parameters['base64Response'];
		}
		
		if ($transaction === null) {
			throw new Exception('No transaction found');
		}
		
		if ($data !== null) {
			$responseCode = $this->extractResponseCode($data);
			if ($responseCode !== null) {
				if ($responseCode == '00' || $responseCode == '60') {
					return $this->createBreakoutHtml($transaction->getSuccessUrl());
				}
				else {
					return $this->createBreakoutHtml($transaction->getFailedUrl());
				}
			}
		}
		if ($transaction->isAuthorizationFailed()) {
			return $this->createBreakoutHtml($transaction->getFailedUrl());
		}
		else {
			return $this->createBreakoutHtml($transaction->getSuccessUrl());
		}
	}

	/**
	 * @Action("post")
	 *
	 * Creates a html form containing the specified parameters received in the response, then submits the form.
	 *
	 * @param Customweb_Core_Http_IRequest $request
	 * @param Customweb_BNPMercanet_Authorization_Transaction
	 * @return Customweb_Core_Http_Response
	 */
	public function post(Customweb_Core_Http_IRequest $request, Customweb_BNPMercanet_Authorization_Transaction $transaction){
		$parameters = $request->getParameters();
		if (!isset($parameters['signature'])) {
			throw new Exception("No signature provided");
		}
		$transaction->checkSecuritySignature("process/post", $parameters['signature']);
		try {
			$formData = array();
			foreach ($parameters as $key => $value) {
				if (substr($key, 0, strlen('bnpmercanetform_')) === 'bnpmercanetform_') {
					$formData[substr($key, strlen('bnpmercanetform_'))] = $value;
				}
			}
			
			$url = $this->getContainer()->getBean('Customweb_BNPMercanet_Configuration')->getPaymentInitEndPoint();
			$builder = new Customweb_BNPMercanet_Authorization_RedirectParameterBuilder($this->getContainer(), $transaction, $formData);
			$redirectParams = $builder->buildParameterArray();
			
			$html = "<html><body onload='document.bnpmercanetRedirectForm.submit()'>";
			$html .= "<form name='bnpmercanetRedirectForm' action='$url' method='post'>";
			$html .= Customweb_Util_Html::buildHiddenInputFields($redirectParams);
			$html .= "</form></body></html>";
			
			return Customweb_Core_Http_Response::_($html);
		}
		catch (Exception $exc) {
			$transaction->setAuthorizationFailed($exc->getMessage());
			$url = $transaction->getFailedUrl();
			return $this->createBreakoutHtml($url);
		}
	}

	/**
	 * @Action("breakout")
	 */
	public function breakOutAction(Customweb_Core_Http_IRequest $request, Customweb_BNPMercanet_Authorization_Transaction $transaction){
		$parameters = $request->getParameters();
		if (!isset($parameters['signature'])) {
			throw new Exception("No signature provided");
		}
		$transaction->checkSecuritySignature("process/breakout", $parameters['signature']);
		
		$url = $transaction->getSuccessUrl();
		if($transaction->isAuthorizationFailed()){
			$url = $transaction->getFailedUrl();
		}
		return $this->createBreakoutHtml($url);
	}
	
	private function createBreakoutHtml($url){
		return '<script type="text/javascript">
				top.location.href = "' . $url . '";
			</script>
		
			<noscript>
				<a class="button btn bnpmercanet-continue-button submit" href="' . $url . '" target="_top">' . Customweb_I18n_Translation::__('Continue') . '</a>
			</noscript>';
	}

	/**
	 *
	 * @return Customweb_BNPMercanet_Helper
	 */
	protected function getHelper(){
		return $this->getContainer()->getBean('Customweb_BNPMercanet_Helper');
	}

	private function extractResponseCode($data){
		$data = base64_decode($data);
		$rs = array();
		preg_match('/responseCode\=\"([^"]+)\"/i', $data, $rs);
		if (isset($rs[1])) {
			return $rs[1];
		}
		else {
			preg_match('/responseCode\=([^|]+)\|/i', $data, $rs);
			if (isset($rs[1])) {
				return $rs[1];
			}
			else {
				return null;
			}
		}
	}
}