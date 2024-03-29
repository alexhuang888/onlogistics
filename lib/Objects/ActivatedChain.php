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

class ActivatedChain extends _ActivatedChain {
    // Constructeur {{{

    /**
     * ActivatedChain::__construct()
     * Constructeur
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    // }}}
    // ActivatedChain::GetTotalDistance() {{{

    /**
     * Retourne le nombre total de kilom�tres des taches de la chaine activ�e
     *
     * @access public
     * @return void
     */
    public function GetTotalDistance() {
        $TotalDistance = 0;
        $ActivatedChainOperationCollection =
            $this->GetActivatedChainOperationCollection();
        for($i = 0;
            $i < $ActivatedChainOperationCollection->GetCount(); $i++) {
            unset($ActivatedChainOperation);
            $ActivatedChainOperation =
                $ActivatedChainOperationCollection->GetItem($i);
            unset($ActivatedChainTaskCollection);
            $ActivatedChainTaskCollection =
                $ActivatedChainOperation->GetActivatedChainTaskCollection();
            for($j = 0; $j < $ActivatedChainTaskCollection->GetCount(); $j++) {
                unset($ActivatedChainTask);
                $ActivatedChainTask =
                    $ActivatedChainTaskCollection->GetItem($j);
                $TotalDistance += $ActivatedChainTask->GetKilometerNumber();
            }
        }
        return $TotalDistance;
    }

    // }}}
    // ActivatedChain::getTotalDuration() {{{

    /**
     * Retourne la dur�e totale des taches de la chaine activ�e
     *
     * @access public
     * @return void
     */
    public function GetTotalDuration() {
        $ActivatedChainOperationCollection =
            $this->GetActivatedChainOperationCollection();
        $TotalDuration = 0;
        for($i = 0; $i <
            $ActivatedChainOperationCollection->GetCount(); $i++) {
            unset($ActivatedChainOperation);
            $ActivatedChainOperation =
                $ActivatedChainOperationCollection->GetItem($i);
            $TotalDuration += $ActivatedChainOperation->getACODuration();
        }
        return $TotalDuration;
    }

    // }}}
    // ActivatedChain::getTotalCost() {{{

    /**
     * Retourne le co�t total des taches de la chaine activ�e
     *
     * @access public
     * @return void
     */
    public function GetTotalCost() {
        $acoCollection = $this->GetActivatedChainOperationCollection();
        $totalCost = 0;
        for($i = 0; $i < $acoCollection->GetCount(); $i++) {
            $aco = $acoCollection->GetItem($i);
            if ($aco instanceof ActivatedChainOperation) {
                $actCollection = $aco->GetActivatedChainTaskCollection();
                for($j = 0; $j < $actCollection->GetCount(); $j++) {
                    $act = $actCollection->GetItem($j);
                    $totalCost += floatval($act->GetCost());
                    unset($act);
                }
            }
            unset($aco, $actCollection);
        }
        return $totalCost;
    }

    // }}}
    // ActivatedChain::GetRealProductCollection() {{{

    /**
     * retourne la collection de produits associ�e
     * quelquesoit le type de commande (transport ou produits)
     *
     * @access public
     * @return object $RealProductCollection
     */
    public function GetRealProductCollection() {
        $RealProductCollection = new Collection();
        $ProductCommandItemCollection = $this->GetCommandItemCollection();
        for($i = 0; $i < $ProductCommandItemCollection->GetCount(); $i++) {
            unset($ProductCommandItem);
            $ProductCommandItem = $ProductCommandItemCollection->getItem($i);
            $ItemProductCollection =
                $ProductCommandItem->GetRealProductCollection();
            for($j = 0; $j < $ItemProductCollection->GetCount(); $j++) {
                $RealProductCollection->SetItem(
                    $ItemProductCollection->GetItem($j));
            } // for
        } // for
        return $RealProductCollection;
    }

    // }}}
    // ActivatedChain::getFirstTask() {{{

    public function getFirstTask() {
        $OperationCollection = $this->getActivatedChainOperationCollection();
        if (0 == $OperationCollection->getCount()) {
            return false;
        }
        $OperationCollection->Sort("order");
        $FirstOperation = $OperationCollection->getItem(0);

        $Tasks = $FirstOperation->getActivatedChainTaskCollection();
        if (0 == $Tasks->getCount()) {
            return false;
        }
        $Tasks->Sort("order");
        return $Tasks->getItem(0);
    }

