<?php

/* vim: set expandtab tabstop=4 shiftwidth=4 softtabstop=4: */

/**
 * IMPORTANT: This is a generated file, please do not edit.
 *
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

/**
 * Invoice class
 *
 * Class containing addon methods.
 */
class Invoice extends _Invoice {
    // Constructeur {{{

    /**
     * Invoice::__construct()
     * Constructeur
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    // }}}
    // Invoice::dataForInvoice() {{{

    /**
     * Sert a generer les lignes de facture du doc pdf pour l'impression
     * @access public
     */
    function dataForInvoice($currency = '�') {
        $PTHT = array();
        $returnData = array();  // sera retourne par la fonction
        require_once('CalculatePriceHanding.php');
        require_once('Objects/InvoiceItem.php');
        require_once('Objects/Operation.const.php');
        require_once('Objects/Task.const.php');
        require_once('Objects/SellUnitType.const.php');
        require_once('Objects/Property.inc.php');
        require_once('FormatNumber.php');

        $InvoiceItemCollection = $this->getInvoiceItemCollection();
        $NewInvoiceItemCollection = new Collection();
        $mapper = Mapper::singleton('Product');

        $dom = $this->findDocumentModel();
        $domPropertyCol = $dom->getDocumentModelPropertyCollection(
                array('PropertyType'=>0), array('Order'=>SORT_ASC));
        $numberOfDomProps = $domPropertyCol->getCount();

        $cmdType = $this->getCommandType();
        for ($i = 0;$i < $InvoiceItemCollection->getCount();$i++) {
            $Item = $InvoiceItemCollection->getItem($i);
            $InvoiceItem = new InvoiceItem();
            $ref = $Item->getReference();
            // Si cmde client de products, on affiche en plus la ref client si
            // elle existe
            if ($cmdType == AbstractDocument::TYPE_CUSTOMER_PRODUCT
            && $Item->getAssociatedReference() != '') {
                $ref .= ' (' . $Item->getAssociatedReference() . ')';
            }
            $InvoiceItem->setReference($ref);
            $InvoiceItem->setName($Item->getName());
            $InvoiceItem->setUnitPriceHT($Item->getUnitPriceHT());
            $InvoiceItem->setQuantity($Item->getQuantity());
            $InvoiceItem->setTva($Item->getTva());
            $InvoiceItem->setInvoice($this);
            // Prestation rattachee si elle existe
            if (method_exists($Item, 'getPrestation') &&
                !Tools::isEmptyObject($Item->getPrestation())) {
                $Prestation = $Item->getPrestation();
                $InvoiceItem->setPrestation($Prestation);
                $InvoiceItem->setPrestationCost($Item->getPrestationCost());
                $InvoiceItem->setQuantityForPrestationCost(
                    $Item->getQuantityForPrestationCost());
                $InvoiceItem->setCostType($Item->getCostType());
                $InvoiceItem->setPrestationPeriodicity($Item->getPrestationPeriodicity());
                $InvoiceItem->setOccupiedLocationCollection(
                    $Item->getOccupiedLocationCollection());
            }

            $HandingType = $Item->HandingType();
            $Command = $this->getCommand();

            if ($HandingType == 'currency'){
                if($Command instanceof PrestationCommand) {
                    $htTotalreal = handingCurrency($Item->getUnitPriceHT(),
                            1, $Item->getHanding());
                } else {
                    $htTotalreal = handingCurrency($Item->getUnitPriceHT(),
                            $Item->getQuantity(), $Item->getHanding());
                }
                $Handing = $Item->getHanding();
                $InvoiceItem->setHanding($Handing);
                $NewInvoiceItemCollection->setItem($InvoiceItem);
                //$PTHT[] = round(($htTotalreal), 2);
                $PTHT[] = $htTotalreal;
            } else if ($HandingType == 'percent'){
                if($Command instanceof PrestationCommand) {
                    $htTotalreal = handingPercent($Item->getUnitPriceHT(),
                            1, $Item->getHanding());
                } else {
                    $htTotalreal = handingPercent($Item->getUnitPriceHT(),
                            $Item->getQuantity(), $Item->getHanding());
                }
                //$PTHT[] = round(($htTotalreal), 2);
                $PTHT[] = $htTotalreal;
                $InvoiceItem->setHanding($Item->getHanding());
                $NewInvoiceItemCollection->setItem($InvoiceItem);
            } else if ($HandingType == 'frac'){
                $htTotalreal = handingFrac($Item->getUnitPriceHT(),
                        $Item->getQuantity(), $Item->getHanding());
                $InvoiceItem1 = new InvoiceItem();

                $handingFracArray = explode("/", $Item->getHanding());
                $handingFrac1 = $handingFracArray[0];
                $handingFrac2 = $handingFracArray[1];

                $InvoiceItem1->setReference($Item->getReference());
                $InvoiceItem1->setName($Item->getName());
                $InvoiceItem1->setUnitPriceHT($Item->getUnitPriceHT());
                if ($handingFrac2 != 0) {
                    $InvoiceItem1->setQuantity(
                        floor(($handingFrac1 / $handingFrac2) *
                            $InvoiceItem->getQuantity()));
                } else {
                    $InvoiceItem1->setQuantity($InvoiceItem->getQuantity());
                }
                $qty = $InvoiceItem->getQuantity() - $InvoiceItem1->getQuantity();
                $InvoiceItem->setQuantity($qty);
                $InvoiceItem->setHanding('');
                //$PTHT[] = round(($htTotalreal), 2);
                $PTHT[] = $htTotalreal;
                $NewInvoiceItemCollection->setItem($InvoiceItem);
                if ($InvoiceItem1->getQuantity() > 0) {
                    $InvoiceItem1->setHanding('100%');
                    $InvoiceItem1->setTva($Item->getTva());
                    $PTHT[] = 0;
                    $NewInvoiceItemCollection->setItem($InvoiceItem1);
                    unset($InvoiceItem1);
                }
            } else {
                $NewInvoiceItemCollection->setItem($InvoiceItem);
                if($Command instanceof PrestationCommand) {
                    //$PTHT[] = round(($Item->getUnitPriceHT()), 2);
                    $PTHT[] = $Item->getUnitPriceHT();
                } else {
                    $htTotalreal = $Item->getQuantity() * $Item->getUnitPriceHT();
                    //$PTHT[] = round(($htTotalreal), 2);
                    $PTHT[] = $htTotalreal;
                }

            }

            unset($InvoiceItem);
        }
        //on reordonne les donnees pour les inserer ligne par ligne ds le doc pdf
        for ($i=0; $i < $NewInvoiceItemCollection->getCount(); $i++) {
            $Item = $NewInvoiceItemCollection->getItem($i);
            $Prestation = $Item->getPrestation();
            $pdt = $mapper->load(array('BaseReference'=>$Item->getReference()));
            // Selon le type de InvoiceItem, il peut y avoir une Prestation
            // au lieu d'un Product
            $Command = $this->getCommand();
            $sc = $this->getSupplierCustomer();
            // C'est un VOL
            if (get_class($Command) == 'CourseCommand') {
                if ($sc instanceof SupplierCustomer) {
                    $firstColumn = $Prestation->getNameForCustomer($sc->getCustomerId());
                } else {
                    $firstColumn = $Prestation->getName();
                }
                $firstColumn = $Prestation->getNameForCustomer($cust);
                //$Command = $this->getCommand();
                $flightACO = $Command->getActivatedChainOperation(OPERATION_VOL);
                $InstructorName = Tools::getValueFromMacro($flightACO, '%RealActor.Name%');
                $InstructorName = ($InstructorName == '0')?
                    _('None'):$InstructorName;
                $firstColumn .= ' - ' . _('Instructor') . ': ' . $InstructorName;
                $flightTaskCollection = $flightACO->getActivatedChainTaskCollection(
                        array('Task' => TASK_FLY));
                if (!Tools::isEmptyObject($flightTaskCollection)) {
                    $flightTask = $flightTaskCollection->getItem(0);
                    $flightTaskDetail = $flightTask->getActivatedChainTaskDetail();
                    $firstColumn .= ' - ' . _('Duration') . ': '
                            . DateTimeTools::hundredthsOfHourToTime(
                                    $flightTaskDetail->getRealCommercialDuration());
                }
                $firstColumnsArray = array($firstColumn);
            }
            elseif(get_class($Command) == 'PrestationCommand') {
                $occLocCol = $Item->getOccupiedLocationCollection();
                $occLoc = $occLocCol->getItem(0);
                /*$storeNameSiteName = '';
                if($occLoc instanceof OccupiedLocation) {
                    $storeName = Tools::getValueFromMacro($occLoc, '%Location.Store.Name%');
                    $siteName = Tools::getValueFromMacro(
                            $occLoc, '%Location.Store.StorageSite.Name%');
                    $storeNameSiteName = ' : ' . $siteName . '/' . $storeName;
                }*/
                if ($sc instanceof SupplierCustomer) {
                    $firstColumn = $Prestation->getNameForCustomer($sc->getCustomerId());
                } else {
                    $firstColumn = $Prestation->getName();
                }
                //$firstColumn .= $storeNameSiteName;
                //ajouter site / magasin factur�s
                $firstColumnsArray = array($firstColumn);
            } else {
                $firstColumnsArray = array($Item->getReference(),
                                               $Item->getName());
                //modifs liees au sc personalisation des documents
                if ($pdt instanceof Product) {
                    for ($foo=0 ; $foo<$numberOfDomProps ; $foo++) {
                        $domProperty = $domPropertyCol->getItem($foo);
                        $property = $domProperty->getProperty();
                        $firstColumnsArray[1] .= ' ' .
                            Tools::getValueFromMacro($pdt, '%' . $property->getName() . '%');
                    }
                }
            }
            $handing = $Item->getHanding();
            if (!empty($handing) && false === strpos($handing, '%') &&
                false === strpos($handing, '/'))
            {
                $handing .= ' ' . $currency;
            }
            // faut il afficher /Kg ou /Litre etc...
            $qtyaddon = '';
            if ($pdt instanceof Product) {
                $qtyaddon = $pdt->getMeasuringUnit();
            }
            $tva = $Item->getTva();
            $tvaRate = (!Tools::isEmptyObject($tva))?
                I18N::formatNumber($tva->getRealTvaRate($this->getTvaSurtaxRate()), 2, false, true):'';
            if($Command instanceof PrestationCommand) {
                $priceAddon = (CostRange::TYPE_UNIT_FOR_QUANTITY==$Item->getCostType()) ?
                    ' / ' . $Item->getQuantityForPrestationCost() : '';
                $returnData[] = array_merge($firstColumnsArray,
                    array(
                        I18N::formatNumber($Item->getQuantity(), 3, true, true) . $qtyaddon,
                        I18N::formatNumber($Item->getPrestationCost(), 3, false, true) . $priceAddon,
                        $handing,
                        $tvaRate,
                        I18N::formatNumber($Item->getUnitPriceHT(), 2, false, true)
                    )
                );
            } elseif (!($Command instanceof CourseCommand)) {
                $returnData[] = array_merge($firstColumnsArray,
                    array(
                        I18N::formatNumber($Item->getQuantity(), 3, true, true) . $qtyaddon,
                        I18N::formatNumber($Item->getUnitPriceHT(), 2, false, true),
                        $handing,
                        $tvaRate,
                        I18N::formatNumber($PTHT[$i], 2, false, true)
                    )
                );
            } else {
                $returnData[] = array_merge($firstColumnsArray,
                    array(
                        I18N::formatNumber($Item->getQuantity(), 3, true, true) . $qtyaddon,
                        $handing,
                        $tvaRate,
                        I18N::formatNumber($PTHT[$i], 2, false, true)
                    )
                );
            }

        }
        return $returnData;
    }

