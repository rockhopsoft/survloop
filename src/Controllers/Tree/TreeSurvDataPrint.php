<?php
/**
  * TreeSurvDataPrint is a mid-level class using a standard branching tree, which provides
  * functions to print data from the database into a survey or page.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.13
  */
namespace SurvLoop\Controllers\Tree;

use Illuminate\Http\Request;
use App\Models\SLNodeResponses;
use SurvLoop\Controllers\Globals\Globals;
use SurvLoop\Controllers\Tree\TreeSurvFormUtils;

class TreeSurvDataPrint extends TreeSurvFormUtils
{

    protected function nodePrintData($nID, $nIDtxt, $curr, $tbl, $fld, $currVisib, 
        $tmpSubTier, $nodePromptText, $currNodeSessData, $itemID, $itemInd)
    {
        if (in_array($curr->nodeType, ['Data Print', 'Data Print Row'])) {
            
            return $this->nodePrintDataRow(
                $nID, 
                $nIDtxt, 
                $curr, 
                $tbl, 
                $fld, 
                $currNodeSessData, 
                $itemID, 
                $itemInd
            );
            
        } elseif (in_array($curr->nodeType, ['Data Print Block', 'Data Print Columns'])) {
            
            return $this->nodePrintDataBlock(
                $nID, 
                $nIDtxt, 
                $curr, 
                $tbl, 
                $fld, 
                $currVisib, 
                $tmpSubTier, 
                $nodePromptText,
                $currNodeSessData
            );
            
        } elseif (in_array($curr->nodeType, ['Print Vert Progress'])) {
            
            return $this->nodePrintVertProgress(
                $nID, 
                $tbl, 
                $fld, 
                $tmpSubTier, 
                $nodePromptText
            );
            
        }

        return false;
    }

    protected function nodePrintDataRow($nID, $nIDtxt, $curr, $tbl, $fld, 
        $currNodeSessData = null, $itemID = 0, $itemInd = 0)
    {
        if (!$this->checkFldDataPerms($curr->getFldRow()) 
            || !$this->checkViewDataPerms($curr->getFldRow())) {
            if ($curr->nodeType == 'Data Print Row') {
                return [];
            }
        } else {
            if (isset($GLOBALS["SL"]->formTree->tree_id) 
                && in_array($nID, $this->checkboxNodes)) {
                $currNodeSessData = $this->valListArr(
                    $this->sessData->currSessDataCheckbox($nID, $tbl, $fld)
                );
                if ($this->hasCycleAncestorActive($nID)) {
                    $cycInd = $GLOBALS["SL"]->currCyc["cyc"][1];
                    $cycInd = intVal(str_replace('cyc', '', $cycInd));
                    if (isset($currNodeSessData[$cycInd])) {
                        $currNodeSessData = $currNodeSessData[$cycInd];
                    }
                }
            }
            $fldRow = $GLOBALS["SL"]->getFldRowFromFullName($tbl, $fld);
            $printRow = true;
            if ((!is_array($currNodeSessData) 
                    && (!$currNodeSessData || trim($currNodeSessData) == ''))
                || (is_array($currNodeSessData) && sizeof($currNodeSessData) == 0)) {
                $printRow = false;
            } else {
                $isDefaultNonArray = (!is_array($currNodeSessData) 
                    && trim($currNodeSessData) != '' 
                    && trim($currNodeSessData) == trim($curr->nodeRow->node_default));
                $isDefaultArray = (is_array($currNodeSessData) 
                    && trim($currNodeSessData[0]) == trim($curr->nodeRow->node_default));
                if (isset($curr->nodeRow->node_default) 
                    && trim($curr->nodeRow->node_default) != '' 
                    && ($isDefaultNonArray || $isDefaultArray)) {
                    $printRow = false;
                }
            }
            if ($printRow) {
                $deetLabel = (($fldRow && isset($fldRow->fld_eng)) ? $fldRow->fld_eng : '');
                $deetLabel = $this->swapLabels($nIDtxt, $deetLabel, $itemID, $itemInd);
                
                $deetVal = $GLOBALS["SL"]->printResponse($tbl, $fld, $currNodeSessData, $fldRow);
                $deetVal = $this->printValCustom($nID, $deetVal);
                if (isset($GLOBALS["SL"]->formTree->tree_id)) {
                    $tID = $GLOBALS["SL"]->formTree->tree_id;
                    $lab = $GLOBALS["SL"]->getFldNodeQuestion($tbl, $fld, $tID);
                    if (trim($lab) != '') {
                        $lab = $this->swapLabels($nIDtxt, $lab, $itemID, $itemInd);
                        if (strip_tags($deetLabel) != strip_tags($lab)) {
                            $deetLabel = '<a id="hidivBtn' . $nIDtxt 
                                .'" class="hidivBtn slGrey" href="javascript:;">' 
                                . $deetLabel . '</a><div id="hidiv' . $nIDtxt 
                                . '" class="disNon">"' . $lab . '"</div>';
                        }
                    }
                }
                if ($curr->nodeType == 'Data Print') {
                    $ret .= '<div id="nLabel' . $nIDtxt 
                        . '" class="nPrompt"><span class="slGrey mR10">' 
                        . $deetLabel . '</span>' . $deetVal . '</div>';
                } elseif ($curr->nodeType == 'Data Print Row') {
                    return [ $deetLabel, $deetVal, $nID ];
                }
            } elseif ($curr->nodeType == 'Data Print Row') {
                return [];
            }
        }
        return [];
    }

