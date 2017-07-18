<?php
namespace SurvLoop\Controllers;

use Illuminate\Bus\Queueable;
use Illuminate\Mail\Mailable;
use Illuminate\Queue\SerializesModels;
use Illuminate\Contracts\Queue\ShouldQueue;

use App\Models\SLDefinitions;

class EmailController extends Mailable
{
    use Queueable, SerializesModels;
    
    public $emaTo      = '';
    public $emaTitle   = '';
    public $emaContent = '';
    
    /**
     * Create a new message instance.
     *
     * @return void
     */
    public function __construct($emaTitle = '', $emaContent = '', $emaTo = '')
    {
        $this->emaTitle   = $emaTitle;
        $this->emaContent = $emaContent;
        $this->emaTo      = $emaTo;
    }

    /**
     * Build the message.
     *
     * @return $this
     */
    public function build()
    {
        $cssColors = [];
        $cssRaw = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'Style Settings')
                ->get();
        if ($cssRaw && sizeof($cssRaw) > 0) {
            foreach ($cssRaw as $c) {
                $cssColors[$c->DefSubset] = $c->DefDescription;
            }
        }
        $cssColors["css-dump"] = '';
        $cssRaw = SLDefinitions::where('DefDatabase', 1)
                ->where('DefSet', 'Style CSS')
                ->where('DefSubset', 'email')
                ->first();
        if ($cssRaw && isset($cssRaw->DefDescription) > 0) {
            $cssColors["css-dump"] = $cssRaw->DefDescription;
        }
        return $this->view('vendor.survloop.emails.master')
            ->with([
                "emaTitle"   => $this->emaTitle,
                "emaContent" => $this->emaContent,
                "cssColors"  => $cssColors
            ])->subject($this->emaTitle);
    }
    
}
