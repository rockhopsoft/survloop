<?php
/**
  * SurvloopPDF is a class which aid exports to PDF.
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since v0.2.17
  */
namespace RockHopSoft\Survloop\Controllers;

use App;
use Mpdf\Mpdf;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use Illuminate\Support\Facades\Storage;
use App\Models\SLDefinitions;
use RockHopSoft\Survloop\Controllers\SurvloopOutputPDF;

class SurvloopPDF
{
    private $mpdf     = null;
    protected $folder = 'api/pdf/';
    protected $output = null;

    public function __construct($group = '')
    {
        if (trim($group) != '') {
            $this->folder .= $group . '/';
        }
        $this->output = new SurvloopOutputPDF;
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
        $xtra = '';
        if ($GLOBALS["SL"]->REQ->has('publicView')) {
            $xtra .= '&publicView=1';
        }
        return '<script type="text/javascript"> '
            . 'setTimeout("window.location=\'?gen-pdf=1&refresh=1' 
            . $xtra . '\'", 10); </script>';
    }

    public function simplifyPdf($input = '', $output = '')
    {
        $exec = "gs -sDEVICE=pdfwrite "
            . "-sPAPERSIZE=a4 -dPDFFitPage -dCompatibilityLevel=1.4 "
            . $this->gsPdfSettings() . " -dNOPAUSE -dQUIET -dBATCH "
            . "-sOutputFile=" . $output . " " . $input;
        return shell_exec($exec);
    }

    public function mergePdfs($files = [], $output = '')
    {
        if (file_exists($output)) {
            unlink($output);
        }
        $exec = "gs -sDEVICE=pdfwrite " . $this->gsPdfSettings() 
            . " -sOUTPUTFILE=" . $output . " -dNOPAUSE -dBATCH "
            . implode(" ", $files);
        return shell_exec($exec);
    }

    private function gsPdfSettings()
    {
        return " -dPDFSETTINGS=/" . $this->output->quality 
            . " -dDownsampleColorImages=true"
            . " -dColorImageResolution=" . $this->output->dpi
            . " -dColorImageDownsampleType=/Bicubic"
            . " -dDownsampleGrayImages=true"
            . " -dGrayImageResolution=" . $this->output->dpi
            . " -dGrayImageDownsampleType=/Bicubic"
            . " -dDownsampleMonoImages=true"
            . " -dMonoImageResolution=" . $this->output->dpi 
            . " -dMonoImageDownsampleType=/Bicubic ";
    }

    public function loadMpdf($fresh = true)
    {
        if ($this->mpdf === null || $fresh) {
            ini_set('max_execution_time', 90);
            $this->mpdf = new Mpdf(['tempDir' => '/tmp']);
            $styles = view('vendor.survloop.css.styles-pdf')->render()
                . "\n" . $this->getSysCustCSS();
            $this->mpdf->WriteHTML($styles, \Mpdf\HTMLParserMode::HEADER_CSS);
        }
        return true;
    }

    public function genCorePdf($filename = 'export.pdf')
    {
        $file = $filename;
        $content = file_get_contents(str_replace('.pdf', '.html', $file));
        $this->loadMpdf();
        $this->mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);
        unset($content);
        if (file_exists($file)) {
            unlink($file);
        }
        $title = $file;
        if (isset($GLOBALS["SL"]->x["pdfFilename"])
            && trim($GLOBALS["SL"]->x["pdfFilename"]) != '') {
            $title = $GLOBALS["SL"]->x["pdfFilename"];
        }
        $this->mpdf->SetTitle($title);
        $this->mpdf->Output($file, \Mpdf\Output\Destination::FILE);

        // check for attachments
        $fileAttach = str_replace('.pdf', '-attach.pdf', $filename);
        if (file_exists($fileAttach)) {
            $fileWithAttach = str_replace('.pdf', '-with-attach.pdf', $filename);
            $this->mergePdfs([$filename, $fileAttach], $fileWithAttach);
        }
        return true;
    }

    public function genSimplePDF($content = '', $filename = '/path/to/export.pdf')
    {
        if (trim($content) == '' || $filename == '/path/to/export.pdf') {
            return '';
        }
        $this->loadMpdf();
        $this->mpdf->WriteHTML($content, \Mpdf\HTMLParserMode::HTML_BODY);
        $this->mpdf->Output($filename, \Mpdf\Output\Destination::FILE);
        return $filename;
    }

    public function write($file = '')
    {
        $this->mpdf->Output($file, \Mpdf\Output\Destination::FILE);
        return $file;
    }

    public function pdfResponse($filename, $fileDeliver = '')
    {
        $file = $filename;
        $lastPos = strrpos($filename, '/');
        if ($lastPos >= 0) {
            $file = substr($filename, ($lastPos+1));
        }
        if ($fileDeliver != '') {
            $file = $fileDeliver;
        }
        if (isset($GLOBALS["SL"]->x["pdfFilename"])
            && trim($GLOBALS["SL"]->x["pdfFilename"]) != '') {
            $file = $GLOBALS["SL"]->x["pdfFilename"];
        } elseif ($fileDeliver == ''
            && isset($this->output->fileDeliver)
            && trim($this->output->fileDeliver) != '') {
            $file = $this->output->fileDeliver;
        }
        $dispo = 'inline';
        if ($GLOBALS["SL"]->REQ->has('download')) {
            $dispo = 'attachment';
        }
        $headers = [
            'Content-Type'        => 'application/pdf',
            'Content-Disposition' => $dispo . '; filename="' . $file . '"',
            'Cache-Control'       => 'no-store, no-cache, must-revalidate, max-age=0',
            'Pragma'              => 'no-cache'
        ];
        $fileWithAttach = str_replace('.pdf', '-with-attach.pdf', $filename);
        if (file_exists($fileWithAttach)) {
            return response()->file($fileWithAttach, $headers);
        }
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

    public function setOutput($fileStore, $fileDeliver = '')
    {
        if ($fileDeliver == '') {
            $fileDeliver = $fileStore;
        }
        $this->output->fileStore   = $fileStore;
        $this->output->fileDeliver = $fileDeliver;
        return $this->output;
    }

}
