<?php
/**
  * SystemDefinitions loads and manages key SurvLoop system variables.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.0.1
  */
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;
use App\Models\SLDefinitions;
use SurvLoop\Controllers\SystemDefinitionsInit;

class SystemDefinitions extends SystemDefinitionsInit
{
    public $dbID = 1;
    public $v    = [];
    
    public function checkDefInstalls()
    {
        $this->chkAppUrl();
        $this->checkStyleDefs();
        $this->checkSysDefs();
        return true;
    }
    
    public function loadCss($dbID = 1)
    {
        if ($dbID <= 0) {
            $dbID = $this->dbID;
        }
        $this->v["css"] = [];
        $cssRaw = SLDefinitions::where('DefDatabase', $dbID)
            ->where('DefSet', 'Style Settings')
            ->orderBy('DefOrder')
            ->get();
        if ($cssRaw->isEmpty()) {
            $dbID = 1;
            $cssRaw = SLDefinitions::where('DefDatabase', $dbID)
                ->where('DefSet', 'Style Settings')
                ->orderBy('DefOrder')
                ->get();
        }
        if ($cssRaw->isNotEmpty()) {
            foreach ($cssRaw as $i => $c) {
                $this->v["css"][$c->DefSubset] = $c->DefDescription;
            }
        }
        return $this->checkStyleDefs($this->v["css"]);
    }
    
    protected function chkSysReqs()
    {
        $GLOBALS["SL"]->loadStates();
        $GLOBALS["SL"]->importZipsUS();
        if (isset($GLOBALS["SL"]->sysOpts["has-canada"]) && intVal($GLOBALS["SL"]->sysOpts["has-canada"]) == 1) {
            $GLOBALS["SL"]->importZipsCanada();
        }
        return true;
    }
    
    public function prepSysSettings(Request $request, $dbID = 1)
    {
        if ($dbID <= 0) {
            $dbID = $this->dbID;
        }
        $this->v["settingsList"] = $this->getDefaultSys();
        $this->v["stylesList"] = $this->getDefaultStyles();
        if (!session()->has('chkSysVars') || $request->has('refresh')) {
            $this->checkSysDefs();
            $this->checkStyleDefs();
            $this->chkSysReqs();
            session()->put('chkSysVars', 1);
        }
        $this->v["sysStyles"] = SLDefinitions::where('DefDatabase', $dbID)
            ->where('DefSet', 'Style Settings')
            ->orderBy('DefOrder')    
            ->get();
        $this->v["custCSS"] = SLDefinitions::where('DefDatabase', $dbID)
            ->where('DefSet', 'Style CSS')
            ->where('DefSubset', 'main')
            ->first();
        if (!$this->v["custCSS"] || !isset($this->v["custCSS"]->DefID)) {
            $this->v["custCSS"] = new SLDefinitions;
            $this->v["custCSS"]->DefDatabase = $dbID;
            $this->v["custCSS"]->DefSet      = 'Style CSS';
            $this->v["custCSS"]->DefSubset   = 'main';
            $this->v["custCSS"]->save();
        }
        $this->v["custCSSemail"] = SLDefinitions::where('DefDatabase', $dbID)
            ->where('DefSet', 'Style CSS')
            ->where('DefSubset', 'email')
            ->first();
        if (!$this->v["custCSSemail"] || !isset($this->v["custCSSemail"]->DefID)) {
            $this->v["custCSSemail"] = new SLDefinitions;
            $this->v["custCSSemail"]->DefDatabase = $dbID;
            $this->v["custCSSemail"]->DefSet      = 'Style CSS';
            $this->v["custCSSemail"]->DefSubset   = 'email';
            $this->v["custCSSemail"]->save();
        }
        $this->v["rawSettings"] = SLDefinitions::where('DefSet', 'Custom Settings')
            ->orderBy('DefOrder', 'asc')
            ->get();
        if ($request->has('sub')) {
            foreach ($GLOBALS["SL"]->sysOpts as $opt => $val) {
                if (isset($this->v["settingsList"][$opt])) {
                    $new = '';
                    if ($request->has('sys-' . $opt)) {
                        $new = $request->get('sys-' . $opt);
                    }
                    if ($opt == 'meta-title' && $request->has('pageTitle')) {
                        $new = $request->get('pageTitle');
                    } elseif ($opt == 'meta-desc' && $request->has('pageDesc')) {
                        $new = $request->get('pageDesc');
                    } elseif ($opt == 'meta-keywords' && $request->has('pageKey')) {
                        $new = $request->get('pageKey');
                    } elseif ($opt == 'meta-img' && $request->has('pageImg')) {
                        $new = $request->get('pageImg');
                    }
                    if ($new != '') {
                        $GLOBALS["SL"]->sysOpts[$opt] = $new;
                        SLDefinitions::where('DefDatabase', $dbID)
                            ->where('DefSet', 'System Settings')
                            ->where('DefSubset', $opt)
                            ->update(['DefDescription' => $new]);
                    }
                }
            }
            foreach ($this->v["sysStyles"] as $opt) {
                if (isset($this->v["stylesList"][$opt->DefSubset]) && $request->has('sty-' . $opt->DefSubset)) {
                    $opt->DefDescription = $request->get('sty-' . $opt->DefSubset);
                    $opt->save();
                }
            }
            $this->v["custCSS"]->DefDescription = trim($request->get('sys-cust-css'));
            $this->v["custCSS"]->save();
            $this->v["custCSSemail"]->DefDescription = trim($request->get('sys-cust-css-email'));
            $this->v["custCSSemail"]->save();
            foreach ($this->v["rawSettings"] as $i => $s) {
                if ($request->has('setting' . $i . '')) {
                    $s->DefValue = $request->get('setting' . $i . '');
                    $s->save();
                }
            }
        }
        $tmp = [];
        if ($this->v["sysStyles"]->isNotEmpty()) {
            foreach ($this->v["sysStyles"] as $sty) {
                $tmp[$sty->DefSubset] = $sty->DefDescription;
            }
        }
        $this->v["sysStyles"] = $tmp;
        return true;
    }
    
