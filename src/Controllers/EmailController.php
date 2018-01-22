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
        $cssColors = $GLOBALS["SL"]->getCssColorsEmail();
        return $this->view('vendor.survloop.emails.master')
            ->with([
                "emaTitle"   => $this->emaTitle,
                "emaContent" => $this->emaContent,
                "cssColors"  => $cssColors
            ])->subject($this->emaTitle);
    }
    
}
