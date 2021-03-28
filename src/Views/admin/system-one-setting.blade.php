<!-- resources/views/vendor/survloop/admin/system-one-setting.blade.php -->
<tr>
    <td class="w50 pT20">
        <label id="sys-{{ $opt }}-id" class="w100 relDiv">
            @if ($opt == 'spinner-code')
                <div class="absDiv" style="right: 0px;">
                    <i class="fa-li fa fa-spinner fa-spin"></i>
                </div>
                <div class="absDiv" style="right: 0px; top: 25px;">
                    <pre class="slGrey" style="font-size: 80%;"
                        >&lt;i class="fa-li fa fa-spinner fa-spin"&gt;&lt;/i&gt;</pre>
                </div>
            @elseif (trim($val[1]) != '')
                <div class="pull-right fPerc80 slGrey">{!! $val[1] !!}</div>
            @endif
            {!! $val[0] !!}
        </label>
    </td>
    <td class="w50">
    @if (in_array($opt, [
            'header-code', 'css-extra-files', 
            'spinner-code', 'sys-cust-js', 'sys-cust-ajax'
        ])) 
        {!! view(
            'vendor.survloop.admin.system-one-setting-textarea', 
            [
                "opt" => $opt, 
                "val" => $val
            ]
        )->render() !!}
    @elseif (in_array($opt, [
            'users-create-db', 'has-canada',
            'has-usernames', 'user-name-req', 
            'has-partners', 'has-volunteers', 
            'req-mfa-users', 'req-mfa-volunteers', 
            'req-mfa-partners', 'req-mfa-staff', 'req-mfa-admin'
        ])) 

        {!! $GLOBALS["SL"]->printToggleSwitch(
            'sys-' . $opt, 
            $GLOBALS["SL"]->sysOpts[$opt]
        ) !!}

    @else
        <input name="sys-{{ $opt }}" id="sys-{{ $opt }}-id" 
            type="text" class="form-control w100 ntrStp slTab" 
            autocomplete="off" {!! $GLOBALS["SL"]->tabInd() !!}
            @if (isset($GLOBALS["SL"]->sysOpts[$opt])) 
                value="{!! $GLOBALS["SL"]->sysOpts[$opt] !!}"
            @endif >
    @endif
    </td>
</tr>
