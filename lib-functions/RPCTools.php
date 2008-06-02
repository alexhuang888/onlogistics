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

require_once('XML/Util.php');
require_once('CityZipCountryHelper.php');
require_once('Objects/Alert.const.php');
require_once('AlertSender.php');

define('XML_VERSION', '1.0');
define('XML_ENCODING', 'ISO-8859-1');
/*  LES 3 CSTES SUIVANTES SONT UTILISEES POUR OPTIM DES APPRO
define('PASSED_WEEK_NUMBER', 6);  // Nbre de semaines anterieures a prendre en compte exple: 6 => de 0 a -5
define('FUTURE_WEEK_NUMBER', 6);  // Nbre de semaines a venir pour lesquelles il faut faire des previsions
define('DEFAULT_DELIVERY_DELAY', 7);  // Delai de livraison (en jours) s'il n'est pas defini dans SupplierCustomer
*/

function getDataForSpreadSheet($sps) {
    if (!($sps instanceof SpreadSheet)) {
        return array();
    }
    $sscCol = $sps->getSpreadSheetColumnCollection(array(), array('Order'=>SORT_ASC));
    $count = $sscCol->getCount();
    $pathes = array();
    for($i = 0; $i < $count; $i++){
        $ssc = $sscCol->getItem($i);
        $pathes[] = $ssc->getPropertyName();
    }
    $ent = $sps->getEntity();
    if (!($ent instanceof Entity)) {
        return array();
    }
    $sm = new StateMachine($ent->getName(), $pathes);
    $nullFilter = false;
    Database::connection()->setFetchMode(ADODB_FETCH_NUM);
    $res = Database::connection()->execute($sm->toSQL($nullFilter));
    $data = array();
    while($res && !$res->EOF) {
        $data[] = array_values($res->fields); //$linedata;
        $res->moveNext();
    }
    $res->close();
    Database::connection()->setFetchMode(ADODB_FETCH_DEFAULT);
    return $data;
}

/**
 * _getStorageSiteList
 * retourne un liste de chaines xml contenant le site/magasins/locations
 *
 * @access private
 * @param integer $actorID l'id de l'acteur connect�
 * @return array un tableau de cha�nes xml
 */
function _getStorageSiteList($actorID)
{

    $mapper = Mapper::singleton('StorageSite');
    $sSiteCollection = $mapper->loadCollection(
        array("Owner.Id" => $actorID), array('Name'=>SORT_ASC));
    $sitelist = array();
    for($i = 0; $i < $sSiteCollection->getCount(); $i++) {
        $storageSite = $sSiteCollection->getItem($i);
    	$xmldoc  = XML_Util::getXMLDeclaration(XML_VERSION, XML_ENCODING) . "\n";
        $xmldoc .= XML_Util::createStartElement("storagesite");
        $xmldoc .= XML_Util::createTag("id", array(), $storageSite->getId());
        $xmldoc .= XML_Util::createTag("name", array(), $storageSite->getName());

        $storeCollection = $storageSite->getStoreCollection(
            array(), array('Name'=>SORT_ASC));
        for($j = 0; $j < $storeCollection->getCount(); $j++) {
            $store = $storeCollection->getItem($j);
            $xmldoc .= XML_Util::createStartElement("store");
            $xmldoc .= XML_Util::createTag("id", array(), $store->getId());
            $xmldoc .= XML_Util::createTag("name", array(), $store->getName());

            $locCollection = $store->getLocationCollection(
                array(), array('Name'=>SORT_ASC));
            for($k = 0; $k < $locCollection->getCount(); $k++) {
                $loc = $locCollection->getItem($k);
                if ($loc->getActivated() == 1) {
                    $xmldoc .= XML_Util::createStartElement("location");
                    $xmldoc .= XML_Util::createTag("id", array(), $loc->getId());
                    $xmldoc .= XML_Util::createTag("reference", array(), $loc->getName());
                    $xmldoc .= XML_Util::createEndElement("location");
                }
            } // for
        	$xmldoc .= XML_Util::createEndElement("store");
        } // for
        $xmldoc .= XML_Util::createEndElement("storagesite");
		$sitelist[] = $xmldoc;
    } // for
    return $sitelist;
}

