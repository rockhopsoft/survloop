<?php
/**
  * TreeSurvFormVarieties is a mid-level class with functions to print specific node types,
  * and swap out various language.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.1.2
  */
namespace RockHopSoft\Survloop\Controllers\Tree;

use Illuminate\Http\Request;
use App\Models\SLTree;
use RockHopSoft\Survloop\Controllers\Tree\TreeSurvDataPrint;

class TreeSurvFormWidgets extends TreeSurvDataPrint
{
    protected function nodePrintWidget($curr)
    {
        $ret = '';
        if ($this->blockNodePrintWidget($curr)) {
            // don't show widget
        } elseif ($curr->nodeType == 'Member Profile Basics') {
            $ret .= $this->showProfileBasics();
        } elseif ($curr->nodeType == 'MFA Dialogue') {
            $ret .= ((isset($this->v["mfaMsg"])) ? $this->v["mfaMsg"] : '');
        } elseif (intVal($curr->nodeRow->node_response_set) > 0) {
            $wdgTree = $curr->nodeRow->node_response_set;
            $wdgLmt  = intVal($curr->nodeRow->node_char_limit);
            $this->initSearcher();
            if ($curr->nodeType == 'Search') {
                $ret .= $this->searcher->nodePrintWidgetSearch($curr, $wdgTree);
            } else { // this widget loads via ajax
                $ret .= $this->nodePrintWidgetStd($curr, $wdgTree, $wdgLmt);
            }
        }
        return $ret;
    }

    protected function nodePrintWidgetStd($curr, $wdgTree, $wdgLmt)
    {
        $ret = '';
        $nID = $curr->nID;
        $spin = (($curr->nodeType != 'Incomplete Sess Check') 
            ? $GLOBALS["SL"]->spinner() : '');
        $loadURL = '/records-full/' . $wdgTree;
        $search = (($GLOBALS["SL"]->REQ->has('s')) 
            ? trim($GLOBALS["SL"]->REQ->get('s')) : '');
        $this->widgetAdvSearchUrlSffx($curr);
        if (in_array($curr->nodeType, ['Record Full', 'Record Full Public'])) {
            list($loadURL, $spin) = $this->printWdgtRecord($curr, $wdgTree, $wdgLmt, $spin);
        } elseif ($curr->nodeType == 'Search Featured') {
            $this->initSearcher();
            $ret .= $this->searcher->searchResultsFeatured($search, $wdgTree);
        } elseif ($curr->nodeType == 'Search Results') {
            $loadURL = $this->printWdgtSearchRes($curr, $wdgTree, $wdgLmt, $search);
        } elseif ($curr->nodeType == 'Record Previews') {
            $loadURL = '/record-prevs/' . $wdgTree . '?limit=' . $wdgLmt;
        } elseif ($curr->nodeType == 'Incomplete Sess Check') {
            $loadURL = '/record-check/' . $wdgTree;
        } elseif ($curr->isGraph()) {
            $loadURL = $this->printWdgtGraph($curr, $wdgTree, $wdgLmt);
        } elseif ($curr->nodeType == 'Widget Custom') {
            $loadURL = $this->printWdgtCust($curr, $wdgTree, $wdgLmt);
        }
        $ret .= view(
            'vendor.survloop.forms.formtree-widget', 
            [
                "curr" => $curr,
                "spin" => $spin
            ]
        )->render();
        $GLOBALS["SL"]->pageAJAX .= '$("#n' . $nID 
            . 'ajaxLoad").load("' . $loadURL . '");' . "\n";
        return $ret;
    }

    protected function printWdgtRecord($curr, $wdgTree, $wdgLmt, $spin)
    {
        $cid = -3;
        if ($GLOBALS["SL"]->REQ->has('i')) {
            $cid = intVal($GLOBALS["SL"]->REQ->get('i'));
        } elseif ($this->treeID == $wdgTree && $this->coreID > 0) {
            $cid = $this->coreID;
        }
        //$loadURL .= '?i=' . $cid . (($search != '') ? '&s=' . $search : '');
        $wTree = SLTree::find($wdgTree);
        if ($cid > 0 && $wTree) {
            $xtra = '';
            if ($curr->nodeType == 'Record Full Public') {
                $xtra = '&publicView=1';
            }
            $loadURL = '/' . $wTree->tree_slug . '/read-' 
                . $cid . '/full?ajax=1&wdg=1' . $xtra 
                . $GLOBALS["SL"]->getAnyReqParams();
            $spin = '<br /><br /><center>' . $spin . '</center><br />';
        }
        return [ $loadURL, $spin ];
    }

