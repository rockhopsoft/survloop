<?php
namespace SurvLoop\Controllers;

use Illuminate\Routing\Controller;

use App\Models\SLTree;
use App\Models\SLNode;
use App\Models\SLDefinitions;

class SurvLoopInstaller extends Controller
{
    
    public function installPageHome()
    {
        $newTree = new SLTree;
        $newTree->TreeType     = 'Page';
        $newTree->TreeName     = 'Home';
        $newTree->TreeSlug     = 'home';
        $newTree->TreeDatabase = 1;
        $newTree->TreeUser     = 1;
        $newTree->TreeOpts     = 7;
        $newTree->save();
        $node = new SLNode;
        $node->NodeTree        = $newTree->TreeID;
        $node->NodeParentID    = -3;
        $node->NodeType        = 'Page';
        $node->NodePromptNotes = 'home';
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