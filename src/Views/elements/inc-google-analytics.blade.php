<!-- resources/views/vender/survloop/elements/inc-google-analytics.blade.php -->
@if (isset($GLOBALS['SL']->sysOpts) 
    && isset($GLOBALS['SL']->sysOpts["google-analytic"])
    && trim($GLOBALS['SL']->sysOpts["google-analytic"]) != '' 
    && !$GLOBALS['SL']->isHomestead())
    <!-- Global site tag (gtag.js) - Google Analytics -->
    <script defer src="https://www.googletagmanager.com/gtag/js?id={!! 
        $GLOBALS['SL']->sysOpts['google-analytic'] !!}"></script>
    <script>
      window.dataLayer = window.dataLayer || [];
      function gtag(){dataLayer.push(arguments);}
      gtag('js', new Date());
      gtag('config', '{!! $GLOBALS["SL"]->sysOpts["google-analytic"] !!}');
    </script>
@endif