<!-- resources/views/vendor/survloop/admin/system-one-style.blade.php -->
<tr>
    <td class="w50 pT20">
        <label id="sty-{{ $opt }}-id" class="w100">
        @if (trim($val[0]) != '')
            <div class="pull-right fPerc80 slGrey">{!! $val[0] !!}</div>
        @endif
            {!! $val[1] !!}
        </label>
    </td>
    <td class="w50">
@if ($opt == 'font-main') 
        <textarea name="sty-{{ $opt }}" id="sty-{{ $opt }}-id" 
            class="form-control w100 ntrStp"
            style="height: 70px;" {!! $GLOBALS['SL']->tabInd() !!} 
            >@if (isset($sysStyles[$opt])){!! $sysStyles[$opt] !!}@endif</textarea>
@else
        {!! view(
            'vendor.survloop.forms.inc-color-picker', 
            [
                'fldName' => 'sty-' . $opt,
                'preSel'  => ((isset($sysStyles[$opt])) ? strtoupper($sysStyles[$opt]) : '')
            ]
        )->render() !!}
@endif
    </td>
</tr>
