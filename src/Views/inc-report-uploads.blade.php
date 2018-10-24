<!-- resources/views/vendor/survloop/inc-report-uploads.blade.php -->
@if (isset($uploads) && sizeof($uploads) > 0)
    <div class="row">
    @foreach ($uploads as $i => $up)
        @if ($i > 0 && $i%3 == 0) </div><div class="row"> @endif
        <div class="col-4">{!! $up !!}</div>
    @endforeach
    </div>
@endif