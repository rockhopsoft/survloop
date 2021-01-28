<?php
/**
  * AdminMenu is responsible for building the menu inside the dashboard area for all user types.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.18
  */
namespace RockHopSoft\Survloop\Controllers\Admin;

use App\Models\SLTree;
use App\Models\SLContact;

class AdminMenu
{
    private $currUser = null;
    private $currPage = '';
    
    public function loadAdmMenu($currUser = null, $currPage = '')
    {
        $this->currUser = $currUser;
        $this->currPage = $currPage;
        $treeMenu = [
            $this->addAdmMenuHome(),
            $this->admMenuLnk(
                'javascript:;', 
                'Submissions', 
                '<i class="fa fa-star"></i>', 
                1, 
                [
                    $this->admMenuLnk(
                        '/dashboard/subs/all',
                        'All Complete'
                    ), 
                    $this->admMenuLnk(
                        '/dashboard/subs/incomplete', 
                        'Incomplete Sessions'
                    )
                ]
            )
        ];
        return $this->addAdmMenuBasics($treeMenu);
    }
    
    protected function addAdmMenuHome()
    {
        return $this->admMenuLnk(
            '/dashboard', 
            'Dashboard', 
            '<i class="fa fa-home" aria-hidden="true"></i>'
        );
    }
    
    protected function addAdmMenuCollapse()
    {
        $arrow = 'fa-arrow-right';
        if ($GLOBALS["SL"]->openAdmMenuOnLoad()) {
            $arrow = 'fa-arrow-left';
        }
        return $this->admMenuLnk(
            'javascript:;" id="admMenuClpsBtn', 
            'Collapse', 
            '<i id="admMenuClpsArr" class="fa ' . $arrow . '" '
                . 'aria-hidden="true"></i>'
        );
    }
    
    protected function addAdmMenuBasics($treeMenu = [])
    {
        list($treeID, $treeLabel, $dbName) = $this->loadDbTreeShortNames();
        $treeOut = [ $this->addAdmMenuCollapse() ];
        if (sizeof($treeMenu) > 0) {
            foreach ($treeMenu as $lnk) {
                $treeOut[] = $lnk;
            }
        }
        $survLnk = '/dashboard/surv-' . $treeID;
        $treeOut[] = $this->admMenuLnk(
            'javascript:;', 
            'Site Content', 
            '<i class="fa fa-file-text-o" aria-hidden="true"></i>', 
            1, 
            [
                $this->admMenuLnk(
                    '/dashboard/pages',
                    'Pages & Reports', 
                    '', 
                    1, 
                    [
                        $this->admMenuLnk(
                            '/dashboard/pages',
                            'Web Content Pages'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/reports',
                            'Dynamic Reports'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/redirects',
                            'URL Redirects'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/pages/snippets',
                            'Content Snippets'
                        )
                    ]
                ), 
                $this->admMenuLnk(
                    '/dashboard/surveys/list', 
                    'Surveys & Forms', 
                    '', 
                    1, 
                    [
                        $this->admMenuLnk(
                            $survLnk . '/map?all=1&alt=1', 
                            'Full Map'
                        ), 
                        $this->admMenuLnk(
                            $survLnk . '/settings',
                            'Settings'
                        ), 
                        $this->admMenuLnk(
                            $survLnk . '/sessions',
                            'Sessions'
                        ), 
                        $this->admMenuLnk(
                            $survLnk . '/stats?all=1',
                            'Responses'
                        ),
                        $this->admMenuLnk(
                            $survLnk . '/data',
                            'Data Structures'
                        ), 
                        $this->admMenuLnk(
                            $survLnk . '/xmlmap',
                            'XML Map'
                        )
                    ]
                ), 
                $this->admMenuLnk(
                    '/dashboard/pages/menus',
                    'Navigation Menus'
                ), 
                $this->admMenuLnk(
                    '/dashboard/images/gallery', 
                    'Media Gallery'
                ),
                $this->admMenuLnk(
                    '/dashboard/emails',
                    'Email Manager', 
                    '', 
                    1, 
                    [
                        $this->admMenuLnk(
                            '/dashboard/emails',
                            'Manage Templates'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/send-email',
                            'Send Email'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/sent-emails',
                            'Sent Emails'
                        )
                    ]
                ),
                $this->admMenuLnkContact(false)
            ]
        );
        $treeOut[] = $this->admMenuLnk(
            'javascript:;', 
            'Database', 
            '<i class="fa fa-database"></i>', 
            1, 
            [
                $this->admMenuLnk(
                    '/dashboard/db', 
                    'Data Tables', 
                    '', 
                    1, 
                    [
                        $this->admMenuLnk(
                            '/dashboard/db',
                            'Full Table List'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/db/addTable',
                            'Add A New Table'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/db/sortTable',
                            'Re-Order Tables'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/db/diagrams',
                            'Data Diagrams'
                        )
                    ]
                ), 
                $this->admMenuLnk(
                    '/dashboard/db/all', 
                    'Data Fields', 
                    '', 
                    1, 
                    [
                        $this->admMenuLnk(
                            '/dashboard/db/all',
                            'Full Field Map'
                        ), 
                        $this->admMenuLnk(
                            '/dashboard/db/field-matrix?alt=1',
                            'Field Matrix: English'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/db/field-matrix',
                            'Field Matrix: Geek'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/db/bus-rules',
                            'Business Rules'
                        )
                    ]
                ), 
                $this->admMenuLnk(
                    '/dashboard/db/definitions', 
                    'Definition Lists'
                ),
                $this->admMenuLnk(
                    '/dashboard/db/conds',
                    'Filters / Conditions', 
                    '', 
                    1, 
                    [
                        $this->admMenuLnk(
                            '/dashboard/db/conds',
                            'All Conditions'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/db/conds?only=public',
                            'Public Only'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/db/conds?only=articles',
                            'Articles Only'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/db/conds/add',
                            'Add New Condition'
                        )
                    ]
                ),
                $this->admMenuLnk(
                    '/dashboard/db/fieldDescs',
                    'Field Descriptions'
                ), 
                $this->admMenuLnk(
                    '/dashboard/db/fieldXML',
                    'Field Privacy Settings'
                ), 
                //$this->admMenuLnk(
                //    '/dashboard/db/workflows',
                //    'Process Workflows'
                //),
                $this->admMenuLnk(
                    '/dashboard/db/export',
                    'Import / Export', 
                    '', 
                    1, 
                    [
                        $this->admMenuLnk(
                            '/dashboard/db/export',
                            'Full Database Export'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/sl/export/laravel',
                            'Survloop Package'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/db/import',
                            'Import'
                        )
                    ]
                ),
                $this->admMenuLnk(
                    '/dashboard/db/switch',
                    'Switch Databases'
                )
            ]
        );
        $treeOut[] = $this->admMenuLnk(
            'javascript:;', 
            'Settings', 
            '<i class="fa fa-cogs"></i>', 
            1, 
            [
                $this->admMenuLnk(
                    '/dashboard/settings',
                    'System Settings', 
                    '', 
                    1, 
                    [
                        $this->admMenuLnk(
                            '/dashboard/settings#search',
                            'Search Engines'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/settings#general',
                            'General Settings'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/settings#logos',
                            'Logos & Fonts'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/settings#color',
                            'Colors'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/settings#hardcode',
                            'Code HTML CSS JS'
                        )
                    ]
                ),
                $this->admMenuLnk(
                    '/dashboard/logs',
                    'System Logs', 
                    '', 
                    1, 
                    [
                        $this->admMenuLnk(
                            '/dashboard/logs', 
                            'All Logs'
                        ),
                        $this->admMenuLnk(
                            '/dashboard/logs/session-stuff', 
                            'Session Stuff'
                        )
                    ]
                ),
                $this->admMenuLnk(
                    '/dashboard/systems-check',
                    'System Check'
                ),
                $this->admMenuLnk(
                    '/dashboard/systems-update',
                    'System Updates'
                ),
                $this->admMenuLnk(
                    '/dashboard/users',
                    'Users'
                )
            ]
        );
        return $treeOut;
    }
    
