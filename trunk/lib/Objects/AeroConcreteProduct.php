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

class AeroConcreteProduct extends _AeroConcreteProduct {
    // Constructeur {{{

    /**
     * AeroConcreteProduct::__construct()
     * Constructeur
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    // }}}
    /**
     * ConcreteProduct::isAvailableFor()
     * Retourne true si l'appareil est disponible pour le cr�neau $start-$end
     *
     * @param integer timestamp $start
     * @param integer timestamp $end
     * @access public
     * @return boolean
     **/
    function isAvailableFor($start, $end){
        require_once('PlanningTools.php');
        $wplanning = $this->getWeeklyPlanning();
        if (!($wplanning instanceof WeeklyPlanning)) {
            trigger_error("ConcreteProduct::isAvailableFor(): l'appareil " .
                $this->toString() ." n'a pas de planning affect�. Veuillez  " .
                "lui en affecter un", E_USER_ERROR);
        }
        $ptools = new PlanningTools($wplanning);
        $range = $ptools->GetNextAvailableRange($start);
        /*
        if ($range && $range['Start'] <= $start && $range['End'] >= $end) {
            return true;
        }
        */
        if (is_array($range) && count($range) == 2 &&
            $range['Start'] == $start && $range['End'] > $end) {
            $unavailabilities = $wplanning->getUnavailabilityCollection();
            $count = $unavailabilities->getCount();
            for($i = 0; $i < $count; $i++){
                $unavail = $unavailabilities->getItem($i);
                $istart = DateTimeTools::MysqlDateToTimeStamp(
                    $unavail->getBeginDate());
                $iend = DateTimeTools::MysqlDateToTimeStamp(
                    $unavail->getEndDate());
                if (($istart > $start || $iend > $start) && $istart < $end) {
                    return false;
                }
            }
            return true;
        }
        return false;
    }

    /**
     * Enoie une alerte si potentiel depasse (?)
     * @access public
     * @return false si pas d'alerte, sinon void
     **/
    function sendPotentialOverAlert(){
        require_once('AlertSender.php');
        require_once('Objects/Prestation.php');

        $realPotential = $this->getRealHourSinceOverall();
        $virtualPotential = $this->getVirtualHourSinceOverall();
        $col = $this->getAllPrestations(Prestation::PRESTATION_TYPE_MAINTENANCE);
        if (Tools::isEmptyObject($col)) {
            return false;
        }
        $count = $col->getCount();
        $PrestationCollection = new Collection();
        for($j=0; $j<$count; $j++){
            $prest  = $col->getItem($j);
            $refPotential = $prest->getPotential();
            if ($prest && ($realPotential >= $refPotential ||
                                $virtualPotential >= $refPotential)) {
                $PrestationCollection->setItem($prest);
            }
        }
        if ($PrestationCollection->getCount() == 0) {
            return false;
        }
        $pdt = $this->getProduct();
        AlertSender::send_ALERT_POTENTIAL_OVER($this, $pdt, $PrestationCollection);
    }

    /**
     * Retourne true ou false selon si le ConcreteProduct
     * peut etre sauve ou non en base.
     * @access public
     * @return boolean
     **/
    function isSalvable(){
        $notNegativeAttributes = array('RealHourSinceNew',
                                       'RealHourSinceOverall',
                                       'RealHourSinceRepared',
                                       'VirtualHourSinceNew',
                                       'VirtualHourSinceOverall',
                                       'RealLandingSinceNew',
                                       'RealLandingSinceOverall',
                                       'RealLandingSinceRepared',
                                       'RealCycleSinceNew',
                                       'RealCycleSinceOverall',
                                       'RealCycleSinceRepared');
        foreach($notNegativeAttributes as $name) {
            $getter = 'get' . $name;
            $val = $this->$getter();
            if ($val < 0) {
                return false;
            }
        }
        return true;
    }

    /**
     * Met a jour les potentiels, pour le ConcreteProduct, et les CP le composant,
     * retourne:
     * - false si un des potentiels se retrouve negatif,
     * auquel cas on ne sauve pas en base, (a appeler dans une transaction)
     * - true sinon
     * @param array $datas de la forme:
     * array(0 => array('attributes' => array('nomAttribut1', 'nomAttribut2', ...),
     *                     'value' => unEntier),
     *         1 => array('attributes' => array('nomAttribut1', 'nomAttribut2', ...),
     *                    'value' => unEntier),
     *         2 => ...
     *        )
     * @access public
     * @return boolean
     **/
    function updatePotentials($datas) {
        require_once('Objects/Product.php');
        // Filtre aussi sur ClassName, car les attr. du potentiel sont ds AeroCP seulemt
        $filter = array('Product.TracingMode' => Product::TRACINGMODE_SN,
                        'OnCondition' => 0,
                        'ClassName' => 'AeroConcreteProduct');
        // Collection des ConcreteProduct contenus ayant les
        // 3 conditions ci-dessus remplies
        $cpCollection = $this->getConcreteProductCollection($filter);
        // Patch pour le cas ou pas de nomenclature liee: $cpCollection est vide
        // dans ce cas
        $cpCollection->acceptDuplicate = false;
        $cpCollection->setItem($this);
        // /patch
        if (!Tools::isEmptyObject($cpCollection)) {
            $count = $cpCollection->getCount();
            for($i = 0; $i < $count; $i++) {
                $cp = $cpCollection->getItem($i);
                foreach($datas as $data) {
                    $cp->updateAttributesWithQty($data['attributes'], $data['value']);
                }
                // Si un des potentiels est negatif, on retourne le SN
                if (false == $cp->isSalvable()) {
                    return $cp->getSerialNumber();
                }
                $cp->save();
                unset($cp);
            }
        }
        return true;
    }


