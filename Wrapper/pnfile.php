<?php

/**
 * Zikula Application Framework
 * $Id:                                                                               $
 * @copyright (c) 2009, Chris Candreva <chris@westnet.com>
 * 
 * 
 * 
 * 
 */


require_once('Common.php');

function Wrapper_file_main($args)
{
global $FileDirs;

  $NWconfigload = include("config/Wrapper.conf.php");  // Configuration variables
  if ($NWconfigload==false) return "<p>Unable to load config file.</p>\n";
  
// $FileDirs = Array('/docs');
  $DocRoot = pnServerGetVar('DOCUMENT_ROOT');
  $filename = pnVarCleanFromInput('file');

  $FullPath = Wrapper_getfullpath($FileDirs, $filename, $DocRoot);
  if ( !$FullPath) return Wrapper_errorpage('404', "Not Found: $filename");
  
  $filename = $FullPath;

  $FileOK = Wrapper_checkperm($FullPath, $NWkeywords);
  if (!$FileOK) return Wrapper_errorpage('403', 'Forbidden', _NWNoAuthorityForFile);

    if(!is_file($filename)) {
	return Wrapper_errorpage('403', 'Forbidden');
    }

    if(!is_readable($filename)) {
	return Wrapper_errorpage('403', 'Forbidden');
    }

    $stat = @stat($filename);
    $etag = sprintf('%x-%x-%x', $stat['ino'], $stat['size'], $stat['mtime'] * 1000000);
    $mimeType = mime_content_type($filename);

    header('Expires: ');
    header('Cache-Control: ');
    header('Pragma: ');
    header("Content-Type: $mimeType"); 
    if(isset($_SERVER['HTTP_IF_NONE_MATCH']) && $_SERVER['HTTP_IF_NONE_MATCH'] == $etag) {
        header('Etag: "' . $etag . '"');
        header('HTTP/1.0 304 Not Modified');
        return true;
    } elseif(isset($_SERVER['HTTP_IF_MODIFIED_SINCE']) && strtotime($_SERVER['HTTP_IF_MODIFIED_SINCE']) >= $stat['mtime']) {
        header('Last-Modified: ' . date('r', $stat['mtime']));
    	return Wrapper_errorpage('403', 'Forbidden');
    header('HTTP/1.0 304 Not Modified');
        return true;
    }

    header('Last-Modified: ' . date('r', $stat['mtime']));
    header('Etag: "' . $etag . '"');
    header('Accept-Ranges: bytes');
    header('Content-Length:' . $stat['size']);
    foreach($http_header_additionals as $header) {
        header($header);
    }

    if(@readfile($filename) === false) {
        return Wrapper_errorpage('500', 'Internal Server Error');
    } else {
        header('HTTP/1.0 200 OK');
        return true;
    }

}
