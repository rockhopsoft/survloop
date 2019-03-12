<!-- resources/views/vendor/survloop/reports/inc-uploads.blade.php -->
@if (isset($uploads) && sizeof($uploads) > 0)
    <div class="row">
    @foreach ($uploads as $i => $up)
        @if ($i > 0 && $i%3 == 0) </div><div class="row"> @endif
        <div class="col-md-4">{!! $up !!}</div>
    @endforeach
    </div>
@endif