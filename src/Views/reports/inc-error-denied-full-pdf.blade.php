<!-- resources/views/vendor/survloop/reports/inc-error-denied-full-pdf.blade.php -->
<br /><br /><center><h3>
You are trying to access the complete details of a record which requires you to 
<a href="/login">login</a> as the owner, or an otherwise authorized user. 
<br /><br />
The public version of this complaint can be found here:<br />
<a href="/{{ $url }}">{{ $GLOBALS["SL"]->sysOpts["app-url"] }}/{{ $url }}</a>
</h3></center>