    protected function checkStyleDefs($css = [], $dbID = 1)
    {
        if ($dbID <= 0) {
            $dbID = $this->dbID;
        }
        $defaults = $this->getDefaultStyles();
        foreach ($defaults as $key => $val) {
            if (!isset($css[$key])) {
                $css[$key] = $val[0];
            }
            $dbID = $this->dbID;
            //if ($this->dbID == 3 && $GLOBALS["SL"]->sysOpts["cust-abbr"] == 'SurvLoop') $dbID = 1;
            $chk = SLDefinitions::where('DefDatabase', $dbID)
                ->where('DefSet', 'Style Settings')
                ->where('DefSubset', $key)
                ->first();
            if (!$chk || !isset($chk->DefSet)) {
                $cssNew = new SLDefinitions;
                $cssNew->DefDatabase = $dbID;
                $cssNew->DefSet = 'Style Settings';
                $cssNew->DefSubset = $key;
                $cssNew->DefDescription = $val[0];
                $cssNew->save();
            }
        }
        return $css;
    }
    
    protected function checkSysDefs($sys = [], $dbID = 1)
    {
        if ($dbID <= 0) {
            $dbID = $this->dbID;
        }
        $defaults = $this->getDefaultSys();
        foreach ($defaults as $key => $val) {
            if (!isset($css[$key])) {
                $sys[$key] = $val[1];
            }
            $chk = SLDefinitions::where('DefDatabase', $dbID)
                ->where('DefSet', 'System Settings')
                ->where('DefSubset', $key)
                ->first();
            if (!$chk || !isset($chk->DefSet)) {
                $cssNew = new SLDefinitions;
                $cssNew->DefDatabase = $dbID;
                $cssNew->DefSet = 'System Settings';
                $cssNew->DefSubset = $key;
                $cssNew->DefDescription = $val[1];
                $cssNew->save();
            }
        }
        $grps = [
            ['administrator', 'Administrator',
                'Highest system administrative privileges, can add, remove, and change permissions of other users'],
            ['databaser',     'Database Designer',      'Permissions to make edits in the database designing tools'],
            ['staff',         'Staff/Analyst',          'Full staff priveleges, can view but not edit technical specs'],
            ['partner',       'Partner Member',         'Basic permission to pages and tools just for partners'],
            ['volunteer',     'Volunteer',              'Basic permission to pages and tools just for volunteers']
            ];
        foreach ($grps as $i => $grp) {
            $chk = SLDefinitions::where('DefDatabase', $dbID)
                ->where('DefSet', 'User Roles')
                ->where('DefSubset', $grp[0])
                ->first();
            if (!$chk || !isset($chk->DefSet)) {
                $chk = new SLDefinitions;
                $chk->DefDatabase = $dbID;
                $chk->DefSet = 'User Roles';
                $chk->DefSubset  = $grp[0];
            }
            $chk->DefValue       = $grp[1];
            $chk->DefDescription = $grp[2];
            $chk->DefOrder       = $i;
            $chk->save();
        }
        return $sys;
    }
    
    protected function chkAppUrl($dbID = 1)
    {
        if ($dbID <= 0) {
            $dbID = $this->dbID;
        }
        $appUrl = SLDefinitions::where('DefDatabase', $dbID)
            ->where('DefSet', 'System Settings')
            ->where('DefSubset', 'app-url')
            ->first();
        if (!$appUrl) {
            $appUrl = new SLDefinitions;
            $appUrl->DefDatabase = $dbID;
            $appUrl->DefSet = 'System Settings';
            $appUrl->DefSubset = 'app-url';
            $appUrl->DefDescription = $_SERVER["APP_URL"];
        } elseif (!isset($appUrl->DefDescription) || trim($appUrl->DefDescription) == '') {
            $appUrl->DefDescription = $_SERVER["APP_URL"];
        }
        $appUrl->save();
        return true;
    }
    
}