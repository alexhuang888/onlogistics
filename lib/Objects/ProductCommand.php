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

class ProductCommand extends _ProductCommand {
    // Constructeur {{{

    /**
     * ProductCommand::__construct()
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
     *
     * @access public
     * @return void
     */
    function GetTotalVolume()
    {
        $cmdItems = $this->GetCommandItemCollection();

        $result = 0;
        if (false != $cmdItems) {
            $count = $cmdItems->getCount();
            for ($i=0; $i<$count; $i++) {
                unset($cmdItem);
                $cmdItem = $cmdItems->GetItem($i);
                $result += $cmdItem->getVolume();
            }
        }
        return $result;
    }

    /**
     *
     * @access public
     * @return void
     */
    function GetTotalWeight()
    {
        $cmdItems = $this->GetCommandItemCollection();

        $result = 0;
        if (false != $cmdItems) {
            $count = $cmdItems->getCount();
            for ($i=0; $i<$count; $i++) {
                unset($cmdItem);
                $cmdItem = $cmdItems->GetItem($i);
                unset($product);
                $product = $cmdItem->GetProduct();
                $result += $cmdItem->getQuantity() * $product->getSellUnitWeight();
            }
        }
        return $result;
    }

    /**
     *
     * @access public
     * @return void
     */
    function GetTotalQuantity()
    {
        $cmdItems = $this->GetCommandItemCollection();
        $result = 0;
        if (false != $cmdItems) {
            $count = $cmdItems->getCount();
            for ($i=0; $i<$count; $i++) {
                unset($cmdItem);
                $cmdItem = $cmdItems->GetItem($i);
                // getBaseUnitCount
                $result += $cmdItem->getQuantity() ;
            }
        }
        return $result;
    }
    /**
     *
     * @access public
     * @return void
     */
    function GetTotalLM()
    {
        require_once("MLcompute.php");
        $cmdItems = $this->GetCommandItemCollection();
        $result = 0;
        if (false != $cmdItems) {
            $count = $cmdItems->getCount();
            for ($i=0; $i<$count; $i++) {
                unset($cmdItem);
                $cmdItem = $cmdItems->GetItem($i);
                unset($product);
                $product = $cmdItem->GetProduct();

                $result += MLcompute($product->GetSellUnitLength(),
                    $product->GetSellUnitWidth(),
                    $product->GetSellUnitHeight(),
                    $product->GetSellUnitMasterDimension(),
                    $product->GetSellUnitGerbability(),
                    $cmdItem->GetQuantity());
            }
        }
        return $result;
    }
    /**
     *
     * @access public
     * @return void
     */
    function GetActivatedChain()
    {
        $pciCollection = $this->GetCommandItemCollection();
        $pci = $pciCollection->GetItem(0);
        return $pci->GetActivatedChain();
    }

    /*
    * Retourne 1 si la facture est ou a ete facturee totalement
    **/
    function isFactured() {
        require_once('Objects/ActivatedMovement.php');
        $ProductCommandItemCollection = $this->getCommandItemCollection();
        $count = $ProductCommandItemCollection->getCount();
        for($i=0; $i<$count; $i++){
            $item = $ProductCommandItemCollection->getItem($i);
            $AMovement = $item->getActivatedmovement();
            //si un ActivatedMovement n'est pas encore completement facture
            if ($AMovement->getHasBeenFactured() != ActivatedMovement::ACM_FACTURE) {
                return 0;
                unset($item, $AMovement);
            }

        } // for
        return 1;
    }

    /**
     * Retourne la collection de produits de la commande
     *
     * @access public
     * @return object Collection
     **/
    function getProductCollection(){

        $cmiCol = $this->getCommandItemCollection();
        $count = $cmiCol->getCount();
        $retCol = new Collection();
        for($i = 0; $i < $count; $i++){
            $cmi = $cmiCol->getItem($i);
            $pdt = $cmi->getProduct();
            if (!($pdt instanceof Product)) {
                continue;
            }
            $retCol->setItem($pdt);
        }
        return $retCol;
    }

