<!-- resources/views/vendor/survloop/admin/tree/widget-email-edit.blade.php -->

<div class="row">
    <div class="col-md-4">
        <h4 class="slBlueDark m0">Send Email To</h4>
        <div class="pL5">
            <label class="w100"><input type="checkbox" name="widgetEmailTo[]" value="-69" class="mR5"
                @if (isset($node->extraOpts["emailTo"]) && in_array('-69', $node->extraOpts["emailTo"])) 
                    CHECKED @endif > User Using Form</label>
            <label class="w100"><input type="checkbox" name="widgetEmailTo[]" value="-68" class="mR5"
                @if (isset($node->extraOpts["emailTo"]) && in_array('-68', $node->extraOpts["emailTo"])) 
                    CHECKED @endif > User Who Owns Related Record</label>
            <div class="w100" style="height: 150px; overflow: auto;">
                @if (isset($emailUsers["admin"]) && sizeof($emailUsers["admin"]) > 0)
                    <div class="pT5"><b class="slGrey pL20">Admin Users:</b></div>
                    @foreach ($emailUsers["admin"] as $usr)
                        <label class="w100"><input type="checkbox" name="widgetEmailTo[]" value="{{ $usr[0] }}"
                            @if (isset($node->extraOpts["emailTo"]) 
                                && in_array(trim($usr[0]), $node->extraOpts["emailTo"])) 
                                CHECKED @endif class="mR5" > {{ $usr[2] }} 
                                <span class="slGrey fPerc66">({{ $usr[1] }})</span></label>
                    @endforeach
                @endif
                @if (isset($emailUsers["volun"]) && sizeof($emailUsers["volun"]) > 0)
                    <div class="pT5"><b class="slGrey pL20">Volunteer Users:</b></div>
                    @foreach ($emailUsers["volun"] as $usr)
                        <label class="w100"><input type="checkbox" name="widgetEmailTo[]" value="{{ $usr[0] }}"
                            @if (isset($node->extraOpts["emailTo"]) 
                                && in_array(trim($usr[0]), $node->extraOpts["emailTo"])) 
                                CHECKED @endif class="mR5" > {{ $usr[2] }} 
                                <span class="slGrey fPerc66">({{ $usr[1] }})</span></label>
                    @endforeach
                @endif
                @if (isset($emailUsers["users"]) && sizeof($emailUsers["users"]) > 0)
                    <div class="pT5"><b class="slGrey pL20">Other Users:</b></div>
                    @foreach ($emailUsers["users"] as $usr)
                        <label class="w100"><input type="checkbox" name="widgetEmailTo[]" value="{{ $usr[0] }}"
                            @if (isset($node->extraOpts["emailTo"]) 
                                && in_array(trim($usr[0]), $node->extraOpts["emailTo"])) 
                                CHECKED @endif class="mR5" > {{ $usr[2] }} 
                                <span class="slGrey fPerc66">({{ $usr[1] }})</span></label>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <h4 class="slBlueDark m0">CC</h4>
        <div class="pL5">
            <label class="w100"><input type="checkbox" name="widgetEmailCC[]" value="-69" class="mR5"
                @if (isset($node->extraOpts["emailCC"]) && in_array('-69', $node->extraOpts["emailCC"])) 
                    CHECKED @endif > User Using Form</label>
            <label class="w100"><input type="checkbox" name="widgetEmailCC[]" value="-68" class="mR5"
                @if (isset($node->extraOpts["emailCC"]) && in_array('-68', $node->extraOpts["emailCC"])) 
                    CHECKED @endif > User Who Owns Related Record</label>
            <div class="w100" style="height: 150px; overflow: auto;">
                @if (isset($emailUsers["admin"]) && sizeof($emailUsers["admin"]) > 0)
                    <div class="pT5"><b class="slGrey pL20">Admin Users:</b></div>
                    @foreach ($emailUsers["admin"] as $usr)
                        <label class="w100"><input type="checkbox" name="widgetEmailCC[]" value="{{ $usr[0] }}"
                            @if (isset($node->extraOpts["emailCC"]) 
                                && in_array(trim($usr[0]), $node->extraOpts["emailCC"])) 
                                CHECKED @endif class="mR5" > {{ $usr[2] }} 
                                <span class="slGrey fPerc66">({{ $usr[1] }})</span></label>
                    @endforeach
                @endif
                @if (isset($emailUsers["volun"]) && sizeof($emailUsers["volun"]) > 0)
                    <div class="pT5"><b class="slGrey pL20">Volunteer Users:</b></div>
                    @foreach ($emailUsers["volun"] as $usr)
                        <label class="w100"><input type="checkbox" name="widgetEmailCC[]" value="{{ $usr[0] }}"
                            @if (isset($node->extraOpts["emailCC"]) 
                                && in_array(trim($usr[0]), $node->extraOpts["emailCC"])) 
                                CHECKED @endif class="mR5" > {{ $usr[2] }} 
                                <span class="slGrey fPerc66">({{ $usr[1] }})</span></label>
                    @endforeach
                @endif
                @if (isset($emailUsers["users"]) && sizeof($emailUsers["users"]) > 0)
                    <div class="pT5"><b class="slGrey pL20">Other Users:</b></div>
                    @foreach ($emailUsers["users"] as $usr)
                        <label class="w100"><input type="checkbox" name="widgetEmailCC[]" value="{{ $usr[0] }}"
                            @if (isset($node->extraOpts["emailCC"]) 
                                && in_array(trim($usr[0]), $node->extraOpts["emailCC"])) 
                                CHECKED @endif class="mR5" > {{ $usr[2] }} 
                                <span class="slGrey fPerc66">({{ $usr[1] }})</span></label>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
    <div class="col-md-4">
        <h4 class="slBlueDark m0">BCC</h4>
        <div class="pL5">
            <label class="w100"><input type="checkbox" name="widgetEmailBCC[]" value="-69" class="mR5"
                @if (isset($node->extraOpts["emailBCC"]) 
                    && in_array('-69', $node->extraOpts["emailBCC"])) 
                    CHECKED @endif > User Using Form</label>
            <label class="w100"><input type="checkbox" name="widgetEmailBCC[]" value="-68" class="mR5"
                @if (isset($node->extraOpts["emailBCC"]) 
                    && in_array('-68', $node->extraOpts["emailBCC"])) 
                    CHECKED @endif > User Who Owns Related Record</label>
            <div class="w100" style="height: 150px; overflow: auto;">
                @if (isset($emailUsers["admin"]) && sizeof($emailUsers["admin"]) > 0)
                    <div class="pT5"><b class="slGrey pL20">Admin Users:</b></div>
                    @foreach ($emailUsers["admin"] as $usr)
                        <label class="w100"><input type="checkbox" name="widgetEmailBCC[]" value="{{ $usr[0] }}"
                            @if (isset($node->extraOpts["emailCC"]) 
                                && in_array(trim($usr[0]), $node->extraOpts["emailCC"])) 
                                CHECKED @endif class="mR5" > {{ $usr[2] }} 
                                <span class="slGrey fPerc66">({{ $usr[1] }})</span></label>
                    @endforeach
                @endif
                @if (isset($emailUsers["volun"]) && sizeof($emailUsers["volun"]) > 0)
                    <div class="pT5"><b class="slGrey pL20">Volunteer Users:</b></div>
                    @foreach ($emailUsers["volun"] as $usr)
                        <label class="w100"><input type="checkbox" name="widgetEmailBCC[]" value="{{ $usr[0] }}"
                            @if (isset($node->extraOpts["emailCC"]) 
                                && in_array(trim($usr[0]), $node->extraOpts["emailCC"])) 
                                CHECKED @endif class="mR5" > {{ $usr[2] }} 
                                <span class="slGrey fPerc66">({{ $usr[1] }})</span></label>
                    @endforeach
                @endif
                @if (isset($emailUsers["users"]) && sizeof($emailUsers["users"]) > 0)
                    <div class="pT5"><b class="slGrey pL20">Other Users:</b></div>
                    @foreach ($emailUsers["users"] as $usr)
                        <label class="w100"><input type="checkbox" name="widgetEmailBCC[]" value="{{ $usr[0] }}"
                            @if (isset($node->extraOpts["emailCC"]) 
                                && in_array(trim($usr[0]), $node->extraOpts["emailCC"])) 
                                CHECKED @endif class="mR5" > {{ $usr[2] }} 
                                <span class="slGrey fPerc66">({{ $usr[1] }})</span></label>
                    @endforeach
                @endif
            </div>
        </div>
    </div>
