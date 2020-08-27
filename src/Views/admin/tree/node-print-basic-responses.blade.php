<!-- resources/views/vendor/survloop/admin/tree/node-print-basic-responses.blade.php -->
@if (sizeof($node->responses) > 0)
    <div class="fPerc125">
    @foreach ($node->responses as $j => $res)
        @if (strlen(strip_tags($res->node_res_eng)) < 100) <nobr> 
        @else <div class="pT5 pB5"> @endif
        <i class="fa fa-circle-o slGrey mL20" style="font-size: 10pt;" aria-hidden="true"></i>
        {{ strip_tags($res->node_res_eng) }}
        @if ($node->indexShowsKid($j))
            <i class="fa fa-code-fork fa-flip-vertical mRn5" 
                title="Children displayed if selected"></i> 
            <sub class="slGrey">{{ $node->indexShowsKidNode($j) }}</sub>
        @endif
        @if (strlen(strip_tags($res->node_res_eng)) < 100) </nobr> 
        @else </div> @endif
    @endforeach
    </div>
@endif
