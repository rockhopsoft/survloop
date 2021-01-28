<?php
/**
  * GlobalsVars is a mid-level class to declare most variables
  * used by the rest of this globals trunk.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.5
  */
namespace RockHopSoft\Survloop\Controllers\Globals;

class GlobalsVars extends GlobalsStatic
{
    public $x              = [];
    public $sysOpts        = [];

    public $coreID         = 0;
    public $pageView       = '';
    public $dataPerms      = 'public';
    public $cacheSffx      = '';
    public $isOwner        = false;

    public $pageSCRIPTS    = '';
    public $pageJAVA       = '';
    public $pageAJAX       = '';
    public $pageCSS        = '';

    public $pageNav2       = '';
    public $pageNav2Scroll = [ 60, 100, 140 ];

    public $cachePath      = 'cache'; // ../storage/app/

    public $def            = null;
    public $isAdmin        = false;
    public $isVolun        = false;
    public $dbID           = 1;
    public $dbRow          = [];
    public $treeID         = 0;
    public $treeRow        = [];
    public $treeName       = '';
    public $treeBaseSlug   = '';
    public $treeIsAdmin    = false;
    public $xmlTree        = [];
    public $reportTree     = [];
    public $formTree       = [];
    
    public $coreTbl        = '';
    public $coreTblUserFld = '';
    public $treeXmlID      = -3;
    public $treeOverride   = -3;
    public $currProTip     = 0;
    
    public $tblModels      = [];
    public $tbls           = [];
    public $tbl            = [];
    public $tblID          = [];
    public $tblAbbr        = [];
    public $tblOpts        = [];
    public $fldTypes       = [];
    public $fldOthers      = [];
    public $defValues      = [];
    public $condTags       = [];
    public $condABs        = [];
    
    public $foreignKeysIn  = [];
    public $foreignKeysOut = [];
    public $fldAbouts      = [];
    
    public $dataLoops      = [];
    public $dataLoopNames  = [];
    public $dataSubsets    = [];
    public $dataHelpers    = [];
    public $dataLinksOn    = [];
    public $currCyc        = [
        "cyc" => ['', '', -3],
        "res" => ['', '', -3],
        "tbl" => ['', '', -3]
    ];
        
    // User's position within potentially nested loops
    public $sessTree       = 0;
    public $sessLoops      = [];
    public $closestLoop    = [];
    public $tblLoops       = [];
    public $nodeCondInvert = [];
    
    public $sysTree        = [
        "forms" => [
            "pub" => [],
            "adm" => []
        ],
        "pages" => [
            "pub" => [],
            "adm" => []
        ]
    ];
    public $treeSettings   = [];
    public $proTips        = [];
    public $allTrees       = [];
    public $allCoreTbls    = [];
    public $pubCoreTbls    = [];
    public $currSearchTbls = [];
    
    public $currTabInd     = 0;
    public $debugOn        = false;

    
    // Trees (Surveys & Pages) are assigned an optional property 
    // when ( SLTree->tree_opts%TREEOPT_PRIME == 0 )

    // Site Map Architecture and Permissions Flags

    // Page Tree acts as home page for site area
    public const TREEOPT_HOMEPAGE   = 7;

    // Tree acts as search results page for site area 
    public const TREEOPT_SEARCH     = 31;

    // This page acts as the default Member Profile for the system
    public const TREEOPT_PROFILE    = 23;

    // Page Tree is a Report for a survey, so they share data structures
    public const TREEOPT_REPORT     = 13; 
    

    // Site Map Architecture and Permissions Flags

    // Access limited to admin users
    public const TREEOPT_ADMIN      = 3;  

    // Access limited to staff users (and higher)
    public const TREEOPT_STAFF      = 43; 

    // Access limited to partner users (and higher)
    public const TREEOPT_PARTNER    = 41; 

    // Access limited to volunteer users (and higher)
    public const TREEOPT_VOLUNTEER  = 17; 
    

    // Other Tree Options

    // Tree's contents are wrapped in the skinny page width 
    public const TREEOPT_SKINNY     = 2;  
    
    // Record edits not allowed after complete (except admins)
    public const TREEOPT_NOEDITS    = 11; 

    // Survey uses a separate unique Public ID for completed records
    public const TREEOPT_PUBLICID   = 47; 

    // A navigation menu is generated below each page of the survey
    public const TREEOPT_SURVNAVBOT = 37; 

    // A navigation menu is generated atop each page of the survey
    public const TREEOPT_SURVNAVTOP = 59; 

    // A thin progress bar is generated atop each page of the survey
    public const TREEOPT_SURVNAVLIN = 61; 

    // Survey is one big loop through editable records
    public const TREEOPT_ONEBIGLOOP = 5;  


    // Page Tree Options

    // Page Tree is currently too complicated to cache
    public const TREEOPT_NOCACHE    = 29; 

    // This page's enclosing form is submittable
    public const TREEOPT_PAGEFORM   = 53; 

    // This page is a Survloop standard contact form 
    public const TREEOPT_CONTACT    = 19; 

    // This whole page has a background color (default: faint)
    public const TREEOPT_BG         = 67; 

    // This whole page fades in after load
    public const TREEOPT_FADEIN     = 71; 

}