    // }}}
    // ActivatedChain::hasBLEditionTask() {{{

    /**
     * retourne true si la chaine comporte une tache d'�dition du BL
     * automatique dans une op�ration de stockage ou false sinon
     *
     * @access    public
     * @return    boolean
     */
    public function hasBLEditionTask() {
        require_once("Objects/Task.const.php");
        return $this->_hasEditionTaskOfType(TASK_BL_EDITING);
    }

    // }}}
    // ActivatedChain::hasInvoiceEditionTask() {{{

    /**
     * retourne true si la chaine comporte une tache d'�dition de facture
     * automatique dans une op�ration de stockage ou false sinon
     *
     * @access    public
     * @return    boolean
     */
    public function hasInvoiceEditionTask() {
        require_once("Objects/Task.const.php");
        return $this->_hasEditionTaskOfType(TASK_INVOICE_EDITING);
    }

    // }}}
    // ActivatedChain::hasDirectionnalLabelEditiontask() {{{

    /**
     * retourne true si la chaine comporte une tache d'�dition d'etiquettes
     * directionnelles automatique dans une op�ration de stockage ou false
     * sinon
     *
     * @access    public
     * @return    boolean
     */
    public function hasDirectionnalLabelEditionTask() {
        require_once("Objects/Task.const.php");
        return $this->_hasEditionTaskOfType(TASK_ETIQUETTE_DIRECTION);
    }

    // }}}
    // ActivatedChain::hasProductLabelEditionTask() {{{

    /**
     * retourne true si la chaine comporte une tache d'�dition d'etiquettes
     * produits automatique dans une op�ration de stockage et false sinon
     *
     * @access    public
     * @return    boolean
     */
    public function hasProductLabelEditionTask() {
        require_once("Objects/Task.const.php");
        return $this->_hasEditionTaskOfType(TASK_ETIQUETTE_PROD);
    }

    // }}}
    // ActivatedChain::_hasEditionTaskOfType() {{{

    /**
     * retourne true si la chaine comporte une tache d'�dition de type $type
     * automatique dans une op�ration de stockage et false sinon
     *
     * @access    private
     * @param    integer $type la contante de type de tache
     * @return    boolean
     **/
    private function _hasEditionTaskOfType($type) {
        require_once("Objects/Operation.const.php");
        require_once("Objects/ActivatedChainTask.php");
         $acoCollection = $this->getActivatedChainOperationCollection();
        for($i = 0; $i < $acoCollection->getCount(); $i++){
            $aco = $acoCollection->getItem($i);
            if ($aco->getOperationID() != OPERATION_STOC) {
                continue;
            }
            $actCollection = $aco->getActivatedChainTaskCollection();
            for($j = 0; $j < $actCollection->getCount(); $j++){
                $act = $actCollection->getItem($j);
                if ($act->getTaskID() == $type &&
                    $act->getTriggerMode() == ActivatedChainTask::TRIGGERMODE_AUTO) {
                    return true;
                }
                unset($act);
            } // for
            unset($aco, $actCollection);
        } // for
        return false;
    }

    // }}}
    // ActivatedChain::hasTaskOfType() {{{

    /**
     * Retourne la 1ere tache trouvee de type $type si la chaine comporte
     * une tache de type $type
     * Retourne false sinon
     *
     * @access public
     * @param  mixed array or integer $type la contante de type de tache
     * @return boolean or integer
     */
    public function hasTaskOfType($type, $reverseOrder=false){
        require_once("Objects/ActivatedChainTask.php");
        $filter = array();
        $order  = $reverseOrder?SORT_DESC:SORT_ASC;
        $acoCollection = $this->getActivatedChainOperationCollection($filter,
            array('Order'=>$order));
        for($i = 0; $i < $acoCollection->getCount(); $i++){
            $aco = $acoCollection->getItem($i);    // $actCollection : lazy
            $actCollection = $aco->getActivatedChainTaskCollection($filter,
                array('Order'=>$order));
            for($j = 0; $j < $actCollection->getCount(); $j++){
                $act = $actCollection->getItem($j);
                $tskID = $act->getTaskId();
                if (is_array($type)) {
                    if (in_array($tskID, $type)) return $act;
                } else {
                    if ($tskID == $type) return $act;
                }
                unset($act);
            }
            unset($aco, $actCollection);
        }
        return false;
    }

