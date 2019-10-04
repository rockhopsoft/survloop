<?php
/**
  * AdminController is the main landing class routing to certain admin tools which 
  * requires a user to be logged in.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author  Morgan Lesko <wikiworldorder@protonmail.com>
  * @since v0.0.1
  */
namespace SurvLoop\Controllers\Admin;

use Auth;
use Cache;
use Storage;
use Illuminate\Http\Request;
use MatthiasMullie\Minify;
use App\Models\User;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLNodeResponses;
use App\Models\SLCaches;
use SurvLoop\Controllers\Globals\Globals;
use SurvLoop\Controllers\SystemDefinitions;
use SurvLoop\Controllers\Admin\AdminEmailController;

class AdminController extends AdminEmailController
{
    
    protected function tmpDbSwitch($dbID = 3)
    {
        $this->v["tmpDbSwitchDb"]   = $GLOBALS["SL"]->dbID;
        $this->v["tmpDbSwitchTree"] = $GLOBALS["SL"]->treeID;
        $this->v["tmpDbSwitchREQ"]  = $GLOBALS["SL"]->REQ;
        $GLOBALS["SL"] = new Globals($this->v["tmpDbSwitchREQ"], $dbID);
        $this->dbID   = $dbID;
        $this->treeID = $GLOBALS["SL"]->treeID;
        return true;
    }

    protected function tmpDbSwitchBack()
    {
        if (isset($this->v["tmpDbSwitchDb"])) {
            $GLOBALS["SL"] = new Globals($this->v["tmpDbSwitchREQ"], 
                $this->v["tmpDbSwitchDb"], $this->v["tmpDbSwitchTree"], $this->v["tmpDbSwitchTree"]);
            $this->dbID   = $GLOBALS["SL"]->dbID;
            $this->treeID = $GLOBALS["SL"]->treeID;
            return true;
        }
        return false;
    }
    
    protected function cacheFlushOld()
    {
        $old = mktime(date("H"), date("i"), date("s"), date("m"), date("d")-5, date("Y"));
        SLCaches::where('created_at', '<', date('Y-m-d H:i:s', $old))
            ->delete();
        Cache::flush();
        return true;
    }
    
    protected function cacheFlush()
    {
        SLCaches::where('created_at', '>', '2000-01-01 00:00:00')
            ->delete();
        Cache::flush();
        return true;
    }
    
    public function switchDB(Request $request, $dbID = -3)
    {
        $this->admControlInit($request, '/dashboard/db/switch');
        if ($dbID > 0) {
            $this->switchDatabase($request, $dbID, '/dashboard/db/switch');
            return $this->redir('/dashboard/db/all');
        }
        $this->v["myDbs"] = SLDatabases::orderBy('DbName', 'asc')
            //->whereIn('DbUser', [ 0, $this->v["user"]->id ])
            ->get();
        return view('vendor.survloop.admin.db.switch', $this->v);
    }
    
    public function switchTreeAdmin(Request $request, $treeID = -3)
    {
        $this->admControlInit($request, '/dashboard/tree/switch');
        if ($treeID > 0) {
            $this->switchTree($treeID, '/dashboard/tree/switch', $request);
            if ($GLOBALS["SL"]->treeRow->TreeType == 'Page') {
                return $this->redir('/dashboard/page/' . $treeID . '?all=1&alt=1&refresh=1');
            }
            return $this->redir('/dashboard/surv-' . $treeID . '/map?all=1&alt=1&refresh=1');
        }
        $this->v["myTrees"] = SLTree::where('TreeDatabase', $GLOBALS["SL"]->dbID)
            ->where('TreeType', 'NOT LIKE', 'Survey XML')
            ->where('TreeType', 'NOT LIKE', 'Other Public XML')
            ->where('TreeType', 'NOT LIKE', 'Page')
            ->orderBy('TreeName', 'asc')
            ->get();
        $this->v["myTreeNodes"] = [];
        if ($this->v["myTrees"]->isNotEmpty()) {
            foreach ($this->v["myTrees"] as $tree) {
                $nodes = SLNode::where('NodeTree', $tree->TreeID)
                    ->select('NodeID')
                    ->get();
                $this->v["myTreeNodes"][$tree->TreeID] = $nodes->count();
            }
        }
        return view('vendor.survloop.admin.tree.switch', $this->v);
    }
    
