<?php
/**
  * SystemDefinitions loads and manages key Survloop system variables.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.1
  */
namespace RockHopSoft\Survloop\Controllers;

use Illuminate\Http\Request;
use App\Models\SLDefinitions;
use RockHopSoft\Survloop\Controllers\SystemDefinitionsInit;

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
        $cssRaw = SLDefinitions::where('def_database', $dbID)
            ->where('def_set', 'Style Settings')
            ->orderBy('def_order')
            ->get();
        if ($cssRaw->isEmpty()) {
            $dbID = 1;
            $cssRaw = SLDefinitions::where('def_database', $dbID)
                ->where('def_set', 'Style Settings')
                ->orderBy('def_order')
                ->get();
        }
        if ($cssRaw->isNotEmpty()) {
            foreach ($cssRaw as $i => $c) {
                $this->v["css"][$c->def_subset] = $c->def_description;
            }
        }
        return $this->checkStyleDefs($this->v["css"]);
    }
    
    protected function chkSysReqs()
    {
        $GLOBALS["SL"]->loadStates();
        $GLOBALS["SL"]->importZipsUS();
        if (isset($GLOBALS["SL"]->sysOpts["has-canada"]) 
            && intVal($GLOBALS["SL"]->sysOpts["has-canada"]) == 1) {
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
            session()->save();
        }
        $this->v["sysStyles"] = SLDefinitions::where('def_database', $dbID)
            ->where('def_set', 'Style Settings')
            ->orderBy('def_order')    
            ->get();
        $this->v["custCSS"] = SLDefinitions::where('def_database', $dbID)
            ->where('def_set', 'Style CSS')
            ->where('def_subset', 'main')
            ->first();
        if (!$this->v["custCSS"] 
            || !isset($this->v["custCSS"]->def_id)) {
            $this->v["custCSS"] = new SLDefinitions;
            $this->v["custCSS"]->def_database = $dbID;
            $this->v["custCSS"]->def_set      = 'Style CSS';
            $this->v["custCSS"]->def_subset   = 'main';
            $this->v["custCSS"]->save();
        }
        $this->v["custCSSemail"] = SLDefinitions::where('def_database', $dbID)
            ->where('def_set', 'Style CSS')
            ->where('def_subset', 'email')
            ->first();
        if (!$this->v["custCSSemail"] || !isset($this->v["custCSSemail"]->def_id)) {
            $this->v["custCSSemail"] = new SLDefinitions;
            $this->v["custCSSemail"]->def_database = $dbID;
            $this->v["custCSSemail"]->def_set      = 'Style CSS';
            $this->v["custCSSemail"]->def_subset   = 'email';
            $this->v["custCSSemail"]->save();
        }
        $this->v["rawSettings"] = SLDefinitions::where('def_set', 'Custom Settings')
            ->orderBy('def_order', 'asc')
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
                        SLDefinitions::where('def_database', $dbID)
                            ->where('def_set', 'System Settings')
                            ->where('def_subset', $opt)
                            ->update([ 'def_description' => $new ]);
                    }
                }
            }
            foreach ($this->v["sysStyles"] as $opt) {
                if (isset($this->v["stylesList"][$opt->def_subset]) 
                    && $request->has('sty-' . $opt->def_subset)) {
                    $opt->def_description = $request
                        ->get('sty-' . $opt->def_subset);
                    $opt->save();
                }
            }
            $this->v["custCSS"]->def_description 
                = trim($request->get('sys-cust-css'));
            $this->v["custCSS"]->save();
            $this->v["custCSSemail"]->def_description
                = trim($request->get('sys-cust-css-email'));
            $this->v["custCSSemail"]->save();
            foreach ($this->v["rawSettings"] as $i => $s) {
                if ($request->has('setting' . $i . '')) {
                    $s->def_value = $request->get('setting' . $i . '');
                    $s->save();
                }
            }
        }
        $tmp = [];
        if ($this->v["sysStyles"]->isNotEmpty()) {
            foreach ($this->v["sysStyles"] as $sty) {
                $tmp[$sty->def_subset] = $sty->def_description;
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
            //if ($this->dbID == 3 && $GLOBALS["SL"]->sysOpts["cust-abbr"] == 'Survloop') $dbID = 1;
            $chk = SLDefinitions::where('def_database', $dbID)
                ->where('def_set', 'Style Settings')
                ->where('def_subset', $key)
                ->first();
            if (!$chk || !isset($chk->def_set)) {
                $cssNew = new SLDefinitions;
                $cssNew->def_database = $dbID;
                $cssNew->def_set = 'Style Settings';
                $cssNew->def_subset = $key;
                $cssNew->def_description = $val[0];
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
            $chk = SLDefinitions::where('def_database', $dbID)
                ->where('def_set', 'System Settings')
                ->where('def_subset', $key)
                ->first();
            if (!$chk || !isset($chk->def_set)) {
                $cssNew = new SLDefinitions;
                $cssNew->def_database = $dbID;
                $cssNew->def_set = 'System Settings';
                $cssNew->def_subset = $key;
                $cssNew->def_description = $val[1];
                $cssNew->save();
            }
        }
        $grps = [
            [
                'administrator', 
                'Administrator',
                'Highest system administrative privileges, can add, '
                    . 'remove, and change permissions of other users'
            ], [
                'databaser',
                'Database Designer',
                'Permissions to make edits in the database designing tools'
            ], [
                'staff',
                'Staff/Analyst',
                'Full staff priveleges, can view but not edit technical specs'
            ], [
                'partner',
                'Partner Member',
                'Basic permission to pages and tools just for partners'
            ], [
                'volunteer', 
                'Volunteer', 
                'Basic permission to pages and tools just for volunteers'
            ]
        ];
        foreach ($grps as $i => $grp) {
            $chk = SLDefinitions::where('def_database', $dbID)
                ->where('def_set', 'User Roles')
                ->where('def_subset', $grp[0])
                ->first();
            if (!$chk || !isset($chk->def_set)) {
                $chk = new SLDefinitions;
                $chk->def_database = $dbID;
                $chk->def_set = 'User Roles';
                $chk->def_subset  = $grp[0];
            }
            $chk->def_value       = $grp[1];
            $chk->def_description = $grp[2];
            $chk->def_order       = $i;
            $chk->save();
        }
        return $sys;
    }
    
    protected function chkAppUrl($dbID = 1)
    {
        if (!isset($_SERVER["APP_URL"])) {
            return false;
        }
        if ($dbID <= 0) {
            $dbID = $this->dbID;
        }
        $appUrl = SLDefinitions::where('def_database', $dbID)
            ->where('def_set', 'System Settings')
            ->where('def_subset', 'app-url')
            ->first();
        if (!$appUrl) {
            $appUrl = new SLDefinitions;
            $appUrl->def_database = $dbID;
            $appUrl->def_set = 'System Settings';
            $appUrl->def_subset = 'app-url';
            $appUrl->def_description = $_SERVER["APP_URL"];
        } elseif (!isset($appUrl->def_description) 
            || trim($appUrl->def_description) == '') {
            $appUrl->def_description = $_SERVER["APP_URL"];
        }
        $appUrl->save();
        return true;
    }
    
}