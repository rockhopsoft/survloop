<!-- resources/views/vendor/survloop/admin/tree/page-about.blade.php -->

<div id="adminAbout" class="jumbotron @if ($showAbout) disBlo @else disNon @endif ">
    <h1>Site Pages</h1>
    <p>
    Site pages use the same tree engine as User Experiences, but are always only one page long, and not focused on 
    collecting data. They can also be navigated to directly from http://domainroot.com/page-slug. Also, one page can be 
    designated as the site's home page.
    </p><p>
    Some nodes have children which respond to certain user choices (<i class="fa fa-code-fork fa-flip-vertical"></i>) 
    by revealing more questions on the page. Many options are provided when editing a node, and most questions can be 
    easily setup to automatically store user responses to a specific field in the database.
    </p>
    <div class="pull-right mBn20">
        <a href="javascript:void(0)" id="adminAboutBtn" >Hide About <i class="fa fa-times" aria-hidden="true"></i></a>
    </div>
    
</div> <!-- end jumbotron -->