    public function sysSettings(Request $request)
    {
        $this->admControlInit($request, '/dashboard/settings#search');
        if ($request->has('refresh') && intVal($request->get('refresh')) == 2) {
            $this->initLoader();
            $chk = SLTree::whereIn('TreeType', [ 'Page', 'Survey' ])
                ->where('TreeDatabase', 1)
                ->select('TreeID', 'TreeDatabase')
                ->orderBy('TreeID', 'asc')
                ->get();
            if ($chk->isNotEmpty()) {
                $next = $curr = $found = 0;
                if ($request->has('next')) {
                    $curr = intVal($request->get('next'));
                }
                foreach ($chk as $tree) {
                    if ($curr == 0) {
                        $curr = $tree->TreeID;
                    }
                    if ($found == 1 && $next <= 0) {
                        $next = $tree->TreeID;
                    }
                    if ($tree->TreeID == $curr) {
                        $found = 1;
                        $this->loader->syncDataTrees($request, $tree->TreeDatabase, $tree->TreeID);
                        $this->loadCustLoop($request, $tree->TreeID, $tree->TreeDatabase);
                        $this->custReport->loadTree($tree->TreeID);
                        $this->custReport->loadProgBar();
                    }
                }
                $this->loader->syncDataTrees($request);
                if ($next > 0) {
                    echo '<center><div class="p20"><br /><br /><h2>Refreshing JavaScript Cache for Tree #' . $curr 
                        . '</h2></div></center><div class="p20">' . $GLOBALS["SL"]->spinner() . '</div>';
                    return $this->redir('/dashboard/settings?refresh=2&next=' . $next, true);
                }
            }
            return $this->redir('/dashboard/settings?refresh=1', true);
        } elseif ($request->has('refresh') && intVal($request->get('refresh')) == 3) {
            $this->cacheFlush();
        }
        $GLOBALS["SL"]->addAdmMenuHshoos([
            '/dashboard/settings#search',
            '/dashboard/settings#general', 
            '/dashboard/settings#logos',
            '/dashboard/settings#color',
            '/dashboard/settings#hardcode'
            ]);
        $this->reloadAdmMenu();
        $this->getCSS($request);
        $this->v["sysDef"] = new SystemDefinitions;
        $this->v["sysDef"]->prepSysSettings($request);
        $this->v["currMeta"] = [
            "title" => ((isset($GLOBALS['SL']->sysOpts['meta-title'])) 
                ? $GLOBALS['SL']->sysOpts['meta-title'] : ''),
            "desc"  => ((isset($GLOBALS['SL']->sysOpts['meta-desc'])) 
                ? $GLOBALS['SL']->sysOpts['meta-desc'] : ''),
            "wrds"  => ((isset($GLOBALS['SL']->sysOpts['meta-keywords'])) 
                ? $GLOBALS['SL']->sysOpts['meta-keywords']:''),
            "img"   => ((isset($GLOBALS['SL']->sysOpts['meta-img'])) 
                ? $GLOBALS['SL']->sysOpts['meta-img'] : ''),
            "slug"  => false, 
            "base"  => ''
            ];
        return view('vendor.survloop.admin.system-all-settings', $this->v);
    }
    
    public function sysSettingsRaw(Request $request)
    {
        $this->admControlInit($request, '/dashboard/settings-raw');
        $this->v["sysDef"] = new SystemDefinitions;
        $this->v["sysDef"]->prepSysSettings($request);
        return view('vendor.survloop.admin.systemsettings', $this->v["sysDef"]->v);
    }
    
    
    protected function blurbLoad($blurbID)
    {
        return SLDefinitions::where('DefID', $blurbID)
            ->where('DefDatabase', $this->dbID)
            ->where('DefSet', 'Blurbs')
            ->first();
    }
    
    public function blurbEdit(Request $request, $blurbID)
    {
        $this->admControlInit($request, '/dashboard/pages');
        $this->v["blurbRow"] = $this->blurbLoad($blurbID);
        $this->v["needsWsyiwyg"] = true;
        if ($this->v["blurbRow"]->DefIsActive <= 0 || $this->v["blurbRow"]->DefIsActive%3 > 0) {
            $GLOBALS["SL"]->pageAJAX .= ' $("#DefDescriptionID").summernote({ height: 500 }); ';
        }
        return view('vendor.survloop.admin.blurb-edit', $this->v);
    }
    
    public function blurbNew(Request $request)
    {
        if (isset($request->newBlurbName) && trim($request->newBlurbName) != '') {
            $blurb = new SLDefinitions;
            $blurb->DefDatabase = $this->dbID;
            $blurb->DefSet      = 'Blurbs';
            $blurb->DefSubset   = $request->newBlurbName;
            $blurb->save();
            return $blurb->DefID;
        }
        return -3;
    }
    
