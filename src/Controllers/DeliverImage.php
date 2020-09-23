<?php
/**
  * DeliverImage is a helper class 
  *
  * Survloop - All Our Data Are Belong
  * @package  rockhopsoft/survloop
  * @author  Morgan Lesko <rockhoppers@runbox.com>
  * @since  v0.2.18
  */
namespace RockHopSoft\Survloop\Controllers;

use Illuminate\Support\Facades\Response;
use Symfony\Component\HttpFoundation\File\File;

class DeliverImage
{

    private $filename = '';
    private $lifetime = 0;
    private $refresh  = false;

    public function __construct($filename = '', $lifetime = 0, $refresh = false)
    {
        $this->filename = $filename;
        $this->lifetime = $lifetime;
        $this->refresh  = $refresh;
        if ($this->lifetime == 0) {
            $this->lifetime = (60*60*24*3); // 3 days in seconds
        }
    }

    public function delivery()
    {
        if ($this->filename == '') {
            return '';
        }
        $handler = new File($this->filename);
        // Get the last modified time for the file (Unix timestamp):
        $file_time = $handler->getMTime(); 
        $header_etag = md5($file_time . $this->filename);
        $header_last_modified = gmdate('r', $file_time);
        $headers = [
            'Content-Disposition' => 'inline; filename="' . $this->filename . '"',
            // override caching for sensitive:
            'Cache-Control'       => 'public, max-age="' . $this->lifetime . '"', 
            'Last-Modified'       => $header_last_modified,
            'Expires'             => gmdate('r', $file_time + $this->lifetime),
            'Pragma'              => 'public',
            'Etag'                => $header_etag
        ];
        
        // Is the resource cached?
        $h1 = (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) 
            && $_SERVER['HTTP_IF_MODIFIED_SINCE'] == $header_last_modified);
        $h2 = (isset($_SERVER['HTTP_IF_NONE_MATCH']) 
            && str_replace('"', '', stripslashes($_SERVER['HTTP_IF_NONE_MATCH'])) 
                == $header_etag);
        if (($h1 || $h2) && !$this->refresh) {
            return Response::make('', 304, $headers); 
        }
        // File (image) is cached by the browser, so we don't have to send it again
        
        $headers = array_merge($headers, [
            'Content-Type'   => $handler->getMimeType(),
            'Content-Length' => $handler->getSize()
        ]);
        return response()->file($this->filename, $headers);
    }

}