    /**
     * Retourne une collection des prestationsCost associ�es au
     * concreteproduct, � son product, et � son flytype, c a d
     * une collection pouvant contenir des ConcreteProductPrestationCost,
     * ProductPrestationCost, et des FlyTypePrestationCost
     *
     * @access public
     * @return Collection object
     **/
    function getAllPrestationCosts($type = false) {
        //  ConcreteProductPrestationCost
        $mapper = Mapper::singleton('ConcreteProductPrestationCost');
        $filter = array();
        if($type) {
            $baseFilter = SearchTools::newFilterComponent('Type', 'Prestation.Type', 
                'Equals', $type, 1);
        }
        $filter[] = SearchTools::newFilterComponent('ConcreteProduct', 
            'ConcreteProduct().Id', 'Equals', $this->getId(), 1, 
            'ConcreteProductPrestationCost');
        if($type) {
            $filter[] = $baseFilter;
            $filter = SearchTools::filterAssembler($filter);
        }
        $Collection = $mapper->loadCollection($filter);

        //  ProductPrestationCost
        $Product = $this->getProduct();
        if (false == $Product) {
            return $Collection;
        }
        $mapper = Mapper::singleton('ProductPrestationCost');
        $filter = array();
        $filter[] = SearchTools::newFilterComponent('Product', 
            'Product().Id', 'Equals', $Product->getId(), 1, 
            'ProductPrestationCost');
        if($type) {
            $filter[] = $baseFilter;
            $filter = SearchTools::filterAssembler($filter);
        }
        $col = $mapper->loadCollection($filter);
        $count = $col->getCount();
        for($i=0; $i<$count; $i++){
            $item = $col->getItem($i);
            $Collection->setItem($item);
        }

        //  FlyTypePrestationCost
        $FlyTypeId = $Product->getFlyTypeId();
        if (false == $FlyTypeId) {
            return $col;
        }
        $mapper = Mapper::singleton('FlyTypePrestationCost');
        $filter = array();
        $filter[] = SearchTools::newFilterComponent('FlyType', 
            'FlyType().Id', 'Equals', $FlyTypeId, 1, 
            'FlyTypePrestationCost');
        if($type) {
            $filter[] = $baseFilter;
            $filter = SearchTools::filterAssembler($filter);
        }
        $col = $mapper->loadCollection($filter);
        $count = $col->getCount();
        for($i=0; $i<$count; $i++){
            $item = $col->getItem($i);
            $Collection->setItem($item);
        }
        return $Collection;
    }

    /**
     * Retourne une collection des Prestations associ�es au
     * concreteproduct, � son product, et � son flytype
     *
     * @access public
     * @return PrestationCollection object
     **/
    function getAllPrestations($type = false){
        $col = $this->getAllPrestationCosts($type);
        $count = $col->getCount();
        if ($count == 0) {
            return false;
        }
        $PrestationCollection = new Collection();
        $PrestationCollection->acceptDuplicate = false;
        for($j=0; $j<$count; $j++){
            $item  = $col->getItem($j);
            $Prestation = $item->getPrestation();
            $PrestationCollection->setItem($Prestation);
        }
        return $PrestationCollection;
    }

