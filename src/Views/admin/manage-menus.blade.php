<!-- resources/views/vendor/survloop/admin/manage-menus.blade.php -->
@extends('vendor.survloop.master')
@section('content')
<div class="container">
<div class="row">
    <div class="col-6">
        <div class="slCard nodeWrap">
            <h2><i class="fa fa-eye"></i> Admin Navigation Menu</h2>
            <span class="slGrey">...soon...</span>
            <div class="p20"></div>
        </div>
    </div>
    <div class="col-6">
        <div class="slCard nodeWrap">
            <h2 class="fL"><i class="fa fa-bars mR5" aria-hidden="true"></i> Burger Navigation Menu</h2>
            <h2 class="fR slBlueDark mR10"><i class="fa fa-level-up" aria-hidden="true"></i></h2>
            <div class="fC"></div>
            
            <form name="mainPageForm" action="/dashboard/pages/menus?sub=1" method="post" >
            <input type="hidden" id="csrfTok" name="_token" value="{{ csrf_token() }}">
            <div class="row slGrey">
                <div class="col-6">Menu Link Text</div>
                <div class="col-6">Link To URL</div>
            </div>
            @for ($i=0; $i < $cntMax; $i++)
                <div id="navMenuTr{{ $i }}" class="row mT5 mB10 
                    @if ($i < (1+sizeof($navMenu))) disBlo @else disNon @endif ">
                    <div class="col-6"><input type="text" id="txt{{ $cnt }}ID" name="mainNavTxt{{ $cnt }}" 
                        @if ($i < sizeof($navMenu)) value="{!! $navMenu[$i][0] !!}" @else value="" @endif 
                        onKeyUp="checkMainNav();" class="form-control" autocomplete="off" ></div>
                    <div class="col-6"><input type="text" id="lnk{{ $cnt }}ID" name="mainNavLnk{{ $cnt++ }}" 
                        @if ($i < sizeof($navMenu)) value="{!! $navMenu[$i][1] !!}" @else value="" @endif 
                        onKeyUp="checkMainNav();" class="form-control" autocomplete="off" ></div>
                </div>
            @endfor
            <center><input type="submit" class="btn btn-primary btn-lg" value="Save Menu Changes"></center>
            </form>
        </div>
    </div>
</div>
</div>

<script type="text/javascript">
function checkMainNav() {
    var maxCnt = 0;
    for (var i=0; i < {{ $cntMax }}; i++) {
        if (document.getElementById('txt'+i+'ID') && document.getElementById('txt'+i+'ID').value.trim() != '') {
            maxCnt = i;
        }
    }
    for (var i=0; i < {{ $cntMax }}; i++) {
        if (document.getElementById('navMenuTr'+i+'')) {
            if (i <= (maxCnt+1)) document.getElementById('navMenuTr'+i+'').style.display="block";
            else document.getElementById('navMenuTr'+i+'').style.display="none";
        }
    }
    return true;
}
</script>

<div class="adminFootBuff"></div>

@endsection