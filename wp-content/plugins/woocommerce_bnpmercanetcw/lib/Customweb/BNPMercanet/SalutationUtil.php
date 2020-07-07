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

require_once 'Customweb/BNPMercanet/SalutationUtil.php';



/**
 * Util which attempts to map different salutations to a strict female / male gender.
 *
 * @author Sebastian Bossert
 *
 */
final class Customweb_BNPMercanet_SalutationUtil {
	private static $males = array(
		'mr',
		'mister',
		'sir',
		'lord',
		'herr',
		'sig',
		'signor',
		'm',
		'monsieur' 
	);
	private static $females = array(
		'mlle',
		'mademoiselle',
		'ms',
		'mrs',
		'frau',
		'miss',
		'misses',
		'lady',
		'signorina' 
	);

	/**
	 * Tries to identify a gender based on the given salutation.
	 *
	 * @param string $salutation
	 * @return 'male' | 'female' | NULL
	 */
	public static function getGender($salutation){
		$salutation = strtolower(trim($salutation, ' .'));
		if (strpos($salutation, ' ') !== false) {
			// possibly Herr Dr. Dr. med Prof.
			$salutations = explode(' ', $salutation);
			foreach ($salutations as $subSalutation) {
				$returnValue = Customweb_BNPMercanet_SalutationUtil::getGender($subSalutation);
				if ($returnValue !== null) {
					return $returnValue;
				}
			}
		}
		if (in_array($salutation, Customweb_BNPMercanet_SalutationUtil::$males)) {
			return 'male';
		}
		else if (in_array($salutation, Customweb_BNPMercanet_SalutationUtil::$females)) {
			return 'female';
		}
		return null;
	}
}