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

class _ChainCommand extends Command {
    
    // Constructeur {{{

    /**
     * _ChainCommand::__construct()
     * Constructeur
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    // }}}
    // InputationNo string property + getter/setter {{{

    /**
     * InputationNo string property
     *
     * @access private
     * @var string
     */
    private $_InputationNo = '';

    /**
     * _ChainCommand::getInputationNo
     *
     * @access public
     * @return string
     */
    public function getInputationNo() {
        return $this->_InputationNo;
    }

    /**
     * _ChainCommand::setInputationNo
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function setInputationNo($value) {
        $this->_InputationNo = $value;
    }

    // }}}
    // DeliveryPayment float property + getter/setter {{{

    /**
     * DeliveryPayment float property
     *
     * @access private
     * @var float
     */
    private $_DeliveryPayment = null;

    /**
     * _ChainCommand::getDeliveryPayment
     *
     * @access public
     * @return float
     */
    public function getDeliveryPayment() {
        return $this->_DeliveryPayment;
    }

    /**
     * _ChainCommand::setDeliveryPayment
     *
     * @access public
     * @param float $value
     * @return void
     */
    public function setDeliveryPayment($value) {
        $this->_DeliveryPayment = ($value===null || $value === '')?
            null:round(I18N::extractNumber($value), 2);
    }

    // }}}
    // DateType string property + getter/setter {{{

    /**
     * DateType int property
     *
     * @access private
     * @var integer
     */
    private $_DateType = 0;

    /**
     * _ChainCommand::getDateType
     *
     * @access public
     * @return integer
     */
    public function getDateType() {
        return $this->_DateType;
    }

    /**
     * _ChainCommand::setDateType
     *
     * @access public
     * @param integer $value
     * @return void
     */
    public function setDateType($value) {
        if ($value !== null) {
            $this->_DateType = (int)$value;
        }
    }

    // }}}
    // Chain foreignkey property + getter/setter {{{

    /**
     * Chain foreignkey
     *
     * @access private
     * @var mixed object Chain or integer
     */
    private $_Chain = false;

    /**
     * _ChainCommand::getChain
     *
     * @access public
     * @return object Chain
     */
    public function getChain() {
        if (is_int($this->_Chain) && $this->_Chain > 0) {
            $mapper = Mapper::singleton('Chain');
            $this->_Chain = $mapper->load(
                array('Id'=>$this->_Chain));
        }
        return $this->_Chain;
    }

    /**
     * _ChainCommand::getChainId
     *
     * @access public
     * @return integer
     */
    public function getChainId() {
        if ($this->_Chain instanceof Chain) {
            return $this->_Chain->getId();
        }
        return (int)$this->_Chain;
    }

    /**
     * _ChainCommand::setChain
     *
     * @access public
     * @param object Chain $value
     * @return void
     */
    public function setChain($value) {
        if (is_numeric($value)) {
            $this->_Chain = (int)$value;
        } else {
            $this->_Chain = $value;
        }
    }

    // }}}
    // CommandItem one to many relation + getter/setter {{{

    /**
     * CommandItem 1..* relation
     *
     * @access private
     * @var Collection
     */
    private $_CommandItemCollection = false;

    /**
     * _ChainCommand::getCommandItemCollection
     *
     * @access public
     * @return object Collection
     */
    public function getCommandItemCollection($filter = array(),
        $sortOrder = array(), $fields = array()) {
        // si un param�tre est pass� on force le rechargement de la collection
        // on ne met en cache m�moire que les collections brutes
        if (!empty($filter) || !empty($sortOrder) || !empty($fields)) {
            $mapper = Mapper::singleton('ChainCommand');
            return $mapper->getOneToMany($this->getId(),
                'CommandItem', $filter, $sortOrder, $fields);
        }
        // si la collection n'est pas en m�moire on la charge
        if (false == $this->_CommandItemCollection) {
            $mapper = Mapper::singleton('ChainCommand');
            $this->_CommandItemCollection = $mapper->getOneToMany($this->getId(),
                'CommandItem');
        }
        return $this->_CommandItemCollection;
    }

