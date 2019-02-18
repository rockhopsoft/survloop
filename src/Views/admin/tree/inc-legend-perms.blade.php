<!-- resources/views/vendor/survloop/admin/tree/inc-legend-perms.blade.php -->
<div class="mB5"><u>Permissions</u></div>
<div class="mB5"><i class="fa fa-eye mR5" aria-hidden="true"></i> Admin-Only Page</div>
<div class="mB5"><i class="fa fa-key mR5" aria-hidden="true"></i> Staff Page</div>
@if ($GLOBALS["SL"]->sysHas('partners'))
<div class="mB5"><i class="fa fa-university mR5" aria-hidden="true"></i> Partners Page</div>
@endif
@if ($GLOBALS["SL"]->sysHas('volunteers'))
<div class="mB5"><i class="fa fa-hand-rock-o mR5" aria-hidden="true"></i> Volunteer Page</div>
@endif