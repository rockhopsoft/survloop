<?php
/**
  * SystemDefinitions loads and manages key SurvLoop system variables.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use Illuminate\Http\Request;
use App\Models\SLDefinitions;

class SystemDefinitions
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
                ->where('DefSubset', $grp)
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
    
    protected function getDefaultStyles()
    {
        return [
            'font-main'         => ['Helvetica,Arial,sans-serif', 'Universal Font Family'],
            'color-main-bg'     => ['#FFF',    'Background Color'],
            'color-main-text'   => ['#333',    'Text Color'],
            'color-main-link'   => ['#416CBD', 'Link Color'],
            'color-main-grey'   => ['#999',    'Grey Color'],
            'color-main-faint'  => ['#EDF8FF', 'Faint Color'],
            'color-main-faintr' => ['#F9FCFF', 'Fainter Color'],
            'color-main-on'     => ['#2B3493', 'Primary Color #1'],
            'color-main-off'    => ['#53F1EB', 'Primary Color #2'],
            'color-info-on'     => ['#5BC0DE', 'Info Color #1'],
            'color-info-off'    => ['#2AABD2', 'Info Color #2'],
            'color-danger-on'   => ['#EC2327', 'Danger Color #1'],
            'color-danger-off'  => ['#F38C5F', 'Danger Color #2'],
            'color-success-on'  => ['#006D36', 'Success Color #1'],
            'color-success-off' => ['#29B76F', 'Success Color #2'],
            'color-warn-on'     => ['#F0AD4E', 'Warning Color #1'],
            'color-warn-off'    => ['#EB9316', 'Warning Color #2'],
            'color-line-hr'     => ['#999',    'Horizontal Rule Color'],
            'color-field-bg'    => ['#FFF',    'Form Field BG Color'],
            'color-form-text'   => ['#333',    'Form Field Text Color'],
            'color-logo'        => ['#53F1EB', 'Primary Logo Color'],
            'color-nav-bg'      => ['#000',    'Navigation BG Color'],
            'color-nav-text'    => ['#888',    'Navigation Text Color']
            ];
    }
    
    protected function getDefaultSys()
    {
        return [
            'site-name'       => ['Installation/Site Name', 'for general reference, in English'], 
            'cust-abbr'       => ['Installation Abbreviation', 'SiteAbrv'], 
            'cust-package'    => ['Vendor Package Name', 'wikiworldorder/survloop'], 
                // for files and folder names, no spaces or special characters
            'app-url'         => ['Primary Application URL', 'http://myapp.com'], 
            'logo-url'        => ['URL Linked To Logo', '/optionally-different'], 
            'meta-title'      => ['SEO Default Meta Title', ''], 
            'meta-desc'       => ['SEO Default Meta Description', ''], 
            'meta-keywords'   => ['SEO Default Meta Keywords', ''], 
            'meta-img'        => ['SEO Default Meta Social Media Sharing Image', ''], 
            'logo-img-lrg'    => ['Large Logo Image', '/siteabrv/uploads/logo-large.png'], 
            'logo-img-md'     => ['Medium Logo Image', '/siteabrv/uploads/logo-medium.png'], 
            'logo-img-sm'     => ['Small Logo Image', '/siteabrv/uploads/logo-small.png'], 
            'shortcut-icon'   => ['Shortcut Icon Image', '/siteabrv/ico.png'],
            'spinner-code'    => ['Spinner Animation', '&lt;i class="fa-li fa fa-spinner fa-spin"&gt;&lt;/i&gt;'], 
            'google-analytic' => ['Google Analytics Tracking ID', 'UA-23427655-1'], 
            'google-map-key'  => ['Google Maps API Key: Server', 'string'], 
            'google-map-key2' => ['Google Maps API Key: Browser', 'string'], 
            'google-cod-key'  => ['Google Geocoding API Key: Server', 'string'], 
            'google-cod-key2' => ['Google Geocoding API Key: Browser', 'string'], 
            'twitter'         => ['Twitter Account', '@SurvLoop'], 
            'show-logo-title' => ['Print Site Name Next To Logo', '1 or 0'], 
            'users-create-db' => ['Users Can Create Databases', '1 or 0'], 
            'user-name-req'   => ['Username Are Required To Register', '1 or 0'], 
            'has-partners'    => ['Has Partners User Area', '1 or 0'], 
            'has-volunteers'  => ['Has Volunteer User Area', '1 or 0'], 
            'has-canada'      => ['Has Canadian Maps', '1 or 0'], 
            'parent-company'  => ['Parent Company of This Installation', 'MegaOrg'], 
            'parent-website'  => ['Parent Company\'s Website URL', 'http://www...'], 
            'login-instruct'  => ['User Login Instructions', 'HTML'], 
            'signup-instruct' => ['New User Sign Up Instructions', 'HTML'], 
            'app-license'     => ['License Info', 'Creative Commons Attribution-ShareAlike License'], 
            'app-license-url' => ['License Info URL', 'http://creativecommons.org/licenses/by-sa/3.0/'], 
            'app-license-img' => ['License Info Image', '/survloop/uploads/creative-commons-by-sa-88x31.png'],
            'css-extra-files' => ['CSS Extra Files', 'comma separated'],
            'header-code'     => ['< head > Header Code < / head >', '&lt;div&gt;Anything&lt;/div&gt;']
            ];
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