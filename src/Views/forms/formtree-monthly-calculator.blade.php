<!-- resources/views/survloop/forms/formtree-monthly-calculator.blade.php -->
<div class="pT15">
    <a id="hidivBtnMonthly{{ $nIDtxt }}" class="hidivBtn" href="javascript:;"
        ><i class="fa fa-calculator mR5" aria-hidden="true"></i> Monthly Calculator</a>
    <div id="hidivMonthly{{ $nIDtxt }}" class="disNon row2 p15">
        <div id="monthers" class="row">
            @for ($i = 1; $i < 13; $i++)
                <div class="col-md-3 col-sm-4 col-6 pB20">
                    {{ date('M', mktime(0, 0, 0, $i, 1, 2000)) }}<br />
                    <input type="number" class="form-control" name="month{{ $nIDtxt }}ly{{ $i }}" id="month{{ $nIDtxt }}ly{{ $i }}ID"
                        value="{{ (($presel[($i-1)]) ? $presel[($i-1)] : '') }}">
                </div>
            @endfor
        </div>
        <a id="monthlyCalcTot{{ $nIDtxt }}" href="javascript:;" class="btn btn-sm btn-secondary"
            ><i class="fa fa-arrow-down" aria-hidden="true"></i> Add Up Total</a>
    </div>
</div>