    /**
     * ProductCommand::getSupplierCommandItemArray()
     *
     * Retourne un tableau de la forme:
     *   SupplierID1=>Collection(CommandItem1,CommandItem2),
     *   SupplierID2=>Collection(CommandItem3,CommandItem4),
     *   ...
     * pour une commande de produits donn�e.
     * Si une collection de composants de la nomenclature est pass�e en
     * param�tre, on ne va pas se baser sur les commanditems de la commande mais
     * plut�t cr�er des commanditems avec les produits de ces composants.
     *
     * @access public
     * @param object Collection une collection de composants (optionnel).
     * @param boolean $qtyRatio si true on utilise la qt� d�finie dans le
     *        composant, sinon on utilise un ratio de 1 (optionnel).
     * @return array
     **/
    function getSupplierCommandItemArray($cpnCol = false, $ratio = true){
        if ($cpnCol instanceof Collection && $cpnCol->getCount() > 0) {
            // on cherche la nomenclature
            $cpn = $cpnCol->getItem(0);
            $nom = $cpn->getNomenclature();
            // on recherche l'item de cette commande correspondant � la
            // nomenclature
            $thisCmiCol = $this->getCommandItemCollection();
            $count = $thisCmiCol->getCount();
            for ($i=0; $i<$count; $i++) {
                $cmi = $thisCmiCol->getItem($i);
                if ($cmi->getProductId() == $nom->getProductId()) {
                    break;
                }
            }
            // on cr�e la collection de commanditems
            $cmiCol = $this->createCommandItemsFromComponents($cpnCol,
                $ratio, $cmi->getQuantity());
        } else {
            // pas de composants, on se base sur les items de la commande
            $cmiCol = $this->getCommandItemCollection();
        }
        $count = $cmiCol->getCount();
        $ret = array();
        for($i = 0; $i < $count; $i++){
            $cmi = $cmiCol->getItem($i);
            $pdt = $cmi->getProduct();
            if (!($pdt instanceof Product)) {
                // on devrait pas �tre l�...
                continue;
            }
            $sup = $pdt->getMainSupplier();
            if (!$sup) {
                // on ne devrait pas �tre dans ce cas, c'est une erreur de
                // param�trage en bases de donn�es
                continue;
            }
            $supID = $sup->getId();
            if (!isset($ret[$supID])) {
                $ret[$supID] = new Collection();
            }
            $ret[$supID]->setItem($cmi);
        }
        return $ret;
    }

    /**
     * Cr�e une collection de commandItems � partir d'une collection de
     * composants et d'un param�tre $qtyRatio qui d�termine si on doit utiliser
     * ou non la quantit� d�finie dans le composant.
     *
     * @access public
     * @param object Collection $cpnCol la collection de composants
     * @param boolean $qtyRatio si true on utilise la qt� d�finie dans le
     *        composant, sinon on utilise un ratio de 1.
     * @param integer $refQty la quantit� command�e initialement
     * @return object Collection la collection de commanditems
     **/
    function createCommandItemsFromComponents($cpnCol, $qtyRatio = true,
        $refQty=1){
        require_once('Objects/ProductCommandItem.php');
        $cmiCol = new Collection();
        $count = $cpnCol->getCount();
        for($i = 0; $i < $count; $i++){
            $cpn = $cpnCol->getItem($i);
            $pdt = $cpn->getProduct();
            // NE PAS ENLEVER L'ESPERLUETTE !
            $cmi = new ProductCommandItem();
            $cmi->setProduct($pdt);
            $cmi->setCommand($this);
            $cmi->setActivatedChain($this->getActivatedChain());
            if ($qtyRatio) {
                $qty = $cpn->getQuantityInHead(true) * $refQty;
            } else {
                $qty = $refQty;
            }
            $cmi->setQuantity($qty);
            $spc = $this->getSupplierCustomer();
            if ($spc instanceof SupplierCustomer && $spc->getHasTVA() == true) {
                $cmi->setTVA($pdt->getTVA());
            }
            $cmiCol->setItem($cmi);
        }
        return $cmiCol;
    }

     /**
     * Methode AddOn pour creer un activatedMovement et mettre a jour
     * la qte virtuelle en stock de product associe et cela seulement
     * dans le cas d'un mouvement interne
     * retourne un tableau de strings vide si pas d'alerte qd on met a jour la qte virtuelle
     * ou contenant le body du ou des mails d'alerte a envoyer
     *
     * @access public
     * @param object ActivatedChainTask
     * @param object ActivatedChain
     * @return array
     */
    function generateActivatedMovement($activatedChainTask, $ActivatedChain) {
        require_once('Objects/Task.const.php');
        require_once('Objects/Command.const.php');
        require_once('Objects/Product.php');
        require_once('Objects/Component.php');
        require_once('MovementType.const.php');

        require_once('Objects/ActivatedMovement.php');

        // Recuperation du ou des Product
        $AlertArray = array();  // donnees des mails d'alerte de stock eventuels
        $cCollection = $activatedChainTask->getComponentCollection();
        if($cCollection->getCount() != 0) {
            for($i=0; $i < $cCollection->getCount(); $i++) {
                $component = $cCollection->getItem($i);
                if($component instanceof Component) {
                    $currentAlert =
                    $this->_createActivatedMovement($activatedChainTask, $component->getProductId(),false,$component->getId());
                    if (is_array($currentAlert) && !empty($currentAlert)) {
                        $AlertArray[] = $currentAlert;
                    }
                }
            }
        } else {
            //On recupere les Product via les CommandItem
            $pcpCollection = $this->getProductCollection();

            //Creation du filtre
            $tabComponentProductId = array();
            for($i=0; $i < $pcpCollection->getCount(); $i++) {
                $product                 = $pcpCollection->getItem($i);
                $tabComponentProductId[] = $product->getId();
            }

            //Recuperation du/des product
            $acpCollection = $ActivatedChain->getProductCollection(array('Id' => $tabComponentProductId));
            for($i=0; $i < $acpCollection->getCount(); $i++) {
                $product = $acpCollection->getItem($i);
                $currentAlert = $this->_createActivatedMovement($activatedChainTask, $product->getId());
                if (is_array($currentAlert) && !empty($currentAlert)) {
                    $AlertArray[] = $currentAlert;
                }
            }
        }
        return $AlertArray;
    }

