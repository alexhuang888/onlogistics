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

require_once('Quantificator.php');
require_once('MLcompute.php');

/**
 * Class ProductCommandQuantificator
 * Classe qui g�re la quantification des taches activ�es lors d'une commande 
 * produit.
 *
 * @package    lib
 * @subpackage Quantificator
 */
class ProductCommandQuantificator extends Quantificator 
{
    // properties {{{

    /**
     * Contient toutes les donn�es de quantifications necessaires � la classe
     * pour �valuer les dur�es et les couts des t�ches de la chaine.
     *
     * @var    array $data
     * @access private
     */
    protected $data = array(
        'Quantity' => 0,
        'Surface'  => 0,
        'Volume'   => 0,
        'LM'       => 0,
        'Weight'   => 0,
        'Task'     => null
    );

    // }}}
    // ProductCommandQuantificator::execute() {{{

    /**
     * Pr�pare les donn�es de quantification avant lancement du
     * parent::execute qui lui va lancer la m�thode processTask() pour chaque 
     * t�che.
     *
     * @return void
     */
    public function execute($sortOrder=SORT_ASC, $limitToAco=false, $limitToAck=false)
    {
        $cmiCol = $this->activatedChain->getCommandItemCollection();
        $count = $cmiCol->getCount();
        for($i = 0; $i < $count; $i++) {
            $cmi = $cmiCol->getItem($i);
            $pdt = $cmi->getProduct();
            $qty = $cmi->getQuantity();
            $this->data['Quantity'] += $qty;
            $this->data['Surface'] += $qty * (
                $pdt->getSellUnitWidth() *
                $pdt->getSellUnitLength()
            );
            $this->data['Volume'] += $qty * (
                $pdt->getSellUnitWidth() *
                $pdt->getSellUnitLength() * 
                $pdt->getSellUnitHeight()
            );
            $this->data['Weight'] += $qty * $pdt->getSellUnitWeight();
            $this->data['LM'] = MLcompute(
                $pdt->getSellUnitLength(),
                $pdt->getSellUnitWidth(),
                $pdt->getSellUnitHeight(),
                $pdt->getSellUnitMasterDimension(),
                $pdt->getSellUnitGerbability(),
                $qty
            );
        }
        return parent::execute($sortOrder, $limitToAco, $limitToAck);
    }

    // }}}
    // ProductCommandQuantificator::getDurationMultiplicator() {{{

    /**
     * Retourne le multiplicateur pour le calcul de la dur�e en fonction du
     * type de dur�e pass� en param�tre.
     *
     * @access protected
     * @param  int $durationType
     * @return mixed
     */
    protected function getDurationMultiplicator($durationType, $ack)
    {
        switch ($durationType) {
            // par forfait
            case ActivatedChainTask::DURATIONTYPE_FORFAIT:
                return 1;
            // par kilogramme
            case ActivatedChainTask::DURATIONTYPE_KG:
                return $this->data['Weight'];
            // par Volume
            case ActivatedChainTask::DURATIONTYPE_METER:
                return $this->data['Volume'];
            // par m�tre lin�aire
            case ActivatedChainTask::DURATIONTYPE_LM:
                return $this->data['LM'];
            // par unit� manipul�e
            case ActivatedChainTask::DURATIONTYPE_QUANTITY:
                return $this->data['Quantity'];
            // par kilom�tre
            case ActivatedChainTask::DURATIONTYPE_KM:
                return $ack->GetKilometerNumber();
            // non g�r� on affiche un warning et on prend forfait par d�faut
            default:
                $msg = sprintf(
                    _('Unknown duration type for task "%s", "amount" was chosen in replacement.'),
                    $ack->getTask()->getName()
                );
                trigger_error($msg, E_USER_WARNING);
                return 1;
        }
    }

    // }}}
    // ProductCommandQuantificator::getCostMultiplicator() {{{

    /**
     * Retourne le multiplicateur pour le calcul du co�t en fonction du type
     * de co�t pass� en param�tre.
     *
     * @access protected
     * @param  int $costType
     * @param  object ActivatedChainTask $ack
     * @param  boolean $forRessource si on veut le multiplicateur pour une 
     * ressource ou pour une quantification classique (false par d�faut).
     * @return mixed
     */
    protected function getCostMultiplicator($costType, $ack, $forRessource=false)
    {
        switch ($costType) {
            // au forfait
            case ActivatedChainTask::COSTTYPE_FORFAIT:
            // au kilowatt
            case ActivatedChainTask::COSTTYPE_KWATT:
                return 1;
            // par qt� d'unit� manipul�e 
            case ActivatedChainTask::COSTTYPE_QUANTITY:
                return $this->real?$ack->getRealQuantity():$this->data['Quantity'];
            // par jour
            case ActivatedChainTask::COSTTYPE_DAILY:
                $duration = $this->real?$ack->getRealDuration():$ack->getDuration();
                return $duration / (3600*24);
            // par heure
            case ActivatedChainTask::COSTTYPE_HOURLY:
                $duration = $this->real?$ack->getRealDuration():$ack->getDuration();
                return $duration / 3600;
            // par kilogramme
            case ActivatedChainTask::COSTTYPE_KG:
                return $this->data['Weight'];
            // par kilom�tre
            case ActivatedChainTask::COSTTYPE_KM:
                if (isTransportTask($ack)) {
                    return $ack->getKilometerNumber();
                }
                return $forRessource?1:$ack->getKilometerNumber();
            // par m�tre carr�  
            case ActivatedChainTask::COSTTYPE_SQUAREMETTER:
                return $forRessource?1:$this->data['Surface'];
            // par m�tre cube
            case ActivatedChainTask::COSTTYPE_CUBEMETTER:
                return $this->data['Volume'];
            // par M�tre lin�aire
            case ActivatedChainTask::COSTTYPE_LM:
                return $forRessource?1:$this->data['LM'];
            // non g�r� on affiche un warning et on prend forfait par d�faut
            default:
                $msg = sprintf(
                    _('Unknown cost type for task "%s", "amount" was chosen in replacement.'),
                    $ack->getTask()->getName()
                );
                trigger_error($msg, E_USER_WARNING);
                return 1;
        }
    }

    // }}}
}

?>