    // }}}
    // ActivatedChain::hasBoxCreatorTask() {{{

    /**
     * Retourne true si la cha�ne comporte au moins une t�che cr�atrice de Box.
     * Retourne false sinon.
     *
     * @access public
     * @return boolean or integer
     */
    public function hasBoxCreatorTask() {
         $acoCollection = $this->getActivatedChainOperationCollection(array(),
            array(), array('Id'));
        $count = $acoCollection->getCount();
        for($i = 0; $i < $count; $i++){
            $aco = $acoCollection->getItem($i);
            $actCollection = $aco->getActivatedChainTaskCollection(array(),
                array(), array('Task'));
            $jcount = $actCollection->getCount();
            for($j = 0; $j < $jcount; $j++){
                $act = $actCollection->getItem($j);
                $tsk = $act->getTask();
                if (true == $tsk->getIsBoxCreator()) {
                    return true;
                }
                unset($act);
            }
            unset($aco, $actCollection);
        }
        return false;
    }

    // }}}
    // ActivatedChain::getActivatedChainTaskCollection() {{{

    /**
     * Retourne la collection des ActivatedChainTask de type $type impliqu�es
     * dans la cha�ne. Si $type vaut false, toutes les taches sont retourn�es.
     *
     * @access public
     * @param mixed filter
     * @return Collection la collection de taches
     */
    public function getActivatedChainTaskCollection($filter = array()){
        $return = new Collection();
        $acoCol = $this->getActivatedChainOperationCollection(array(),
            array(), array('Id'));
        $count = $acoCol->getCount();
        for($i = 0; $i < $count; $i++){
            $aco = $acoCol->getItem($i);
            $ackCol = $aco->getActivatedChainTaskCollection($filter);
            if ($ackCol instanceof Collection && $ackCol->getCount() > 0) {
                $return = $return->merge($ackCol);
            }
        }
        return $return;
    }

    // }}}
    // ActivatedChain::updateUnavailabilities() {{{

    /**
     * ActivatedChain::updateUnavailabilities()
     * met � jour les
     *
     * @access public
     * @return void
     */
    public function updateUnavailabilities($command){
        require_once('Objects/Unavailability.php');
        require_once('Objects/Chain.php');
        require_once('Objects/Operation.const.php');

        $validtypes = array(Chain::CHAIN_TYPE_COURSE, Chain::CHAIN_TYPE_MAINTENANCE,
            Chain::CHAIN_TYPE_HIRING, Chain::CHAIN_TYPE_PRODUCT);
        if (!in_array($this->getType(), $validtypes)) {
            return false;
        }
        $opeMapper = Mapper::singleton('Operation');
        $coll = $opeMapper->loadCollection(
            array('Type' => array(
                Operation::OPERATION_TYPE_PROD, 
                Operation::OPERATION_TYPE_CONS)));
        $opeIds = $coll->getItemIds();
        $opeIds[] = OPERATION_VOL;
        
        $acoCollection = $this->getActivatedChainOperationCollection(
            array('Operation.Id' => $opeIds)
        );
        if (Tools::isEmptyObject($acoCollection)) {
            return false;
        }
        $count = $acoCollection->getCount();
        for($i = 0; $i < $count; $i++){
            $aco = $acoCollection->getItem($i);
            // on ajoute une indisponibilit� pour l'acteur
            $act = $aco->getActor();
            if ($act instanceof Actor) {
                // on verifie que l'acteur est disponible
                /* XXX comment� pour l'instant
                if(!$act->isAvailableFor(
                    DateTimeTools::MySQLDateToTimeStamp($aco->getBegin()),
                    DateTimeTools::MySQLDateToTimeStamp($aco->getEnd()))) {
                    $operation = $aco->getOperation();
                    return new Exception(sprintf(
                        _('Actor assigned to operation is not available for given period.'),
                        $act->getName(),
                        $operation->getName())
                    );
                }
                */
                $act_planning = $act->getWeeklyPlanning();
                $act_unavail = new Unavailability();
                $act_unavail->setBeginDate($aco->getBegin());
                $act_unavail->setEndDate($aco->getEnd());
                $act_unavail->setWeeklyPlanning($act_planning);
                $act_unavail->setCommand($command);
                $act_unavail->setActivatedChainOperation($aco);
                $act_unavail->save();
            }
            if ($this->getType() == Chain::CHAIN_TYPE_PRODUCT) {
                continue;  // La suite ne concerne pas la Production
            }
            // si la commande est avec instructeur, on cr�e les indisponibilit�s
            // pour le client de la commande
            $cus = $command->getCustomer();
            if (!$command->getSoloFly() && $cus instanceof Actor) {
                $cus_planning = $cus->getWeeklyPlanning();
                $cus_unavail = new Unavailability();
                $cus_unavail->setBeginDate($aco->getBegin());
                $cus_unavail->setEndDate($aco->getEnd());
                $cus_unavail->setWeeklyPlanning($cus_planning);
                $cus_unavail->setCommand($command);
                $cus_unavail->setActivatedChainOperation($aco);
                $cus_unavail->save();
            }
            // on ajoute une indisponibilit� pour le concreteproduct
            $ccp = $aco->getConcreteProduct();
            if ($ccp instanceof ConcreteProduct) {
                $ccp_planning = $ccp->getWeeklyPlanning();
                $ccp_unavail = new Unavailability();
                $ccp_unavail->setBeginDate($aco->getBegin());
                $ccp_unavail->setEndDate($aco->getEnd());
                $ccp_unavail->setWeeklyPlanning($ccp_planning);
                $ccp_unavail->setCommand($command);
                $ccp_unavail->setActivatedChainOperation($aco);
                $ccp_unavail->save();
            }
        }
        return true;
    }