    protected function nodePrintDataBlock($nID, $nIDtxt, $curr, $tbl, $fld, 
        $currVisib, $tmpSubTier, $nodePromptText, $currNodeSessData = null)
    {
        $ret = '';
        $deets = [];
        if (sizeof($tmpSubTier[1]) > 0) {
            foreach ($tmpSubTier[1] as $childNode) {
                $kidID = $childNode[0];
                if ($this->allNodes[$kidID]->nodeType == 'Data Print Row') {

                    $newDeet = $this->printNodePublic($kidID, $childNode, $currVisib);
                    if (is_array($newDeet) 
                        && sizeof($newDeet) > 0 
                        && trim($newDeet[0]) != '' 
                        && trim($newDeet[1]) != '') {
                        $deets[] = $newDeet;
                    }

                } elseif ($this->allNodes[$kidID]->isDataManip()) {

                    $this->loadManipBranch($kidID, ($currVisib == 1));
                    foreach ($childNode[1] as $gNode) {
                        if ($this->allNodes[$gNode[0]]->nodeType == 'Data Print Row') {
                            $deet = $this->printNodePublic($gNode[0], $gNode, $currVisib);
                            if ($deet && is_array($deet) && sizeof($deet) > 0) {
                                $deets[] = $deet;
                            }
                        }
                    }
                    $this->closeManipBranch($kidID);

                } elseif ($this->allNodes[$kidID]->isLoopCycle()) {

                    list($tbl, $fld, $newVal) = $this->nodeBranchInfo($nID, $this->allNodes[$kidID]);
                    $loop = str_replace('LoopItems::', '', $this->allNodes[$kidID]->responseSet);
                    $loopCycle = $this->sessData->getLoopRows($loop);
                    // if this is a simple loop of a just a table's rows
                    if (trim($loop) == '' 
                        && isset($this->allNodes[$kidID]->dataBranch)
                        && trim($this->allNodes[$kidID]->dataBranch) != '') {
                        $s = sizeof($this->sessData->dataBranches);
                        if ($s > 0) {
                            $branch = $this->sessData->dataBranches[($s-1)];
                            if (isset($branch["itemID"]) && intVal($branch["itemID"]) > 0) {
                                $loopCycle = $this->sessData->getChildRows(
                                    $branch["branch"], 
                                    $branch["itemID"], 
                                    $this->allNodes[$kidID]->dataBranch
                                );
                            }
                        }
                    }
                    if (sizeof($childNode[1]) > 0 && $loopCycle && sizeof($loopCycle) > 0) {
                        $GLOBALS["SL"]->currCyc["cyc"][0] = $tbl;
                        if ($loop != '') {
                            $GLOBALS["SL"]->currCyc["cyc"][0] = $GLOBALS["SL"]
                                ->getLoopTable($loop);
                        }
                        foreach ($loopCycle as $i => $loopItem) {
                            $GLOBALS["SL"]->currCyc["cyc"][1] = 'cyc' . $i;
                            $GLOBALS["SL"]->currCyc["cyc"][2] = $loopItem->getKey();
                            $this->sessData->startTmpDataBranch($tbl, $loopItem->getKey());
                            $label = (1+$i) . ')';
                            if ($loop != '') {
                                $GLOBALS["SL"]->fakeSessLoopCycle($loop, $loopItem->getKey());
                                $label = $this->getLoopItemLabel($loop, $loopItem, $i);
                            }
                            foreach ($childNode[1] as $gNode) {
                                if ($this->allNodes[$gNode[0]]->nodeType == 'Data Print Row') {
                                    $deet = $this->printNodePublic($gNode[0], $gNode, $currVisib);
                                    if (isset($deet[0]) 
                                        && trim($deet[0]) != '' 
                                        && trim($deet[1]) != '') {
                                        $deet[0] = $label . ' ' . $deet[0];
                                        $deets[] = $deet;
                                    }
                                }
                            }
                            if ($loop != '') {
                                $GLOBALS["SL"]->removeFakeSessLoopCycle($loop, $loopItem->getKey());
                            }
                            $this->sessData->endTmpDataBranch($tbl);
                            $GLOBALS["SL"]->currCyc["cyc"][1] = '';
                            $GLOBALS["SL"]->currCyc["cyc"][2] = -3;
                        }
                        $GLOBALS["SL"]->currCyc["cyc"][0] = '';
                    }
                    
                }
            }
        }
        //if ($this->allNodes[$curr->parentID]->isLoopCycle()) {
        //    $label = $this->getLoopItemLabel($GLOBALS["SL"]->closestLoop["loop"], 
        //        $this->sessData->getRowById($GLOBALS["SL"]->closestLoop["obj"]->data_loop_table, $itemID), 
        //        $itemInd);
        //    $nodePromptText = $label . ' ' . $nodePromptText;
        //}
        if (sizeof($deets) > 0) {
            $promptTxt = strip_tags($nodePromptText);
            if ($curr->nodeType == 'Data Print Block') {
                $ret .= $this->printReportDeetsBlock($deets, $promptTxt, $nID);
            } else {
                $colCnt = 2;
                $ret .= $this->printReportDeetsBlockCols($deets, $promptTxt, $colCnt, $nID);
            }
        }
        return $ret;
    }

