<?php
/**
  * SurvloopInstaller initiallizes a basic Survloop installation, after command line installation is complete.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author   Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.0.7
  */
namespace RockHopSoft\Survloop\Controllers;

use DB;
use Illuminate\Routing\Controller;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLDefinitions;
use RockHopSoft\Survloop\Controllers\Globals\Globals;
use RockHopSoft\Survloop\Controllers\SystemDefinitions;

class SurvloopInstaller extends Controller
{
    
    public function checkSysInit()
    {
        $chkSysDef = new SystemDefinitions;
        $chkSysDef->checkDefInstalls();
        $type = Globals::TREEOPT_HOMEPAGE*Globals::TREEOPT_ADMIN;
        $this->installPageSimpl('Dashboard', $type, 'dashboard');
        $this->installPageSimpl('Home', Globals::TREEOPT_HOMEPAGE);
        $type = Globals::TREEOPT_SEARCH*Globals::TREEOPT_ADMIN;
        $this->installPageSimpl('Admin Search', $type, 'search');
        $this->installPageSimpl('Search', Globals::TREEOPT_SEARCH);
        $specialOpts = [
            [ 'Dashboard', Globals::TREEOPT_HOMEPAGE ],
            [ 'Search',    Globals::TREEOPT_SEARCH   ]
        ];
        $permOpts = [
            [ 'Staff',     Globals::TREEOPT_STAFF     ],
            [ 'Partner',   Globals::TREEOPT_PARTNER   ],
            [ 'Volunteer', Globals::TREEOPT_VOLUNTEER ]
        ];
        foreach ($specialOpts as $keyType) {
            if (!$this->chkPagePerm($keyType[1], Globals::TREEOPT_ADMIN)) {
                $type = $keyType[1]*Globals::TREEOPT_ADMIN;
                $this->installPageSimpl($keyType[0], $type);
            }
            foreach ($permOpts as $perm) {
                if (!$this->chkPagePerm($keyType[1], $perm[1])) {
                    $type = $keyType[1]*$perm[1];
                    $this->installPageSimpl($perm[0] . ' ' . $keyType[0], $type);
                }
            }
        }
        if (!$this->chkPagePerm(Globals::TREEOPT_PROFILE)) {
            $this->installPageMyProfile();
        }
        return true;
    }
    
    public function chkPagePerm($keyOptType, $perm = 0)
    {
        $adm = Globals::TREEOPT_ADMIN;
        $stf = Globals::TREEOPT_STAFF;
        $prt = Globals::TREEOPT_PARTNER;
        $vol = Globals::TREEOPT_VOLUNTEER;
        $chk = SLTree::where('tree_type', 'Page')
            /* ->whereRaw("tree_opts%" . $keyOptType  . " = 0")
            ->whereRaw("tree_opts%" . $adm  . " > 0")
            ->whereRaw("tree_opts%" . $stf  . " > 0")
            ->whereRaw("tree_opts%" . Globals::TREEOPT_PARTNER  . " > 0")
            ->whereRaw("tree_opts%" . Globals::TREEOPT_VOLUNTEER  . " > 0") */
            ->get();
        if ($chk->isNotEmpty()) {
            foreach ($chk as $tree) {
                if (isset($tree->tree_opts) 
                    && $tree->tree_opts%$keyOptType == 0) {
                    if ($perm <= 1) {
                        return true;
                    }
                    if ($perm == $adm) {
                        if ($tree->tree_opts%$adm == 0 
                            && $tree->tree_opts%$stf > 0 
                            && $tree->tree_opts%$prt > 0 
                            && $tree->tree_opts%$vol > 0) {
                            return true;
                        }
                    } elseif ($perm == $stf) {
                        if ($tree->tree_opts%$adm > 0 
                            && $tree->tree_opts%$stf == 0 
                            && $tree->tree_opts%$prt > 0 
                            && $tree->tree_opts%$vol > 0) {
                            return true;
                        }
                    } elseif ($perm == $prt) {
                        if ($tree->tree_opts%$adm > 0 
                            && $tree->tree_opts%$stf > 0 
                            && $tree->tree_opts%$prt == 0 
                            && $tree->tree_opts%$vol > 0) {
                            return true;
                        }
                    } elseif ($perm == $vol) {
                        if ($tree->tree_opts%$adm > 0 
                            && $tree->tree_opts%$stf > 0 
                            && $tree->tree_opts%$prt > 0 
                            && $tree->tree_opts%$vol == 0) {
                            return true;
                        }
                    }
                }
            }
        }
        return false;
    }
    