    /**
     * Affiche le detail de certaines donnees du ConcreteProduct
     * @access public
     * @return string: table HTML
     **/
    function displayDetail(){
        require_once('ActorAddEditTools.php');
        $smarty = new Template();
        // Toutes les heures sont affichees en hh::mm
        $smarty->register_function('date_hundredthsOfHourToTime',
                                   array('DataConverter', "hundredthsOfHourToTime"));

        assignObjectAttributes($smarty, $this, array_keys($this->getProperties()));
        $smarty->assign('ownerName', Tools::getValueFromMacro($this, '%Owner.Name%'));

        $smarty->assign('divId', 'ConcreteProductDetail');  // nom du layer

        require_once('Objects/Prestation.php');
        $PrestationCollection = $this->getAllPrestations(Prestation::PRESTATION_TYPE_MAINTENANCE);

        if (!Tools::isEmptyObject($PrestationCollection)) {
            $count = $PrestationCollection->getCount();
            $PrestationNameArray = array();
            $PrestationDateArray = array();

            for($j=0; $j<$count; $j++){
                $Prestation  = $PrestationCollection->getItem($j);
                $refPotential = $Prestation->getPotential();
                if (count($PrestationNameArray) == 5) {
                    break;  // pas plus de 5 prestations a afficher
                }
                if ($Prestation->getPotential() < $this->getVirtualHourSinceOverall()
                  || $Prestation->getPotentialDate() > date('Y-m-d H:i:s', time())) {
                    $PrestationNameArray[] = $Prestation->getName();
                    $PrestationDateArray[] = $Prestation->getPotentialDate()==0?
                        _('Potential reached') . ': ' 
                        . $Prestation->getPotential():
                        _('Date reached') . ': ' 
                        . I18N::formatDate($Prestation->getPotentialDate(), DATETIME_SHORT);
                }

            }
            $smarty->assign('PrestationNameArray', $PrestationNameArray);
            $smarty->assign('PrestationDateArray', $PrestationDateArray);
        }

        $detail = $smarty->fetch('ConcreteProduct/ConcreteProductDetail.html');
        return $detail;
    }

    /**
     * Affiche le detail de certaines donnees du ConcreteProduct
     *
     * @param string $begin date: limite inferieure
     * @param string $end date: limite superieure
     * @access public
     * @return string: table HTML
     **/
    function displayCarburantDetail($begin, $end){
        require_once('Objects/Task.const.php');
        $smarty = new Template();
        $smarty->assign('divId', 'carburantDetail');  // nom du layer

        $actMapper = Mapper::singleton('ActivatedChainTask');
        $FilterComptArray = array();
        $FilterComptArray[] = SearchTools::NewFilterComponent('ConcreteProduct',
                                                 'ActivatedOperation.RealConcreteProduct',
                                                 'Equals', $this->getId(), 1);
        $FilterComptArray[] = SearchTools::NewFilterComponent('Task', '', 'In', array(TASK_FLY, TASK_FLY_PREPARATION), 1);
        $FilterComptArray[] = SearchTools::NewFilterComponent('Begin', '', 'GreaterThanOrEquals', $begin, 1);
        $FilterComptArray[] = SearchTools::NewFilterComponent('End', '', 'LowerThanOrEquals', $end, 1);
        $FilterComptArray[] = SearchTools::NewFilterComponent('State', '', 'Equals', 2, 1);
        $Filter = SearchTools::FilterAssembler($FilterComptArray);
        $actCollection = $actMapper->loadCollection($Filter, array('Begin' => SORT_ASC));

        if (!Tools::isEmptyObject($actCollection)) {
            $count = $actCollection->getCount();
            $carburantAddedArray = array();
            $carburantUsedArray = array();

            for($j=0; $j<$count; $j++){
                $ActivatedChainTask  = $actCollection->getItem($j);
                $ActivatedChainTaskDetail = $ActivatedChainTask->getActivatedChainTaskDetail();
                $date = $ActivatedChainTask->getBegin();
                $month = I18N::formatDate($date, "%B");

                // CarburantAdded: Taches de preparation de vol
                if ($ActivatedChainTask->getTaskId() == TASK_FLY_PREPARATION) {
                    if (!isset($carburantAddedArray[$month])) {
                        $carburantAddedArray[$month] = 0;
                    }
                    $carburantAddedArray[$month] += $ActivatedChainTaskDetail->getCarburantAdded();
                }

                // CarburantUsed: Taches de vol
                elseif ($ActivatedChainTask->getTaskId() == TASK_FLY) {
                    if (!isset($carburantUsedArray[$month])) {
                        $carburantUsedArray[$month] = 0;
                    }
                    $carburantUsedArray[$month] += $ActivatedChainTaskDetail->getCarburantUsed();
                }
            }

			require_once('Objects/ActivatedChainTaskDetail.const.php');
			//$carburantUnitArray = getCarburantUnitArray();
			$carburantUnitArray = $this->getTankUnitTypeConstArray();
			foreach($carburantAddedArray as $key => $value) {
				$carburantAddedArray[$key] = I18N::formatNumber($carburantAddedArray[$key]) . ' L';
			}
			foreach($carburantUsedArray as $key => $value) {
				$carburantUsedArray[$key] = I18N::formatNumber($carburantUsedArray[$key]) . ' L';
			}
			$smarty->assign('carburantAddedArray', $carburantAddedArray);
			$smarty->assign('carburantUsedArray', $carburantUsedArray);
		}

		$detail = $smarty->fetch('ConcreteProduct/CarburantDetail.html');
		return $detail;
	}

}

?>