    protected function nodePrintVertProgress($nID, $tbl, $fld, $tmpSubTier, $nodePromptText)
    {
        $deets = [];
        foreach ($tmpSubTier[1] as $cNode) {
            $childNode = $this->allNodes[$cNode[0]];
            if ($childNode->nodeType == 'Data Print Row') {
                list($tbl, $fld) = $childNode->getTblFld();
                list($itemInd, $itemID) = $this->sessData->currSessDataPos($tbl);
                if ($itemID > 0 && isset($this->sessData->dataSets[$tbl])) {
                    $tblSet = $this->sessData->dataSets[$tbl]; 
                    if (isset($tblSet[$itemInd]) > 0) {
                        $dateTime = 0;
                        if (isset($tblSet[$itemInd]->{ $fld })
                            && trim($tblSet[$itemInd]->{ $fld }) != '') {
                            $dateTime = strtotime($tblSet[$itemInd]->{ $fld });
                        }
                        if (!isset($childNode->nodeRow->node_default)
                            || trim($childNode->nodeRow->node_default) != trim($dateTime)) {
                            $fldRow = $GLOBALS["SL"]->getTblFldRow('', $tbl, $fld);
                            if ($fldRow && isset($fldRow->fld_eng)) {
                                $deets[] = [ $fldRow->fld_eng, $dateTime, $cNode[0] ];
                            }
                        }
                    }
                }
            }
        }
        return $this->printReportDeetsVertProg($deets, strip_tags($nodePromptText), $nID);
    }

}
