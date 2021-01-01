<!-- resources/views/vendor/survloop/admin/contact-row.blade.php -->
@if (!isset($forEmail) || !$forEmail)
    <div class="col-9">
@endif
    <h3 class="slBlueDark">{{ $contact->cont_subject }}</h3>
    <div>{!! $GLOBALS["SL"]->printThrottledHtml($contact->cont_body) !!}</div>
    <a href="mailto:{{ $contact->cont_email }}" class="mR10">{{ $contact->cont_email }}</a> 
    {{ date('n/j/y g:ia', strtotime( $contact->created_at )) }}
    @if (!isset($forEmail) || !$forEmail)
        <span @if ($contact->cont_flag == 'Unread') class="red" @endif >
            <i class="fa fa-envelope-o mL15" aria-hidden="true"></i> 
            {{ $contact->cont_flag }}
        </span>
    @endif
@if (!isset($forEmail) || !$forEmail)
    </div>
    <div class="col-1">
        <div id="rec{{ $contact->cont_id }}loading"></div>
    </div>
    <div class="col-2">
        <label for="n175FldID">
            #{{ number_format( $contact->cont_id ) }} Status:
        </label>
        <div class="nFld">
            <select name="ContFlag{{ $contact->cont_id }}" 
                id="ContFlag{{ $contact->cont_id }}ID" 
                class="form-control form-control-lg changeContStatus"
                autocomplete="off" {!! $GLOBALS["SL"]->tabInd() !!} >
                <option value="Unread" 
                    @if ($contact->cont_flag == 'Unread') SELECTED @endif 
                    >Unread</option>
                <option value="On Hold" 
                    @if ($contact->cont_flag == 'On Hold') SELECTED @endif 
                    >On Hold</option>
                <option value="Resolved" 
                    @if ($contact->cont_flag == 'Resolved') SELECTED @endif 
                    >Resolved</option>
                <option value="Trash" 
                    @if ($contact->cont_flag == 'Trash') SELECTED @endif 
                    >Trash</option>
            </select>
        </div>
    </div>
@endif