    /**
     * _ChainCommand::getCommandItemCollectionIds
     *
     * @access public
     * @param $filter FilterComponent or array
     * @return array
     */
    public function getCommandItemCollectionIds($filter = array()) {
        $col = $this->getCommandItemCollection($filter, array(), array('Id'));
        return $col instanceof Collection?$col->getItemIds():array();
    }

    /**
     * _ChainCommand::setCommandItemCollection
     *
     * @access public
     * @param object Collection $value
     * @return void
     */
    public function setCommandItemCollection($value) {
        $this->_CommandItemCollection = $value;
    }

    // }}}
    // getTableName() {{{

    /**
     * Retourne le nom de la table sql correspondante
     *
     * @static
     * @access public
     * @return string
     */
    public static function getTableName() {
        return 'Command';
    }

    // }}}
    // getObjectLabel() {{{

    /**
     * Retourne le "label" de la classe.
     *
     * @static
     * @access public
     * @return string
     */
    public static function getObjectLabel() {
        return _('None');
    }

    // }}}
    // getProperties() {{{

    /**
     * Retourne le tableau des propri�t�s.
     * Voir Object pour documentation.
     *
     * @static
     * @access public
     * @return array
     * @see Object.php
     */
    public static function getProperties($ownOnly = false) {
        $return = array(
            'InputationNo' => Object::TYPE_STRING,
            'DeliveryPayment' => Object::TYPE_DECIMAL,
            'DateType' => Object::TYPE_INT,
            'Chain' => 'Chain');
        return $ownOnly?$return:array_merge(parent::getProperties(), $return);
    }

    // }}}
    // getLinks() {{{

    /**
     * Retourne le tableau des entit�s li�es.
     * Voir Object pour documentation.
     *
     * @static
     * @access public
     * @return array
     * @see Object.php
     */
    public static function getLinks($ownOnly = false) {
        $return = array(
            'CommandItem'=>array(
                'linkClass'     => 'ChainCommandItem',
                'field'         => 'Command',
                'ondelete'      => 'cascade',
                'multiplicity'  => 'onetomany'
            ));
        return $ownOnly?$return:array_merge(parent::getLinks(), $return);
    }

    // }}}
    // getUniqueProperties() {{{

    /**
     * Retourne le tableau des propri�t�s qui ne peuvent prendre la m�me valeur
     * pour 2 occurrences.
     *
     * @static
     * @access public
     * @return array
     */
    public static function getUniqueProperties() {
        $return = array();
        return array_merge(parent::getUniqueProperties(), $return);
    }

    // }}}
    // getEmptyForDeleteProperties() {{{

    /**
     * Retourne le tableau des propri�t�s doivent �tre "vides" (0 ou '') pour
     * qu'une occurrence puisse �tre supprim�e en base de donn�es.
     *
     * @static
     * @access public
     * @return array
     */
    public static function getEmptyForDeleteProperties() {
        $return = array();
        return array_merge(parent::getEmptyForDeleteProperties(), $return);
    }

    // }}}
    // getFeatures() {{{

    /**
     * Retourne le tableau des "fonctionalit�s" pour l'objet en cours.
     * Voir Object pour documentation.
     *
     * @static
     * @access public
     * @return array
     * @see Object.php
     */
    public static function getFeatures() {
        return array();
    }

    // }}}
    // getMapping() {{{

    /**
     * Retourne le mapping n�cessaires aux composants g�n�riques.
     * Voir Object pour documentation.
     *
     * @static
     * @access public
     * @return array
     * @see Object.php
     */
    public static function getMapping($ownOnly = false) {
        $return = array();
        return $ownOnly?$return:array_merge(parent::getMapping(), $return);
    }

    // }}}
    // useInheritance() {{{

    /**
     * D�termine si l'entit� est une entit� qui utilise l'h�ritage.
     * (classe parente ou classe fille). Ceci afin de differencier les entit�s
     * dans le mapper car classes filles et parentes sont mapp�es dans la m�me
     * table.
     *
     * @static
     * @access public
     * @return bool
     */
    public static function useInheritance() {
        return true;
    }

    // }}}
    // getParentClassName() {{{

    /**
     * Retourne le nom de la premi�re classe parente
     *
     * @static
     * @access public
     * @return string
     */
    public static function getParentClassName() {
        return 'Command';
    }

    // }}}
}

?>