    // }}}
    // Invoice::dataForRTWInvoice() {{{

    /**
     * Sert a generer les lignes de facture du doc pdf pour l'impression
     * Version pour le pret-a-porter
     *
     * @access public
     */
    function dataForRTWInvoice($currency = '�') {
        $data = $this->dataForInvoice($currency);
        $ret  = array();
        $sizes = array();
        foreach ($data as $item) {
            if (!is_array($item)) {
                continue;
            }
            $rtwProduct = Object::load('RTWProduct', array('BaseReference' => $item[0]));
            if (!($rtwProduct instanceof RTWProduct)) { 
                continue;
            }
            if (!(($size = $rtwProduct->getSize()) instanceof RTWSize)) {
                $size = false;
            }
            $model = $rtwProduct->getModel();
            $ref = $model->getStyleNumber() . "\n" . $model->getPressName()->toString();
            if (!isset($ret[$model->getId()])) {
                $sizes[$model->getId()] = array();
                $sizes[$model->getId()][$size->getName()] = intval($item[2]);
                $ret[$model->getId()] = array(
                    $ref,
                    // taille: qte
                    $rtwProduct->getName(),
                    // qte: sera incrementee
                    intval($item[2]),
                    // PUHT
                    $item[3],
                    // REM: idem
                    $item[4],
                    // PTHT: idem
                    $item[6]
                );
            } else {
                $sizes[$model->getId()][$size->getName()] = intval($item[2]);
                $ret[$model->getId()][2] += intval($item[2]);
                foreach (array(4 => 4, 5 => 6) as $retIndex => $itemIndex) {
                    $ret[$model->getId()][$retIndex] = I18N::formatNumber(
                        I18N::extractNumber($ret[$model->getId()][$retIndex]) 
                      + I18N::extractNumber($item[$itemIndex])
                    );
                }
            }
        }
        foreach ($ret as $i=>&$array) {
            $model = Object::load('RTWModel', $i);
            $legalMentions = $model->getLegalMentions();
            if (!empty($legalMentions)) {
                $array[1] .= "\n\n" . $legalMentions;
            }
        }
        return array(array_values($ret), array_values($sizes));
    }

