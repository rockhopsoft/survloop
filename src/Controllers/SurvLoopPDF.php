<?php
/**
  * SurvLoopPDF is a class which aid exports to PDF.
  *
  * SurvLoop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.17
  */
namespace SurvLoop\Controllers;

use App;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\SLDefinitions;

use Spipu\Html2Pdf\Html2Pdf;
use Mpdf\Mpdf;

class SurvLoopPDF
{
    protected $folder = 'api/pdf/';

    public function __construct($group = '')
    {
        if (trim($group) != '') {
            $this->folder .= $group . '/';
        }
    }

    public function getPdfFile($filename = 'export.pdf')
    {
        $ret = '../storage/app/' . $this->folder . $filename;
        return str_replace(
            '../storage/app/' . $this->folder . '../storage/app/' . $this->folder,
            '../storage/app/' . $this->folder,
            $ret
        );
    }

    public function storeHtml($content, $filename = 'export.pdf')
    {
        $file = str_replace('../storage/app/', '', $filename);
        $file = str_replace('.pdf', '.html', $file);
        Storage::put($file, $this->cleanHtml($content));
        return '<script type="text/javascript"> '
            . 'setTimeout("window.location=\'?gen-pdf=1\'", 10); '
            . '</script>';
    }

    public function genCorePdf($filename = 'export.pdf')
    {
        $file = $filename;
        $content = file_get_contents(str_replace('.pdf', '.html', $file));
        ini_set('max_execution_time', 90);
        $mpdf = new Mpdf();
        $styles = view('vendor.survloop.css.styles-pdf')->render()
            . "\n" . $this->getSysCustCSS();
        $mpdf->WriteHTML($styles, \Mpdf\HTMLParserMode::HEADER_CSS);
        $mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);
        unset($content);
        if (file_exists($file)) unlink($file);
        $mpdf->Output($file, \Mpdf\Output\Destination::FILE);
        return true;
    }

    public function pdfResponse($filename)
    {
        $file = $filename;
        $lastPos = strrpos($filename, '/');
        if ($lastPos >= 0) {
            $file = substr($filename, ($lastPos+1));
        }
        $headers = [
            'Content-Type' => 'application/pdf',
            'Content-Disposition' => 'inline; filename="' . $file . '"'
        ];
        return response()->file($filename, $headers);
    }

    public function cleanHtml($content = '')
    {
        $content = str_ireplace('<nobr>', '', str_ireplace('</nobr>', '', $content));
        $content = preg_replace('/<!--(.|\s)*?-->/', '', $content);
        if (isset($GLOBALS["SL"]) && $GLOBALS["SL"]->REQ->has('debug-pdf')) {
            $content = str_ireplace("<div", "\n<div", str_ireplace("</div", "\n</div", $content));
            $content = str_ireplace("<tr", "\n<tr", str_ireplace("</tr", "\n</tr", $content));
            $content = str_ireplace("<td", "\n<td", str_ireplace("</td", "\n</td", $content));
            $content = str_ireplace("<input", "\n<input", $content);
            $content = str_ireplace("\t\t", "\t", str_ireplace("\t\t\t\t", "\t", $content));
            $content = str_ireplace("\t\t", "\t", str_ireplace("\t\t\t\t", "\t", $content));
            $content = str_ireplace("\n\n", "\n", str_ireplace("\n\n\n", "\n", $content));
            $content = str_ireplace("\n\n", "\n", str_ireplace("\n\n\n", "\n", $content));
        }
        return $content;
    }

    // Copied from GlobalsImportExport
    protected function getSysCustCSS()
    {
        $custCSS = SLDefinitions::where('def_database', 1)
            ->where('def_set', 'Style CSS')
            ->where('def_subset', 'main')
            ->first();
        if ($custCSS && isset($custCSS->def_description)) {
            return trim($custCSS->def_description);
        }
        return '';
    }

}