function _getStorageSiteList2($actorID)
{

    $mapper = Mapper::singleton('StorageSite');
    $sSiteCollection = $mapper->loadCollection(
        array("Owner.Id" => $actorID), array('Name'=>SORT_ASC));
    $stoList = array();
    for($i = 0; $i < $sSiteCollection->getCount(); $i++) {
        $storageSite = $sSiteCollection->getItem($i);
        $storeCollection = $storageSite->getStoreCollection(
            array(), array('Name'=>SORT_ASC));
        $storeList = array();
        for($j = 0; $j < $storeCollection->getCount(); $j++) {
            $store = $storeCollection->getItem($j);
            $locCollection = $store->getLocationCollection(
                array(), array('Name'=>SORT_ASC));
            $locList = array();
            for($k = 0; $k < $locCollection->getCount(); $k++) {
                $loc = $locCollection->getItem($k);
                $locList[] = array(
                    'id' => $loc->getId(),
                    'reference' => $loc->getName()
                );
            } // for
            $storeList[] = array(
                'name' => $store->getName(),
                'location' => $locList
            );
        } // for
        $stoList[] = array(
            'name' => $storageSite->getName(),
            'store'=> $storeList
        );
    } // for
    return $stoList;
}
/**
 * _getInventory
 * Construit le fichier xml pour l'inventaire � partir des emplacements
 * s�lectionn�s par l'utilisateur.
 * Si un emplacement (ou un emplacement li� � un produit) est d�j� en cours
 * d'inventaire, une erreur est remont�e.
 *
 * @access private
 * @param array $locationIds un tableau d'ids d'emplacements
 * @return string une cha�ne xml
 **/
function _getInventory($locationIds){

	$mapper = Mapper::singleton('Location');
    $locationCollection = $mapper->loadCollection(
        array("Id"=>$locationIds), array('Name'=>SORT_ASC));
	$errors = array();
	if (empty($locationCollection)) {
	    $errors[] = "Le(s) emplacement(s) sp�cifi�(s) n'ont pas �t� trouv�(s) "
				   . "en base de donn�es";
	}
	$xmldoc  = XML_Util::getXMLDeclaration(XML_VERSION, XML_ENCODING) . "\n";
	$xmldoc .= XML_Util::createStartElement("inventory");
	for($i = 0; $i < $locationCollection->getCount(); $i++){
		$loc = $locationCollection->getItem($i);
		if(!($loc instanceof Location)) {
			$errors[] = "Un des emplacements s�lectionn�s n'a pas �t� trouv� en base de donn�es.";
			break;
		}
        if($loc->getActivated() == 0){
			$msg = "L'emplacement %s est d�j� bloqu� pour inventaire.";
            $errors[] = sprintf($msg, $loc->getName());
            continue;
        }
		$xmldoc .= XML_Util::createStartElement("location");
        $xmldoc .= XML_Util::createTag("id", array(), $loc->getId());
        $xmldoc .= XML_Util::createTag("reference", array(), $loc->getName());
		$lpqCollection = $loc->getLocationProductQuantitiesCollection();
		for($j = 0; $j < $lpqCollection->getCount(); $j++){
			$lpq = $lpqCollection->getItem($j);
			if (!($lpq instanceof LocationProductQuantities)) {
			    continue;
			}
			$pdt = $lpq->getProduct();
            $linkedLoc = $lpq->getLocation();
            if($linkedLoc instanceof Location && $linkedLoc->getActivated() == 0){
				$msg = "Le produit %s est sur un emplacement (%s) "
					 . "d�j� bloqu� pour inventaire.";
	            $errors[] = sprintf($msg, $pdt->getBaseReference(), $loc->getName());
                continue;
            }
			if ($pdt instanceof Product) {
				$xmldoc .= XML_Util::createStartElement("product");
				$xmldoc .= XML_Util::createTag("id", array(), $pdt->getId());
				$xmldoc .= XML_Util::createTag("reference", array(), $pdt->getBaseReference());
				$xmldoc .= XML_Util::createTag("name", array(), $pdt->getName());
				$xmldoc .= XML_Util::createTag("quantity", array(), $lpq->getRealQuantity());
				$xmldoc .= XML_Util::createEndElement("product");
			}
		} // for
		$xmldoc .= XML_Util::createEndElement("location");
	} // for
	if (!empty($errors)) {
		$errString  = "L'inventaire est impossible sur les emplacements s�lectionn�s:<ul><li>";
		$errString .= implode("</li><li>", $errors);
		$errString .= "</li></ul><br>Veuillez modifier votre s�lection.";
	    $xmldoc .= XML_Util::createTag("error", array(), $errString);
	}
	$xmldoc .= XML_Util::createEndElement("inventory");
	return $xmldoc;
}

