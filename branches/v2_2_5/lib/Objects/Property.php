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

class Property extends _Property {
    // Constructeur {{{

    /**
     * Property::__construct()
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
     **/
    function getTypeToString(){
        $type = $this->GetType();
        $typeArray = $this->getTypeConstArray();
        
        if (isset($type) && array_key_exists($type, $typeArray)) {
            return $typeArray[$type];
        }
        return '';
    }
    
    /**
     * Certaines propri�t�s sont juste d�finies en tant que telles mais ne sont 
     * pas des propri�t�s dynamiques, cad que leur valeur est bien stock�e dans 
     * Product. Cette m�thode permet de s'assurer que la propri�t� est bien une 
     * propri�t� dynamique.
     * XXX: si on ajoute des fils � product et qu'on veut m�langer les types 
     * de produits dans un catalogue, il faudra modifier cette m�thode, pour 
     * tester aussi sur la classe qu'on aura rajout�e.
     * 
     * @access public
     * @return boolean 
     **/
    function isDynamic() {
        $context = Preferences::get('TradeContext', array());
        if (in_array('readytowear', $context)) {
            return !method_exists(new RTWProduct(), 'get' . $this->getName());
        }
        return !method_exists(new AeroProduct(), 'get' . $this->getName());
    }
    
    /**
     * R�cup�re une valeur de PropertyValue en tenant compte du type de donn�es
     *
     * @access public
     * @param  int $productID l'id du produit
     * @return mixed 
     */
    function getValue($productID){
        require_once('Objects/Product.php');
        $ptyValue = Object::load(
            'PropertyValue', 
            array(
                'Property' => $this->getId(),
                'Product'  => $productID
            )
        );
        if ($ptyValue instanceof PropertyValue) {
            switch($this->getType()){
                case Property::STRING_TYPE: 
                    return $ptyValue->getStringValue();
                case Property::INT_TYPE:
                case Property::BOOL_TYPE: 
                    return $ptyValue->getIntValue();
                case Property::FLOAT_TYPE: 
                    return $ptyValue->getFloatValue();
                case Property::DATE_TYPE: 
                    return $ptyValue->getDateValue();
                case Property::OBJECT_TYPE:
                    $props = Product::getPropertiesByContext();
                    $name = isset($props[$this->getName()])?
                        $props[$this->getName()]:$this->getName();
                    $obj = Object::load($name, $ptyValue->getIntValue());
                    if ($obj instanceof $name && $obj->getId() > 0) {
                        return $obj;
                    }
                    return false;
                default:
                    return false;
            }
        }
        return false;
    }
    
    /**
     * Assigne une valeur � une PropertyValue en tenant compte du type de 
     * donn�es.
     *
     * @access public
     * @param  int $productID l'id du produit
     * @param  mixed $value la valeur de la propri�t�
     * @return boolean
     */
    function setValue($productID, $value) {
        $ptyValue = Object::load(
            'PropertyValue', 
            array(
                'Property' => $this->getId(),
                'Product'  => $productID
            )
        );
        if (!($ptyValue instanceof PropertyValue)) {
            $ptyValue = new PropertyValue();
            $ptyValue->setProduct($productID);
            $ptyValue->setProperty($this->getId());
        }
        switch($this->getType()){
            case Property::STRING_TYPE:
                $ptyValue->setStringValue($value);
                break;
            case Property::INT_TYPE: 
            case Property::BOOL_TYPE: 
            case Property::OBJECT_TYPE:
                $ptyValue->setIntValue($value);
                break;
            case Property::FLOAT_TYPE: 
                $ptyValue->setFloatValue($value);
                break;
            case Property::DATE_TYPE: 
                $ptyValue->setDateValue($value);
                break;
            default:
                return false;
        } // switch
        $ptyValue->save();
        return true;
    }

}

?>