    // }}}
    // Invoice::getPaymentCollection() {{{

    /**
     * Retourne la collection des Payments associes, via InvoicePayment
     * @param $state integer:
     *      0 (par defaut): tous,
     *      1: seulement ceux qui n'ont pas ete annules!
     *         (CancellationDate est NULL)
     * @access public
     * @return void
     **/
    function getPaymentCollection($state=0){
        // la collection qui sera retournee
        $PaymentCollection = new Collection();
        $PaymentCollection->acceptDuplicate = false;
        $InvoicePaymentCollection = $this->getInvoicePaymentCollection();
        if (!Tools::isEmptyObject($InvoicePaymentCollection)) {
            $count = $InvoicePaymentCollection->getCount();
            for($i = 0; $i < $count; $i++) {
                $InvoicePayment = $InvoicePaymentCollection->getItem($i);
                $Payment = $InvoicePayment->getPayment();
                $CancellationDate = $Payment->getCancellationDate();
                if ($state==0 || ($state==1 && (empty($CancellationDate)
                       || '0000-00-00 00:00:00' == $CancellationDate)))  {
                    $PaymentCollection->setItem($Payment);
                }
                unset($Payment, $InvoicePayment);
            }
        }
        return $PaymentCollection;
    }

    // }}}
    // Invoice::getSNLotArray() {{{

