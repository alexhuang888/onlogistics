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
 * @version   SVN: $Id: SiteAddEdit.php 9 2008-06-06 09:12:09Z izimobil $
 * @link      http://www.onlogistics.org
 * @link      http://onlogistics.googlecode.com
 * @since     File available since release 0.1.0
 * @filesource
 */

/**
 * TermsOfPayment class
 *
 */
class TermsOfPayment extends Object {
    
    // Constructeur {{{

    /**
     * TermsOfPayment::__construct()
     * Constructeur
     *
     * @access public
     * @return void
     */
    public function __construct() {
        parent::__construct();
    }

    // }}}
    // Name string property + getter/setter {{{

    /**
     * Name string property
     *
     * @access private
     * @var string
     */
    private $_Name = '';

    /**
     * TermsOfPayment::getName
     *
     * @access public
     * @return string
     */
    public function getName() {
        return $this->_Name;
    }

    /**
     * TermsOfPayment::setName
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function setName($value) {
        $this->_Name = $value;
    }

    // }}}
    // TermsOfPaymentItem one to many relation + getter/setter {{{

    /**
     * TermsOfPaymentItem 1..* relation
     *
     * @access private
     * @var Collection
     */
    private $_TermsOfPaymentItemCollection = false;

    /**
     * TermsOfPayment::getTermsOfPaymentItemCollection
     *
     * @access public
     * @return object Collection
     */
    public function getTermsOfPaymentItemCollection($filter = array(),
        $sortOrder = array(), $fields = array()) {
        // si un param�tre est pass� on force le rechargement de la collection
        // on ne met en cache m�moire que les collections brutes
        if (!empty($filter) || !empty($sortOrder) || !empty($fields)) {
            $mapper = Mapper::singleton('TermsOfPayment');
            return $mapper->getOneToMany($this->getId(),
                'TermsOfPaymentItem', $filter, $sortOrder, $fields);
        }
        // si la collection n'est pas en m�moire on la charge
        if (false == $this->_TermsOfPaymentItemCollection) {
            $mapper = Mapper::singleton('TermsOfPayment');
            $this->_TermsOfPaymentItemCollection = $mapper->getOneToMany($this->getId(),
                'TermsOfPaymentItem');
        }
        return $this->_TermsOfPaymentItemCollection;
    }

    /**
     * TermsOfPayment::getTermsOfPaymentItemCollectionIds
     *
     * @access public
     * @param $filter FilterComponent or array
     * @return array
     */
    public function getTermsOfPaymentItemCollectionIds($filter = array()) {
        $col = $this->getTermsOfPaymentItemCollection($filter, array(), array('Id'));
        return $col instanceof Collection?$col->getItemIds():array();
    }

    /**
     * TermsOfPayment::setTermsOfPaymentItemCollection
     *
     * @access public
     * @param object Collection $value
     * @return void
     */
    public function setTermsOfPaymentItemCollection($value) {
        $this->_TermsOfPaymentItemCollection = $value;
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
        return 'TermsOfPayment';
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
        return _('Terms of payment');
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
    public static function getProperties() {
        $return = array(
            'Name' => Object::TYPE_STRING);
        return $return;
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
    public static function getLinks() {
        $return = array(
            'SupplierCustomer'=>array(
                'linkClass'     => 'SupplierCustomer',
                'field'         => 'TermsOfPayment',
                'ondelete'      => 'nullify',
                'multiplicity'  => 'onetomany'
            ),
            'TermsOfPaymentItem'=>array(
                'linkClass'     => 'TermsOfPaymentItem',
                'field'         => 'TermsOfPayment',
                'ondelete'      => 'cascade',
                'multiplicity'  => 'onetomany'
            ));
        return $return;
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
        return $return;
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
        return $return;
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
        return array('add', 'edit', 'del', 'grid', 'searchform');
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
    public static function getMapping() {
        $return = array(
            'Name'=>array(
                'label'        => _('Designation'),
                'shortlabel'   => _('Designation'),
                'usedby'       => array('grid', 'searchform', 'addedit'),
                'required'     => false,
                'inplace_edit' => false,
                'add_button'   => false,
                'section'      => ''
            ));
        return $return;
    }

    // }}}
}

?>