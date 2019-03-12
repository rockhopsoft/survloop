<!-- resources/views/vender/survloop/admin/tree/pages-row-redir.blade.php -->
<tr><td>
    <a class="float-right ajx" data-dst="ajxT{{ $redir[2] }}" data-ajx="/ajadm/redir-edit?t={{ $redir[2] }}" 
        href="javascript:;" ><i class="fa fa-pencil" aria-hidden="true"></i></a>
    <h4><a href="{{ $redir[0] }}" target="_blank">{{ $redir[0] }}</a></h4>
    <div class="pT5 pB5 pL20">
        <i class="slGreenDark">redirects to</i><br />
        <a href="{{ $redir[1] }}" target="_blank">{{ $redir[1] }}</a> 
    </div>
    <div id="ajxT{{ $redir[2] }}"></div>
</td></tr>