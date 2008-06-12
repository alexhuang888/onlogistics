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

class ActorProduct extends _ActorProduct {
    // Constructeur {{{

    /**
     * ActorProduct::__construct()
     * Constructeur
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    // }}}
    // ActorProduct::getPriceByActor() {{{

    /**
     * Retourne le prix de l'UV dans la devise de l'acteur pass� en param�tre
     *
     * @access public
     * @param  $actor le client
     * @return float le prix dans la devise d�finie pour le client
     */
    public function getPriceByActor($actor=false)
    {
        $actor = $this->getActor();
        $currencyID = $actor->getCurrencyId();
        $zoneID     = $actor->getPricingZoneId();
        // on essaie d'abord de r�cup�rer le prix associ� � la devise *et* � la 
        // zone param�tr�e de l'acteur, s'il y en a une
        if ($zoneID > 0 && $currencyID > 0) {
            $pbc = Object::load('PriceByCurrency', array(
                'ActorProduct' => $this->getId(),
                'Currency'     => $currencyID,
                'PricingZone'  => $zoneID
            ));
            if ($pbc instanceof PriceByCurrency) {
                return $pbc->getPrice();
            }
        }
        // sinon, on essaie de r�cup�rer le prix associ� � la devise
        if ($currencyID > 0) {
            $pbc = Object::load('PriceByCurrency', array(
                'ActorProduct' => $this->getId(),
                'Currency'     => $currencyID
            ));
            if ($pbc instanceof PriceByCurrency) {
                return $pbc->getPrice();
            }
        }
        return 0;
    }

    // }}}
    // ActorProduct::getUBPrice() {{{

    /**
     * Product::GetUBPrice()
     * Retourne le prix de l'unit� de base du couple acteur/produit
     *
     * @param Object $actor: si renseign� le prix est dans la devise de celui-ci
     * @return float
     **/
    public function getUBPrice() {
        // on ne connait pas l'acteur donc on prend l'acteur du couple
        $product = $this->getProduct();
        $numberUBInUV = $product->GetNumberUBInUV();
        if ($numberUBInUV != 0) {
            $price = $this->GetPriceByActor();
            return round($price/$numberUBInUV, 2);
        }
        return 0;
    }

    // }}}
    // ActorProduct::getPriceByCurrencyForInventory() {{{

    /**
     * retourne le pricebycurrency correspondant � la devise du propri�taire
     * du stock, ou si non trouv� le 1er pricebycurrency d�fini dans une devise
     * ou bien false en dernier recours.
     *
     * @access public
     * @param object $stockOwner le propri�taire du stock de l'inventaire
     * @return mixed un objet PriceByCurrency ou false sinon
     **/
    public function getPriceByCurrencyForInventory($stockOwner){
        if (!($stockOwner instanceof Actor)) {
            return false;
        }
        $currencyID = $stockOwner->getCurrencyId();
        $zoneID     = $stockOwner->getPricingZoneId();
        // on essaie d'abord de r�cup�rer le prix associ� � la devise *et* � la 
        // zone param�tr�e de l'acteur, s'il y en a une
        if ($zoneID > 0 && $currencyID > 0) {
            $pbc = Object::load('PriceByCurrency', array(
                'ActorProduct' => $this->getId(),
                'Currency'     => $currencyID,
                'PricingZone'  => $zoneID
            ));
            if ($pbc instanceof PriceByCurrency) {
                return $pbc;
            }
        }
        // sinon, on essaie de r�cup�rer le prix associ� � la devise
        if ($currencyID > 0) {
            $pbc = Object::load('PriceByCurrency', array(
                'ActorProduct' => $this->getId(),
                'Currency'     => $currencyID
            ));
            if ($pbc instanceof PriceByCurrency) {
                return $pbc;
            }
        }
        return false;
    }

    // }}}
    // ActorProduct::getCSVDataSQL() {{{

    /**
     * Retourne une requ�te sql pour la methode OnlogisticsXmlRpcServer::getCSVData()
     *
     * @access public
     * @return string
     */
    public function getCSVDataSQL() {
        $ret  = 'SELECT apd._Id, CONCAT(pdt._BaseReference, "-", act._Name) ';
        $ret .= 'FROM ActorProduct apd, Actor act, Product pdt ';
        $ret .= 'WHERE apd._Actor=act._Id AND apd._Product=pdt._Id';
        return $ret;
    }

    // }}}
    // ActorProduct::canBeDeleted() {{{

    /**
     * ActorProduct::canBeDeleted()
     * Retourne true si l'objet peut �tre d�truit en base de donnees.
     * Concerne les references client:
     * Il ne faut pas qu'une commande client ait deja
     * ete passee pour ActorProduct.Product
     *
     * @access public
     * @return boolean
     */
    public function canBeDeleted() {
        $test = parent::canBeDeleted();
        $actor = $this->getActor();
        if (parent::canBeDeleted()
        && !(($actor instanceof Customer) || ($actor instanceof AeroCustomer))) {
            return true;
        }
        // C'est bien une occurrence pour stocker une ref client
        // Au vu du path, pas possible d'utiliser $mapper->alreadyExists()
        $mapper = Mapper::singleton('ProductCommandItem');
        $testColl = $mapper->loadCollection(
                array(
                    'Command.Destinator' => $this->getActorId(),
                    'Product '=> $this->getProductId()));

        if ($testColl->getCount() > 0) {
            throw new Exception('A customer command already exists with this customer and this product.');
        }
        return true;
    }

    // }}}
    // ActorProduct::getToStringAttribute() {{{

    /**
     * Retourne le nom des attributs utilisés par la méthode toString()

     * @access public
     * @return array
     */
    function getToStringAttribute() {
        return array('Actor', 'AssociatedProductReference');
    }

    // }}}
    // ActorProduct::toString() {{{

    /**
     * Retourne la representation textuelle de l'ActorProduct

     * @access public
     * @return string
     */
    function toString() {
        $ret  = $this->getAssociatedProductReference();
        if (($actor = $this->getActor()) instanceof Actor) {
            $ret .= ' / ' . $actor->getName();
        }
        return $ret;
    }

    // }}}
}

?>
