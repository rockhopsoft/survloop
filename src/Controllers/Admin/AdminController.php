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
use DB;
use Storage;
use Illuminate\Http\Request;
use MatthiasMullie\Minify;
use App\Models\SLDatabases;
use App\Models\SLDefinitions;
use App\Models\SLNode;
use App\Models\SLTree;
use App\Models\User;
use RockHopSoft\Survloop\Controllers\SystemDefinitions;
use RockHopSoft\Survloop\Controllers\SystemUpdate;
use RockHopSoft\Survloop\Controllers\Admin\AdminEmailController;
use RockHopSoft\Survloop\Controllers\Admin\NodeSaveSet;
use RockHopSoft\Survloop\Controllers\Admin\SurvLogAnalysis;

class AdminController extends AdminEmailController
{
    /**
     * Load interface used to switch which database
     * is being managed at the moment.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $dbID
     * @return Illuminate\Support\Facades\Response
     */
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

    /**
     * Load and process system settings interfaces for admin users.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $set
     * @return Illuminate\Support\Facades\Response
     */
    public function sysSettings(Request $request, $set = 'seo')
    {
        $this->v["sysSet"] = $set;
        $pageUrl = '/dashboard/settings/' . $set . '?ajax=1';
        $this->admControlInit($request, $pageUrl, '', false);
        $this->sysSettingsCheckRefresh($request);
        $this->reloadAdmMenu();
        $this->getSysCssJs($request);
        $this->v["sysDef"] = new SystemDefinitions;
        if ($request->has('refresh')) {
            $GLOBALS["SL"]->clearAllSystemCaches();
            $this->v["sysDef"]->checkDefInstalls();
        }
        $this->v["sysDef"]->prepSysSettings($request);
        if ($request->has('sub')) {
            return $this->v["sysDef"]->submitSysSettings(
                $request,
                $this->v["sysSet"]
            );
        }
        $this->sysSettingsLoadCurrMeta();
        $blade = 'vendor.survloop.admin.system-settings-'
            . $this->v["sysSet"];
        $this->v["content"] = view($blade, $this->v)->render();
        if ($request->has('ajax')) {
            echo $this->v["content"];
            exit;
        }
        $this->v["content"] = $GLOBALS["SL"]->pullPageJsCss($this->v["content"]);
        return view('vendor.survloop.master', $this->v);
    }

    /**
     * Load default meta data for a page load,
     * based on the system settings.
     *
     * @return void
     */
    private function sysSettingsLoadCurrMeta()
    {
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
    }

    /**
     * Check for special admin refresh commands.
     *
     * @param  Illuminate\Http\Request  $request
     * @return void
     */
    private function sysSettingsCheckRefresh(Request $request)
    {
        if ($request->has('refresh')) {
            $this->cacheFlush();
            $ref = intVal($request->get('refresh'));
            if ($ref == 2) {
                $this->initLoader();
                $this->sysSettingsRefresh($request);
                return $this->redir('/dashboard/settings?refresh=1', true);
            }
        }
    }

