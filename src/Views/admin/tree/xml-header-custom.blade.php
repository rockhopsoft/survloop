<?php print '<?xml version="1.0" encoding="UTF-8" ?>'; ?>
<!--
@forelse ($GLOBALS["SL"]->mexplode("\n", $apiDesc) as $line)
{!! trim(strip_tags($line)) !!}
@empty
@endforelse

-->