<center><div class="treeWrapForm taL">
    <div class="container">
        <div class="p30"></div>
        <h1 class="slBlueDark">{{ $apiName }}</h1>
        <h2>API Schema for {{ $corePlural }} / {{ $coreSingle }}</h2>
        {!! $apiDesc !!}
        <div class="p15"></div>
        <div class="p15"><hr></div>
        <h2>Format Data Fields</h2>

        @forelse ($apiTables as $i => $apiTbl)
            @if ($i == 0 && $apiTbl->table == $corePlural)
                {!! $apiTbl->printFlds($type) !!}
            @else
                <div class="pT30 pB30"><hr></div>
                <h3 class="slBlueDark">
                    Collection &lt;{{ $apiTbl->table }}&gt; 
                    <?php /* &lt;{{ $apiTbl->singular }}&gt; */ ?>
                </h3>
                {!! $apiTbl->printFlds($type) !!}
            @endif
        @empty
        @endforelse
        <div class="p30"></div>
        <div class="p30"></div>
    </div>
</div></center>
<style>
.schema-fld-label { min-width: 50px; }
</style>