    /**
     * Force a refresh of trees and their baseline javascript.
     *
     * @param  Illuminate\Http\Request  $request
     * @return void
     */
    private function sysSettingsRefresh(Request $request)
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
                $this->sysSettingsRefreshTree($request, $curr, $next, $found, $tree);
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
    }

    /**
     * Force a refresh of trees and their baseline javascript.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $curr
     * @param  int  $next
     * @param  boolean  $found
     * @param  App\Models\SLTree  $tree
     * @return void
     */
    private function sysSettingsRefreshTree($request, &$curr, &$next, &$found, $tree)
    {
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
            $this->custReport->loadProgBarBot();
        }
        return true;
    }

    /**
     * Load system blurb record from its unique ID.
     *
     * @param  int  $blurbID
     * @return App\Models\SLDefinitions
     */
    protected function blurbLoad($blurbID)
    {
        return SLDefinitions::where('def_id', $blurbID)
            ->where('def_database', $this->dbID)
            ->where('def_set', 'Blurbs')
            ->first();
    }

    /**
     * Load interface to edit system blurb record from its unique ID.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $blurbID
     * @return Illuminate\Support\Facades\Response
     */
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

    /**
     * Create new system blurb for some chunk of HTML/CSS/JS.
     * Returns new blurb's unique ID.
     *
     * @param  Illuminate\Http\Request  $request
     * @return int
     */
    public function blurbNew(Request $request)
    {
        if ($request->has('newBlurbName')
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

    /**
     * Save edits to system blurb for some chunk of HTML/CSS/JS.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
    public function blurbEditSave(Request $request)
    {
        $blurb = $this->blurbLoad($request->DefID);
        $blurb->def_subset      = $request->DefSubset;
        $blurb->def_description = $request->DefDescription;
        $blurb->def_order = 1;
        if ($request->has('optHardCode')
            && intVal($request->optHardCode) == 3) {
            $blurb->def_order *= 3;
        }
        $blurb->save();
        $redir = '/dashboard/pages/snippets/' . $blurb->def_id;
        return $this->redir($redir);
    }

    /**
     * Regenerate all system CSS and JS files.
     *
     * @param  Illuminate\Http\Request  $request
     * @return string
     */
    public function getSysCssJs(Request $request)
    {
        $this->survloopInit($request, '/dashboard/settings', false);
        if (!is_dir('../storage/app/sys')) {
            mkdir('../storage/app/sys');
        }
        $this->v["sysDef"] = new SystemDefinitions;
        $css = $this->v["sysDef"]->loadCss();
        $css["raw"] = $GLOBALS["SL"]->getSysCustCSS();
        $this->getCssFile1($css);
        $this->getCssFile2($css);
        $this->getJsFile1();
        $this->getJsFile2($css);
        $log = SLDefinitions::where('def_set', 'System Settings')
            ->where('def_subset', 'log-css-reload')
            ->update([ 'def_description' => time() ]);
        return ':) ';
    }

    /**
     * Regenerate the first, basic system CSS file.
     *
     * @param  array  $css
     * @return void
     */
    private function getCssFile1($css)
    {
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
            $files = $GLOBALS["SL"]->sysOpts["css-extra-files"];
            $files = $GLOBALS["SL"]->mexplode(',', $files);
            foreach ($files as $f) {
                $minifier->add(trim($f));
            }
        }
        $minifier->minify("../storage/app/sys/sys1.min.css");
    }

    /**
     * Regenerate the second system-specific CSS file.
     *
     * @param  array  $css
     * @return void
     */
    private function getCssFile2($css)
    {
        $syscss = view(
            'vendor.survloop.css.styles-2',
            [ "css" => $css ]
        )->render();
        file_put_contents("../storage/app/sys/sys2.css", $syscss);
        $minifier = new Minify\CSS("../storage/app/sys/sys2.css");
        $minifier->minify("../storage/app/sys/sys2.min.css");
    }

    /**
     * Regenerate the first, basic system JS file.
     *
     * @return void
     */
    private function getJsFile1()
    {
        $minifier = new Minify\JS("../vendor/components/jquery/jquery.min.js");
        $minifier->add("../vendor/components/jqueryui/jquery-ui.min.js");
        $minifier->add("../vendor/rockhopsoft/survloop-libraries/src/js/popper.min.js");
        $minifier->add("../vendor/twbs/bootstrap/dist/js/bootstrap.min.js");
        //$minifier->add("../vendor/rockhopsoft/survloop-libraries/src/js/parallax.min.js");
        $minifier->add("../vendor/rockhopsoft/survloop-libraries/src/js/typewatch.js");
        $minifier->add("../vendor/rockhopsoft/survloop-libraries/src/js/copy-to-clipboard.js");
        //$minifier->add("../vendor/rockhopsoft/survloop-libraries/src/js/radialIndicator.min.js");
        $minifier->minify("../storage/app/sys/sys1.min.js");
    }

    /**
     * Regenerate the second system-specific JS file.
     *
     * @param  array  $css
     * @return void
     */
    private function getJsFile2($css)
    {
        $treeJs = '';
        $chk = SLTree::whereIn('tree_type', ['Page', 'Survey'])
            ->select('tree_id', 'tree_type')
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tree) {
                $treeFile = '../storage/app/sys/tree-'
                    . $tree->tree_id . '.js';
                if (file_exists($treeFile)) {
                    $tmpJs = file_get_contents($treeFile);
                    $tmpJs = str_replace("\t", "",
                        str_replace("\n", "\n", $tmpJs));
                    $treeJs .= "\n" . 'function treeLoad'
                        . $tree->tree_id . '() {' . "\n"
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
    }

    /**
     * Check if the table ID is a core table for any system trees.
     *
     * @param  int  $tblID
     * @return boolean
     */
    protected function isCoreTbl($tblID)
    {
        $chkCore = SLTree::where('tree_core_table', '=', $tblID)
            ->get();
        return $chkCore->isNotEmpty();
    }

    /**
     * Check if the table ID is a core table for any system trees.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
    public function userManage(Request $request)
    {
        ini_set('max_execution_time', 300);
        $this->admControlInit($request, '/dashboard/users', 'administrator|staff');
        $this->loadPrintUsers();
        return view('vendor.survloop.admin.user-manage', $this->v);
    }

    /**
     * Load all users organized by their highest permissions.
     *
     * @return void
     */
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

    /**
     * Process posting of login-required survey.
     *
     * @param  Illuminate\Http\Request  $request
     * @return void
     */
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

    /**
     * Process login-required ajax/jquery requests.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  string  $type
     * @return string
     */
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

    /**
     * Display page which checks several aspects of the Survloop systems.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
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

    /**
     * Display page to send test emails from this Survloop installation.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
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

    /**
     * Load system session logs for display or analysis.
     *
     * @return void
     */
    public function loadLogs()
    {
        $logs = new SurvLogAnalysis;
        $this->v["logs"] = [
            "session" => $logs->logPreview('session-stuff')
        ];
    }

    /**
     * Load and print system overview logs.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
    public function logsOverview(Request $request)
    {
        $this->admControlInit($request, '/dashboard/logs');
        $this->loadLogs();
        $this->v["phpInfo"] = $request->has('phpinfo');
        return view('vendor.survloop.admin.logs-overview', $this->v);
    }

    /**
     * Load and print system session logs.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
    public function logsSessions(Request $request)
    {
        $this->admControlInit($request, '/dashboard/logs/session-stuff');
        if (!Auth::user() || !Auth::user()->hasRole('administrator|staff')) {
            return $this->redir('/dashboard');
        }
        $this->loadLogs();
        return view('vendor.survloop.admin.logs-sessions', $this->v);
    }

    /**
     * Generate interface to manage navigation menus.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
    public function navMenus(Request $request)
    {
        $this->admControlInit($request, '/dashboard/pages/menus');
        $this->v["cntMax"] = 25;
        if ($request->has('sub') && intVal($request->get('sub')) == 1) {
            $this->saveNavMenus($request);
        }
        $this->v["cnt"] = 0;
        return view('vendor.survloop.admin.manage-menus', $this->v);
    }

    /**
     * Save changes to system navigation menus.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
    public function saveNavMenus(Request $request)
    {
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

    /**
     * Generate interface to manage the
     * system gallery of uploaded images.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
    public function imgGallery(Request $request)
    {
        $this->admControlInit($request, '/dashboard/images/gallery');
        if ($request->has('sub') && intVal($request->get('sub')) == 1) {

        }
        $this->v["imgSelect"] = $GLOBALS["SL"]->getImgSelect('-3', $GLOBALS["SL"]->dbID);
        return view('vendor.survloop.admin.images-gallery', $this->v);
    }

    /**
     * Generically print view of system's survey submission.
     *
     * @param  Illuminate\Http\Request  $request
     * @param  int  $treeID
     * @param  int  $cid
     * @return Illuminate\Support\Facades\Response
     */
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

    /**
     * Generate report of specific node saves within a survey experience.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
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
        $logs = new SurvLogAnalysis;
        $this->v["nodeLog"] = $logs->logPreviewCore('session-stuff', $coreID);
        return view('vendor.survloop.admin.debug-node-saves', $this->v);
    }

    /**
     * Run system cleanup processes.
     *
     * @param  Illuminate\Http\Request  $request
     * @return Illuminate\Support\Facades\Response
     */
    public function systemsClean(Request $request)
    {
        $sysUp = new SystemUpdate;
        return $sysUp->index($request);
    }

    /**
     * Totally flush the current cache.
     *
     * @return void
     */
    protected function cacheFlush()
    {
        DB::table('sl_caches')->truncate();
        Cache::flush();
        $GLOBALS["SL"]->flushRedis();
    }
}