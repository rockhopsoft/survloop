<?php
/**
  * TreeSurvReport is a mid-level class with functions related to generating reports within a tree.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;
use App\Models\SLSessEmojis;
use SurvLoop\Controllers\TreeSurvBasicNav;

class TreeSurvReport extends TreeSurvBasicNav
{
    public function printAdminReport($coreID)
    {
        $this->v["cID"] = $coreID;
        return $this->printFullReport('', true);
    }
    
    public function printFullReport($reportType = '', $isAdmin = false, $inForms = false)
    {
        return '';
    }
    
    public function printPreviewReportCustom($isAdmin = false)
    {
        return '';
    }
    
    public function printPreviewReport($isAdmin = false)
    {
        $ret = $this->printPreviewReportCustom($isAdmin);
        if (trim($ret) != '') {
            return $ret;
        }
        $fldNames = $found = [];
        if (sizeof($this->nodesRawOrder) > 0) {
            foreach ($this->nodesRawOrder as $i => $nID) {
                if (isset($this->allNodes[$nID]) && $this->allNodes[$nID]->isRequired()) {
                    $tblFld = $this->allNodes[$nID]->getTblFld();
                    $fldNames[] = [ $tblFld[0], $tblFld[1] ];
                }
            }
        }
        if (sizeof($fldNames) > 0) {
            foreach ($fldNames as $i => $fld) {
                if (isset($this->sessData->dataSets[$fld[0]]) && sizeof($this->sessData->dataSets[$fld[0]]) > 0
                    && isset($this->sessData->dataSets[$fld[0]][0]->{ $fld[1] }) && sizeof($found) < 6) {
                    $found[] = $fld[1];
                    $ret .= '<span class="mR20">' . $this->sessData->dataSets[$fld[0]][0]->{ $fld[1] } . '</span>';
                }
            }
        }
        return $ret;
    }
    
    public function ajaxEmojiTag(Request $request, $recID = -3, $defID = -3)
    {
        if ($recID <= 0) {
            return '';
        }
        $this->survLoopInit($request, '');
        if ($this->v["uID"] <= 0) {
            return '<h4><i>Please <a href="/login">Login</a></i></h4>';
        }
        $this->loadSessionData($GLOBALS["SL"]->coreTbl, $recID);
        $this->loadEmojiTags($defID);
        if (sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 && $recID > 0) {
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $i => $emo) {
                if ($emo["id"] == $defID) {
                    if (isset($this->emojiTagUsrs[$emo["id"]]) 
                        && in_array($this->v["uID"], $this->emojiTagUsrs[$emo["id"]])) {
                        SLSessEmojis::where('SessEmoRecID', $this->coreID)
                            ->where('SessEmoDefID', $emo["id"])
                            ->where('SessEmoTreeID', $this->treeID)
                            ->where('SessEmoUserID', $this->v["uID"])
                            ->delete();
                        $this->emojiTagOff($emo["id"]);
                    } else {
                        $newTag = new SLSessEmojis;
                        $newTag->SessEmoRecID  = $this->coreID;
                        $newTag->SessEmoDefID  = $emo["id"];
                        $newTag->SessEmoTreeID = $this->treeID;
                        $newTag->SessEmoUserID = $this->v["uID"];
                        $newTag->save();
                        $this->emojiTagOn($emo["id"]);
                    }
                }
            }
            $this->loadEmojiTags($defID);
        }
        $isActive = false;
        if (isset($GLOBALS["SL"]->treeSettings['emojis']) && sizeof($GLOBALS["SL"]->treeSettings["emojis"]) > 0) {
            foreach ($GLOBALS["SL"]->treeSettings["emojis"] as $emo) {
                if ($emo["id"] == $defID) {
                    if ($this->v["uID"] > 0 && isset($this->emojiTagUsrs[$defID])
                        && in_array($this->v["uID"], $this->emojiTagUsrs[$defID])) {
                        $isActive = true;
                    }
                    return view('vendor.survloop.inc-emoji-tag', [
                        "spot"     => 't' . $this->treeID . 'r' . $this->coreID, 
                        "emo"      => $emo, 
                        "cnt"      => sizeof($this->emojiTagUsrs[$defID]),
                        "isActive" => $isActive
                        ])->render();
                }
            }
        }
        return '';
    }
    
    public function emojiTagOn($defID = -3)
    {
        return true;
    }
    
    public function emojiTagOff($defID = -3)
    {
        return true;
    }
    
    protected function loadEmojiTags($defID = -3)
    {
        if (isset($GLOBALS["SL"]->treeSettings['emojis']) 
            && sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 && $this->coreID > 0) {
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $emo) {
                if ($defID <= 0 || $emo["id"] == $defID) {
                    $this->emojiTagUsrs[$emo["id"]] = [];
                    $chk = SLSessEmojis::where('SessEmoRecID', $this->coreID)
                        ->where('SessEmoDefID', $emo["id"])
                        ->where('SessEmoTreeID', $this->treeID)
                        ->get();
                    if ($chk->isNotEmpty()) {
                        foreach ($chk as $tag) {
                            $this->emojiTagUsrs[$emo["id"]][] = $tag->SessEmoUserID;
                        }
                    }
                }
            }
        }
        return true;
    }
    
    protected function printEmojiTags()
    {
        $ret = '';
        $this->loadEmojiTags();
        if (isset($GLOBALS["SL"]->treeSettings['emojis']) && sizeof($GLOBALS["SL"]->treeSettings['emojis']) > 0 
            && $this->coreID > 0) {
            $admPower = ($this->v["user"] && $this->v["user"]->hasRole('administrator|staff'));
            $spot = 't' . $this->treeID . 'r' . $this->coreID;
            foreach ($GLOBALS["SL"]->treeSettings['emojis'] as $emo) {
                if (!$emo["admin"] || $admPower) {
                    $GLOBALS["SL"]->pageAJAX .= '$(document).on("click", "#' . $spot . 'e' . $emo["id"] 
                        . '", function() { $("#' . $spot . 'e' . $emo["id"] . 'Tag").load("/ajax-emoji-tag/' 
                        . $this->treeID . '/' . $this->coreID . '/' . $emo["id"] . '/"); });' . "\n";
                }
            }
            $ret .= view('vendor.survloop.inc-emoji-tags', [
                "spot"     => $spot, 
                "emojis"   => $GLOBALS["SL"]->treeSettings["emojis"], 
                "users"    => $this->emojiTagUsrs,
                "uID"      => (($this->v["uID"] > 0) ? $this->v["uID"] : -3),
                "admPower" => $admPower
            ])->render();
        }
        return $ret;
    }
    
    protected function fillGlossary()
    {
        $this->v["glossaryList"] = [];
        return true;
    }
    
    protected function printGlossary()
    {
        if (!isset($this->v["glossaryList"]) || sizeof($this->v["glossaryList"]) == 0) $this->fillGlossary();
        if (sizeof($this->v["glossaryList"]) > 0) {
            $ret = '<h3 class="mT0 mB20 slBlueDark">Glossary of Terms</h3><div class="glossaryList">';
            foreach ($this->v["glossaryList"] as $i => $gloss) {
                $ret .= '<div class="row' . (($i%2 == 0) ? ' row2' : '') . ' pT15 pB15"><div class="col-md-3">' 
                    . $gloss[0] . '</div><div class="col-md-9">' . ((isset($gloss[1])) ? $gloss[1] : '') 
                    . '</div></div>';
            }
            return $ret . '</div>';
        }
        return '';
    }
    
    protected function swapSeo($str)
    {
        return str_replace('[coreID]', $this->corePublicID, str_replace('[cID]', $this->corePublicID, 
            str_replace('#1111', '#' . $this->corePublicID, $str)));
    }
    
    protected function runPageLoad($nID)
    {
        if (!$this->isPage && $GLOBALS["SL"]->treeRow->TreeOpts%13 == 0) { // report
            $GLOBALS["SL"]->sysOpts['meta-title'] = $this->swapSeo($GLOBALS["SL"]->sysOpts['meta-title']);
            $GLOBALS["SL"]->sysOpts['meta-desc'] = $this->swapSeo($GLOBALS["SL"]->sysOpts['meta-desc']);
        }
        return true;
    }
    
    protected function runPageExtra($nID) { return true; }
    
    protected function printNodePageFoot()
    {
        return (isset($GLOBALS["SL"]->sysOpts["footer-master"]) ? $GLOBALS["SL"]->sysOpts["footer-master"] : '');
    }
    
    protected function printCurrRecMgmt()
    {
        $recDesc = '';
        if (isset($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) 
            && sizeof($this->sessData->dataSets[$GLOBALS["SL"]->coreTbl]) > 0) {
            $recDesc = trim($this->getTableRecLabel($GLOBALS["SL"]->coreTbl, 
                $this->sessData->dataSets[$GLOBALS["SL"]->coreTbl][0]));
        }
        return view('vendor.survloop.formfoot-record-mgmt', [
            "coreID"          => $this->coreID,
            "treeID"          => $this->treeID,
            "multipleRecords" => $this->v["multipleRecords"],
            "isUser"          => ($this->v["uID"] > 0),
            "recDesc"         => $recDesc
            ])->render();
    }
    
    public function chkDeets($deets)
    {
        $new = [];
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                if (isset($deet[0]) && trim($deet[0]) != '') {
                    $new[] = $deet;
                }
            }
        }
        return $new;
    }
    
    public function printReportDeetsBlock($deets, $blockName = '', $nID = -3)
    {
        $deets = $this->chkDeets($deets);
        return view('vendor.survloop.inc-report-deets', [
            "nID"       => $nID,
            "deets"     => $deets,
            "blockName" => $blockName
            ])->render();
    }
    
    public function printReportDeetsBlockCols($deets, $blockName = '', $cols = 2, $nID = -3)
    {
        $deets = $this->chkDeets($deets);
        $deetCols = $deetsTots = $deetsTotCols = [];
        $colChars = (($cols == 2) ? 37 : (($cols == 3) ? 24 : 16));
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                $size = 0; //strlen($deet[0]);
                if (sizeof($deet) > 1 && isset($deet[1]) && $size < strlen($deet[1])) {
                    $size = strlen($deet[1]);
                }
                if ($size > $colChars) {
                    $size -= $colChars;
                } else {
                    $size = 0;
                }
                $size = ($size/$colChars)+2;
                if ($i == 0) {
                    $deetsTots[$i] = $size;
                } else {
                    $deetsTots[$i] = $deetsTots[$i-1]+$size;
                }
            }
            for ($c = 0; $c < $cols; $c++) {
                $deetCols[$c] = [];
                $deetsTotCols[$c] = [ (($c/$cols)*$deetsTots[sizeof($deetsTots)-1]), -3 ];
            }
            $c = $deetsTotCols[0][1] = 0;
            foreach ($deets as $i => $deet) {
                $chk = 1+$c;
                if ($chk < $cols && $deetsTotCols[$chk][1] < 0 && $deetsTotCols[$chk][0] < $deetsTots[$i]
                    && sizeof($deetCols[$c]) > 0) {
                    $deetsTotCols[$chk][1] = $i;
                    $c++;
                }
                $deetCols[$c][] = $deet;
            }
        }
        return view('vendor.survloop.inc-report-deets-cols', [
            "nID"       => $nID,
            "deetCols"  => $deetCols,
            "blockName" => $blockName,
            "colWidth"  => $GLOBALS["SL"]->getColsWidth($cols)
            ])->render();
    }
    
    public function printReportDeetsVertProg($deets, $blockName = '', $nID = -3)
    {
        $last = 0;
        if (sizeof($deets) > 0) {
            foreach ($deets as $i => $deet) {
                if (isset($deet[1]) && intVal($deet[1]) > 0) {
                    $last = $i;
                }
            }
        }
        return view('vendor.survloop.inc-report-deets-vert-prog', [
            "nID"       => $nID,
            "deets"     => $deets,
            "blockName" => $blockName,
            "last"      => $last
            ])->render();
    }
    
}
