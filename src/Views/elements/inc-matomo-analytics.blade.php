<!-- resources/views/vender/survloop/elements/inc-matomo-analytics.blade.php -->
@if (isset($GLOBALS['SL']->sysOpts) 
    && isset($GLOBALS['SL']->sysOpts["matomo-analytic-url"])
    && trim($GLOBALS['SL']->sysOpts["matomo-analytic-url"]) != '' 
    && isset($GLOBALS['SL']->sysOpts["matomo-analytic-site-id"])
    && trim($GLOBALS['SL']->sysOpts["matomo-analytic-site-id"]) != '' 
    && !$GLOBALS['SL']->isHomestead())
<!-- Matomo Cloud -->
<script type="text/javascript">
  var _paq = window._paq || [];
  /* tracker methods like "setCustomDimension" should be called before "trackPageView" */
  _paq.push(['disableCookies']);
  _paq.push(['trackPageView']);
  _paq.push(['enableLinkTracking']);
  (function() {
    var u="https://{{ $GLOBALS['SL']->sysOpts['matomo-analytic-url'] }}/";
    _paq.push(['setTrackerUrl', u+'matomo.php']);
    _paq.push(['setSiteId', '{{ $GLOBALS["SL"]->sysOpts["matomo-analytic-site-id"] }}']);
    var d=document, g=d.createElement('script'), s=d.getElementsByTagName('script')[0];
    g.type='text/javascript'; g.async=true; g.defer=true; g.src='//cdn.matomo.cloud/{{ $GLOBALS["SL"]->sysOpts["matomo-analytic-url"] }}/matomo.js'; s.parentNode.insertBefore(g,s);
  })();
</script>
<!-- End Matomo Cloud Code -->
@endif