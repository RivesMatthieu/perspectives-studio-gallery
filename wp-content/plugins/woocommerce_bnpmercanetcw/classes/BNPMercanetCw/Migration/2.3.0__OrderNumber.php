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
require_once 'Customweb/Database/Migration/IScript.php';

class BNPMercanetCw_Migration_2_3_0 implements Customweb_Database_Migration_IScript {

	public function execute(Customweb_Database_IDriver $driver){
		
		$entityManager = BNPMercanetCw_Util::getEntityManager();
		
		$tableNameTransaction = $entityManager->getTableNameForEntityByClassName('BNPMercanetCw_Entity_Transaction');
		$result = $driver->query("SHOW COLUMNS FROM `" . $tableNameTransaction . "` LIKE 'postId'");
		if ($result->getRowCount() <= 0) {
			$driver->query("ALTER TABLE `" . $tableNameTransaction . "` ADD COLUMN  `postId` VARCHAR( 63 )")->execute();
		}
	
		return true;
	}
}