    /**
     * Retourne un tableau PN, SN/LOT, Qt� pour chaque produit (PN) pr�sent
     * dans la facture.
     *
     * @access public
     * @return array
     */
     function getSNLotArray() {
        $ret = array();
        // Pour controler que pas 2 lignes avec le m�me couple (pdt/SN)
        $tempArray = array();
        $col = $this->getInvoiceItemCollection();
        $cnt = $col->getCount();
        $k = 0;
        for ($i=0; $i<$cnt; $i++) {
            $item = $col->getItem($i);
            $lemccpCol = $item->getLemConcreteProductCollection();
            $j_cnt = $lemccpCol->getCount();
            for ($j=0; $j<$j_cnt; $j++) {
                $lemccp = $lemccpCol->getItem($j);
                if (!($lemccp instanceof LEMConcreteProduct)) {
                    continue;
                }
                $ccp = $lemccp->getConcreteProduct();
                if (!($ccp instanceof ConcreteProduct)) {
                    continue;
                }
                $ref = $item->getReference();
                // gestion des changements de reference
                $pdtMapper = Mapper::singleton('Product');
                $pdt = $pdtMapper->load(array('BaseReference'=>$ref));
                if(!($pdt instanceof Product) || $pdt->getTracingMode() == 0) {
                    continue;
                }
                $qty = $lemccp->getQuantity()
                        - $lemccp->getCancelledQuantity($this->getEditionDate());
                if ($qty == 0) {
                    continue;
                }
                // On evite les doublons
                $key = array_search(
                        array($pdt->getBaseReference(), $ccp->getSerialNumber()),
                        $tempArray
                        );
                if ($key === false) {
                    $tempArray[$k] = array($pdt->getBaseReference(), $ccp->getSerialNumber());
                    $ret[$k] = array(
                        $pdt->getBaseReference(), $ccp->getSerialNumber(), $qty);
                    $k++;
                }
                else {
                    $initValue = $ret[$key];
                    $initQty = $initValue[2];
                    $ret[$k] = array(
                        $pdt->getBaseReference(), $ccp->getSerialNumber(), $initQty + $qty);
                }
            }
        }
        return $ret;
    }

