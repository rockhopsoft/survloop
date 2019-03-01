/* resources/views/vendor/survloop/formtree-form-ajax.blade.php */
@if ($GLOBALS["SL"]->treeRow->TreeType == 'Survey' || $GLOBALS["SL"]->treeRow->TreeOpts%19 == 0 
    || $GLOBALS["SL"]->treeRow->TreeOpts%53 == 0)

@if ($hasFixedHeader)
    var mainFixed = function(){
        if (document.getElementById('fixedHeader')) {
            var fixer = $('#fixedHeader');
            var scrollMin = 40;
            if ($(window).width() <= 480) scrollMin = 30;
            if ($(this).scrollTop() >= scrollMin) fixer.addClass('fixed');
            $(document).scroll(function(){
                if ($(this).scrollTop() >= scrollMin) fixer.addClass('fixed');
                else fixer.removeClass('fixed');
            });
        }
    }
    $(document).ready(mainFixed);
@endif

@endif