    static public function admMenuLnk($url = '', $text = '', $ico = '', $opt = 1, $children = [])
    {
        return [ 
            $url, 
            $text, 
            $ico, 
            $opt, 
            $children 
        ];
    }
    
    protected function admMenuLnkContact($icon = true)
    {
        $cnt = $this->admMenuLnkContactCnt();
        $lnk = 'Contact Form' . (($cnt > 0) 
            ? '<sup id="contactPush" class="red mL5">' 
                . $cnt . '</sup> ' 
            : '');
        $ico = '';
        if ($icon) {
            $ico = '<i class="fa fa-envelope-o" '
                . 'aria-hidden="true"></i> ';
        }
        $ret = [
            '/dashboard/contact', 
            $lnk, 
            $ico, 
            1, 
            [
                $this->admMenuLnk(
                    '/dashboard/contact', 
                    'Unread'
                ),
                $this->admMenuLnk(
                    '/dashboard/contact/hold', 
                    'On Hold'
                ),
                $this->admMenuLnk(
                    '/dashboard/contact/resolved', 
                    'Resolved'
                ),
                $this->admMenuLnk(
                    '/dashboard/contact/trash', 
                    'Trash'
                )
            ]
        ];
        return $ret;
    }
    
    public function admMenuLnkContactCnt()
    {
        $chk = SLContact::where('cont_flag', 'Unread')
            ->select('cont_id')
            ->get();
        return $chk->count();
    }
    
    protected function loadDbTreeShortNames()
    {
        $dbName = '';
        if (isset($GLOBALS["SL"]->dbRow->db_name)) {
            $dbName = $GLOBALS["SL"]->dbRow->db_name;
        }
        $prefix = str_replace('_', '', $GLOBALS["SL"]->dbRow->db_prefix);
        if (strlen($dbName) > 20 && isset($GLOBALS["SL"]->dbRow->db_name)) {
            $dbName = str_replace(
                $GLOBALS["SL"]->dbRow->db_name, 
                $prefix, 
                $dbName
            );
        }
        $treeID = 1;
        $treeName = '';
        if (isset($GLOBALS["SL"]->treeRow->tree_id)) {
            $treeID = $GLOBALS["SL"]->treeRow->tree_id;
            if (isset($GLOBALS["SL"]->treeName)) {
                $treeName = $GLOBALS["SL"]->treeName;
            }
            if ($GLOBALS["SL"]->treeRow->tree_type == 'Page') {
                $tree = SLTree::find(1);
                if ($tree && isset($tree->tree_id)) {
                    $treeID = $tree->tree_id;
                    $treeName = 'Tree: ' . $tree->tree_name;
                }
            }
        }
        return [ $treeID, $treeName, $dbName ];
    }
    
}