    // }}}
    // ActivatedChain::getChainCommandCost() {{{

    /**
     * M�htode de calcul du cout d'une commande
     * de transport.
     * Retourne un tableau dont les cl� sont 'ht' et 'ttc'.
     *
     * @param object $chainCommand la commande
     * @return array
     */
    public function getChainCommandCost($chainCommand, $ht = false)
    {
        require_once('PrestationManager.php');
        if (!$ht || empty($ht)) {
            //list($ht, $ttc) = $this->getChainCommandPrestationCost($chainCommand);
            $prsManager = new PrestationManager();
            $prices = $prsManager->calculChainCommandCost($chainCommand);
            $ht = $ttc = 0;
            foreach($prices as $key=>$values) {
                $ht += $values['totalht'];
                $ttc += $values['totalttc'];
            }
        } else {
            $tva = $this->getPrestationTVARate($chainCommand);
            $ttc = $ht + ($ht * $tva/100);
        }
        $rawht = $ht;
        $handing   = $chainCommand->getHanding();
        $insurance = $chainCommand->getInsurance();
        $packing   = $chainCommand->getPacking();
        $ht  = ($ht - ($ht * $handing/100)) + $insurance + $packing;
        $ttc = ($ttc - ($ttc * $handing/100)) + $insurance + $packing;
        $sc  = $chainCommand->getSupplierCustomer();
        if (!($sc instanceof SupplierCustomer) || $sc->getHasTVA()) {
            require_once('Objects/TVA.inc.php');
            $ttc += ($insurance * (getTVARateByCategory(TVA::TYPE_INSURANCE)/100))
                  + ($packing   * (getTVARateByCategory(TVA::TYPE_PACKING)/100));
        }
        return array('raw_ht'=>$rawht, 'ht'=>$ht, 'ttc'=>$ttc, 'tva'=>$ttc-$ht);
    }

    // }}}
    // ActivatedChain::getChainCommandPrestationCost() {{{

