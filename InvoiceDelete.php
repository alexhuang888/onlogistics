<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * This file is part of Onlogistics, a web based ERP and supply chain 
 * management application. 
 *
 * Copyright (C) 2003-2008 ATEOR
 *
 * This program is free software: you can redistribute it and/or modify it 
 * under the terms of the GNU Affero General Public License as published by 
 * the Free Software Foundation, either version 3 of the License, or (at your 
 * option) any later version.
 *
 * This program is distributed in the hope that it will be useful, but WITHOUT 
 * ANY WARRANTY; without even the implied warranty of MERCHANTABILITY or 
 * FITNESS FOR A PARTICULAR PURPOSE.  See the GNU Affero General Public 
 * License for more details.
 *
 * You should have received a copy of the GNU Affero General Public License
 * along with this program.  If not, see <http://www.gnu.org/licenses/>.
 *
 * PHP version 5.1.0+
 *
 * @package   Onlogistics
 * @author    ATEOR dev team <dev@ateor.com>
 * @copyright 2003-2008 ATEOR <contact@ateor.com> 
 * @license   http://www.fsf.org/licensing/licenses/agpl-3.0.html GNU AGPL
 * @version   SVN: $Id$
 * @link      http://www.onlogistics.org
 * @link      http://onlogistics.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

require_once('config.inc.php');
require_once('Objects/Task.inc.php');

$auth = Auth::Singleton();
$auth->checkProfiles(array());
//Database::connection()->debug = true;
$retURL = 'InvoiceCommandList.php?CommandId=' . $_REQUEST['cmdID']
        . '&returnURL=' . $_REQUEST['retURL'];

if (isset($_REQUEST['docId']) && is_array($_REQUEST['docId'])) {
    //On demarre la transaction
    Database::connection()->startTrans();

	foreach($_REQUEST['docId'] as $i => $docId) {
		$invoice = Object::load('Invoice', $docId);
		// S'il y a des Payments associes, c'est non supprimable
		if (!Tools::isEmptyObject($invoice->getInvoicePaymentCollection())) {
		    continue;
		}
        
        // si cmd de transport on repasse les aco � non factur�
        $cmd = $invoice->getCommand();
        $commandItemCol = $cmd->getCommandItemCollection();
        foreach($commandItemCol as $commandItem) {
            $ach = $commandItem->getActivatedChain();
            if(!($ach instanceof ActivatedChain)) {
                continue;
            }
            $acoCol = $ach->getActivatedChainOperationCollection();
            foreach($acoCol as $aco) {
                /* Met a jour:
                 * ACO.PrestationFactured = false
                 * ACO->setPrestationCommandDate('0000-00-00 00:00:00')
                 * Ppasse �galement les box � non factur� si il y a une ack de 
                 * regroupement pr�c�dent une aco de transport, ou les lem � non 
                 * factur� si il y a une tache de sortie de stock
                 */
                $aco->updateWhenDeleteInvoice();
                saveInstance($aco, $retURL);
            }
        }

		// methode surchargee=> impacts en cascade
        deleteInstance($invoice, $retURL);
		unset($invoice);
    }

	//On commite
    if (Database::connection()->hasFailedTrans()) {
        trigger_error(Database::connection()->errorMsg(), E_USER_WARNING);
    	Database::connection()->rollbackTrans();
    	Template::errorDialog(E_ERROR_IMPOSSIBLE_ACTION, $retURL);
    	exit;
    }
    Database::connection()->completeTrans();
}

Tools::redirectTo($retURL);
?>
