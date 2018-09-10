<!-- Stored in resources/views/vendor/survloop/admin/contact-row.blade.php -->
@if (!isset($forEmail) || !$forEmail)
    <div class="col-md-9">
@endif
    <h3 class="slBlueDark">{{ $contact->ContSubject }}</h3>
    <div>{{ $contact->ContBody }}</div>
    <a href="mailto:{{ $contact->ContEmail }}" class="mR10">{{ $contact->ContEmail }}</a> 
    {{ date('n/j/y g:ia', strtotime( $contact->created_at )) }}
    @if (!isset($forEmail) || !$forEmail && $contact->ContFlag == 'Unread')
        <span class="red"><i class="fa fa-envelope-open-o mL10" aria-hidden="true"></i> Unread</span>
    @endif
@if (!isset($forEmail) || !$forEmail)
    </div>
    <div class="col-md-1">
        <div id="rec{{ $contact->ContID }}loading"></div>
    </div>
    <div class="col-md-2">
        <label for="n175FldID">
            #{{ number_format( $contact->ContID ) }} Status:
        </label>
        <div class="nFld">
            <select name="ContFlag{{ $contact->ContID }}" id="ContFlag{{ $contact->ContID }}ID" 
                class="form-control form-control-lg changeContStatus" {!! $GLOBALS["SL"]->tabInd() !!}>
                <option value="Unread" @if ($contact->ContFlag == 'Unread') SELECTED @endif 
                    >Unread</option>
                <option value="Read" @if ($contact->ContFlag == 'Read') SELECTED @endif 
                    >Read</option>
                <option value="Trash" @if ($contact->ContFlag == 'Trash') SELECTED @endif 
                    >Trash</option>
            </select>
        </div>
    </div>
@endif