</div>
<div class="mT20">
    <h4 class="slBlueDark m0 mB5">Select Email Template</h4>
    <label class="w100 mB20" id="widgetEmaDefDump1">
        <input type="radio" name="widgetEmaDef" id="widgetEmaDefDump1" 
            onClick="return changeWidgetEmailDef(-69);"
            @if (intVal($node->nodeRow->NodeDefault) == -69) CHECKED @endif
            value="-69" > <span class="mL5">Dump Entire Form</span>
        <div class="pL20"><i class="slGrey">
        This template will just dumps all the questions and answers provided by the user</i></div>
    </label>
    @forelse ($emailList as $i => $email)
        <label class="w100 mB20" id="widgetEmaDef{{ $email->EmailID }}">
            <input type="radio" name="widgetEmaDef" id="widgetEmaDef{{ $email->EmailID }}" 
                onClick="return changeWidgetEmailDef({{ $email->EmailID }});"
                @if ($email->EmailID == $node->nodeRow->NodeDefault) CHECKED @endif
                value="{{ $email->EmailID }}" > <span class="mL5">{{ $email->EmailSubject }}</span>
            <div class="pL20"><i class="slGrey">{{ $email->EmailName }}</i></div>
        </label>
    @empty
        <div class="mT20"><i>No emails found</i></div>
    @endforelse
</div>