/**
 *
 * @access public
 * @return boolean (true if OK) or string if error
 **/
function _handleInventory($auth, $xmldata) {
    require_once('ExecutionXMLParsing.php');
	// on demarre la transaction
	Database::connection()->startTrans();
    // on construit une pile d'erreurs
    $errors = array();
    // on parse le xml
    $xmlInv = simplexml_load_string($xmldata);
    if (!$xmlInv) {
        $errors[] = "Erreur de parsing...";
    }

	// le site existe ?
	$site = Object::load('Site', (int)$xmlInv->storagesiteid);
    $sitename = clean((string)$xmlInv->storagesitename);
	if (Tools::isEmptyObject($site)) {
		$msg  = "Le site %s n'existe pas en base de donn�es. ";
		$msg .= "L'inventaire a �t� annul�.";
		// pas la peine de continuer
	    return sprintf($msg, $sitename);
	}



	// le magasin existe ?
	$store = Object::load('Store', (int)$xmlInv->storeid);
	$storename = clean((string)$xmlInv->storename);
	if (Tools::isEmptyObject($store)) {
		$msg  = "Le magasin %s n'existe pas en base de donn�es. ";
		$msg .= "L'inventaire a �t� annul�.";
		// pas la peine de continuer
	    return sprintf($msg, $storename);
	}
    // le propri�taire du stock et sa devise
    $owner = false;
    if ($site instanceof StorageSite) {
        $owner = $site->getStockOwner();
    }
    if (!($owner instanceof Actor)) {
        $owner = $site->getOwner();
    }
	// on charge les mappers n�cessaires
    $acpMapper = Mapper::singleton('ActorProduct');
	$lpqMapper = Mapper::singleton('LocationProductQuantities');
	$productMapper = Mapper::singleton('Product');
	// on cr�e un nouvel inventaire
	$inventory = Object::load('Inventory');
	$inventory->setBeginDate(clean((string)$xmlInv->startdate));
	$inventory->setEndDate(clean((string)$xmlInv->enddate));
	$inventory->setUserAccount($auth->getUser());
	$inventory->setUserName($auth->getIdentity());
	$inventory->setStorageSite($site);
	$inventory->setStorageSiteName($site->getName());
	$inventory->setStore($store);
	$inventory->setStoreName($store->getName());

	// on sauve l'inventaire ici pour lui g�n�rer un id
	// � noter que la transaction n'a pas �t� commit�e encore
	$inventory->save();

	// on met les id des locations dans un tableau pour les passer � _blockLocation
	$locationIds = array();
	// pour chaque location on cr�e un inventorydetail
	foreach ($xmlInv->location as $xmlLoc) {
	   $loc = Object::load('Location', (int)$xmlLoc->id);
		// on verifie que l'emplacement existe
		if (Tools::isEmptyObject($loc)) {
            $locname = clean((string)$xmlLoc->reference);
			$msg  = "L'emplacement %s n'a pas �t� trouv� dans le magasin %s. ";
			$msg .= "L'inventaire sur cet emplacement a �t� annul�.";
		    $errors[] = sprintf($msg, $locname, $storename);
			// on passe � l'emplacement suivant
			continue;
		}
		// s'il existe on parcours les r�f�rences inventori�es
		foreach ($xmlLoc->product as $xmlPdt) {
            $pdtref = clean((string)$xmlPdt->reference);
            $pdt = $pdtMapper->load(array('BaseReference' => $pdtref));
			// on v�rifie que le produit existe
			if (Tools::isEmptyObject($pdt)) {
				$msg = "La r�f�rence %s n'a pas �t� trouv�e en base de donn�es," .
                    " elle ne sera pas prise en compte dans l'inventaire.";
			    $errors[] = sprintf($msg, $pdtref);
				// on passe � l'emplacement suivant
				continue 2;
			}
			// s'il existe, on met � jour le lpq correspondant
	        $lpq = $lpqMapper->load(
                array('Product' => $pdt->getId(), 'Location' => $loc->getId()));
			// s'il n'existe pas on le cr�e
			if (Tools::isEmptyObject($lpq)) {
				$lpq = Object::load('LocationProductQuantities');
				$lpq->setProduct($pdt);
				$lpq->setLocation($loc);
			}
			$qty = (float)$xmlPdt->realquantity;  // float, et non plus int!!
			$difference = $qty - $lpq->getRealQuantity();
			$lpq->setRealQuantity($qty);
			// si quantit� nulle on supprime le lpq, sinon on le sauve
			if ($qty == 0) {
				$lpqID = $lpq->getId();
				if ($lpqID > 0) {
				    $lpqMapper->delete($lpqID);
				}
			} else {
				$lpq->save();
			}
			// on met � jour la qt� virtuelle du produit
			$virtualQty = $pdt->getSellUnitVirtualQuantity() + $difference;
			$pdt->setSellUnitVirtualQuantity($virtualQty);
			$pdt->save();
			// envoi des �ventuelles alertes
			// si QV produit atteint stock mini
			if ($virtualQty <= $pdt->getSellUnitMinimumStoredQuantity()) {
                AlertSender::send_ALERT_STOCK_QV_MINI($pdt);
			}
			// si QV produit atteint stock 0
			if ($virtualQty <= 0) {
                AlertSender::send_ALERT_STOCK_QV_REACH_ZERO($pdt);
			}
			// on cr�e l'entr�e inventorydetail
			$inventoryDetail = Object::load('InventoryDetail');
			$inventoryDetail->setInventory($inventory);
			$inventoryDetail->setLocation($loc);
			$inventoryDetail->setLocationName($loc->getName());
			$inventoryDetail->setProduct($pdt);
			$inventoryDetail->setProductReference($pdt->getBaseReference());
			$inventoryDetail->setQuantity($qty);
            // prix d'achat du produit et devise
            $acp = $acpMapper->load(
                array(
                    'Priority'=>1,
                    'Product'=>$pdt->getId()
                )
            );
            if ($acp instanceof ActorProduct) {
                $pbc = $acp->getPriceByCurrencyForInventory($owner);
                if ($pbc instanceof PriceByCurrency && $pbc->getPrice() > 0) {
                    $cur = $pbc->getCurrency();
        			$inventoryDetail->setBuyingPriceHT($pbc->getPrice());
        			$inventoryDetail->setCurrency($cur->getSymbol());
                }
            }

			$inventoryDetail->save();
			// cleanage
			unset($pdt, $lpq, $inventoryDetail);
		} // foreach $xmlPdt
		$locationIds[] = $loc->getId();
		// cleanage
		unset($loc);
	} // foreach $xmlLoc

	// on d�bloque les emplacements
	_blockLocations($locationIds, true);
	// on commite la transaction
	Database::connection()->completeTrans();
	// le tableau des erreurs s'il y en a ou true sinon
	if (!empty($errors)) {
	    $errstr  = "<h3>Des erreurs sont survenues:<h3><ul><li>";
		$errstr .= implode("</li><li>", $errors);
		$errstr .= '</li></ul>';
		return $errstr;
	}
    return true;
}


