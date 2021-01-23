<!-- resources/views/vendor/survloop/admin/db/import.blade.php -->

<div class="container">

@if ($uploadImport 
    && isset($uploadImport->arr) 
    && sizeof($uploadImport->arr) > 0
    && sizeof($uploadImport->arr[0]) > 0
    && sizeof($uploadImport->arr[0][0]) > 0)

    @if ($uploadImport->dataRowsAdded > 0)

        <div class="slCard mB20">
            <h4 class="slBlueDark mB30">
                New Table Created for {{ $uploadImport->tblEng }} with
                {{ number_format($uploadImport->dataRowsAdded) }} Rows Imported!
            </h4>
            <a href="/dashboard/db/tbl-raw?tbl={{ $uploadImport->tblName 
                }}" class="btn btn-primary btn-xl mT30"
                >View Raw Data Table</a>
        </div>

    @else 

        @if ($uploadImport->tblNew)
            <div class="slCard mB20">

                <form method="post" name="importExcelForm" action="?import=fldNames">
                <input type="hidden" name="_token" value="{{ csrf_token() }}">
                <input type="hidden" name="import" value="fldNames">
                <input type="hidden" name="file" value="{{ $uploadImport->getFile() }}">
                <input type="hidden" name="rowCnt" id="rowCntID" 
                    value="{{ sizeof($uploadImport->arr[0]) }}">
                <input type="hidden" name="colCnt" id="colCntID" 
                    value="{{ sizeof($uploadImport->arr[0][0]) }}">

                <div id="importSkipAll" class="disBlo pull-right mT10">
                    <a id="importSkipAllBtn" class="btn btn-secondary"
                        href="javascript:;">Skip All Columns</a>
                </div>
                <div id="importSkipNone" class="disNon pull-right mT10">
                    <a id="importSkipNoneBtn" class="btn btn-secondary"
                        href="javascript:;">Import All Columns</a>
                </div>
                <h3 class="slBlueDark">
                    <i class="fa fa-upload mR3"></i> 
                    Importing Into New Data Table
                </h3>
                <h4 class="slBlueDark">
                    {{ $uploadImport->tblEng }} 
                    ({{ number_format(sizeof($uploadImport->arr[0][0])) }} columns,
                    {{ number_format(sizeof($uploadImport->arr[0])) }} rows)
                </h4>

                <div class="row mT30 mB30">
                    <div class="col-4">
                        <label class="w100">
                            <b class="slGrey">Plain Title for Table</b><br />
                            <input name="tblEng" id="tblEngID" type="text" 
                                class="form-control form-control-lg w100"
                                value="{{ $uploadImport->tblEng }}">
                        </label>
                    </div>
                    <div class="col-4">
                        <label class="w100">
                            <b class="slGrey">Database Table Name</b><br />
                            <input name="tblName" id="tblNameID" type="text" 
                                class="form-control form-control-lg w100"
                                value="{{ $uploadImport->tblName }}">
                        </label>
                    </div>
                    <div class="col-4">
                        <label class="w100">
                            <b class="slGrey">Database Field Name Prefix</b><br />
                            <input name="tblAbbr" id="tblAbbrID" type="text" 
                                class="form-control form-control-lg w100"
                                value="{{ $uploadImport->tblAbbr }}">
                        </label>
                    </div>
                </div>

                <hr>
                <p>
                    Each column imported will have it's own
                    data field within this new data table...
                </p>
                <p><br /></p>
                <div class="row mBn15">
                    <div class="col-2">
                        <label for="fldImport0ID">
                            <b class="slGrey">Import Column</b>
                        </label>
                    </div>
                    <div class="col-6">
                        <label for="fldEng0ID">
                            <b class="slGrey">Plain Title for Field</b>
                        </label>
                    </div>
                    <div class="col-4">
                        <label for="fldName0ID">
                            <b class="slGrey">Database Field Name</b>
                        </label>
                    </div>
                </div>

            <?php $cnt = 0; ?>
            @foreach ($uploadImport->arr[0][0] as $i => $colHeader)
                @if (trim(strip_tags($colHeader)) != '')
                    <?php $cnt++; ?>
                    <h5 class="mT30 mB5">Column #{{ $cnt }}: {{ $colHeader }}</h5>
                    <div class="row mB30">
                        <div class="col-2">
                            <label class="disBlo w100">
                                <select name="fldImport{{ $i }}" id="fldImport{{ $i }}ID"
                                    class="form-control form-control-lg w100 updateFldImport"
                                    data-fld-ind="{{ $i }}" autocomplete="off">
                                    <option value="1" SELECTED >Yes, Import</option>
                                    <option value="0">No, Skip</option>
                                </select>
                            </label>
                        </div>
                        <div class="col-6">
                            <label id="fldEng{{ $i }}Wrap" class="disBlo w100">
                                <input name="fldEng{{ $i }}" id="fldEng{{ $i }}ID"
                                    type="text" class="form-control form-control-lg w100"
                                    value="{{ $colHeader }}" autocomplete="off">
                            </label>
                        </div>
                        <div class="col-4">
                            <label id="fldName{{ $i }}Wrap" class="disBlo w100">
                                <input name="fldName{{ $i }}" id="fldName{{ $i }}ID"
                                    type="text" class="form-control form-control-lg w100"
                                    value="{{ $GLOBALS['SL']->slugify($colHeader, '_') }}"
                                    autocomplete="off">
                            </label>
                        </div>
                    </div>
                @endif
            @endforeach

                <input type="submit" value="Create Data Table & Import" 
                    class="btn btn-primary btn-lg mT30 mB30" autocomplete="off">

                </form>

                <?php /*
                <div class="p30"><hr></div>
                <h4>{{ $uploadImport->getFile() }}</h4>
                <pre>{!! print_r($uploadImport->arr) !!}</pre>
                */ ?>

            </div>

        @else 
            <?php /* Importing into existing table */ ?>


        @endif

    @endif
