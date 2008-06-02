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

class CronTask extends Object {
    
    // Constructeur {{{

    /**
     * CronTask::__construct()
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
     * CronTask::getName
     *
     * @access public
     * @return string
     */
    public function getName() {
        return $this->_Name;
    }

    /**
     * CronTask::setName
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function setName($value) {
        $this->_Name = $value;
    }

    // }}}
    // ScriptName string property + getter/setter {{{

    /**
     * ScriptName string property
     *
     * @access private
     * @var string
     */
    private $_ScriptName = '';

    /**
     * CronTask::getScriptName
     *
     * @access public
     * @return string
     */
    public function getScriptName() {
        return $this->_ScriptName;
    }

    /**
     * CronTask::setScriptName
     *
     * @access public
     * @param string $value
     * @return void
     */
    public function setScriptName($value) {
        $this->_ScriptName = $value;
    }

    // }}}
    // DayOfMonth string property + getter/setter {{{

    /**
     * DayOfMonth int property
     *
     * @access private
     * @var integer
     */
    private $_DayOfMonth = 0;

    /**
     * CronTask::getDayOfMonth
     *
     * @access public
     * @return integer
     */
    public function getDayOfMonth() {
        return $this->_DayOfMonth;
    }

    /**
     * CronTask::setDayOfMonth
     *
     * @access public
     * @param integer $value
     * @return void
     */
    public function setDayOfMonth($value) {
        if ($value !== null) {
            $this->_DayOfMonth = (int)$value;
        }
    }

    // }}}
    // DayOfWeek string property + getter/setter {{{

    /**
     * DayOfWeek int property
     *
     * @access private
     * @var integer
     */
    private $_DayOfWeek = -1;

    /**
     * CronTask::getDayOfWeek
     *
     * @access public
     * @return integer
     */
    public function getDayOfWeek() {
        return $this->_DayOfWeek;
    }

    /**
     * CronTask::setDayOfWeek
     *
     * @access public
     * @param integer $value
     * @return void
     */
    public function setDayOfWeek($value) {
        if ($value !== null) {
            $this->_DayOfWeek = (int)$value;
        }
    }

    // }}}
    // HourOfDay string property + getter/setter {{{

    /**
     * HourOfDay int property
     *
     * @access private
     * @var integer
     */
    private $_HourOfDay = 0;

    /**
     * CronTask::getHourOfDay
     *
     * @access public
     * @return integer
     */
    public function getHourOfDay() {
        return $this->_HourOfDay;
    }

    /**
     * CronTask::setHourOfDay
     *
     * @access public
     * @param integer $value
     * @return void
     */
    public function setHourOfDay($value) {
        if ($value !== null) {
            $this->_HourOfDay = (int)$value;
        }
    }

    // }}}
    // Active string property + getter/setter {{{

    /**
     * Active int property
     *
     * @access private
     * @var integer
     */
    private $_Active = 1;

    /**
     * CronTask::getActive
     *
     * @access public
     * @return integer
     */
    public function getActive() {
        return $this->_Active;
    }

    /**
     * CronTask::setActive
     *
     * @access public
     * @param integer $value
     * @return void
     */
    public function setActive($value) {
        if ($value !== null) {
            $this->_Active = (int)$value;
        }
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
        return 'CronTask';
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
        return _('Add/Update scheduled task');
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
            'Name' => Object::TYPE_STRING,
            'ScriptName' => Object::TYPE_STRING,
            'DayOfMonth' => Object::TYPE_INT,
            'DayOfWeek' => Object::TYPE_INT,
            'HourOfDay' => Object::TYPE_INT,
            'Active' => Object::TYPE_BOOL);
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
        $return = array();
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
                'label'        => _('Task name'),
                'shortlabel'   => _('Task name'),
                'usedby'       => array('grid', 'addedit'),
                'required'     => true,
                'inplace_edit' => false,
                'add_button'   => false,
                'section'      => _('Scheduled task definition')
            ),
            'ScriptName'=>array(
                'label'        => _('Script'),
                'shortlabel'   => _('Script'),
                'usedby'       => array('grid', 'addedit'),
                'required'     => false,
                'inplace_edit' => false,
                'add_button'   => false,
                'section'      => _('Scheduled task definition')
            ),
            'DayOfMonth'=>array(
                'label'        => _('day of month'),
                'shortlabel'   => _('day of month'),
                'usedby'       => array('addedit'),
                'required'     => false,
                'inplace_edit' => false,
                'add_button'   => false,
                'section'      => _('Scheduled task periodicity')
            ),
            'DayOfWeek'=>array(
                'label'        => _('day of week'),
                'shortlabel'   => _('day of week'),
                'usedby'       => array('addedit'),
                'required'     => false,
                'inplace_edit' => false,
                'add_button'   => false,
                'section'      => _('Scheduled task periodicity')
            ),
            'HourOfDay'=>array(
                'label'        => _('hour of day'),
                'shortlabel'   => _('hour of day'),
                'usedby'       => array('addedit'),
                'required'     => false,
                'inplace_edit' => false,
                'add_button'   => false,
                'section'      => _('Scheduled task periodicity')
            ),
            'Active'=>array(
                'label'        => _('Active'),
                'shortlabel'   => _('Active'),
                'usedby'       => array('searchform', 'grid', 'addedit'),
                'required'     => false,
                'inplace_edit' => false,
                'add_button'   => false,
                'section'      => _('Scheduled task definition')
            ));
        return $return;
    }

    // }}}
}

?>