/**
 * Bloque/D�bloque les emplacements bloqu�s pour inventaire.
 *
 * @access public
 * @param array $locationIds le tableau d'id des locations � d�bloquer
 * @param boolean $activated false: bloque true: d�bloque les locations
 * @return void
 **/
function _blockLocations($locationIds, $activated = false){
	if (!is_array($locationIds) || count($locationIds) == 0) {
	    return true;
	}

	$mapper = Mapper::singleton('Location');
	// on demarre la transaction
	Database::connection()->startTrans();
	$locationCollection = $mapper->loadCollection(array('Id'=>$locationIds));
	if (!($locationCollection instanceof Collection)) {
	    return true;
	}
	for($i = 0; $i < $locationCollection->getCount(); $i++){
		$loc = $locationCollection->getItem($i);
		if (!($loc instanceof Location)) {
		    continue;
		}
		$loc->setActivated($activated);
		$mapper->save($loc);
		// les locations li�es
		$lpqCollection = $loc->getLocationProductQuantitiesCollection();
		if ($lpqCollection instanceof Collection) {
			for($j = 0; $j < $lpqCollection->getCount(); $j++){
				$lpq = $lpqCollection->getItem($j);
				if ($lpq instanceof LocationProductQuantities) {
					$linkedLoc = $lpq->getLocation();
					if ($linkedLoc instanceof Location) {
					    $linkedLoc->setActivated($activated);
						$mapper->save($linkedLoc);
					}
				}
			}
		}
	}
	// on commite la transaction
	Database::connection()->completeTrans();
	return true;
}