    public function installPageSimpl($name = 'Home', $opts = 1, $slug = '')
    {
        if (trim($slug) == '') {
            $slug = $GLOBALS["SL"]->slugify($name);
        }
        $add = true;
        if ($opts > 1) {
            $chk = SLTree::where('tree_type', 'Page')
                ->where('tree_opts', $opts)
                ->first();
            if ($chk && isset($chk->tree_opts)) {
                $add = false;
            }
        }
        if (!$add) {
            return null;
        }
        $newTree = new SLTree;
        $newTree->tree_type      = 'Page';
        $newTree->tree_name      = $name;
        $newTree->tree_slug      = $slug;
        $newTree->tree_database  = 1;
        $newTree->tree_user      = 0;
        $newTree->tree_opts      = $opts;
        $newTree->save();
        $node = new SLNode;
        $node->node_tree         = $newTree->tree_id;
        $node->node_parent_id    = -3;
        $node->node_type         = 'Page';
        $node->node_prompt_notes = $slug;
        $node->save();
        $newTree->tree_root      = $node->node_id;
        $newTree->save();
        $n = new SLNode;
        $n->node_tree            = $newTree->tree_id;
        $n->node_parent_id       = $node->node_id;
        $n->node_type            = 'Instructions';
        $n->node_prompt_text     = '<center><h1 style="margin-top: 50px;">'
            . 'Coming Soon</h1></center>';
        $n->save();
        return $newTree;
    }
    
    public function installPageMyProfile()
    {
        if ($opts > 1) {
            $chk = SLTree::where('tree_type', 'Page')
                ->where('tree_opts', 23)
                ->first();
            if ($chk && isset($chk->tree_opts)) {
                return null;
            }
        }
        $newTree = new SLTree;
        $newTree->tree_type       = 'Page';
        $newTree->tree_name       = 'My Profile';
        $newTree->tree_slug       = 'my-profile';
        $newTree->tree_database   = 1;
        $newTree->tree_user       = 1;
        $newTree->tree_opts       = 23;
        $newTree->save();
        $nPage = new SLNode;
        $nPage->node_tree         = $newTree->tree_id;
        $nPage->node_parent_id    = -3;
        $nPage->node_type         = 'Page';
        $nPage->node_prompt_notes = 'my-profile';
        $nPage->save();
        $newTree->tree_root       = $nPage->node_id;
        $newTree->save();
        $n = new SLNode;
        $n->node_tree             = $newTree->tree_id;
        $n->node_parent_id        = $nPage->node_id;
        $n->node_parent_order     = 0;
        $n->node_type             = 'Member Profile Basics';
        $n->save();
        $nRow = new SLNode;
        $nRow->node_tree         = $newTree->tree_id;
        $nRow->node_parent_id     = $nPage->node_id;
        $nRow->node_parent_order  = 1;
        $nRow->node_type          = 'Layout Row';
        $nRow->save();
        $nColL = new SLNode;
        $nColL->node_tree         = $newTree->tree_id;
        $nColL->node_parent_id    = $nRow->node_id;
        $nColL->node_parent_order = 0;
        $nColL->node_type         = 'Layout Column';
        $nColL->node_char_limit   = 7;
        $nColL->save();
        $n = new SLNode;
        $n->node_tree             = $newTree->tree_id;
        $n->node_parent_id        = $nRow->node_id;
        $n->node_parent_order     = 1;
        $n->node_type             = 'Layout Column';
        $n->node_char_limit       = 1;
        $n->save();
        $nColR = new SLNode;
        $nColR->node_tree         = $newTree->tree_id;
        $nColR->node_parent_id    = $nRow->node_id;
        $nColR->node_parent_order = 2;
        $nColR->node_type         = 'Layout Column';
        $nColR->node_char_limit   = 4;
        $nColR->save();
        $n = new SLNode;
        $n->node_tree             = $newTree->tree_id;
        $n->node_parent_id        = $nColL->node_id;
        $n->node_type             = 'Search Results';
        $n->node_prompt_text      = '<h2>Your Participation</h2>';
        $n->node_response_set     = 1;
        $n->node_data_branch      = 'users';
        $n->save();
        $n = new SLNode;
        $n->node_tree             = $newTree->tree_id;
        $n->node_parent_id        = $nColR->node_id;
        $n->node_type             = 'Incomplete Sess Check';
        $n->node_response_set     = 1;
        $n->save();
        return $newTree;
    }
    