    /**
     * M�htode de calcul du cout prestation d'une commande de transport.
     * Retourne un tableau (ht,ttc).
     *
     * @param object $chainCommand la commande
     * @return array
     */
    public function getChainCommandPrestationCost($chainCommand) {
        $ACOHTPrice = 0;
        $totalCostHT = 0;
        $totalCostTTC = 0;
        // Calcul du poids et du volume de la commande
        $cmdItemCol = $this->getCommandItemCollection();
        $weight = 0;
        $volume = 0;
        $totalQty = 0;
        $loop = $cmdItemCol->getCount();
        for ($i=0 ; $i<$loop ; $i++) {
            $cmdItem = $cmdItemCol->getItem($i);
            $qt = $cmdItem->getQuantity();
            $weight += $cmdItem->getWeight() * $qt;
            $volume += $cmdItem->getHeight() * $cmdItem->getLength() *
                $cmdItem->getWidth() * $qt;
            $totalQty += $qt;
        }
        $acoCollection = $this->getActivatedChainOperationCollection();
        // pour chaque aco de transport
        $count = $acoCollection->getCount();
        for($i=0 ; $i<$count ; $i++) {
            $aco = $acoCollection->GetItem($i);
            // recup�ration zones
            $departureZoneId = Tools::getValueFromMacro($aco,
                '%FirstTask.ActorSiteTransition.DepartureZone.Id%');
            $arrivalZoneId = Tools::getValueFromMacro($aco,
                '%LastTask.ActorSiteTransition.ArrivalZone.Id%');

            if ($aco instanceof ActivatedChainOperation) {
                $acoTime = $aco->getACODuration();
                // recherche prestation associ�e � l'aco
                $filter = array(
                    SearchTools::newFilterComponent('Actor',
                        'PrestationCustomer().Actor', 'Equals',
                        $chainCommand->getCustomerId(), 1, 'Prestation'),
                    SearchTools::newFilterComponent('Operation', '', 'Equals',
                        $aco->getOperationId(), 1),
                    SearchTools::newFilterComponent('Active', '', 'Equals', 1, 1),
                    SearchTools::newFilterComponent('Facturable', '', 'Equals', 1, 1)
                );
                $filter = SearchTools::filterAssembler($filter);
                $prestationCollection = Object::loadCollection('Prestation', $filter);
                $jcount = $prestationCollection->getCount();
                for ($j=0; $j<$jcount; $j++) {
                    $prestation = $prestationCollection->getItem($j);
                    if($prestation instanceof Prestation) {
                        $params = array(
                            'time'            => $acoTime/3600,
                            'departureZoneId' => $departureZoneId,
                            'arrivalZoneId'   => $arrivalZoneId,
                            'weight'          => $weight,
                            'volume'          => $volume,
                            'qty'             => $totalQty
                        );
                        $ACOHTPrice = $prestation->getTotalPrestationPrice($params);
                        // calcul du prix ttc
                        $tvaPrestation = $prestation->getTVA();
                        if($tvaPrestation instanceof TVA) {
                            $ACOTTCPrice = $ACOHTPrice
                                + $ACOHTPrice * ($tvaPrestation->getRate() / 100);
                        } else {
                            $ACOTTCPrice = $ACOHTPrice;
                        }
                        $totalCostHT += $ACOHTPrice;
                        $totalCostTTC += $ACOTTCPrice;
                    }
                }
            }
            unset($aco, $ackCol);
        }
        return array($totalCostHT, $totalCostTTC);
    }

    // }}}
    // ActivatedChain::getPrestationTVARate() {{{

    /**
     * Essaie de retourner un taux de tva pour le calcul du cout de la
     * commmande de transport quand un prix est modifi�.
     *
     * @param
     * @return array
     */
    public function getPrestationTVARate($chainCommand) {
        $tva = -1;
        $acoCollection = $this->getActivatedChainOperationCollection();
        $count = $acoCollection->getCount();
        for($i=0 ; $i<$count ; $i++) {
            $aco = $acoCollection->GetItem($i);
            // recherche prestation associ�e � l'aco
            $filter = array(
                SearchTools::newFilterComponent('Actor',
                    'PrestationCustomer().Actor.Id', 'Equals',
                    $chainCommand->getDestinatorId(), 1, 'Prestation'),
                SearchTools::newFilterComponent('Operation', '', 'Equals',
                    $aco->getOperationId(), 1),
                SearchTools::newFilterComponent('Active', '', 'Equals', 1, 1),
                SearchTools::newFilterComponent('Facturable', '', 'Equals', 1, 1)
            );
            $filter = SearchTools::filterAssembler($filter);
            $prestationCollection = Object::loadCollection('Prestation', $filter);
            $jcount = $prestationCollection->getCount();
            for ($j=0; $j<$jcount; $j++) {
                $prestation = $prestationCollection->getItem($j);
                if($prestation instanceof Prestation) {
                    $prestationTVA = $prestation->getTVA() instanceof TVA?
                        $prestation->getTVA()->getRate():0;
                    if ($tva !== -1 && $tva != $prestationTVA) {
                        throw new Exception(
                            _('Price excl. VAT cannot be modified.')
                        );
                    } else {
                        $tva = $prestationTVA;
                    }
                }
            }
        }
        return $tva==-1?0:$tva;
    }

    // }}}

}

?>