    protected function printWdgtSearchRes($curr, $wdgTree, $wdgLmt, $search)
    {
        $this->initSearcher();
        $this->searcher->getSearchFilts();
        $tmp = str_replace('[[search]]', $search, $curr->nodeRow->node_prompt_text);
        $curr->nodeRow->node_prompt_text = $GLOBALS["SL"]->extractJava($tmp, $curr->nID);
        $tmp = str_replace('[[search]]', $search, $curr->nodeRow->node_prompt_after);
        $curr->nodeRow->node_prompt_after = $GLOBALS["SL"]->extractJava($tmp, $curr->nID);
        return '/search-results/' . $wdgTree . '?s=' 
            . urlencode($this->searcher->searchTxt) 
            . $this->searcher->searchFiltsURL() 
            . $this->searcher->advSearchUrlSffx;
    }

    protected function printWdgtGraph($curr, $wdgTree, $wdgLmt)
    {
        $GLOBALS["SL"]->x["needsCharts"] = true;
        $loadURL = '/record-graph/' 
            . str_replace(' ', '-', strtolower($curr->nodeType)) 
            . '/' . $wdgTree . '/' . $curr->nodeID;
        $GLOBALS["SL"]->pageAJAX .= 'addGraph("' . $curr->nIDtxt 
            . '", "' . $loadURL .'");'."\n";
        return $loadURL;
    }

    protected function printWdgtCust($curr, $wdgTree, $wdgLmt)
    {
        return '/widget-custom/' . $wdgTree . '/' . $curr->nID . '?txt=' 
            . str_replace($curr->nID, '', $curr->nIDtxt) 
            . $this->sessData->getDataBranchUrl()
            . $this->widgetCustomLoadUrl($curr->nID, $curr->nIDtxt, $curr);
    }

    protected function blockNodePrintWidget($curr)
    {
        $blockWidget = false;
        if ($curr->nodeType == 'Incomplete Sess Check' 
            && isset($this->v["profileUser"]) 
            && isset($this->v["profileUser"]->id)) {
            if (!isset($this->v["uID"]) 
                || $this->v["uID"] != $this->v["profileUser"]->id) {
                $blockWidget = true;
            }
        }
        return $blockWidget;
    }

    protected function widgetAdvSearchUrlSffx($curr)
    {
        if (isset($this->v["profileUser"]) 
            && isset($this->v["profileUser"]->id)) {
            $this->searcher->advSearchUrlSffx .= '&u=' 
                . $this->v["profileUser"]->id;
        } elseif (isset($curr->nodeRow->node_data_branch) 
            && trim($curr->nodeRow->node_data_branch) == 'users') {
            $this->searcher->advSearchUrlSffx .= '&mine=1';
        }
        return true;
    }
    
    // customizable function for what URL is used to load the widget's div
    protected function widgetCustomLoadUrl($nID, $nIDtxt, $curr)
    {
        // if ($nID == ...
        return '';
    }
    
    // customizable function for what content is loaded in the widget's div 
    public function widgetCust(Request $request, $nID = -3)
    {
        $this->survloopInit($request, '');
        $this->loadAllSessData();
        //$this->loadTree();
        $txt = (($request->has('txt')) ? trim($request->get('txt')) : '');
        $nIDtxt = $nID . $txt;
        $branches = (($request->has('branch')) ? trim($request->get('branch')) : '');
        $this->sessData->loadDataBranchFromUrl($branches);
        return $this->widgetCustomRun($nID, $nIDtxt);
    }
    
    // customizable function for what content is loaded in the widget's div 
    public function widgetCustomRun($nID = -3, $nIDtxt)
    {
        // if ($nID == ...
        return '';
    }

    protected function nodePrintWidgetSearch($curr, $wdgTree)
    {
        return $this->searcher->printSearchBar(
            '', 
            $wdgTree, 
            trim($curr->nodeRow->node_prompt_text), 
            trim($curr->nodeRow->node_prompt_after), 
            $curr->nID, 
            0
        );
    }

    protected function nodePrintSignUp($curr)
    {  
        return view(
            'vendor.survloop.forms.formtree-widget-signup', 
            [ "curr" => $curr ]
        )->render();
    }

    protected function nodePrintUploads($curr)
    {
        $this->pageHasUpload[] = $curr->nID;
        return $curr->nodePrompt . '<div class="nFld">' 
            . $this->uploadTool($curr->nID, $curr->nIDtxt) . '</div>';
    }


}