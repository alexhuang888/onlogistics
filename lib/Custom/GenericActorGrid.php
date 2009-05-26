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

class GenericActorGrid extends GenericGrid {
    // GenericActorGrid::__construct() {{{

    /**
     * Constructor
     *
     * @param array $params
     * @access public
     */
    public function __construct($params) {
        $params['title'] = _('Generic actors');
        parent::__construct($params);
    }

    // }}}
    // GenericActorGrid::getMapping() {{{

    /**
     * Surcharg�e ici pour retourner un mapping sp�cifique.
     *
     * @access protected
     * @return array
     */
    protected function getMapping() {
        return array(
            'Name'=>array(
                'usedby'=>array('grid', 'addedit'),
                'label'=>_('Name'), 
                'shortlabel'=>_('Name'),
                'required'=>true
            ),
            'Code'=>array(
                'usedby'=>array('grid', 'addedit'),
                'label'=>_('Code'), 
                'shortlabel'=>_('Code'),
                'required'=>true
            )
        );
    }

    // }}}
    // GenericActorGrid::getFeatures() {{{

    /**
     * Surcharg�e ici pour retourner les features sp�cifiques.
     *
     * @access protected
     * @return array
     */
    protected function getFeatures() {
        return array('grid', 'add', 'edit', 'del');
    }

    // }}}
    // GenericActorGrid::getGridFilter() {{{

    /**
     * Surcharg�e ici pour n'afficher que les acteurs g�n�riques.
     *
     * @access protected
     * @return array
     */
    protected function getGridFilter() {
        return array('Generic'=>true);
    }

    // }}}
}

?>
