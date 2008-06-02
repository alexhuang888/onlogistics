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

class _FlyType extends Object {
    
    // Constructeur {{{

    /**
     * _FlyType::__construct()
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
     * _FlyType::getName
     *
     * @access public
     * @return string
     */
    public function getName() {
        return $this->_Name;
    }

    /**
     * _FlyType::setName
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function setName($value) {
        $this->_Name = $value;
    }

    // }}}
    // AeroProduct one to many relation + getter/setter {{{

    /**
     * AeroProduct 1..* relation
     *
     * @access private
     * @var Collection
     */
    private $_AeroProductCollection = false;

    /**
     * _FlyType::getAeroProductCollection
     *
     * @access public
     * @return object Collection
     */
    public function getAeroProductCollection($filter = array(),
        $sortOrder = array(), $fields = array()) {
        // si un param�tre est pass� on force le rechargement de la collection
        // on ne met en cache m�moire que les collections brutes
        if (!empty($filter) || !empty($sortOrder) || !empty($fields)) {
            $mapper = Mapper::singleton('FlyType');
            return $mapper->getOneToMany($this->getId(),
                'AeroProduct', $filter, $sortOrder, $fields);
        }
        // si la collection n'est pas en m�moire on la charge
        if (false == $this->_AeroProductCollection) {
            $mapper = Mapper::singleton('FlyType');
            $this->_AeroProductCollection = $mapper->getOneToMany($this->getId(),
                'AeroProduct');
        }
        return $this->_AeroProductCollection;
    }

    /**
     * _FlyType::getAeroProductCollectionIds
     *
     * @access public
     * @param $filter FilterComponent or array
     * @return array
     */
    public function getAeroProductCollectionIds($filter = array()) {
        $col = $this->getAeroProductCollection($filter, array(), array('Id'));
        return $col instanceof Collection?$col->getItemIds():array();
    }

    /**
     * _FlyType::setAeroProductCollection
     *
     * @access public
     * @param object Collection $value
     * @return void
     */
    public function setAeroProductCollection($value) {
        $this->_AeroProductCollection = $value;
    }

    // }}}
    // Rating one to many relation + getter/setter {{{

    /**
     * Rating 1..* relation
     *
     * @access private
     * @var Collection
     */
    private $_RatingCollection = false;

    /**
     * _FlyType::getRatingCollection
     *
     * @access public
     * @return object Collection
     */
    public function getRatingCollection($filter = array(),
        $sortOrder = array(), $fields = array()) {
        // si un param�tre est pass� on force le rechargement de la collection
        // on ne met en cache m�moire que les collections brutes
        if (!empty($filter) || !empty($sortOrder) || !empty($fields)) {
            $mapper = Mapper::singleton('FlyType');
            return $mapper->getOneToMany($this->getId(),
                'Rating', $filter, $sortOrder, $fields);
        }
        // si la collection n'est pas en m�moire on la charge
        if (false == $this->_RatingCollection) {
            $mapper = Mapper::singleton('FlyType');
            $this->_RatingCollection = $mapper->getOneToMany($this->getId(),
                'Rating');
        }
        return $this->_RatingCollection;
    }

    /**
     * _FlyType::getRatingCollectionIds
     *
     * @access public
     * @param $filter FilterComponent or array
     * @return array
     */
    public function getRatingCollectionIds($filter = array()) {
        $col = $this->getRatingCollection($filter, array(), array('Id'));
        return $col instanceof Collection?$col->getItemIds():array();
    }

    /**
     * _FlyType::setRatingCollection
     *
     * @access public
     * @param object Collection $value
     * @return void
     */
    public function setRatingCollection($value) {
        $this->_RatingCollection = $value;
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
        return 'FlyType';
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
        return _('Add/Update airplane type');
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
            'AeroProduct'=>array(
                'linkClass'     => 'AeroProduct',
                'field'         => 'FlyType',
                'ondelete'      => 'nullify',
                'multiplicity'  => 'onetomany'
            ),
            'CourseCommand'=>array(
                'linkClass'     => 'CourseCommand',
                'field'         => 'FlyType',
                'ondelete'      => 'nullify',
                'multiplicity'  => 'onetomany'
            ),
            'Rating'=>array(
                'linkClass'     => 'Rating',
                'field'         => 'FlyType',
                'ondelete'      => 'nullify',
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
        $return = array('Name');
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
        return array('grid', 'add', 'edit', 'del');
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
                'label'        => _('Name'),
                'shortlabel'   => _('Name'),
                'usedby'       => array('grid', 'addedit'),
                'required'     => true,
                'inplace_edit' => false,
                'add_button'   => false,
                'section'      => ''
            ));
        return $return;
    }

    // }}}
}

?>