    // }}}
    // Invoice::getTVADetail() {{{

    /**
     * Retourne le detail de la TVA par taux
     * exple: array('5,50' => 120, '19,60' => 200)
     * @access public
     * @return array
     **/
    function getTVADetail() {
        require_once('FormatNumber.php');
        $InvoiceItemCollection = $this->getInvoiceItemCollection();
        $tvaRateArray = array();
        if($this->hasTVA()) {
            // calcul la part tva des InvoiceItems
            $globalHanding = $this->getGlobalHanding();
            $count = $InvoiceItemCollection->getCount();
            for($i = 0; $i < $count; $i++) {
                $InvoiceItem = $InvoiceItemCollection->getItem($i);
                $ptht = $InvoiceItem->getTotalPriceHT();
                $tva = $InvoiceItem->getTva();
                $tvaRate = $InvoiceItem->getRealTvaRate();  // Tient compte de l'eventuelle surtaxe
                $tvaLine = $ptht * $tvaRate / 100;
                $tvaRate = troncature($tvaRate);

                // on applique la remise globale
                $globalHandingLine = $tvaLine * $globalHanding / 100;
                if (!in_array($tvaRate, array_keys($tvaRateArray))) {
                    $tvaRateArray[$tvaRate] = troncature($tvaLine - $globalHandingLine);
                }
                else {
                    $tvaRateArray[$tvaRate] += troncature($tvaLine - $globalHandingLine);
                }
            }
        }

        // calcul la part tva des frais
        $tvaCategories = array(TVA::TYPE_DELIVERY_EXPENSES, TVA::TYPE_PACKING, TVA::TYPE_INSURANCE);
        foreach($tvaCategories as $category) {
            $tva = Object::load('TVA', array('Type' => $category));
            if(!($tva instanceof TVA)) {
                continue;
            }
            $tvaRate = $tva->getRealTvaRate($this->getTvaSurtaxRate());
            $tvaRateFormatted = troncature($tvaRate);

            switch($category) {
                case TVA::TYPE_DELIVERY_EXPENSES:
                    $tvaAmount = troncature($this->getPort() * $tvaRate / 100);
                    break;
                case TVA::TYPE_PACKING:
                    $tvaAmount = troncature($this->getPacking() * $tvaRate / 100);
                    break;
                case TVA::TYPE_INSURANCE:
                    $tvaAmount = troncature($this->getInsurance() * $tvaRate / 100);
                    break;
                default:
                    $tvaAmount = 0;
            }

            if ($tvaAmount == 0) {
                continue;
            }
            if (!in_array($tvaRateFormatted, array_keys($tvaRateArray))) {
                $tvaRateArray[$tvaRateFormatted] = $tvaAmount;
            } else {
                $tvaRateArray[$tvaRateFormatted] += $tvaAmount;
            }
        }

        return $tvaRateArray;
    }