@endif

    <div class="slCard mB20">

        <h2 class="slBlueDark">
            <i class="fa fa-upload"></i> 
            {{ $GLOBALS['SL']->dbRow->db_name }}: Import
        </h2>

        <h4 class="mT30">Import From Excel</h4>
        <p>
            The first row of this Excel import should be reserved for
            column headers, one for each column to be imported.
            Every imported column header must be unique.
        </p>

        <form method="post" enctype="multipart/form-data" 
            action="?import=excel" name="importExcelForm">
        <input type="hidden" name="_token" value="{{ csrf_token() }}">
        <input type="hidden" name="import" value="excel">

        <div class="row">
            <div class="col-5 pB30">
                <label id="importTblTypeNewlab" class="fingerAct">
                    <div class="disIn mR5">
                        <input type="radio" value="None" CHECKED
                            id="importTblTypeNew" name="importType" 
                            class="updateImportType slTab ntrStp" 
                            autocomplete="off">
                    </div>
                    Import into new data table
                </label>
                <label id="importTblTypeOldlab" class="finger">
                    <div class="disIn mR5">
                        <input type="radio" value="Existing" 
                            id="importTblTypeOld" name="importType" 
                            class="updateImportType slTab ntrStp" 
                            autocomplete="off">
                    </div>
                    Import into existing data table
                </label>
            </div>
            <div class="col-1 pB30"></div>
            <div class="col-6 pB30">
                <div id="importTblNew" class="w100 disBlo">
                    <label class="w100">
                        New Table Name<br />
                        <input name="importTblName" id="importTblNameID"
                            type="text" class="form-control form-control-lg">
                    </label>
                </div>
                <div id="importTblOld" class="w100 disNon">
                    <label class="w100">
                        Existing Table Name<br />
                        <select name="importTblNameOld" id="importTblNameOldID"
                            class="form-control form-control-lg">
                        {!! $GLOBALS["SL"]->tablesDropdown('', 'select table', '', true) !!}
                        </select>
                        <i>Coming soon</i>
                    </label>
                </div>
            </div>
        </div>

        <input type="file" name="importExcel" id="importExcelID"
            class="form-control form-control-lg" autocomplete="off"
            {!! $GLOBALS["SL"]->tabInd() !!}>

        <input type="submit" value="Upload Excel" autocomplete="off" 
            class="btn btn-primary btn-lg mT30">

        </form>

    </div>

</div>
