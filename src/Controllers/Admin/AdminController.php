<?php
/**
  * AdminController is the main landing class routing to certain admin tools which 
  * requires a user to be logged in.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.1
  */
namespace RockHopSoft\Survloop\Controllers\Admin;

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
use App\Models\SLSess;
use App\Models\SLNodeSaves;
use RockHopSoft\Survloop\Controllers\SystemDefinitions;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\Admin\NodeSaveSet;
use RockHopSoft\Survloop\Controllers\Admin\AdminEmailController;

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
            $GLOBALS["SL"] = new Globals(
                $this->v["tmpDbSwitchREQ"], 
                $this->v["tmpDbSwitchDb"], 
                $this->v["tmpDbSwitchTree"], 
                $this->v["tmpDbSwitchTree"]
            );
            $this->dbID   = $GLOBALS["SL"]->dbID;
            $this->treeID = $GLOBALS["SL"]->treeID;
            return true;
        }
        return false;
    }
    
    protected function cacheFlushOld()
    {
        $old = mktime(date("H"), date("i"), date("s"), 
            date("m"), date("d")-5, date("Y"));
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
        $this->v["myDbs"] = SLDatabases::orderBy('db_name', 'asc')
            //->whereIn('db_user', [ 0, $this->v["user"]->id ])
            ->get();
        return view('vendor.survloop.admin.db.switch', $this->v);
    }
    
    public function switchTreeAdmin(Request $request, $treeID = -3)
    {
        $this->admControlInit($request, '/dashboard/tree/switch');
        if ($treeID > 0) {
            $this->switchTree($treeID, '/dashboard/tree/switch', $request);
            $redir = '/dashboard/surv-' . $treeID . '/map?all=1&alt=1&refresh=1';
            if ($GLOBALS["SL"]->treeRow->tree_type == 'Page') {
                $redir = '/dashboard/page/' . $treeID . '?all=1&alt=1&refresh=1';
            }
            return $this->redir($redir);
        }
        $this->v["myTrees"] = SLTree::where('tree_database', $GLOBALS["SL"]->dbID)
            ->where('tree_type', 'NOT LIKE', 'Survey XML')
            ->where('tree_type', 'NOT LIKE', 'Other Public XML')
            ->where('tree_type', 'NOT LIKE', 'Page')
            ->orderBy('tree_name', 'asc')
            ->get();
        $this->v["myTreeNodes"] = [];
        if ($this->v["myTrees"]->isNotEmpty()) {
            foreach ($this->v["myTrees"] as $tree) {
                $nodes = SLNode::where('node_tree', $tree->tree_id)
                    ->select('node_id')
                    ->get();
                $this->v["myTreeNodes"][$tree->tree_id] = $nodes->count();
            }
        }
        return view('vendor.survloop.admin.tree.switch', $this->v);
    }
    
    public function sysSettings(Request $request)
    {
        $this->admControlInit($request, '/dashboard/settings#search', '', false);
        if ($request->has('refresh') 
            && intVal($request->get('refresh')) == 2) {
            $this->initLoader();
            $this->sysSettingsRefresh($request);
            return $this->redir('/dashboard/settings?refresh=1', true);
        } elseif ($request->has('refresh') 
            && intVal($request->get('refresh')) == 3) {
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
        if ($request->has('refresh')) {
            $GLOBALS["SL"]->clearAllSystemCaches();
            $this->v["sysDef"]->checkDefInstalls();
        }
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
    
    protected function sysSettingsRefresh(Request $request)
    {
        $chk = SLTree::whereIn('tree_type', [ 'Page', 'Survey' ])
            ->where('tree_database', 1)
            ->select('tree_id', 'tree_database')
            ->orderBy('tree_id', 'asc')
            ->get();
        if ($chk->isNotEmpty()) {
            $next = $curr = $found = 0;
            if ($request->has('next')) {
                $curr = intVal($request->get('next'));
            }
            foreach ($chk as $tree) {
                if ($curr == 0) {
                    $curr = $tree->tree_id;
                }
                if ($found == 1 && $next <= 0) {
                    $next = $tree->tree_id;
                }
                if ($tree->tree_id == $curr) {
                    $found = 1;
                    $this->loader->syncDataTrees(
                        $request, 
                        $tree->tree_database, 
                        $tree->tree_id
                    );
                    $this->loadCustLoop(
                        $request, 
                        $tree->tree_id, 
                        $tree->tree_database
                    );
                    $this->custReport->loadTree($tree->tree_id);
                    $this->custReport->loadProgBar();
                }
            }
            $this->loader->syncDataTrees($request);
            if ($next > 0) {
                echo '<center><div class="p20"><br /><br /><h2>'
                    . 'Refreshing JavaScript Cache for Tree #' . $curr 
                    . '</h2></div></center><div class="p20">' 
                    . $GLOBALS["SL"]->spinner() . '</div>';
                $redir = '/dashboard/settings?refresh=2&next=' . $next;
                return $this->redir($redir, true);
            }
        }
        return true;
    }
    
    public function sysSettingsRaw(Request $request)
    {
        $this->admControlInit($request, '/dashboard/settings-raw');
        $this->v["sysDef"] = new SystemDefinitions;
        $this->v["sysDef"]->prepSysSettings($request);
        return view(
            'vendor.survloop.admin.systemsettings', 
            $this->v["sysDef"]->v
        );
    }
    
    
    protected function blurbLoad($blurbID)
    {
        return SLDefinitions::where('def_id', $blurbID)
            ->where('def_database', $this->dbID)
            ->where('def_set', 'Blurbs')
            ->first();
    }
    
    public function blurbEdit(Request $request, $blurbID)
    {
        $this->admControlInit($request, '/dashboard/pages');
        $this->v["blurbRow"] = $this->blurbLoad($blurbID);
        $this->v["needsWsyiwyg"] = true;
        if ($this->v["blurbRow"]->def_order <= 0 
            || $this->v["blurbRow"]->def_order%3 > 0) {
            $GLOBALS["SL"]->pageAJAX .= ' $("#DefDescriptionID")'
                . '.summernote({ height: 500 }); ';
        }
        return view('vendor.survloop.admin.blurb-edit', $this->v);
    }
    
    public function blurbNew(Request $request)
    {
        if (isset($request->newBlurbName) 
            && trim($request->newBlurbName) != '') {
            $blurb = new SLDefinitions;
            $blurb->def_database = $this->dbID;
            $blurb->def_set      = 'Blurbs';
            $blurb->def_subset   = $request->newBlurbName;
            $blurb->save();
            return $blurb->def_id;
        }
        return -3;
    }
    
    public function blurbEditSave(Request $request)
    {
        $blurb = $this->blurbLoad($request->DefID);
        $blurb->def_subset      = $request->DefSubset;
        $blurb->def_description = $request->DefDescription;
        $blurb->def_order = 1;
        if ($request->has('optHardCode') && intVal($request->optHardCode) == 3) {
            $blurb->def_order *= 3;
        }
        $blurb->save();
        return $this->redir('/dashboard/pages/snippets/' . $blurb->def_id);
    }
    
    public function getCSS(Request $request)
    {
        $this->survloopInit($request, '/dashboard/settings', false);
        if (!is_dir('../storage/app/sys')) {
            mkdir('../storage/app/sys');
        }
        $this->v["sysDef"] = new SystemDefinitions;
        $css = $this->v["sysDef"]->loadCss();
        $css["raw"] = $GLOBALS["SL"]->getSysCustCSS();
        $syscss = view(
            'vendor.survloop.css.styles-1', 
            [ "css" => $css ]
        )->render();
        file_put_contents("../storage/app/sys/sys1.css", $syscss);
        $minifier = new Minify\CSS("../storage/app/sys/sys1.css");
        $minifier->add("../vendor/components/jqueryui/themes/base/jquery-ui.min.css");
        $minifier->add("../vendor/twbs/bootstrap/dist/css/bootstrap.min.css");
        //$minifier->add("../vendor/forkawesome/fork-awesome/css/fork-awesome.min.css");
        if (isset($GLOBALS["SL"]->sysOpts["css-extra-files"]) 
            && trim($GLOBALS["SL"]->sysOpts["css-extra-files"]) != '') {
            $files = $GLOBALS["SL"]->mexplode(
                ',', 
                $GLOBALS["SL"]->sysOpts["css-extra-files"]
            );
            foreach ($files as $f) {
                $minifier->add(trim($f));
            }
        }
        $minifier->minify("../storage/app/sys/sys1.min.css");
        
        $syscss = view(
            'vendor.survloop.css.styles-2', 
            [ "css" => $css ]
        )->render();
        file_put_contents("../storage/app/sys/sys2.css", $syscss);
        $minifier = new Minify\CSS("../storage/app/sys/sys2.css");
        $minifier->minify("../storage/app/sys/sys2.min.css");
        
        $minifier = new Minify\JS("../vendor/components/jquery/jquery.min.js");
        $minifier->add("../vendor/components/jqueryui/jquery-ui.min.js");
        $minifier->add("../vendor/rockhopsoft/survloop-libraries/src/js/popper.min.js");
        $minifier->add("../vendor/twbs/bootstrap/dist/js/bootstrap.min.js");
        //$minifier->add("../vendor/rockhopsoft/survloop-libraries/src/js/parallax.min.js");
        $minifier->add("../vendor/rockhopsoft/survloop-libraries/src/js/typewatch.js");
        $minifier->add("../vendor/rockhopsoft/survloop-libraries/src/js/copy-to-clipboard.js");
        //$minifier->add("../vendor/rockhopsoft/survloop-libraries/src/js/radialIndicator.min.js");
        $minifier->minify("../storage/app/sys/sys1.min.js");
        
        $treeJs = '';
        $chk = SLTree::whereIn('tree_type', ['Page', 'Survey'])
            ->select('tree_id', 'tree_type')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tree) {
                $treeFile = '../storage/app/sys/tree-' . $tree->tree_id . '.js';
                if (file_exists($treeFile)) {
                    $tmpJs = file_get_contents($treeFile);
                    $tmpJs = str_replace("\t", "", str_replace("\n", "\n", $tmpJs));
                    $treeJs .= "\n" . 'function treeLoad' . $tree->tree_id . '() {' . "\n" 
                        . 'treeID = ' . $tree->tree_id . ';' . "\n"
                        . 'treeType = "' . $tree->tree_type . '";' . "\n"
                        . $tmpJs . "\n\treturn true;\n" . '}' . "\n";
                }
            }
        }
        $GLOBALS["SL"]->loadStates();
        $scriptsjs = view(
                'vendor.survloop.js.scripts', 
                [
                    "css" => $css, 
                    "treeJs" => $treeJs 
                ]
            )->render() . view(
                'vendor.survloop.js.scripts-ajax', 
                [ 
                    "css" => $css, 
                    "treeJs" => $treeJs 
                ]
            )->render();
        file_put_contents("../storage/app/sys/sys2.js", $scriptsjs);
        $minifier = new Minify\JS("../storage/app/sys/sys2.js");
        $minifier->minify("../storage/app/sys/sys2.min.js");
        
        $log = SLDefinitions::where('def_set', 'System Settings')
            ->where('def_subset', 'log-css-reload')
            ->update([ 'def_description' => time() ]);
        return ':) ';
    }
    
    protected function eng2data($name)
    {
        return preg_replace("/[^a-z0-9]+/", "", strtolower($name));
    }
    
    protected function eng2abbr($name)
    {
        $name = strtolower($name);
        $words = $GLOBALS["SL"]->mexplode(' ', $name);
        $abbr = '';
        if (sizeof($words) > 0) {
            foreach ($words as $word) {
                $word = preg_replace("/[^a-z0-9]+/", "", $word);
                $abbr .= substr($word, 0, 3) . '_';
            }
        }
        return $abbr;
    }
    
    protected function isCoreTbl($tblID)
    {
        $chkCore = SLTree::where('tree_core_table', '=', $tblID)
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
            $list = 5;
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
        $this->v["disableAdmin"] = '';
        if (!$this->v["user"]->hasRole('administrator'))  {
            $this->v["disableAdmin"] = ' DISABLED ';
        }
        return true;
    }
    
    public function postNodeURL(Request $request)
    {
        if ($request->has('step') 
            && $request->has('tree') 
            && intVal($request->tree) > 0) {
            $this->initLoader();
            $this->loader->loadTreeByID($request, $request->tree);
            $url = '/dash/u/' . $GLOBALS["SL"]->treeRow->tree_slug 
                . '/' . $request->nodeSlug;
            $this->admControlInit($request, $url);
            $this->loadCustLoop($request, $request->tree);
            echo '<div class="pT20">' 
                . $this->custReport->loadNodeURL($request, $request->nodeSlug) 
                . '</div>';
        }
        exit;
    }
    
    public function ajaxChecksAdmin(Request $request, $type = '')
    {
        $this->admControlInit($request, '/ajadm/' . $type);
        $this->loadCustLoop($request, $this->treeID);
        $newStatus = '';
        if ($request->has('status')) {
            $newStatus = trim($request->get('status'));
        }
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
            return $this->systemsCheckTestEmail($request);
        }
        if ($request->has('testCache') 
            && intVal($request->get('testCache')) == 1) {
            if ($request->has('sendTest') 
                && intVal($request->get('sendTest')) == 1) {
                Cache::put('testCache', trim($request->get('cacheVal')));
            }
            $testCache = '';
            if (Cache::has('testCache')) {
                $testCache = Cache::get('testCache');
            }
            return view(
                'vendor.survloop.admin.systems-check-cache', 
                [ "testCache" => $testCache ]
            );
        }
        $tree1 = SLTree::find(1);
        $this->v["sysChks"] = [];
        $this->v["sysChks"][] = ['Home',         '/'];
        $this->v["sysChks"][] = ['Survey Start', '/start/' . $tree1->tree_slug . ''];
        $this->v["sysChks"][] = ['Search Empty', '/search-results/1?s='];
        $this->v["sysChks"][] = ['Search Test',  '/search-results/1?s=testing'];
        $this->v["sysChks"][] = ['XML-Example',  '/' . $tree1->tree_slug . '-xml-example'];
        $this->v["sysChks"][] = ['XML-All',      '/' . $tree1->tree_slug . '-xml-all'];
        $this->v["sysChks"][] = ['XML-Schema',   '/' . $tree1->tree_slug . '-xml-schema'];
        return view('vendor.survloop.admin.systems-check', $this->v);
    }
    
    protected function systemsCheckTestEmail(Request $request)
    {
        $this->v["testResults"] = '';
        if ($request->has('sendTest') 
            && intVal($request->get('sendTest')) == 1
            && $request->has('emailTo') 
            && trim($request->emailTo) != '') {
            $to = trim($request->emailTo);
            $toArr = [ [ $to, 'Test Message' ] ];
            $subj = 'Email Flight Test from ' 
                . $GLOBALS["SL"]->sysOpts["site-name"];
            $cont = '<p>Hi there friend,</p><p>This has been a flight test from ' 
                . $GLOBALS["SL"]->sysOpts["site-name"] . '</p>';
            if (!$GLOBALS["SL"]->isHomestead()) {
                $this->sendEmail($cont, $subj, $toArr);
            }
            $tree = $this->treeID;
            $core = $this->coreID;
            $uID = $this->v["uID"];
            $this->logEmailSent($cont, $subj, $to, 0, $tree, $core, $uID);
            $this->v["testResults"] .= '<div class="container">'
                . '<h2>' . $subj . '</h2>' . $cont . '<hr><hr>'
                . '<i class="slBlueDark">to ' . $to . '</i></div>';
        }
        return view('vendor.survloop.admin.systems-check-email', $this->v);
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
        if (!Auth::user() || !Auth::user()->hasRole('administrator|staff')) {
            return $this->redir('/dashboard');
        }
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
                        SLDefinitions::where('def_set', 'Menu Settings')
                            ->where('def_subset', 'main-navigation')
                            ->where('def_database', 1)
                            ->where('def_order', $i)
                            ->update([
                                'def_value'       => $request->get('mainNavTxt' . $i),
                                'def_description' => $request->get('mainNavLnk' . $i)
                            ]);
                    } else {
                        SLDefinitions::where('def_set', 'Menu Settings')
                            ->where('def_subset', 'main-navigation')
                            ->where('def_database', 1)
                            ->where('def_order', $i)
                            ->delete();
                    }
                } elseif ($request->has('mainNavTxt' . $i)) {
                    $newLnk = new SLDefinitions;
                    $newLnk->def_set         = 'Menu Settings';
                    $newLnk->def_subset      = 'main-navigation';
                    $newLnk->def_database    = 1;
                    $newLnk->def_order       = $i;
                    $newLnk->def_value       = $request->get('mainNavTxt' . $i);
                    $newLnk->def_description = $request->get('mainNavLnk' . $i);
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
            if ($treeRow && isset($treeRow->tree_slug)) {
                return redirect('/' . $treeRow->tree_slug . '/read-' . $cid);
            }
        }
        return 'Not found :(';
    }
    
    public function debugNodeSaves(Request $request)
    {
        $this->admControlInit($request, '/dashboard/debug-node-saves');
        if (!Auth::user() || !Auth::user()->hasRole('administrator|staff')) {
            return $this->redir('/dashboard');
        }
        $treeID = $coreID = 0;
        if ($request->has('tree') && intVal($request->get('tree')) > 0) {
            $treeID = intVal($request->get('tree'));
            if ($request->has('cidi') && intVal($request->get('cidi')) > 0) {
                $coreID = intVal($request->get('cidi'));
            }
        }
        $this->v["nodeSaveReport"] = new NodeSaveSet($coreID, $treeID);
        $this->v["nodeLog"] = $this->logPreviewCore('session-stuff', $coreID);
        return view('vendor.survloop.admin.debug-node-saves', $this->v);
    }
    
}