    public function blurbEditSave(Request $request)
    {
        $blurb = $this->blurbLoad($request->DefID);
        $blurb->DefSubset      = $request->DefSubset;
        $blurb->DefDescription = $request->DefDescription;
        $blurb->DefIsActive = 1;
        if ($request->has('optHardCode') && intVal($request->optHardCode) == 3) {
            $blurb->DefIsActive *= 3;
        }
        $blurb->save();
        return $this->redir('/dashboard/pages/snippets/' . $blurb->DefID);
    }
    
    public function getCSS(Request $request)
    {
        $this->survLoopInit($request, '/dashboard/settings');
        if (!is_dir('../storage/app/sys')) {
            mkdir('../storage/app/sys');
        }
        $this->v["sysDef"] = new SystemDefinitions;
        $css = $this->v["sysDef"]->loadCss();
        $custCSS = SLDefinitions::where('DefDatabase', $this->dbID)
            ->where('DefSet', 'Style CSS')
            ->where('DefSubset', 'main')
            ->first();
        $css["raw"] = (($custCSS && isset($custCSS->DefDescription)) ? $custCSS->DefDescription : '');
        
        $syscss = view('vendor.survloop.css.styles-1', [ "css" => $css ])->render();
        file_put_contents("../storage/app/sys/sys1.css", $syscss);
        $minifier = new Minify\CSS("../storage/app/sys/sys1.css");
        $minifier->add("../vendor/components/jqueryui/themes/base/jquery-ui.min.css");
        $minifier->add("../vendor/twbs/bootstrap/dist/css/bootstrap.min.css");
        //$minifier->add("../vendor/forkawesome/fork-awesome/css/fork-awesome.min.css");
        if (isset($GLOBALS["SL"]->sysOpts["css-extra-files"]) 
            && trim($GLOBALS["SL"]->sysOpts["css-extra-files"]) != '') {
            $files = $GLOBALS["SL"]->mexplode(',', $GLOBALS["SL"]->sysOpts["css-extra-files"]);
            foreach ($files as $f) {
                $minifier->add(trim($f));
            }
        }
        $minifier->minify("../storage/app/sys/sys1.min.css");
        
        $syscss = view('vendor.survloop.css.styles-2', [ "css" => $css ])->render();
        file_put_contents("../storage/app/sys/sys2.css", $syscss);
        $minifier = new Minify\CSS("../storage/app/sys/sys2.css");
        $minifier->minify("../storage/app/sys/sys2.min.css");
        
        $minifier = new Minify\JS("../vendor/components/jquery/jquery.min.js");
        $minifier->add("../vendor/components/jqueryui/jquery-ui.min.js");
        $minifier->add("../vendor/wikiworldorder/survloop-libraries/src/js/popper.min.js");
        $minifier->add("../vendor/twbs/bootstrap/dist/js/bootstrap.min.js");
        //$minifier->add("../vendor/wikiworldorder/survloop-libraries/src/js/parallax.min.js");
        $minifier->add("../vendor/wikiworldorder/survloop-libraries/src/js/typewatch.js");
        $minifier->add("../vendor/wikiworldorder/survloop-libraries/src/js/copy-to-clipboard.js");
        //$minifier->add("../vendor/wikiworldorder/survloop-libraries/src/js/radialIndicator.min.js");
        $minifier->minify("../storage/app/sys/sys1.min.js");
        
        $treeJs = '';
        $chk = SLTree::whereIn('TreeType', ['Page', 'Survey'])
            ->select('TreeID', 'TreeType')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tree) {
                $treeFile = '../storage/app/sys/tree-' . $tree->TreeID . '.js';
                if (file_exists($treeFile)) {
                    $treeJs .= "\n" . 'function treeLoad' . $tree->TreeID . '() {' . "\n" 
                        . 'treeID = ' . $tree->TreeID . ';' . "\n"
                        . 'treeType = "' . $tree->TreeType . '";' . "\n"
                        . str_replace("\t", "", str_replace("\n", "\n", 
                            file_get_contents($treeFile))) . "\n\t"
                        . 'return true;' . "\n" . '}' . "\n";
                }
            }
        }
        $GLOBALS["SL"]->loadStates();
        $scriptsjs = view('vendor.survloop.js.scripts', [ "css" => $css, "treeJs" => $treeJs ])->render()
            . view('vendor.survloop.js.scripts-ajax', [ "css" => $css, "treeJs" => $treeJs ])->render();
        file_put_contents("../storage/app/sys/sys2.js", $scriptsjs);
        $minifier = new Minify\JS("../storage/app/sys/sys2.js");
        $minifier->minify("../storage/app/sys/sys2.min.js");
        
        $log = SLDefinitions::where('DefSet', 'System Settings')
            ->where('DefSubset', 'log-css-reload')
            ->update([ 'DefDescription' => time() ]);
        return ':)';
    }
    
    protected function eng2data($name)
    {
        return preg_replace("/[^a-zA-Z0-9]+/", "", ucwords($name));
    }
    
    protected function eng2abbr($name)
    {
        $abbr = preg_replace("/[^A-Z]+/", "", $name);
        if (strlen($abbr) > 1) return $abbr;
        return substr(preg_replace("/[^a-zA-Z0-9]+/", "", $name), 0, 3);
    }
    
    protected function isCoreTbl($tblID)
    {
        $chkCore = SLTree::where('TreeCoreTable', '=', $tblID)
            ->get();
        return $chkCore->isNotEmpty();
    }
    
    public function userManage(Request $request)
    {
        $this->admControlInit($request, '/dashboard/users', 'administrator|staff');
        $this->loadPrintUsers();
        return view('vendor.survloop.admin.user-manage', $this->v);
    }
    
    protected function loadPrintUsers()
    {
        $this->v["printVoluns"] = [ [], [], [], [], [], [] ]; // voluns, staff, admin
        $users = User::orderBy('name', 'asc') // where('name', 'NOT LIKE', 'Session#%')
            ->get();
        foreach ($users as $i => $usr) {
            $list = 3;
            if ($usr->hasRole('administrator')) {
                $list = 0;
            } elseif ($usr->hasRole('databaser')) {
                $list = 1;
            } elseif ($usr->hasRole('staff')) {
                $list = 2;
            } elseif ($usr->hasRole('partner')) {
                $list = 3;
            } elseif ($usr->hasRole('volunteer')) {
                $list = 4;
            } else {
                $list = 5;
            }
            $this->v["printVoluns"][$list][] = $usr;
        }
        $this->v["disableAdmin"] = ((!$this->v["user"]->hasRole('administrator')) ? ' DISABLED ' : '');
        return true;
    }
    
    public function postNodeURL(Request $request)
    {
        if ($request->has('step') && $request->has('tree') && intVal($request->tree) > 0) {
            $this->initLoader();
            $this->loader->loadTreeByID($request, $request->tree);
            $this->admControlInit($request, '/dash/u/' . $GLOBALS["SL"]->treeRow->TreeSlug . '/' . $request->nodeSlug);
            $this->loadCustLoop($request, $request->tree);
            echo '<div class="pT20">' . $this->custReport->loadNodeURL($request, $request->nodeSlug) . '</div>';
        }
        exit;
    }
    
    public function ajaxChecksAdmin(Request $request, $type = '')
    {
        $this->admControlInit($request, '/ajadm/' . $type);
        $this->loadCustLoop($request, $this->treeID);
        $newStatus = (($request->has('status')) ? trim($request->get('status')) : '');
        if ($type == 'contact') {
            return $this->ajaxContact($request);
        } elseif ($type == 'contact-tabs') {
            return $this->ajaxContactTabs($request);
        } elseif ($type == 'contact-push') {
            return $this->admMenuLnkContactCnt();
        } elseif ($type == 'redir-edit') {
            return $this->admRedirEdit($request);
        } elseif ($type == 'send-email') {
            return $this->ajaxSendEmail($request);
        }
        return $this->custReport->ajaxChecks($request, $type);
    }
    
    public function systemsCheck(Request $request)
    {
        $this->admControlInit($request, '/dashboard/systems-check');
        if ($request->has('testEmail') && intVal($request->get('testEmail')) == 1) {
            $this->v["testResults"] = '';
            if ($request->has('sendTest') && intVal($request->get('sendTest')) == 1
                && $request->has('emailTo') && trim($request->emailTo) != '') {
                $emaTo = trim($request->emailTo);
                $emaToArr = [ [ $emaTo, 'Test Message' ] ];
                $emaSubj = 'Email Flight Test from ' . $GLOBALS["SL"]->sysOpts["site-name"];
                $emaCont = '<p>Hi there friend,</p><p>This has been a flight test from ' 
                    . $GLOBALS["SL"]->sysOpts["site-name"] . '</p>';
                if (!$GLOBALS["SL"]->isHomestead()) {
                    $this->sendEmail($emaCont, $emaSubj, $emaToArr);
                }
                $this->logEmailSent($emaCont, $emaSubj, $emaTo, 0, $this->treeID, $this->coreID, $this->v["uID"]);
                $this->v["testResults"] .= '<div class="container"><h2>' . $emaSubj . '</h2>' . $emaCont 
                    . '<hr><hr><i class="slBlueDark">to ' . $emaTo . '</i></div>';
            }
            return view('vendor.survloop.admin.systems-check-email', $this->v);
        }
        if ($request->has('testCache') && intVal($request->get('testCache')) == 1) {
            if ($request->has('sendTest') && intVal($request->get('sendTest')) == 1) {
                Cache::put('testCache', trim($request->get('cacheVal')));
            }
            return view('vendor.survloop.admin.systems-check-cache', [
                "testCache" => ((Cache::has('testCache')) ? Cache::get('testCache') : '')
            ]);
        }
        $tree1 = SLTree::find(1);
        $this->v["sysChks"] = [];
        $this->v["sysChks"][] = ['Home',         '/'];
        $this->v["sysChks"][] = ['Survey Start', '/start/' . $tree1->TreeSlug . ''];
        $this->v["sysChks"][] = ['Search Empty', '/search-results/1?s='];
        $this->v["sysChks"][] = ['Search Test',  '/search-results/1?s=testing'];
        $this->v["sysChks"][] = ['XML-Example',  '/' . $tree1->TreeSlug . '-xml-example'];
        $this->v["sysChks"][] = ['XML-All',      '/' . $tree1->TreeSlug . '-xml-all'];
        $this->v["sysChks"][] = ['XML-Schema',   '/' . $tree1->TreeSlug . '-xml-schema'];
        return view('vendor.survloop.admin.systems-check', $this->v);
    }
    
    public function loadLogs()
    {
        $this->v["logs"] = [
            "session" => $this->logPreview('session-stuff')
            ];
        return true;
    }
    
    public function logsOverview(Request $request)
    {
        $this->admControlInit($request, '/dashboard/logs');
        $this->loadLogs();
        $this->v["phpInfo"] = $request->has('phpinfo');
        return view('vendor.survloop.admin.logs-overview', $this->v);
    }
    
    public function logsSessions(Request $request)
    {
        $this->admControlInit($request, '/dashboard/logs/session-stuff');
        $this->loadLogs();
        return view('vendor.survloop.admin.logs-sessions', $this->v);
    }
    
    
    public function navMenus(Request $request)
    {
        $this->admControlInit($request, '/dashboard/pages/menus');
        $this->v["cntMax"] = 25;
        if ($request->has('sub') && intVal($request->get('sub')) == 1) {
            for ($i = 0; $i < $this->v["cntMax"]; $i++) {
                if ($i < sizeof($this->v["navMenu"])) {
                    if ($request->has('mainNavTxt' . $i)) {
                        SLDefinitions::where('DefSet', 'Menu Settings')
                            ->where('DefSubset', 'main-navigation')
                            ->where('DefDatabase', 1)
                            ->where('DefOrder', $i)
                            ->update([
                                'DefValue'       => $request->get('mainNavTxt' . $i),
                                'DefDescription' => $request->get('mainNavLnk' . $i)
                            ]);
                    } else {
                        SLDefinitions::where('DefSet', 'Menu Settings')
                            ->where('DefSubset', 'main-navigation')
                            ->where('DefDatabase', 1)
                            ->where('DefOrder', $i)
                            ->delete();
                    }
                } elseif ($request->has('mainNavTxt' . $i)) {
                    $newLnk = new SLDefinitions;
                    $newLnk->DefSet         = 'Menu Settings';
                    $newLnk->DefSubset      = 'main-navigation';
                    $newLnk->DefDatabase    = 1;
                    $newLnk->DefOrder       = $i;
                    $newLnk->DefValue       = $request->get('mainNavTxt' . $i);
                    $newLnk->DefDescription = $request->get('mainNavLnk' . $i);
                    $newLnk->save();
                }
            }
            $this->loadNavMenu();
        }
        $this->v["cnt"] = 0;
        return view('vendor.survloop.admin.manage-menus', $this->v);
    }
    
    
    public function imgGallery(Request $request)
    {
        $this->admControlInit($request, '/dashboard/images/gallery');
        if ($request->has('sub') && intVal($request->get('sub')) == 1) {
            
        }
        $this->v["imgSelect"] = $GLOBALS["SL"]->getImgSelect('-3', $GLOBALS["SL"]->dbID);
        return view('vendor.survloop.admin.images-gallery', $this->v);
    }
    
    public function printSubView(Request $request, $treeID = 1, $cid = -3)
    {
        if ($treeID > 0 && $cid > 0) {
            $treeRow = SLTree::find($treeID);
            if ($treeRow && isset($treeRow->TreeSlug)) {
                return redirect('/' . $treeRow->TreeSlug . '/read-' . $cid);
            }
        }
        return 'Not found :(';
    }
    
}