/**
 * _blockMovements: fonction utilis�e par block/unblockMovements
 *
 * @return void
 * @param array $movementIds
 * @param boolean true: bloquer, false: d�bloquer
 */
function _blockMovements($movementIds, $block=true) {
	if (!is_array($movementIds) || count($movementIds) == 0) {
	    return true;
	}
    require_once('Objects/ActivatedMovement.php');

    $acmMapper = Mapper::singleton('ActivatedMovement');
    $acmCollection = $acmMapper->loadCollection(array('Id'=>$movementIds));
    if($acmCollection instanceof Collection){
        for($i=0; $i<$acmCollection->getCount(); $i++){
            $acm = $acmCollection->getItem($i);
			$state = -1;
			if ($block != true) {
				// d�blocage:
			    // il faut v�rifier que le mouvement est � en cours et
				// n'�tait pas � l'�tat partiel avant execution
				if ($acm->getState() == ActivatedMovement::ACM_EN_COURS) {
                    $state = false==$acm->getExecutedMovement()?
                        ActivatedMovement::CREE : ActivatedMovement::ACM_EXECUTE_PARTIELLEMENT;
				}
			} else {
				// blocage
				$state = ActivatedMovement::ACM_EN_COURS;
			}

			if ($state != -1) {
	            $acm->setState($state);
	            $acmMapper->save($acm);
			}
        }
    }
	return true;
}

/**
 *
 * @access public
 * @return void
 **/
function _getProductList(){
	require_once('SQLRequest.php');
    $mapper = Mapper::singleton('Product');
	$pdtlist = array();
	$result = Request_ProductListForRPCClient();
	while(!$result->EOF){
        $tmode = $result->fields['_TracingMode'];
        $ccplist = array();
        if ($tmode > 0) {
            $pdtID = $result->fields['_Id'];
	        $ccpResult = Request_ConcreteProductListForRPCClient($pdtID);
            if ($ccpResult) {
	            while(!$ccpResult->EOF){
                    $ccplist[] = array(
                        'serialnumber'=> $ccpResult->fields['_SerialNumber'],
                        'mode'=> $tmode
                    );
                    $ccpResult->moveNext();
                }
                $ccpResult->close();
            }

        }
		$pdtlist[] = array(
            'reference'=>$result->fields['_BaseReference'],
            'name'=>$result->fields['_Name'],
            'tracingmode'=>$result->fields['_TracingMode'],
            'concreteproduct'=>$ccplist
        );
        $result->moveNext();
	} // for
    $result->close();
	return $pdtlist;
}

/**
 *
 * @access public
 * @return void
 **/
function _getActorList(){
	require_once('SQLRequest.php');
	require_once('Objects/Site.php');
	$result = Request_ActorListForRPCClient();
	$actorlist = array();
	$lastId = 0;
    $array = Site::getStreetTypeConstArray();
	while(!$result->EOF){
		$currentId = $result->fields['actId'];
        $streettype = isset($array[$result->fields['adrStreetType']])?
            $array[$result->fields['adrStreetType']]:'';
		if ($currentId != $lastId) {
            $actorlist[] = array(
                'name'=>$result->fields['actName'],
                'address'=>array(
                    array(
                        'streetnumber'=>$result->fields['adrStreetNumber'],
                        'streettype'=>$streettype,
                        'streetname'=>$result->fields['adrStreetName'],
                        'streetaddons'=>$result->fields['adrStreetAddons'],
                        'zipcode'=>$result->fields['zipCode'],
                        'cityname'=>$result->fields['ctnName'],
                        'cedex'=>$result->fields['adrCedex'],
                        'countryname'=>$result->fields['ctrName']
                    )
                ),
                'siteName'=>$result->fields['siteName'],
                'phone'=>$result->fields['sitePhone']
            );
        } else {
            $actorlist[count($actorlist)-1]['address'][] = array(
                'streetnumber'=>$result->fields['adrStreetNumber'],
                'streettype'=>$streettype,
                'streetname'=>$result->fields['adrStreetName'],
                'streetaddons'=>$result->fields['adrStreetAddons'],
                'zipcode'=>$result->fields['zipCode'],
                'cityname'=>$result->fields['ctnName'],
                'cedex'=>$result->fields['adrCedex'],
                'countryname'=>$result->fields['ctrName']
            );
        }
        $lastId = $currentId;
        $result->moveNext();
	} // for
    $result->close();
	return $actorlist;
}

