<!-- Stored in resources/views/vender/survloop/admin/tree/pages-row-redir.blade.php -->
<tr><td class="slGrey">
    <a class="pull-right ajx" data-dst="ajxT{{ $redir[2] }}" data-ajx="/ajadm/redir-edit?t={{ $redir[2] }}" 
        href="javascript:;" ><i class="fa fa-pencil" aria-hidden="true"></i></a>
    <i class="fa fa-share fa-flip-vertical opac20 pL5 pR10" aria-hidden="true"></i>
    <a href="{{ $redir[0] }}" target="_blank">{{ $redir[0] }}</a> <i class="mL5 mR5 slGreenDark">redirects to</i>
    <a href="{{ $redir[1] }}" target="_blank">{{ $redir[1] }}</a> 
    <div id="ajxT{{ $redir[2] }}"></div>
</td></tr>