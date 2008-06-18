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

class Department extends _Department {
    // Constructeur {{{

    /**
     * Department::__construct()
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
     * Retourne true ou false selon s'il n'existe pas d�j� de Departement 
     * avec le meme couple (State.Country, (Name et/ou Number))
     * @access public
     * @return void 
     **/
    function isNameCorrect() {
        $mapper = Mapper::singleton(get_class($this));
        
        $FilterComponent = new FilterComponent();
        $FilterComponent->setItem(new FilterRule(
                'Name',
                FilterRule::OPERATOR_EQUALS,
                $this->getName()));
        $FilterComponent->operator = FilterComponent::OPERATOR_OR;        
        $FilterComponent->setItem(new FilterRule(
                'Number',
                FilterRule::OPERATOR_EQUALS,
                $this->getNumber()));
        
        $filter = new FilterComponent();  // le filtre qui est le 'contenant' de niveau sup�rieur
        $rule = new FilterRule('Country',
                               FilterRule::OPERATOR_EQUALS,
                               $this->getCountryId());
        $filter->setItem($rule);
        $rule = new FilterRule('Id',
                               FilterRule::OPERATOR_NOT_EQUALS,
                               $this->getId());
        $filter->setItem($rule);
        $filter->operator = FilterComponent::OPERATOR_AND;
        $filter->setItem($FilterComponent);
        
        $State = $mapper->load($filter);
        if (Tools::isEmptyObject($State)) {
            return true;
        }
        return false;
    }
}

?>