    // }}}
    // Invoice::getDataForACOList() {{{

    /**
     * retourne les donn�es pour le tableau listant les aco
     * dans la facture de prestation
     * @return array
     * @access public
     */
    function getDataForACOList()
    {
        require_once('FormatNumber.php');

        $data = array();
        $acoMapper = Mapper::singleton('ActivatedChainOperation');
        $ivItemCol = $this->getInvoiceItemCollection();
        $count = $ivItemCol->getCount();

        for ($i=0 ; $i<$count ; $i++) {
            $ivItem = $ivItemCol->getItem($i);
            $acoCol = $acoMapper->loadCollection(
                    array('InvoiceItem' => $ivItem->getId()));
            $jcount = $acoCol->getCount();
            for ($j=0 ; $j<$jcount ; $j++) {
                $aco = $acoCol->getItem($j);
                if(!($aco instanceof ActivatedChainOperation)) {
                    continue;
                }
                $prestation = $ivItem->getPrestation();
                $firstTask = $aco->getFirstTask();

                $weight = $volume = 0;
                $ach = $aco->getActivatedChain();
                $cmdItemCol = $ach->getCommandItemCollection();
                $kcount = $cmdItemCol->getCount();
                for ($k=0 ; $k<$kcount ; $k++) {
                    $cmdItem = $cmdItemCol->getItem($k);
                    $qt = $cmdItem->getQuantity();
                    $weight += $cmdItem->getWeight() * $qt;
                    $volume += $cmdItem->getWidth() * $cmdItem->getHeight()
                                * $cmdItem->getLength() * $qt;
                    unset($cmdItem);
                } // for kcount
                $cmdItem = $cmdItemCol->getItem(0);
                $command = $cmdItem->getCommand();
                $cmdNo = $command->getCommandNo();

                $departureActor = $aco->getStartActor();
                $arrivalActor = $aco->getEndActor();

                $data[] = array($prestation->getName(),
                                !empty($cmdNo)?$cmdNo:'',
                                !empty($departureActor)?$departureActor->getName():'',
                                !empty($arrivalActor)?$arrivalActor->getName():'',
                                $firstTask->getBegin('localedate_short'),
                                I18N::formatNumber($weight, 3, true, true),
                                I18N::formatNumber($volume, 3, true, true));
                unset($prestation, $aco, $firstTask, $ach, $command, $cmdNo,
                      $departureActor, $arrivalActor);
            } // for jcount
            unset($ivItem);
        } // for i

        return $data;
    }

    // }}}
    // Invoice::getSupplierCustomer() {{{

    /**
     * Retourne le SupplierCustomer, s'il est introuvable on recherche celui de
     * la commande associ�e.
     *
     * @access public
     * @return object SupplierCustomer
     */
    function getSupplierCustomer() {
        $spc = parent::getSupplierCustomer();
        if (!($spc instanceof SupplierCustomer)) {
            // on appelle la m�thode command.getSupplierCustomer()
            $cmd = $this->getCommand();
            $spc = $cmd->getSupplierCustomer();
        }
        return $spc;
    }

    // }}}
    // Invoice::hasTVA() {{{