/**
 * Importe les donn�es csv $data (avec les s�parateurs pass�s en param�tre)
 * du tableur avec l'id $ssId dans la base de donn�es.
 *
 * @access private
 * @param  integer $ssId l'id du tableur
 * @param  string $data les donn�es au format csv
 * @param  string $line_sep le s�parateur de lignes
 * @param  string $field_sep le s�parateur de colonnes
 * @return mixed boolean or string error
 **/
function _importSpreadSheetCSVData($ssId, $data, $line_sep, $field_sep) {
    $error = "Erreur lors de l'import des donn�es:\n";
    // chargement du tableur
    $mapper = Mapper::singleton('SpreadSheet');
    $sheet = $mapper->load(array('Id'=>$ssId));
    if (!($sheet instanceof SpreadSheet)) {
        return $error . _('Spreadsheet model was not found in the database.');
    }
    // instancier l'objet pour g�n�rer un id
    $entity = $sheet->getEntity();
    if (!($entity instanceof Entity)) {
        return $error . _('Spreadsheet model was not found in the database.');
    }
    $entity = $entity->getName();
    require_once('Objects/' . $entity . '.php');
    $links = call_user_func(array($entity, 'getLinks'));
    // collection de colonnes
    $col = $sheet->getSpreadSheetColumnCollection();
    $col->sort('Order', SORT_ASC);
    $count = $col->getCount();
    $fkeys = array();
    $onetomany = array();
    $manytomany = array();
    // construction de la liste des champs
    $has_countrycity = false;
    for ($i=0; $i<$count; $i++) {
        $column = $col->getItem($i);
        $name = $column->getPropertyName();
        $type = $column->getPropertyType();
        $class = $column->getPropertyClass();
        if ($name == 'CountryCity') {
            $has_countrycity = true;
            continue;
        }
        if ($type == Object::TYPE_ONETOMANY) {
            $mapper = Mapper::singleton($class);
            $fieldname = $links[substr($name, 0, -10)]['field'];
            $onetomany[$i] = array($mapper, $name, $fieldname);
        } else if ($type == Object::TYPE_MANYTOMANY) {
            $manytomany[$i] = $name;
        } else if ($type == Object::TYPE_FKEY && $class != 'CountryCity') {
            $mapper = Mapper::singleton($class);
            $fkeys[$i] = array($mapper, $name);
        }
        $fields[] = $name;
    }
    if ($has_countrycity) {
        $fields[] = 'CountryCity';
    }

    $lines = explode($line_sep, $data);
    $linecount = count($lines);
    // construction des valeurs
    Database::connection()->startTrans();
    for($i = 0; $i < $linecount; $i++) {
        $cells = explode($field_sep, $lines[$i]);
        $cellcount = count($cells);
        if ($cellcount != $count) {
            continue;
        }
        $instance = new $entity();
        $instance->generateId();
        for($j = 0; $j < $cellcount; $j++) {
            $cell = $cells[$j];
            // test: v�rification que les valeurs des fkeys sont correctes
            if (isset($fkeys[$j]) && $cell > 0) {
                list($mapper, $type) = $fkeys[$j];
                if (!$mapper->alreadyExists(array('Id'=>$cell))) {
                    return $error . sprintf(
                       _("Line: %s, column: %s, %s with ID %s not found in the database."),
                       $i+1, $j+1, $type, $cell
                    );
                }
            }
            // cas particulier des countrycity
            if ($fields[$j] == 'CountryCity') {
                $cell = trim(str_replace("'", "", $cell));
                $countryCity = findCountryCityFromString($cell);
                if (!($countryCity instanceof CountryCity)) {
                    return sprintf(
                        _("Line: %s, column: %s, address \"%s\" was not found in the database."),
                        $i+1, $j+1, str_replace('|', ' ', $cell)
                    );
                }
                $cell = $countryCity;
            }
            if (isset($onetomany[$j]) && !empty($cell)) {
                list($mapper, $name, $fieldname) = $onetomany[$j];
                $ids = explode('#', $cell);
                if (count($ids) > 0) {
                    $col = $mapper->loadCollection(array('Id'=>$ids));
                    $count = $col->getCount();
                    for ($k=0; $k<$count; $k++) {
                        $item = $col->getItem($k);
                        $setter = 'set' . $fieldname;
                        $item->$setter($instance->getId());
                        $item->save();
                    }
                    $method = 'set' . $name;
                }
            } else if (isset($manytomany[$j]) && !empty($cell)) {
                $method = 'set' . $manytomany[$j] . 'Ids';
                $cell = explode('#', $cell);
            } else {
                $method = 'set' . $fields[$j];
            }
            if (($entity == 'Product' || $entity == 'AeroProduct')
              || method_exists($instance, $method)) {
                $instance->$method($cell);
            }
        }
        try {
            $instance->save();
        } catch(Exception $e) {
            return $e->getMessage();
        }
        if (method_exists($instance, 'onAfterImport')) {
            $instance->onAfterImport();
        }
    }
    if (Database::connection()->hasFailedTrans()) {
        Database::connection()->rollbackTrans();
        return $error . Database::connection()->errorMsg();
    }
    Database::connection()->completeTrans();
    return true;
}

