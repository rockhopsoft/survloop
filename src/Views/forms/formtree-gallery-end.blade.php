<!-- resources/views/vendor/survloop/forms/formtree-gallery-end.blade.php -->

<div id="sliNavDiv{{ $curr->nIDtxt }}" class="sliNavDiv">
    <a href="javascript:;" class="sliLft" id="sliLft{{ $curr->nIDtxt }}"
        ><div id="sliLftHvr{{ $curr->nIDtxt }}"></div>
        <i class="fa fa-chevron-left" aria-hidden="true"></i></a>
    <a href="javascript:;" class="sliRgt" id="sliRgt{{ $curr->nIDtxt }}"
        ><div id="sliRgtHvr{{ $curr->nIDtxt }}"></div>
        <i class="fa fa-chevron-right" aria-hidden="true"></i></a>
    <div class="pT5">
    @foreach ($curr->tmpSubTier[1] as $j => $kid)
        <a id="sliNav{{ $curr->nIDtxt }}dot{{ $j }}" href="javascript:;" 
            class=" @if ($j == 0) sliNavAct @else sliNav @endif " 
            ><i class="fa fa-dot-circle-o" aria-hidden="true"></i></a>
    @endforeach
    </div>
</div>
