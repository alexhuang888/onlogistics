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

class FormModel extends _FormModel {
    // Constructeur {{{

    /**
     * FormModel::__construct()
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
     * Retourne la collection de paragraphModel du 
     * FormModel tri� selon paragraphOrder.
     *
     * @access public
     * @return Object Collection
     */
    function getParagraphModelCollection($filter=array(), 
        $order=array('ParagraphOrder'=>SORT_ASC))
    {
        $collection = new Collection();
        $paragraphModelMapper = Mapper::singleton('ParagraphModel');
        $linkCol = $this->getLinkFormModelParagraphModelCollection($filter, $order);
        $count = $linkCol->getCount();
        for($i=0 ; $i<$count ; $i++) {
            $item = $linkCol->getItem($i);
            $collection->setItem($item->getParagraphModel());
        }
        $collection->entityName = 'ParagraphModel';
        return $collection;
    }

    /**
     * Retourne un tableau reprenant la structure en arbre du formulaire
     * - paragraph1
     *    - question1
     *        - reponse1_1
     *        - reponse1_2
     *    + question2
     * + paragraph2
     *
     * @param boolean $full true pour le mode edition
     * @access public
     * @return array
     */
    function getTreeViewStructure($full=true)
    {
        require_once('Objects/Question.php');
        $answerTypeConstArray = Question::getAnswerTypeConstArray();
        // racine du TreeView
        $structure = array();
        $structure[0] = $this->getName();
        $structure[1] = '';
        // Ajout des branche paragraphe
        $paragraphCol = $this->getParagraphModelCollection();
        $counti = $paragraphCol->getCount();
        for($i=0 ; $i<$counti ; $i++) {
            $paragraph = $paragraphCol->getItem($i);
            $link = !$full?'#paragraph'.$paragraph->getId():
                            'dispatcher.php?entity=ParagraphModel&action=edit&objID=' . 
                            $paragraph->getId() . '&delete=1&fmID=' . 
                            $this->getId();
                
            $structure[$i+2][0] = $paragraph->getTitle();
            $structure[$i+2][1] = $link;
            // Ajout des branches question
            $questionCol = $paragraph->getQuestionCollection();
            $countj = $questionCol->getCount();
            for($j=0 ; $j<$countj ; $j++) {
                $question = $questionCol->getItem($j);
                $link = !$full?'#question'.$question->getId():
                            'dispatcher.php?entity=Question&action=edit&objID=' . 
                            $question->getId() . '&delete=1&pmID=' . 
                            $paragraph->getId() . '&fmID=' .
                            $this->getId();
                $structure[$i+2][$j+2][0] = $question->getText() . ' ' .
                                _('Answer type') . ' : ' .
                                $answerTypeConstArray[$question->getAnswerType()];
                $structure[$i+2][$j+2][1] = $link;
                // Ajout des branches R�ponse
                if($full) {
                    $answerCol = $question->getAnswerModelCollection();
                    $countk = $answerCol->getCount();
                    for ($k=0 ; $k<$countk ; $k++) {
                        $answer = $answerCol->getItem($k);
                        $structure[$i+2][$j+2][$k+2][0] = $answer->getValue();
                        $structure[$i+2][$j+2][$k+2][1] = 'AnswerModelAddEdit.php?amID=' . 
                                                          $answer->getId() . 
                                                          '&delete=1&qID=' . 
                                                          $question->getId() . '&fmID=' .
                                                          $this->getId();
                    }
                }
            }
        }
        
        return $structure;
    }
    
