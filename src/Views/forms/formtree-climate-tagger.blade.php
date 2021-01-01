<!-- generated from resources/views/vendor/survloop/forms/formtree-climate-tagger.blade.php -->

@if ($print == 'select')
    
    <select name="n{{ $nID }}fld" id="n{{ $nID }}FldID" data-nid="{{ $nID }}" 
        class="form-control ntrStp slTab psChangeFilterDelay"
        autocomplete="off" {!! $GLOBALS["SL"]->tabInd() !!}
        onChange="selectTag('{{ $nID }}', this.value); this.value='';">
        <option value="" SELECTED
            >Add Climate or State Filter...</option>
        <option disabled ></option>
    @foreach ([ '1A', '2A', '2B', '3A', '3B', '3C', 
        '4A', '4B', '4C', '5A', '5B', '6A', '6B', '7A', '7B' ] as $zone)
        <option value="{{ $zone }}" >Climate Zone {{ $zone }} ({{ 
            $GLOBALS["SL"]->states->getAshraeZoneLabel($zone) }})</option>
    @endforeach
        <option value="" DISABLED ></option>
    @foreach ($stateList as $abbr => $name)
        <option value="{{ $abbr }}">{{ $name }} ({{ $abbr }})</option>
    @endforeach
    @if (isset($hasCanada) && $hasCanada)
        <option value="" DISABLED ></option>
        @foreach ($stateListCa as $abbr => $name)
            <option value="{{ $abbr }}">{{ $name }} ({{ $abbr }})</option>
        @endforeach
    @endif
    </select>
    <!-- initial fltStateClimTag: {{ print_r($fltStateClimTag) }} -->

@elseif ($print == 'tag')

    <div id="n{{ $nID }}tags" class="slTagList"></div>
    <input id="fltStateClimNIDID" name="fltStateClimNID" 
        value="{{ $nID }}" type="hidden">
    <input id="n{{ $nID }}tagIDsID" name="n{{ $nID }}tagIDs"
        data-nid="{{ $nID }}" type="hidden" 
        @if (sizeof($fltStateClimTag) == 0) value="," 
        @else value=",{{ implode(',', $fltStateClimTag) }},"
        @endif >

@elseif ($print == 'js')

    <script type="text/javascript">

    function loadFltTagStateClim{{ $nID }}() {
        var nID = "{{ $nID }}";
        var classExtra = "{{ $classExtra }}";
    @foreach ([ '1A', '2A', '2B', '3A', '3B', '3C', 
        '4A', '4B', '4C', '5A', '5B', '6A', '6B', '7A', '7B' ] as $zone)
        <?php $select = ((in_array($zone, $fltStateClimTag)) ? 1 : 0); ?>
        addTagOptExtra(nID, {!! json_encode($zone) !!}, {!! json_encode('Zone ' . $zone) !!}, {{ $select }}, classExtra);
    @endforeach
    @foreach ($stateList as $abbr => $name)
        <?php $select = ((in_array($abbr, $fltStateClimTag)) ? 1 : 0); ?>
        addTagOptExtra(nID, {!! json_encode($abbr) !!}, {!! json_encode($name) !!}, {{ $select }}, classExtra);
    @endforeach
    @if (isset($hasCanada) && $hasCanada)
        @foreach ($stateListCa as $abbr => $name)
            <?php $select = ((in_array($abbr, $fltStateClimTag)) ? 1 : 0); ?>
            addTagOptExtra(nID, {!! json_encode($abbr) !!}, {!! json_encode($name) !!}, {{ $select }}, classExtra);
        @endforeach
    @endif
        updateTagList("{{ $nID }}");
    }
    setTimeout("loadFltTagStateClim{{ $nID }}()", 50);

    </script>

@endif
