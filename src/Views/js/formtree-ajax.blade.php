/* resources/views/vendor/survloop/js/formtree-ajax.blade.php */

@if ($GLOBALS["SL"]->treeRow->tree_type == 'Survey' 
    || $GLOBALS["SL"]->treeRow->tree_opts%19 == 0 
    || $GLOBALS["SL"]->treeRow->tree_opts%53 == 0)

    @if ($hasFixedHeader)
        var mainFixed = function(){
            if (document.getElementById('fixedHeader')) {
                var fixer = $('#fixedHeader');
                var scrollMin = 40;
                if ($(window).width() <= 480) scrollMin = 30;
                if ($(this).scrollTop() >= scrollMin) {
                    fixer.addClass('fixed');
                }
                $(document).scroll(function(){
                    if ($(this).scrollTop() >= scrollMin) {
                        fixer.addClass('fixed');
                    } else {
                        fixer.removeClass('fixed');
                    }
                });
            }
        }
        $(document).ready(mainFixed);
    @endif

@endif