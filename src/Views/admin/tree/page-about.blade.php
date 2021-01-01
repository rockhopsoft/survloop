<!-- resources/views/vendor/survloop/admin/tree/page-about.blade.php -->

<div id="adminAbout" class="jumbotron @if ($showAbout) disBlo @else disNon @endif ">
    <h1>Site Pages</h1>
    <p>
    Site pages use the same tree engine as Surveys/Forms, but are always only one page long. 
    Also, pages are less focused on collecting data. They can also be navigated to directly from 
    http://domainroot.com/page-slug. Also, one page can be designated as the site's homepage, or only for site admins.
    </p>
    <div class="float-right mBn20">
        <a href="javascript:;" id="adminAboutBtn" >Hide About <i class="fa fa-times" aria-hidden="true"></i></a>
    </div>
    
</div> <!-- end jumbotron -->