    /**
     * Retourne true si la facture � �t� faite en tenant compte d'une
     * tva.
     * on ne doit pas de fier au couple suppliercustomer mais au fait
     * que les lignes de factures (invoiceitem) pr�sentent un taux de tva.
     *
     * @access public
     * @return boolean
     */
    function hasTVA()
    {
        $invoiceItemCollection = $this->getInvoiceItemCollection();
        $count = $invoiceItemCollection->getCount();
        for ($i=0 ; $i<$count ; $i++) {
            $invoiceItem = $invoiceItemCollection->getItem($i);
            $tva = $invoiceItem->getTva();
            if(!Tools::isEmptyObject($tva)) {
                return true;
            }
        }
        return false;
    }

    // }}}
    // Invoice::getHandingDetail() {{{

    /**
     * Calcule et retourne le montant de la remise
     * globale et le prxi total ht avant remise globale
     *
     * retourne un tableau avec 'ht' et 'handing' comme cl�
     *
     * @access public
     * @return array
     */
    function getHandingDetail()
    {
        $hbr = $this->getCommand()->getHandingByRangePercent();
        $globalHanding = $this->getGlobalHanding();
        if(empty($globalHanding)) {
            return array(
                'handing'=>0,
                'handingbyrangepercent'=>$hbr,
                'ht'=>$this->getTotalPriceHT()
            );
        }
        $invoiceItemCol = $this->getInvoiceItemCollection();
        $count = $invoiceItemCol->getCount();
        $totalHT = 0;
        for ($i=0 ; $i<$count ; $i++) {
            $invoiceItem = $invoiceItemCol->getItem($i);
            $totalHT += $invoiceItem->getTotalPriceHT();
        }
        $handing = $totalHT/100*$globalHanding;
        $totalHT += $this->getPacking() + $this->getPort() + $this->getInsurance();
        return array(
            'handing'=>troncature($handing),
            'handingbyrangepercent'=>$hbr,
            'ht'=>troncature($totalHT)
        );
    }

    // }}}
    // Invoice::delete() {{{

    /**
     * V�rifie si la facture est supprimable ou non, et gere les impacts
     * a la suppression
     *
     * @access public
     * @return boolean
     */
    function delete()
    {
        if (!Tools::isEmptyObject($this->getInvoicePaymentCollection())) {
             return false;
        }
        // Mise a jour de l'encours courant
        $spc = $this->getSupplierCustomer();
        if (!Tools::isEmptyObject($spc)) {
            $spc->setUpdateIncur($spc->getUpdateIncur() - $this->getToPay());
            $spc->save();
        }
        // Mise a jour de la Command
        $cmd = $this->getCommand();
        $state = $cmd->getState();
        switch($state) {
            case Command::FACT_PARTIELLE:
            case Command::FACT_COMPLETE:
                // On regarde s'il existe d'autres factures
                $invoiceColl = $cmd->getAbstractDocumentCollection(
                        array('ClassName' => 'Invoice'));
                if ($invoiceColl->getCount() > 1) {
                    $cmd->setState(Command::FACT_PARTIELLE);
                }else {
                    $cmd->setState(Command::LIV_COMPLETE);
                }
                $cmd->save();
                break;
            case Command::REGLEMT_PARTIEL:
                $cmd->setState(Command::FACT_PARTIELLE);
                $cmd->save();
                break;
            default: // rien dans ce cas
                break;
        } // switch

        $lemColl = $this->getLocationExecutedMovementCollection();
        $count  = $lemColl->getCount();
        for($i = 0; $i < $count; $i++){
            $lem = $lemColl->getItem($i);
            $lem->setInvoicePrestation(0);
            $lem->setTransportPrestationFactured(0);
            $lem->save();
        }
        $acoColl = $this->getActivatedChainOperationCollection();
        $count  = $acoColl->getCount();
        for($i = 0; $i < $count; $i++){
            $aco = $acoColl->getItem($i);
            $aco->setInvoicePrestation(0);
            $aco->setPrestationFactured(0);
            $aco->setPrestationCommandDate(NULL);
            $aco->save();
        }
        // Mise a jour des ActivatedMovement: dans InvoiceItem::delete()
        // Mise a jour des LocationExecutedMovement: idem

        parent::delete();
    }

