<!-- generated from resources/views/vendor/survloop/css/inc-block.blade.php -->
@if (sizeof($node->colors) > 0)
    <style>
    @if (isset($node->colors["blockHeight"]) && trim($node->colors["blockHeight"]) != '' 
        && trim($node->colors["blockHeight"]) != 'auto')
        #blockWrap{{ $nIDtxt }} {
            height: @if ($node->colors["blockHeight"] == 'h100') 95% @else {{ 
                substr($node->colors["blockHeight"], 1) }}% @endif;
            padding-bottom: 0px;
            overflow: visible;
        }
        #blockWrap{{ $nIDtxt }} .nodeWrap, #blockWrap{{ $nIDtxt }} .nodeWrapError { 
            height: 100%;
            padding-top: 20px;
        }
    @endif
    @if (isset($node->colors["blockAlign"]) && trim($node->colors["blockAlign"]) != '')
        #blockWrap{{ $nIDtxt }}, #blockWrap{{ $nIDtxt }} .nodeWrap, #blockWrap{{ $nIDtxt }} .nodeWrapError { 
            text-align: {{ $node->colors["blockAlign"] }}; 
        }
    @endif
    @if (isset($node->colors["blockImg"]) && trim($node->colors["blockImg"]) != '')
        @if (!isset($node->colors["blockImgFix"]) || $node->colors["blockImgFix"] != 'P')
            /* CSS for other than iOS devices */
            @supports not (-webkit-overflow-scrolling: touch) {
                #blockWrap{{ $nIDtxt }} {
                    background: url('{{ $node->colors["blockImg"] }}') @if (isset($node->colors["blockBG"]) 
                        && trim($node->colors["blockBG"]) != '') {{ $node->colors["blockBG"] }} @endif ;
                    @if (isset($node->colors["blockImgType"]) && trim($node->colors["blockImgType"]) == 'tiles')
                        background-repeat: repeat;
                    @else
                        background-position: center;
                        background-repeat: no-repeat;
                        -webkit-background-size: cover;
                        -moz-background-size: cover;
                        -o-background-size: cover;
                        background-size: cover;
                    @endif
                    @if (isset($node->colors["blockImgFix"]) && $node->colors["blockImgFix"] == 'Y') 
                        background-attachment: fixed;
                    @else
                        background-attachment: scroll;
                    @endif
                }
            }
            /* CSS specific to iOS devices */
            @supports (-webkit-overflow-scrolling: touch) {
                #blockWrap{{ $nIDtxt }} {
                    background: url('{{ $node->colors["blockImg"] }}') no-repeat;
                    background-position: center top;
                }
            }
            #blockWrap{{ $nIDtxt }} .nodeWrap, #blockWrap{{ $nIDtxt }} .nodeWrapError { 
                background: none;
            }
        @endif
    @elseif (isset($node->colors["blockBG"]) && trim($node->colors["blockBG"]) != '')
        #blockWrap{{ $nIDtxt }} {
            background: {{ $node->colors["blockBG"] }};
        }
        #blockWrap{{ $nIDtxt }} .nPrompt, #blockWrap{{ $nIDtxt }} div div .nPrompt,
        #blockWrap{{ $nIDtxt }} .nodeWrap, #blockWrap{{ $nIDtxt }} div .nodeWrap {
            background: none;
        }
    @endif
    @if (isset($node->colors["blockText"]) && trim($node->colors["blockText"]) != '')
        #blockWrap{{ $nIDtxt }}, #blockWrap{{ $nIDtxt }} p, #blockWrap{{ $nIDtxt }} div, 
        #blockWrap{{ $nIDtxt }} h1, #blockWrap{{ $nIDtxt }} h2, #blockWrap{{ $nIDtxt }} h3, 
        #blockWrap{{ $nIDtxt }} h4, #blockWrap{{ $nIDtxt }} h5, #blockWrap{{ $nIDtxt }} h6 {
            color: {{ $node->colors["blockText"] }};
        }
    @endif
    @if (isset($node->colors["blockLink"]) && trim($node->colors["blockLink"]) != '')
        #blockWrap{{ $nIDtxt }} a:link, #blockWrap{{ $nIDtxt }} a:visited, 
        #blockWrap{{ $nIDtxt }} a:active, #blockWrap{{ $nIDtxt }} a:hover {
            color: {{ $node->colors["blockLink"] }};
        }
    @endif
    </style>
@endif