    /**
     * Retourne le code html du formulaire
     *
     * @param array $givenAnswers les r�ponse (mode edition)
     * @access public
     * @return string
     */
    function getEditableFormModel($givenAnswers=array())
    {
        require_once('Objects/Question.php');
        $answerTypeConstArray = Question::getAnswerTypeConstArray();
        // racine du Formulaire
        $html = '<table width="100%" border="0" '
            . 'cellspacing="0" cellpadding="4" class="form">';
        // Ajout des branche paragraphe
        $paragraphCol = $this->getParagraphModelCollection();
        $counti = $paragraphCol->getCount();
        for($i=0 ; $i<$counti ; $i++) {
            $paragraph = $paragraphCol->getItem($i);
            $html .= '<thead><tr id="paragraph'.$paragraph->getId()
                . '"><td colspan="2">'
                . $paragraph->getTitle() . '</td></tr></thead>';
            
            // Ajout des branches question
            $questionCol = $paragraph->getQuestionCollection();
            $countj = $questionCol->getCount();
            for($j=0 ; $j<$countj ; $j++) {
                $question = $questionCol->getItem($j);
                $html .= '<tr id="question'.$question->getId().'">'
                    . '<td>'.$question->getText().'</td>';
                
                // Ajout des branches R�ponse
                $answerCol = $question->getAnswerModelCollection();
                $countk = $answerCol->getCount();
                $questionName = 'question_' . $question->getId();
                $answerKey = $questionName;
                $questionNameMulti = $questionName . '[]';
                $style = ' style="width:100%"';
                $complement = '';
                $html .= '<td>';
                
                switch ($question->getAnswerType()) {
                    case Question::ANSWER_TYPE_TEXT:
                    $value = isset($givenAnswers[$questionName])?
                        $givenAnswers[$questionName]:'';
                    $html .= '<input type="text" name="' . $questionName
                        .'" value="' . $value . '"' . $style . '></input>';
                    break;
                    
                    case Question::ANSWER_TYPE_SINGLE_CHECKBOX:
                        $complement = ' onclick="checkIsSingle(\''
                            .$questionNameMulti.'\', this);"';
                    case Question::ANSWER_TYPE_CHECKBOX:                        
                    for ($k=0 ; $k<$countk ; $k++) {
                        $answer = $answerCol->getItem($k);
                        $checked = '';
                        if(isset($givenAnswers[$questionName])) {
                            if(is_array($givenAnswers[$questionName])) {
                                if(in_array($answer->getId(), $givenAnswers[$questionName])) {
                                    $checked = ' checked';
                                }
                            } else {
                                if($givenAnswers[$questionName] == $answer->getId()) {
                                    $checked = ' checked';
                                }
                            }
                        }
                        $html .= '<input type="checkbox" name="'. $questionNameMulti
                            . '" value="' . $answer->getId().'"'
                            . $complement . $checked . '>' . $answer->getValue()
                            . '</input>';
                    }
                    break;
                    case Question::ANSWER_TYPE_MULTI_SELECT:
                        $questionName .= '[]';
                        $complement = ' multiple size= "3"';
                    case Question::ANSWER_TYPE_SINGLE_SELECT:
                    $html .= '<select name="' . $questionName . '"'
                            . $complement . $style . '>';
                    for ($k=0 ; $k<$countk ; $k++) {
                        $answer = $answerCol->getItem($k);
                        $selected = '';
                        if(isset($givenAnswers[$answerKey])) {
                            if(is_array($givenAnswers[$answerKey])) { 
                                if(in_array($answer->getId(), $givenAnswers[$answerKey])) {
                                    $selected = ' selected';
                                }
                            } else {
                                if($givenAnswers[$answerKey]==$answer->getId()) {
                                    $selected = ' selected';
                                }
                            }
                        }
                        
                        $html .= '<option value="' . $answer->getId() 
                            . '"' . $selected . '>' . $answer->getValue() . '</option>';
                    }
                    $html .= '</select>';
                    break;
                    case Question::ANSWER_TYPE_RADIO:
                    for ($k=0 ; $k<$countk ; $k++) {
                        //$checked = $k==0?' checked':'';
                        $answer = $answerCol->getItem($k);
                        $checked = (isset($givenAnswers[$questionName]) && $givenAnswers[$questionName]==$answer->getId())?' checked':$k==0?' checked':'';
                        
                        $html .= '<input type="radio" name="'. $questionName
                            . '" value="' . $answer->getId().'"'
                            . $checked . '>' . $answer->getValue()
                            . '</input>';
                    }
                    break;
                }
                
                $html .= '</td></tr>';
            }
        }
        $html .= '</table>';
        return $html;
    }

}

?>