    function _createActivatedMovement($activatedChainTask, $productId,
    $percentWasted = false, $componentId = 0) {
        require_once('Objects/Task.const.php');
        require_once('Objects/Command.const.php');
        require_once('Objects/Product.php');
        require_once('Objects/Component.php');
        require_once('MovementType.const.php');

        require_once('Objects/ActivatedMovement.php');

        $ActivatedMovement = new ActivatedMovement();

        /* Mise en commentaire provisoire, tant que pas cable avec la plannification
        $ActivatedMovement->SetEndDate(Tools::getValueFromMacro($this, "%Command.WishedEndDate%")); */
        $ActivatedMovement->setState(0);

        $MvtTypeMapper = Mapper::singleton('MovementType');
        // Entree interne
        if ($activatedChainTask->getTaskId() == TASK_INTERNAL_STOCK_ENTRY) {
            $MovementType = $MvtTypeMapper->load(array('Id'=>ENTREE_INTERNE));
        }
        // Sortie interne
        elseif ($activatedChainTask->getTaskId() == TASK_INTERNAL_STOCK_EXIT) {
            $MovementType = $MvtTypeMapper->load(array('Id'=>SORTIE_INTERNE));
        }
        else return false;
        
        $MovementTypeID = $MovementType->getId();
        $ActivatedMovement->setStartDate($activatedChainTask->getBegin());
        $ActivatedMovement->setEndDate($activatedChainTask->getEnd());
        $ActivatedMovement->setActivatedChainTask($activatedChainTask);
        $ActivatedMovement->setType($MovementType);
        $ActivatedMovement->setProductCommand($this);
        $ActivatedMovement->setProduct($productId);

        $quantity = 0;
        $mustCreateWastedMovement = false;
        // Si mvt interne, on renseigne la Quantity
        if($MovementTypeID == ENTREE_INTERNE || $MovementTypeID == SORTIE_INTERNE) {
            if (!($activatedChainTask instanceof ActivatedChainTask)) {
                return;
            }
            $cCollection = $activatedChainTask->getComponentCollection(
                    array('Product.Id' => $productId));
            /*
            echo "\n<pre> collection : \n";
            print_r($cCollection);
            echo "</pre>" ;
            */
            //La liste retourne un productcommanditem unique
            if ($componentId) {
               $component = $cCollection->getItemById($componentId);
            } else {
               $component = $cCollection->getItem(0);
            }

            $useNomenclature = $activatedChainTask->getComponentQuantityRatio();
            if ($useNomenclature && $component instanceof Component) {
                if ($MovementTypeID == ENTREE_INTERNE) {
                    $qty = 1;
                    $method = 'getPreviousTaskFromRule';
                    $cId = $component->getId();
                } else {
                    if ($percentWasted) {
                        $qty = $component->getQuantity(true) - $component->getQuantity();
                    } else {
                        $qty = $component->getQuantity();
                    }
                    $method = 'getNextTaskFromRule';
                    $cId = $component->getParentId();
                    if (!$percentWasted && $component->getPercentWasted() > 0) {
                        $mustCreateWastedMovement = true;
                    }
                }
                // pour quantifier le mouvement on utilise la quantit� assembl�e 
                // de la t�che d'assemblage suivante ou pr�c�dente qui est li�e 
                // au composant � mouvementer pour une entr� ou au composant 
                // parent du composant � mouvementer pour une sortie.
                $found = false;
                $assemblyAck = $activatedChainTask->$method('isAssemblyTask');
                while(!$found) {
                    if ($assemblyAck) {
                        $c = $assemblyAck->getComponent();
                        if(($c instanceof Component) && $c->getId()==$cId) {
                            $quantity = $qty * $assemblyAck->getAssembledQuantity();
                            $found = true;
                        } else {
                            $assemblyAck = $assemblyAck->$method('isAssemblyTask');
                        }
                    } else {
                        $found = true;
                        $quantity = $qty;
                    }
                }
            } else {
                // Recuperation de la quantite via le commanditem
                $pciCollection = $this->getCommandItemCollection(
                    array('Product.Id' => $productId)
                );
                // La liste retourne un commanditem unique
                $pci = $pciCollection->getItem(0);
                if ($pci instanceof CommandItem) {
                    $quantity = $pci->getQuantity();
                }
            }
            $ActivatedMovement->setQuantity($quantity);
            /*
            echo "\n<pre>";
            print_r($component);
            echo $quantity; 
            echo "</pre>" ;
           */ 
        }
        $ActivatedMovement->save();
        if ($mustCreateWastedMovement) {
            $this->_createActivatedMovement($activatedChainTask, $productId, true, $componentId);
        }
        // Mise a jour de la qte virtuelle
        $AlertMailData = $ActivatedMovement->setProductVirtualQuantity();
        return $AlertMailData;
    }