    // }}}
    // Invoice::isWithStock() {{{

    /**
     * V�rifie si la facture est liee a une prestation de stockage notamment
     *
     * @access public
     * @return boolean
     */
    public function isWithStock() {
        require_once('Objects/Prestation.php');
        $invoiItemColl = $this->getInvoiceItemCollection(
                array('Prestation.Type' => Prestation::PRESTATION_TYPE_STOCKAGE),
                array(), array('Prestation'));
        return (!Tools::isEmptyObject($invoiItemColl));
    }

    // }}}
    // Invoice::getStockageLocationList() {{{

    /**
     * Retourne la liste des Location si prestation de stockage facturee
     *
     * @access public
     * @return mixed array of strings
     */
    public function getStockageLocationList() {
        $return = array();
        if (!$this->isWithStock()) {
            return $return;
        }
        // L'InvoiceItem pour le Stockage (pas plus d'1 seule, a priori...)
        $invoiceItemMapper = Mapper::singleton('InvoiceItem');
        $invoiceItem = $invoiceItemMapper->load(
                array('Invoice' => $this->getId(),
                      'Prestation.Type' => Prestation::PRESTATION_TYPE_STOCKAGE));

        $olColl = $invoiceItem->getOccupiedLocationCollection(
                array(),
                array('Location.Store' => SORT_ASC, 'Location' => SORT_ASC),
                array('Location'));  // lazy

        if (Tools::isEmptyObject($olColl)) {
            return $return;
        }
        $count = $olColl->getCount();
        for($i = 0; $i < $count; $i++){
            $itemLocation = $olColl->getItem($i)->getLocation();
            $itemArray = array(
                    $itemLocation->getStore()->getName(),
                    $itemLocation->getName()
                );
            if (!in_array($itemArray, $return)) {
                $return[] = $itemArray;
            }
        }
        return $return;
    }

    // }}}
    // Invoice::getToPayForDocument() {{{

    /**
     * Retourne le montant � r�gler � afficher sur le doc pdf.
     * La r�gle c'est:
     *   Invoice.ToPay + somme des Invoice.Payment
     *
     * @access public
     * @return float le montant � r�gler
     */
    public function getToPayForDocument() {
        $toAdd = 0;

        // Liste des reglements non annules
        $paymentCol = $this->getPaymentCollection(1);
        $count = $paymentCol->getCount();
        for ($i=0; $i<$count; $i++) {
            $payment = $paymentCol->getItem($i);
            $toAdd += $payment->getTotalPriceTTC();
        }

        return $this->getToPay() + $toAdd;
    }
    // }}}
    // Invoice::isFirstInvoiceForCommand() {{{

    /**
     * Retourne true ou false selon si c'est la premiere facture editee pour
     * la commande associee
     *
     * @access public
     * @return boolean
     */
    public function isFirstInvoiceForCommand() {
        $invoiceColl  = Object::loadCollection(
                'Invoice',
                array('Command' => $this->getCommandId()),
                array('EditionDate' => SORT_ASC),
                array('EditionDate')
        );
        return ($invoiceColl->getItem(0)->getId() == $this->getId());
    }
    // }}}
    // Invoice::updateCommercialCommission() {{{

    /**
     * Stocke le pourcentage et le montant de la commission du commercial.
     *
     * @access public
     * @return void
     */
    public function updateCommercialCommission() {
        $cmd = $this->getCommand();
        if (!($cmd instanceof Command)) {
            return;
        }
        $com = $cmd->getCommercial();
        if ($com instanceof UserAccount && ($percent = $com->getCommissionPercent()) > 0) {
            $amount = floatval($this->getTotalPriceHT() * ($percent / 100));
            $this->setCommercialCommissionPercent($percent);
            $this->setCommercialCommissionAmount($amount);
        }
    }

    // }}}

}

?>