function findCountryCityFromString($str) {
    list($zip, $city, $country) = explode('|', $str);
    $fake_smarty = false;
    $helper = new CityZipCountryHelper(
        $fake_smarty,
        $zip,
        $country,
        $city
    );
    $result = $helper->findExactMatch();
    return $result;
}

/**
 * Retourne la chaine $str apr�s avoir remplac� les caract�res
 * sp�ciaux &lt;, &gt; et &amp;
 *
 * @access public
 * @param string $str la chaine � �chapper
 * @return string la cha�ne trait�e
 **/
function clean($str){
	return str_replace(array('&gt;', '&lt;', '&amp;'),
					   array('>', '<', '&'), trim($str));
}


//////////////////////////////////////////////////////////////////////////////
/////////////////  PARTIE UTILISEE POUR OPTIM DES APPRO  /////////////////////
//////////////////////////////////////////////////////////////////////////////
/**
 * Retourne les timestamps des 1er jours des semaines encadrant la semaine actuelle
 * @return array of integer
 **/
function getWeekTimeStamps() {
	// Calcul du timestamp correspondant a la date du jour a 00:00:00
	$date = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
	$dayNo = DateTimeTools::getDayValue(time());
    // Nbre de jours a enlever pour atteindre le 1er jour de la semaine
	$dayNberToDecrease = $dayNo - 1;

	// Calcul du timestamp correspondant au 1er jour de la semaine, a 00:00:00
	$dateFirstDayOfWeek = strtotime("-". $dayNberToDecrease . " day", $date);

	// 604800: Nbre de secondes dans une semaine
	$weekTimeStamps = array();
	for ($i=-$_SESSION['passedWeekNumber']+1;$i<=$_SESSION['futureWeekNumber'] + 1;$i++) {
		$weekTimeStamps[$i] = strtotime($i * 7 . " day", $dateFirstDayOfWeek);
	}
	return $weekTimeStamps;
}

/**
 * Retourne le timestamp du dernier jour de la semaine courante (23:59:59)
 * @return integer (timestamp)
 **/
function getWeekEndTimeStamp() {
	// Calcul du timestamp correspondant a la date du jour a 00:00:00
	$date = mktime(0, 0, 0, date('n'), date('j'), date('Y'));
	$dayNo = DateTimeTools::getDayValue(time());
	// Nbre de jours a enlever pour atteindre le 1er jour de la semaine
	$dayNberToDecrease = $dayNo - 1;

	// Calcul du timestamp correspondant au 1er jour de la semaine, a 00:00:00
	$dateFirstDayOfWeek = strtotime("-". $dayNberToDecrease . " day", $date);
	$weekEndTimeStamp = $dateFirstDayOfWeek + 604800;
	return $weekEndTimeStamp;
}

?>
