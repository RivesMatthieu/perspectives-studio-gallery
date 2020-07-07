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
require_once 'Customweb/I18n/Translation.php';
require_once 'Customweb/Payment/Util.php';

final class Customweb_BNPMercanet_Util {

	const UPDATE_INTERVAL_NONAUTHORIZED = 10;
	const UPDATE_INTERVAL_AUTHORIZED = 120;

	private function __construct(){
		// prevent any instantiation of this class
	}

	public static function getCleanLanguageCode($lang){
		$supportedLanguages = array(
			'de_DE',
			'en_US',
			'fr_FR',
			'es_ES',
			'it_IT',
			'nl_NL',
			'cy_GB' 
		);
		return substr(Customweb_Payment_Util::getCleanLanguageCode($lang, $supportedLanguages), 0, 2);
	}

	public static function formatCurrencyAmount($amount, $currencyCode){
		return Customweb_Util_Currency::formatAmount($amount, $currencyCode, '', '');
	}

	public static function readDataString($data){
		if (empty($data)) {
			return array();
		}
		
		$data = base64_decode($data);
		$data = explode('|', $data);
		$parsed = array();
		foreach ($data as $v) {
			list($key, $value) = explode('=', $v);
			$parsed[$key] = $value;
		}
		
		return $parsed;
	}

	public static function getErrorMessageByResponseCode($responseCode){
		if ($responseCode == '00') {
			return null;
		}
		
		$postFix = ' ' . Customweb_I18n_Translation::__("The returned response code is '!code'.", array(
			'!code' => $responseCode 
		));
		
		switch ($responseCode) {
			case '02':
				return Customweb_I18n_Translation::__(
						"Authorisation request to be performed via telephone with the issuer, as the card authorisation threshold has been exceeded, if the forcing is authorised for the merchant.") .
						 $postFix;
			case '03':
				return Customweb_I18n_Translation::__("You have an invalid distance selling contract.") . $postFix;
			case '05':
				return Customweb_I18n_Translation::__("The authorisation is refused.") . $postFix;
			case '12':
				return Customweb_I18n_Translation::__("The request failed because of invalid parameters in the reqeust.") . $postFix;
			case '14':
				return Customweb_I18n_Translation::__("The provided bank details or the provided CVC is invlaid.") . $postFix;
			case '17':
				return Customweb_I18n_Translation::__("The transaction was canceld by the customer.");
			case '24':
				return Customweb_I18n_Translation::__(
						"The operation can not be performed. The transaction status does not allow this transaction or the operation is currenlty not possible.") .
						 $postFix;
			case '25':
				return Customweb_I18n_Translation::__("The transaction reference was not found in the remote system.") . $postFix;
			case '30':
				return Customweb_I18n_Translation::__("The request failed because of invalid format in some field.") . $postFix;
			case '34':
				return Customweb_I18n_Translation::__("The reqeust is refused because it was treated as fraud.") . $postFix;
			case '40':
				return Customweb_I18n_Translation::__("This operation is not activated on your merchant account.") . $postFix;
			case '51':
				return Customweb_I18n_Translation::__("The amount is too high.") . $postFix;
			case '54':
				return Customweb_I18n_Translation::__("The expiry date is invalid or the card is expired.") . $postFix;
			case '60':
				return Customweb_I18n_Translation::__("The transaction is pending.") . $postFix;
			case '63':
				return Customweb_I18n_Translation::__("The security rules are not observed, hence the transaction is stopped.") . $postFix;
			case '75':
				return Customweb_I18n_Translation::__("The number of attempts to enter the card number exceeded.") . $postFix;
			case '90':
			case '99':
				return Customweb_I18n_Translation::__("The service is temporarily unavailable.") . $postFix;
			case '94':
				return Customweb_I18n_Translation::__("The transaction is a duplicated of an other transaction. Therefore it is refused.") . $postFix;
			case '97':
				return Customweb_I18n_Translation::__("The timeframe is exceeded, hence the transaction is refused.") . $postFix;
			default:
				return Customweb_I18n_Translation::__("The operation was rejected by an unkown error.") . $postFix;
		}
	}

	public static function getPlanErrors($name, $min, $max, $number, $period, $first){
		$error = array();
		if (empty($name)) {
			$error['name'] = Customweb_I18n_Translation::__('Can not be empty');
		}
		if (!is_numeric($min) || $min < 0) {
			$error['min'] = Customweb_I18n_Translation::__('Must not be negative');
		}
		if (!is_numeric($max) || $max <= $min) {
			$error['max'] = Customweb_I18n_Translation::__('Must be bigger than the minimum');
		}
		if (!is_numeric($number) || $number < 2) {
			$error['number'] = Customweb_I18n_Translation::__('Must be bigger than 1');
		}
		if (!is_numeric($period) || $period < 1 || $period > 30) {
			$error['period'] = Customweb_I18n_Translation::__('Must be bigger than 1 and smaller than 31');
		}
		if (!is_numeric($first) || $first < 1 || $first > 100 - $number + 1) {
			$error['first'] = Customweb_I18n_Translation::__('Must be bigger than 0 and smaller than 100');
		}
		return $error;
	}
}