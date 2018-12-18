<?php
/**
  * SurvLoopInstaller initiallizes a basic SurvLoop installation, after command line installation is complete.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  wikiworldorder/survloop
  * @author   Morgan Lesko <mo@wikiworldorder.org>
  * @since 0.0
  */
namespace SurvLoop\Controllers;

use DB;
use Illuminate\Routing\Controller;
use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLDefinitions;
use SurvLoop\Controllers\Globals;
use SurvLoop\Controllers\SystemDefinitions;

class SurvLoopInstaller extends Controller
{
    
    public function checkSysInit()
    {
        $chkSysDef = new SystemDefinitions;
        $chkSysDef->checkDefInstalls();
        foreach ([ Globals::TREEOPT_HOMEPAGE, Globals::TREEOPT_SEARCH ] as $keyOptType) {
            $typeName = (($keyOptType == Globals::TREEOPT_HOMEPAGE) ? 'Dashboard' : 'Search');
            $chk = SLTree::where('TreeType', 'Page')
                ->whereRaw("TreeOpts%" . $keyOptType  . " = 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_ADMIN  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_STAFF  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_PARTNER  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_VOLUNTEER  . " > 0")
                ->first();
            if (!$chk || !isset($chk->TreeID)) {
                $this->installPageSimpl('Home', $keyOptType);
            }
            $chk = SLTree::where('TreeType', 'Page')
                ->whereRaw("TreeOpts%" . $keyOptType  . " = 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_ADMIN  . " = 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_STAFF  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_PARTNER  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_VOLUNTEER  . " > 0")
                ->first();
            if (!$chk || !isset($chk->TreeID)) {
                $this->installPageSimpl($typeName, ($keyOptType*Globals::TREEOPT_ADMIN));
            }
            $chk = SLTree::where('TreeType', 'Page')
                ->whereRaw("TreeOpts%" . $keyOptType  . " = 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_ADMIN  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_STAFF  . " = 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_PARTNER  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_VOLUNTEER  . " > 0")
                ->first();
            if (!$chk || !isset($chk->TreeID)) {
                $this->installPageSimpl('Staff ' . $typeName, ($keyOptType*Globals::TREEOPT_STAFF));
            }
            $chk = SLTree::where('TreeType', 'Page')
                ->whereRaw("TreeOpts%" . $keyOptType  . " = 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_ADMIN  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_STAFF  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_PARTNER  . " = 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_VOLUNTEER  . " > 0")
                ->first();
            if (!$chk || !isset($chk->TreeID)) {
                $this->installPageSimpl('Partner ' . $typeName, ($keyOptType*Globals::TREEOPT_PARTNER));
            }
            $chk = SLTree::where('TreeType', 'Page')
                ->whereRaw("TreeOpts%" . $keyOptType  . " = 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_ADMIN  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_STAFF  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_PARTNER  . " > 0")
                ->whereRaw("TreeOpts%" . Globals::TREEOPT_VOLUNTEER  . " = 0")
                ->first();
            if (!$chk || !isset($chk->TreeID)) {
                $this->installPageSimpl('Volunteer ' . $typeName, ($keyOptType*Globals::TREEOPT_VOLUNTEER));
            }
        }
        $chk = SLTree::where('TreeType', 'Page')
            ->whereRaw("TreeOpts%" . Globals::TREEOPT_PROFILE  . " = 0")
            ->first();
        if (!$chk || !isset($chk->TreeID)) {
            $this->installPageMyProfile();
        }
        return true;
    }
    
    public function installPageSimpl($name = 'Home', $opts = 1, $slug = '')
    {
        if (trim($slug) == '') $slug = $GLOBALS["SL"]->slugify($name);
        $newTree = new SLTree;
        $newTree->TreeType     = 'Page';
        $newTree->TreeName     = $name;
        $newTree->TreeSlug     = $slug;
        $newTree->TreeDatabase = 1;
        $newTree->TreeUser     = 0;
        $newTree->TreeOpts     = $opts;
        $newTree->save();
        $node = new SLNode;
        $node->NodeTree        = $newTree->TreeID;
        $node->NodeParentID    = -3;
        $node->NodeType        = 'Page';
        $node->NodePromptNotes = $slug;
        $node->save();
        $newTree->TreeRoot     = $node->NodeID;
        $newTree->save();
        $n = new SLNode;
        $n->NodeTree           = $newTree->TreeID;
        $n->NodeParentID       = $node->NodeID;
        $n->NodeType           = 'Instructions';
        $n->NodePromptText     = '<center><h1 style="margin-top: 50px;">Coming Soon</h1></center>';
        $n->save();
        return $newTree;
    }
    
    public function installPageMyProfile()
    {
        $newTree = new SLTree;
        $newTree->TreeType      = 'Page';
        $newTree->TreeName      = 'My Profile';
        $newTree->TreeSlug      = 'my-profile';
        $newTree->TreeDatabase  = 1;
        $newTree->TreeUser      = 1;
        $newTree->TreeOpts      = 23;
        $newTree->save();
        $nPage = new SLNode;
        $nPage->NodeTree        = $newTree->TreeID;
        $nPage->NodeParentID    = -3;
        $nPage->NodeType        = 'Page';
        $nPage->NodePromptNotes = 'my-profile';
        $nPage->save();
        $newTree->TreeRoot      = $nPage->NodeID;
        $newTree->save();
        $n = new SLNode;
        $n->NodeTree            = $newTree->TreeID;
        $n->NodeParentID        = $nPage->NodeID;
        $n->NodeParentOrder     = 0;
        $n->NodeType            = 'Member Profile Basics';
        $n->save();
        $nRow = new SLNode;
        $nRow->NodeTree         = $newTree->TreeID;
        $nRow->NodeParentID     = $nPage->NodeID;
        $nRow->NodeParentOrder  = 1;
        $nRow->NodeType         = 'Layout Row';
        $nRow->save();
        $nColL = new SLNode;
        $nColL->NodeTree        = $newTree->TreeID;
        $nColL->NodeParentID    = $nRow->NodeID;
        $nColL->NodeParentOrder = 0;
        $nColL->NodeType        = 'Layout Column';
        $nColL->NodeCharLimit   = 7;
        $nColL->save();
        $n = new SLNode;
        $n->NodeTree            = $newTree->TreeID;
        $n->NodeParentID        = $nRow->NodeID;
        $n->NodeParentOrder     = 1;
        $n->NodeType            = 'Layout Column';
        $n->NodeCharLimit       = 1;
        $n->save();
        $nColR = new SLNode;
        $nColR->NodeTree        = $newTree->TreeID;
        $nColR->NodeParentID    = $nRow->NodeID;
        $nColR->NodeParentOrder = 2;
        $nColR->NodeType        = 'Layout Column';
        $nColR->NodeCharLimit   = 4;
        $nColR->save();
        $n = new SLNode;
        $n->NodeTree            = $newTree->TreeID;
        $n->NodeParentID        = $nColL->NodeID;
        $n->NodeType            = 'Search Results';
        $n->NodePromptText      = '<h2>Your Participation</h2>';
        $n->NodeResponseSet     = 1;
        $n->NodeDataBranch      = 'users';
        $n->save();
        $n = new SLNode;
        $n->NodeTree            = $newTree->TreeID;
        $n->NodeParentID        = $nColR->NodeID;
        $n->NodeType            = 'Incomplete Sess Check';
        $n->NodeResponseSet     = 1;
        $n->save();
        return $newTree;
    }
    
    public function installPageContact()
    {
        $newTree = new SLTree;
        $newTree->TreeType         = 'Page';
        $newTree->TreeName         = 'Contact';
        $newTree->TreeSlug         = 'contact';
        $newTree->TreeDatabase     = 1;
        $newTree->TreeUser         = 1;
        $newTree->TreeOpts         = 19; // %19 indicates contact form
        $newTree->save();
        
        $nodePage = new SLNode;
        $nodePage->NodeTree        = $newTree->TreeID;
        $nodePage->NodeParentID    = -3;
        $nodePage->NodeType        = 'Page';
        $nodePage->NodePromptNotes = 'contact';
        $nodePage->NodeOpts        = 67;
        $nodePage->save();
        $newTree->TreeRoot         = $nodePage->NodeID;
        $newTree->save();
        
        $n = new SLNode;
        $n->NodeTree           = $newTree->TreeID;
        $n->NodeParentID       = $nodePage->NodeID;
        $n->NodeParentOrder    = 0;
        $n->NodeType           = 'Instructions';
        $n->NodePromptText     = '<h2>Contact Us</h2>';
        $n->save();
        $n = new SLNode;
        $n->NodeTree           = $newTree->TreeID;
        $n->NodeParentID       = $nodePage->NodeID;
        $n->NodeParentOrder    = 1;
        $n->NodeType           = 'Text';
        $n->NodePromptText     = 'Your Email Address';
        $n->NodeDataStore      = 'SLContact:ContEmail';
        $n->save();
        $n = new SLNode;
        $n->NodeTree           = $newTree->TreeID;
        $n->NodeParentID       = $nodePage->NodeID;
        $n->NodeParentOrder    = 2;
        $n->NodeType           = 'Text';
        $n->NodePromptText     = 'Subject Line';
        $n->NodeDataStore      = 'SLContact:ContSubject';
        $n->save();
        $n = new SLNode;
        $n->NodeTree           = $newTree->TreeID;
        $n->NodeParentID       = $nodePage->NodeID;
        $n->NodeParentOrder    = 3;
        $n->NodeType           = 'Long Text';
        $n->NodePromptText     = 'Your Message';
        $n->NodePromptAfter    = '<style> #n[[nID]]FldID { height: 250px; } </style>';
        $n->NodeDataStore      = 'SLContact:ContBody';
        $n->save();
        $n = new SLNode;
        $n->NodeTree           = $newTree->TreeID;
        $n->NodeParentID       = $nodePage->NodeID;
        $n->NodeParentOrder    = 4;
        $n->NodeType           = 'Spambot Honey Pot';
        $n->NodePromptText     = 'Reason For Contact';
        $n->NodeDataStore      = 'SLContact:ContType';
        $n->save();
        $n = new SLNode;
        $n->NodeTree           = $newTree->TreeID;
        $n->NodeParentID       = $nodePage->NodeID;
        $n->NodeParentOrder    = 5;
        $n->NodeType           = 'Big Button';
        $n->NodeDefault        = 'Send Your Message';
        $n->NodeDataStore      = 'document.postNode.submit();';
        $n->save();
        
        $def = new SLDefinitions;
        $def->DefDatabase      = 1;
        $def->DefSet           = 'Value Ranges';
        $def->DefSubset        = 'Contact Reasons';
        $def->DefOrder         = 0;
        $def->DefValue         = 'General Feedback';
        $def->save();
        $def = new SLDefinitions;
        $def->DefDatabase      = 1;
        $def->DefSet           = 'Value Ranges';
        $def->DefSubset        = 'Contact Reasons';
        $def->DefOrder         = 1;
        $def->DefValue         = 'Website Problems';
        $def->save();
        $def = new SLDefinitions;
        $def->DefDatabase      = 1;
        $def->DefSet           = 'Value Ranges';
        $def->DefSubset        = 'Contact Reasons';
        $def->DefOrder         = 2;
        $def->DefValue         = 'Networking Opportunities';
        $def->save();
        
        return $newTree;
    }
    
}