    public function installPageContact()
    {
        $newTree = new SLTree;
        $newTree->tree_type     = 'Page';
        $newTree->tree_name     = 'Contact';
        $newTree->tree_slug     = 'contact';
        $newTree->tree_database = 1;
        $newTree->tree_user     = 1;
        $newTree->tree_opts     = 19; // %19 indicates contact form
        $newTree->save();
        
        $nodePage = new SLNode;
        $nodePage->node_tree         = $newTree->tree_id;
        $nodePage->node_parent_id    = -3;
        $nodePage->node_type         = 'Page';
        $nodePage->node_prompt_notes = 'contact';
        $nodePage->node_opts         = 67;
        $nodePage->save();
        $newTree->tree_root          = $nodePage->node_id;
        $newTree->save();
        
        $n = new SLNode;
        $n->node_tree         = $newTree->tree_id;
        $n->node_parent_id    = $nodePage->node_id;
        $n->node_parent_order = 0;
        $n->node_type         = 'Instructions';
        $n->node_prompt_text  = '<h2>Contact Us</h2>';
        $n->save();
        $n = new SLNode;
        $n->node_tree         = $newTree->tree_id;
        $n->node_parent_id    = $nodePage->node_id;
        $n->node_parent_order = 1;
        $n->node_type         = 'Text';
        $n->node_prompt_text  = 'Your Email Address';
        $n->node_data_store   = 'SLContact:ContEmail';
        $n->save();
        $n = new SLNode;
        $n->node_tree         = $newTree->tree_id;
        $n->node_parent_id    = $nodePage->node_id;
        $n->node_parent_order = 2;
        $n->node_type         = 'Text';
        $n->node_prompt_text  = 'Subject Line';
        $n->node_data_store   = 'SLContact:ContSubject';
        $n->save();
        $n = new SLNode;
        $n->node_tree         = $newTree->tree_id;
        $n->node_parent_id    = $nodePage->node_id;
        $n->node_parent_order = 3;
        $n->node_type         = 'Long Text';
        $n->node_prompt_text  = 'Your Message';
        $n->node_prompt_after = '<style> #n[[nID]]FldID { height: 250px; } </style>';
        $n->node_data_store   = 'SLContact:ContBody';
        $n->save();
        $n = new SLNode;
        $n->node_tree         = $newTree->tree_id;
        $n->node_parent_id    = $nodePage->node_id;
        $n->node_parent_order = 4;
        $n->node_type         = 'Spambot Honey Pot';
        $n->node_prompt_text  = 'Reason For Contact';
        $n->node_data_store   = 'SLContact:ContType';
        $n->save();
        $n = new SLNode;
        $n->node_tree         = $newTree->tree_id;
        $n->node_parent_id    = $nodePage->node_id;
        $n->node_parent_order = 5;
        $n->node_type         = 'Big Button';
        $n->node_default      = 'Send Your Message';
        $n->node_data_store   = 'document.postNode.submit();';
        $n->save();
        
        $def = new SLDefinitions;
        $def->def_database = 1;
        $def->def_set      = 'Value Ranges';
        $def->def_subset   = 'Contact Reasons';
        $def->def_order    = 0;
        $def->def_value    = 'General Feedback';
        $def->save();
        $def = new SLDefinitions;
        $def->def_database = 1;
        $def->def_set      = 'Value Ranges';
        $def->def_subset   = 'Contact Reasons';
        $def->def_order    = 1;
        $def->def_value    = 'Website Problems';
        $def->save();
        $def = new SLDefinitions;
        $def->def_database = 1;
        $def->def_set      = 'Value Ranges';
        $def->def_subset   = 'Contact Reasons';
        $def->def_order    = 2;
        $def->def_value    = 'Networking Opportunities';
        $def->save();
        
        return $newTree;
    }
    
}