     /**
     * Methode AddOn pour mettre en session les donnees necessaires au bon affichage 
     * des bons de cmde web, lorsqu'on passe un devis en cmde notamment.
     *
     * @access public
     * @return void
     */
    function putDataInSessionForWebOrderForm() {
        require_once('Objects/Command.const.php');
        // les Id de produit a commander, et leur qte, remises...
        $pdtIds = $pdtQties = $hdg = $ueQties = $cmdItemDate = array();
        // Recuperation du ou des Product, classes par ordre de BaseReference
        $coll= $this->getCommandItemCollection(array(), array('Product.BaseReference' => SORT_ASC));
        $count = $coll->getCount();
        for($i=0; $i < $coll->getCount(); $i++) {
            $cmdItem = $coll->getItem($i);
            $pdtIds[] = $cmdItem->getProductId();
            $pdtQties[] = I18N::formatNumber($cmdItem->getQuantity());
            $hdg[] = $cmdItem->getHanding();
            $ueQties[] = I18N::formatNumber($cmdItem->getPackagingUnitQuantity());
            $cmdItemDate[] = $cmdItem->getWishedDate();
        }
        
        
        $session = Session::singleton();
        $session->register('fromEstimateId', $this->getId(), 3);
        $session->register('pdt', $pdtIds, 3);
        $session->register('qty', $pdtQties, 3);
        $session->register('hdg', $hdg, 3);
        $session->register('ueQty', $ueQties, 3);
        $session->register('CommandItemDate', $cmdItemDate, 3);
        $session->register('cmdExpeditor', $this->getExpeditorId(), 3);
        $session->register('cmdExpeditorSite', $this->getExpeditorSiteId(), 3);
        $session->register('cmdDestinator', $this->getDestinatorId(), 3);
        $session->register('cmdDestinatorSite', $this->getDestinatorSiteId(), 3);
        $session->register('supplier', $this->getExpeditorId(), 3);
        $session->register('customer', $this->getDestinatorId(), 3);
        
        $session->register('Port', I18N::formatNumber($this->getPort()), 3);
        $session->register('Emballage', I18N::formatNumber($this->getPacking()), 3);
        $session->register('Assurance', I18N::formatNumber($this->getInsurance()), 3);        
        $session->register('GlobalHanding', I18N::formatNumber($this->getHanding()), 3);        
        $session->register('cmdIncoterm', $this->getIncotermId(), 3);        
        return true;
    }
    
     /**
     * Methode AddOn pour maj les QV lors du passage d'un devis en commande:
     * Si la pref ad hoc est a true, les acm sont crees, et les QV modifiees 
     * lors de la creation d'un devis. Il faut supprimer les acm et maj les QV.
     * A appeler dans une transaction
     *
     * @param boolean $deleteACM true si on supprime les acm a ce stade
     * @access public
     * @return boolean true
     */
    function updateQvBeforeDeleteACM($deleteACM=false) {
        require_once('MovementType.const.php');
        $acmMapper = Mapper::singleton('ActivatedMovement');
        $acmCol = $acmMapper->loadCollection(array('ProductCommand' => $this->getId()));
        $count = $acmCol->getCount();
        for ($i=0; $i<$count; $i++) {
            $acm = $acmCol->getItem($i);
            $pdt = $acm->getProduct();
            if ($pdt instanceof Product) {
                // on recupere la qte virtuelle du produit
                $initVirtualQuantity = $pdt->getSellUnitVirtualQuantity();
                $entrieExit = Tools::getValueFromMacro($acm, '%Type.EntrieExit%');
                if ($entrieExit == MovementType::TYPE_ENTRY) {
                    $qty = $initVirtualQuantity - $acm->getQuantity();
                } elseif ($entrieExit == MovementType::TYPE_EXIT) {
                    $qty = $initVirtualQuantity + $acm->getQuantity();
                }
                $pdt->setSellUnitVirtualQuantity($qty);
                saveInstance($pdt);
            }
        }
        //$acmMapper->delete($acmCol->getItemIds());
        
        return true;
    }

}

?>
