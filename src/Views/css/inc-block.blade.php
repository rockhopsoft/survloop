<!-- generated from resources/views/vendor/survloop/css/inc-block.blade.php -->
@if (sizeof($curr->colors) > 0)
    <style>
    @if (isset($curr->colors["blockHeight"]) 
        && trim($curr->colors["blockHeight"]) != '' 
        && trim($curr->colors["blockHeight"]) != 'auto')
        #blockWrap{{ $curr->nIDtxt }} {
            height: @if ($curr->colors["blockHeight"] == 'h100') 95% 
                @else {{ substr($curr->colors["blockHeight"], 1) }}% @endif ;
            padding-bottom: 0px;
            overflow: visible;
        }
        #blockWrap{{ $curr->nIDtxt }} .nodeWrap, 
        #blockWrap{{ $curr->nIDtxt }} .nodeWrapError { 
            height: 100%;
            padding-top: 20px;
        }
    @endif
    @if (isset($curr->colors["blockAlign"]) 
        && trim($curr->colors["blockAlign"]) != '')
        #blockWrap{{ $curr->nIDtxt }}, #blockWrap{{ $curr->nIDtxt }} .nodeWrap, 
        #blockWrap{{ $curr->nIDtxt }} .nodeWrapError { 
            text-align: {{ $curr->colors["blockAlign"] }}; 
        }
    @endif
    @if (isset($curr->colors["blockImg"]) 
        && trim($curr->colors["blockImg"]) != '')
        @if (!isset($curr->colors["blockImgFix"]) 
            || $curr->colors["blockImgFix"] != 'P')
            /* CSS for other than iOS devices */
            @supports not (-webkit-overflow-scrolling: touch) {
                #blockWrap{{ $curr->nIDtxt }} {
                    background: url('{{ $curr->colors["blockImg"] }}') 
                    @if (isset($curr->colors["blockBG"]) 
                        && trim($curr->colors["blockBG"]) != '') 
                        {{ $curr->colors["blockBG"] }} 
                    @endif ;
                    @if (isset($curr->colors["blockImgType"]) 
                        && trim($curr->colors["blockImgType"]) == 'tiles')
                        background-repeat: repeat;
                    @else
                        background-position: center;
                        background-repeat: no-repeat;
                        -webkit-background-size: cover;
                        -moz-background-size: cover;
                        -o-background-size: cover;
                        background-size: cover;
                    @endif
                    @if (isset($curr->colors["blockImgFix"]) 
                        && $curr->colors["blockImgFix"] == 'Y') 
                        background-attachment: fixed;
                    @else
                        background-attachment: scroll;
                    @endif
                }
            }
            /* CSS specific to iOS devices */
            @supports (-webkit-overflow-scrolling: touch) {
                #blockWrap{{ $curr->nIDtxt }} {
                    background: url('{{ $curr->colors["blockImg"] }}') no-repeat;
                    background-position: center top;
                }
            }
            #blockWrap{{ $curr->nIDtxt }} .nodeWrap, #blockWrap{{ $curr->nIDtxt }} .nodeWrapError { 
                background: none;
            }
        @endif
    @elseif (isset($curr->colors["blockBG"]) && trim($curr->colors["blockBG"]) != '')
        #blockWrap{{ $curr->nIDtxt }} {
            background: {{ $curr->colors["blockBG"] }};
        }
        #blockWrap{{ $curr->nIDtxt }} .nPrompt, #blockWrap{{ $curr->nIDtxt }} div div .nPrompt,
        #blockWrap{{ $curr->nIDtxt }} .nodeWrap, #blockWrap{{ $curr->nIDtxt }} div .nodeWrap {
            background: none;
        }
    @endif
    @if (isset($curr->colors["blockText"]) && trim($curr->colors["blockText"]) != '')
        #blockWrap{{ $curr->nIDtxt }}, #blockWrap{{ $curr->nIDtxt }} p, #blockWrap{{ $curr->nIDtxt }} div, 
        #blockWrap{{ $curr->nIDtxt }} h1, #blockWrap{{ $curr->nIDtxt }} h2, #blockWrap{{ $curr->nIDtxt }} h3, 
        #blockWrap{{ $curr->nIDtxt }} h4, #blockWrap{{ $curr->nIDtxt }} h5, #blockWrap{{ $curr->nIDtxt }} h6 {
            color: {{ $curr->colors["blockText"] }};
        }
    @endif
    @if (isset($curr->colors["blockLink"]) && trim($curr->colors["blockLink"]) != '')
        #blockWrap{{ $curr->nIDtxt }} a:link, #blockWrap{{ $curr->nIDtxt }} a:visited, 
        #blockWrap{{ $curr->nIDtxt }} a:active, #blockWrap{{ $curr->nIDtxt }} a:hover {
            color: {{ $curr->colors["blockLink"] }};
        }
    @endif
    </style>
@endif