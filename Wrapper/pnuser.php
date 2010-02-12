<?php
/**
 * Wrapper : Wrap plain files in Zikula theme and permissions
 *
 * @copyright (c) 2010 Chris Candreva, Martin Stær Andersen
 * @link http://code.ziklula.org/wrapper/
 * @version $Id:                                              $
 * @license GNU/GPL - http://www.gnu.org/copyleft/gpl.html
 * @package Wrapper
 *
 *
 * Based on NukeWrapper by Martin Stær Andersen msandersen@tpg.com.au
 * This script will integrate any local HTML, PHP or txt file as well as
 * external web pages into your Zikula site (wrap the Zikula site around the file).
 */
 
global $ModName, $DocRoot, $FullPath, $RelDir, $WebRoot, $nukeurl, $nukeroot;
$ModName = basename( dirname( __FILE__ ) );
$DocumentRoot = "";  // Set this if your ISP has no Document Root set. Else for your own server ensure the Document Root/Home directory is set.
Wrapper_paths();

require_once('Common.php');

/**
 * the main user function
 * This function is the default function, and is called whenever the module is
 * initiated without defining arguments.  
 */
function Wrapper_user_main($args) {
    // Security check - lowest level is generally either 'overview' or 'read'
    if (!pnSecAuthAction(0, 'Wrapper::', '::', ACCESS_READ)) {
      return Wrapper_errorpage('403', 'Forbidden', _NWNOAUTHORITY);
    }

global $ModName, $DocRoot, $SiteRoot, $FullPath, $RelDir, $WebRoot, $WebDir, $nukeurl, $nukeroot, $PostnukeDir, 
	$PHPdir, $PHPdirs, $HTMLdir, $HTMLdirs, $URLkeys, $HTMLroot, $FullPath, $AllowPHP, $AllowURLs, 
	$extension, $URLwrap, $URLs, $query, $filewrap, $filewrapname, $FileBase, $PNGsuffix;

$NWrap=true;

// Load config file from Zikulz config directory.
$NWconfigload = include("config/Wrapper.conf.php");  // Configuration variables
if($NWconfigload==false) echo '<div style="color: red" align="center">'._NWConfigLoadFailed.'</div><br />';

    if (is_array($URLkeys2))
        $URLkeys = array_merge($URLkeys, $URLkeys2);

if ($WrapDebug && !pnSecAuthAction(0, 'Wrapper::', '::', ACCESS_ADMIN)) {
	$WrapDebug = false; // Only show debug info for Admins
}
// Start page if no file or URL given
//$starturl = pnModGetVar($GLOBALS['ModName'], 'StartPage');
//if (!empty($starturl)) $StartPage=$starturl;

$Request = array_merge($args, $_POST, $_GET);

//$filewrap = $Request['file']; 
$filewrap = pnVarCleanFromInput('file');
$urlwrap = pnVarCleanFromInput('url');
$url2wrap = pnVarCleanFromInput('url2');
$opt = pnVarCleanFromInput('opt');
if (empty($opt)) $opt=$UseTables;
$index = pnVarCleanFromInput('idx');
if (empty($index)) $index = $Layout;
$FrameHeight=pnVarCleanFromInput('height');
if (! (isset($FrameHeight) && is_numeric($FrameHeight) )) $FrameHeight='600';

$URLwrap=""; $msg=""; $checked="0"; $ValidDir="1";

// $query=$_SERVER['QUERY_STRING'];
$nukeurl = pnGetBaseURI(); // dirname($_SERVER['PHP_SELF']); // dirname returns \ if empty! // URI of index file, eg /nuke
$pnconfig['nukeurl']=$nukeurl;
$SiteRoot = $WebRoot.$nukeroot."/"; // $nukeurl Can also use $SERVER_NAME
// $SiteRoot=pnGetBaseURL(); // PN URL root using the Postnuke API
// $DocRoot=$_SERVER['DOCUMENT_ROOT']; // Server file path to site root 
if (!empty($DocumentRoot)) $DocRoot = $DocumentRoot;
global $PostnukeDir;
$PostnukeDir = $DocRoot.$nukeroot."/";

// If not allowing external connections, redirect to index page
if ($WrapDebug) echo " Remote Address: ".Wrapper_user_getip()." <br> Server Address: &nbsp;".$_SERVER['SERVER_ADDR']."<br>\n";
if (!empty($urlwrap) and $AllowExtLink==false and !empty($_SERVER['SERVER_ADDR']) and Wrapper_user_getip() !== $_SERVER['SERVER_ADDR']) // As Referer is easily spoofed and unreliable, use IP even though a server may host many domains on the one IP
    { session_write_close(); header("Location: ".$SiteRoot."index.php?External_links_not_allowed"); exit(); }

if(empty($filewrap) and empty($urlwrap) and empty($url2wrap) and !empty($StartPage))  {
    if (substr($StartPage,0,7)=="http://" || substr($StartPage,0,8)=="https://") { 
    	$urlwrap = $StartPage;
    } else { 
    	$filewrap = $StartPage;
    }
} 
// Make sure $filewrap has a leading slash
if (substr($filewrap, 0, 1) != "/") $filewrap = '/' . $filewrap;
$filewrapname = $filewrap;

$ValidExpr = "(".implode("|", $ValidExp1).($AllowPHP ? "|".implode("|", $ValidExp2) : "" ).")"; 
$ValidExpr = str_replace(".", "\.", $ValidExpr); 
$ValidFile = preg_match("/$ValidExpr/i", $filewrap);


////////// Local File Parsing. Determine path and validate  //////////
if (!empty($filewrap) && $ValidFile) { // Filename set & with valid extension
  $PathParts = pathinfo($filewrap); $extension = strtolower($PathParts["extension"]); 
  $PHProot="Not set"; $HTMLroot="Not set";
  if ($extension!='php') { // strstr($extension,'htm') or $extension=='txt'
	//$HTMLdir = $nukeroot; // Default to PN site root
	$HTMLdir = Wrapper_getdir ($HTMLdirs, $filewrap, $DocRoot);
	$direxists = is_dir($DocRoot.$HTMLdir) ; // ? true : false;

	// Add HTMLdir to filewrap if it's not there already
	$filewrap = (strpos($filewrap, $HTMLdir)===false ? $HTMLdir : "").$filewrap;
 	if ($WrapDebug) echo " File: $filewrap<br />\n";
	$HTMLroot=$WebRoot.$HTMLdir;  // If HTML page, use HTMLdir as root
	// $WebDir = (strpos($HTMLdir, $nukeurl)===false ? $nukeurl.$HTMLdir : $HTMLdir);
	$WebDir = dirname($filewrap);
	$FileDir=$DocRoot.$HTMLdir;
  } // end html, txt

  //elseif ($AllowPHP and ($extension=="php" or $extension=="php3")) {
  elseif ($AllowPHP) {
	$PHPdir=Wrapper_getdir ($PHPdirs, $filewrap, $DocRoot);; 
	$direxists = is_dir($DocRoot.$PHPdir); // ? true : false;
	if ($WrapDebug) echo " File: $filewrap<br />\n";

 	if ($direxists==false){
		$AllowPHP="0"; $ValidDir="0"; $msg=_NoValidPHPDir;
  	} // Needs to have a valid directory to use PHP pages
	if (strpos($filewrap, $PHPdir)===false)  $filewrap=$PHPdir.$filewrap; 
	if ($WrapDebug) echo " File: $filewrap<br />\n";
	$PHProot=$WebRoot.$PHPdir;  // If PHP page, use PHPdir as root
	// $WebDir = (strpos($PHPdir, $nukeurl)===false ? $nukeurl.$PHPdir : $PHPdir);
	$WebDir = dirname($filewrap);
	$FileDir=$DocRoot.$PHPdir;
  } // end php

  /*
  $fileOK=false; if ($WrapDebug)  echo "<BR /><strong>Security check:</strong><br /> Component: Wrapper : : file<br /> Instance: ".basename($filewrap)." : keyword : $extension<br />";
  if (is_array($NWkeywords) && !empty($NWkeywords)) {
      foreach($NWkeywords as $key) {
          if (stristr($filewrap, $key)!==false) { 
		if ($WrapDebug)  echo "Matched <em>$key</em> &nbsp;\n"; 
		if (!pnSecAuthAction(0, 'Wrapper::file', basename($filewrap).":$key:$extension", ACCESS_READ)) { 
			if ($WrapDebug)  echo "<span style=\"color: red;\">failed</span><br />";
                        return Wrapper_errorpage('403', 'Forbidden', _NWNoAuthorityForFile);
    		} 
          $fileOK=true;
          if ($WrapDebug)  echo "<span style=\"color: green;\">passed</span><br />";
          } 
      }
  } 

  // If we haven't passed yet, check extension permissions.
  if ($fileOK==false) { 
    $fileOK = pnSecAuthAction(0, 'Wrapper::file', basename($filewrap)."::$extension", ACCESS_READ);
  }
  */
  /* Check Zikula permissions for access to this file */
  $fileOK = Wrapper_checkperm($filewrap, $NWkeywords);
  // If we still haven't passed, return an error.
  if ($fileOK==false) { 
    return Wrapper_errorpage('403', 'Forbidden', _NWNoAuthorityForFile);  
  }
  if ($WrapDebug)  echo "<span style=\"color: green;\">passed</span><br />";
  // end file check

  $FullPath=str_replace("\\", "/", realpath($DocRoot.$filewrap)); 
  $FullPathDir=dirname($FullPath);
  $FileBase=basename($filewrap);
  if (empty($FileBase)) {$FullPath.="/"; }

  $ValidURL = (strpos($FullPath, $DocRoot)!==false) && $direxists; 
if ($WrapDebug) echo "<br /> WebDir: $WebDir<br /> FileDir: $FileDir &nbsp;&nbsp;".($direxists ? " <span style=\"color: green\">Directory OK.</span>" : " <span style=\"color: red\">Directory <strong>NOT</strong> valid!</span>")."<br />\n"
	." Filebase: $FileBase<br />" 
	." FullPathDir: $FullPathDir<br /> <strong>Fullpath: $FullPath</strong><br /> <strong>DocRoot: $DocRoot/</strong><br />"
	.(strpos($FullPath, $DocRoot)===false ? " <span style=\"color:red\">Filepath <strong>OUTSIDE</strong> document root!</span>" : " <span style=\"color:green\"><strong>File onsite.</strong></span>")."<br /><br />";
  if ($ValidURL===false) {
  	if ($WrapDebug 		// && !isset($_REQUEST['WrapDebug']) && !isset($_SESSION['WrapDebug']))  debugtable();
		&& !isset($HTTP_GET_VARS['WrapDebug']) 
		&& !isset($HTTP_POST_VARS['WrapDebug']) 
		&& !isset($HTTP_COOKIE_VARS['WrapDebug']) 
		&& !isset($HTTP_SESSION_VARS['WrapDebug']))  
	{ $index=4; include("header.php"); debugtable();  include("footer.php"); exit; }// Output debugtable amd then exit
	return Wrapper_errorpage('404', "Not Found: $FileBase");

  }  // If webroot root and valid directory is not in the full filepath, an attempt has been made to hack the site by using ../ in the filepath
  if ($extension=='pdf') { 
  	$URLwrap=$filewrap; $AutoResize=false; // If a PDF file, wrap in an iFrame.
  }
} 

////////// Target is a URL //////////
elseif (!empty($urlwrap) && $AutoResize) {
  $URLwrap = Wrapper_admin_checkurl($urlwrap, $WrapDebug);
  $URLarray = parse_url($URLwrap);
  $wrapHost = $_SERVER['HTTP_HOST'];
  $ExternalUrl = ($URLarray['host'] != $wrapHost);
  if ($WrapDebug) echo " Requested Host: $URLarray[host]<br /> Site Host: $wrapHost<br /> ".($URLarray['host']==$wrapHost ? "Same host" : "Different host")."<br />\n";
  
  // If target site not local, read target page for processing, and embed in local page
  if ($ExternalUrl) {
  	if (ini_get('allow_url_fopen')==false) {
            return Wrapper_errorpage('500', 'Internal Server Error', _FopenDisallowed1);
	}
  	if (!empty($_SERVER['HTTP_USER_AGENT'])) { // Valid User-Agent required for some sites
		ini_set('user_agent','$_SERVER["HTTP_USER_AGENT"]');
  	} else {  ini_set('user_agent','Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)'); }  //'MSIE 4\.0b2;'
	// header('Content-Type: text/html; charset=utf-8'); // charset=ISO-8859-1
	//  header("Cache-Control:");
	//  header("Cache-Control: no-store, no-cache, must-revalidate");  // HTTP/1.1
	if (isset($_COOKIE)) { 
		foreach ($_COOKIE as $key=>$cookie) { setcookie($key, $cookie); }
	} 
  	$URLhandle=@fopen($URLwrap, "r"); 
  	if ($URLhandle) {
    	// $contents = file_get_contents($URLwrap); 
	    $contents = "";
	    do {
	      $data = fread($URLhandle, 8192);
	      if (strlen($data) == 0) {  break; }
	      $contents .= $data;
	    } while (true);
	    fclose($URLhandle);
  	} else {
    	    /* There was a problem opening the file. */
    	    $msg = _FileCantOpen1.$URLwrap._FileCantOpen2.$URLwrap._FileCantOpen3; 
            return Wrapper_errorpage('500', 'Internal Server Error', $msg);
	}
  }
  
  // Javascript in HEAD to resize iFrame.
  if ($ExternalUrl) {
    PageUtil::AddVar('javascript', "modules/$ModName/pnjavascript/writeiframe.js");
  } else {
    PageUtil::AddVar('javascript', "modules/$ModName/pnjavascript/iFrameHeight.js");
  }

  // Target site not local, process page and embed in local page
  if ($ExternalUrl) {
  // Determine domain from URL
  $domain=""; 
  $endstring=substr(strrchr($URLwrap, "/"),1); 
  if (substr($URLwrap,-1)=="/") { 
	$domain = $URLwrap;
  } elseif((strrpos(substr($URLwrap,8), "/")===false) OR !preg_match("/(\.htm|\.cgi|\.asp|\.iasp|\.jsp|\.php|\.cfm|\.pl|\.adp|\.orm)/i", $endstring)) {
	$domain = $URLwrap."/"; 
  } elseif(strrchr(substr($URLwrap,8), "/")!==false) {
	$pos = strrpos($URLwrap, "/");
	$domain = substr($URLwrap, 0, $pos + 1);	
  } else {
	$domain = $URLwrap;
  }
 // Open links in new window if not the same host
$target="";
if ($OpenInNewWindow) $target=" target=\"_blank\""; // and ($URLarray['host'] != $wrapHost)
$BaseURL = "<base href=\"$domain\"$target>\n";

  // Removes 3 types of frame-breaking code in page
  $pattern = array('/top\.location\s*=\s*(self|window)(\.document)?\.location/',
	'/top\.location\.replace\(self\.location\)/', 
	'|if\s+\(parent\.location\s*!=[^)]+\)\s*(\{)?[^{}]*parent\.location\s*=[^;]+;(?(1)[^}]*\})|Us',
	'|if\s+\(self\.parent\.frames\.length\s*!=\s*0\)\s*(\{)?[^{}]*(self\.)?parent\.location\s*=[^;]+;(?(1)[^}]*\})|Us'); 
  $contents = preg_replace($pattern, "var donothing=0; // Let's not break out of this lovely frame", $contents); 

  // Apply BASE tag to fix links in external page
  if(!preg_match('|<base\s+href\s*=[^>]+>|Usi', $contents)) {
	// No BASE tag, apply our own
  	if (preg_match('|<head>|i', $contents))
  		$contents = preg_replace('|<head>|i', "<head>\n".$BaseURL, $contents,1); 
  	else 
		$contents = $BaseURL.$contents;
  } else { 
	// Existing BASE tag, set _blank TARGET attribute to open links in new window
	if (!preg_match('|<base.+target[^>]+>|Usi', $contents)) // BASE with no Target
		$contents = preg_replace('|<(base[^>]+)>|Usi', "<$1 target=\"_blank\">", $contents,1);
	elseif (preg_match('|<base.+target\s*=[^_]*_top[^>]*>|Usi', $contents)) // target="_top" to "_blank"
		$contents = preg_replace('|<(base.+)target\s*=[^>]+>|Usi', "<$1target=\"_blank\">", $contents,1);
//	elseif (preg_match('|<base.+target\s*=[^>]+>|Usi', $contents)) 
//		$contents = preg_replace('|<(base.+)target\s*=[^>]+>|Usi', "<$1target=\"_blank\">", $contents,1);
  }
  } // end if external host
} // end URL
elseif(!empty($url2wrap) or $AutoResize==false) { 
  if ($AutoResize==false && !empty($urlwrap)) 
      $url2wrap=$urlwrap; 
  $URLwrap = Wrapper_admin_checkurl($url2wrap, $WrapDebug);
}


////////// URL Output //////////
if (!empty($URLwrap)) {
  wrap_opentable($opt); 
  if ($ShowLink) { ?>
  <p class="wrapURL" align="center"><a href="<?PHP echo $URLwrap ?>" target="_blank"><strong>Open in a new window</strong></a></p>
  <?PHP 
  if ($WrapDebug) echo "     <div align=\"center\" id=\"dimensions\">Height: <span id=\"Height\">-</span> &nbsp;Width: <span id=\"Width\">-</span>
  <!--<br />
	Computed Height: <span id=\"CompHeight\">-</span>--></div><br />\n"; ?>
  <hr>
<?PHP } ?>
<noscript><div align="center"><strong><?PHP echo _EnableJS ?></strong></div></noscript>
  <iframe id="ContentFrame" name="ContentFrame" scrolling="auto" frameborder="no" 
        <?PHP echo ((!empty($url2wrap) or !$AutoResize or !$ExternalUrl) ? "src=\"$URLwrap\" " : 'src="" ') ?> 
	onLoad="window.setTimeout('iFrameHeight(this)',50);" 
	style="width: 100%; height: <?PHP echo $FrameHeight ?>px;" marginwidth="0" marginheight="0">
  </iframe>
<?PHP if(isset($urlwrap) && $AutoResize && $ExternalUrl) { ?>

<!---------------- Buffer ----------------->
<DIV id="buffer" style="display: none;">
<?PHP 
$input = array('<', '>', '"'); $output = array('&l2;', '&g2;', '&q2;'); /* Escape < > to avoid rendering */
$all1 = (is_array($wrapIn['all']) && is_array($wrapOut['all'])); 
$all2 = (is_array($wrapIn2['all']) && is_array($wrapOut2['all']));
if (is_array($URLkeys) OR $all1 OR $all2) {
	// $key = array_search($domain, $URLkeys);
	if ($WrapDebug) echo " <strong>URLkeys:</strong><br />\n";
	$key=false;
	foreach ($URLkeys as $k=>$url) {
		if ($WrapDebug) echo "<strong>Key:</strong> $k &nbsp;<strong>url:</strong> $url ".(strpos($domain, $url)!==false?' &nbsp;<span style="color: green;">Match</span>':' &nbsp;<span style="color: red;">No match</span>')."<br />\n"; 
		if (strpos($domain, $url) !==false) { $key=$k; break; }
	}
	if ($WrapDebug) echo " <strong>Domain:</strong> $domain &nbsp;&nbsp;".($key!==false ? "<span style='color: green'>Key match</span>" : "No key match")."<br />\n";
	if ($all1) { 
		$input = array_merge($wrapIn['all'], $input); 
		$output = array_merge($wrapOut['all'], $output);
	}
	if (($key!==false) && is_array($wrapIn[$key]) && is_array($wrapOut[$key])) { //  && in_array($domain, $URLkeys)
		$input = array_merge($wrapIn[$key], $input); 
		$output = array_merge($wrapOut[$key], $output);
	}
	if ($all2) {
		$contents = preg_replace($wrapIn2['all'], $wrapOut2['all'], $contents);
	}
	if (($key!==false) && is_array($wrapIn2[$key]) && is_array($wrapOut2[$key])) {
		$contents = preg_replace($wrapIn2[$key], $wrapOut2[$key], $contents);
	}
}
//if (is_array($wrapIn) && is_array($wrapOut)) {
//	$input = array_merge($wrapIn, $input); 
//	$output = array_merge($wrapOut, $output);
//}
//if (is_array($wrapIn2) && is_array($wrapOut2)) {
//	$contents = preg_replace($wrapIn2, $wrapOut2, $contents);
//}
 $contents = str_replace($input, $output, $contents);

 echo $contents ?>
</DIV>
<script language="JavaScript" type="text/javascript">writeiframe();<!--  iFrameHeight(); -->
</script>
<!-------------- End Buffer --------------->

<?PHP }
  wrap_closetable($opt, $WrapDebug); 
  exit;
}


////////// Local file output //////////
if (empty($filewrap)){ 
  $msg = _NoFileSelected1.$SiteRoot."index.php?module=Wrapper&"._NoFileSelected2.$SiteRoot."index.php?module=Wrapper&"._NoFileSelected3; $checked=$opt; $opt="1";
  // $msg="<strong>No file selected. Please enter filename and try again.<br />URL is in format <span style=\"color: red;\">www.yoursite.com/wrap.php?file=SomePage.html</span><br /><nobr><span style=\"margin-left:9em;\">or <span style=\"color: red;\">www.yoursite.com/wrap.php?url=www.somesite.com</span></span></nobr></strong>"; $checked=$opt; $opt="1";
  wrap_opentable($opt); wrap_output($msg, $filewrap, $checked, $index); wrap_closetable($opt, $WrapDebug); exit;
} else {
  $ValidFile = $ValidFile && $extension!="txt"; 
  if ($WrapDebug) echo $DocRoot.(substr($dir, 0, 1)=="/" ? "" : "/").$filewrap."&nbsp; Valid type: ".(($ValidFile or (stristr($filewrap, ".txt")!=false)) ? "Yes" : "No")."<br />";
  $opens=@fopen($DocRoot.$filewrap, "r");
  if ($opens and $ValidFile) { // Checks if can open file, and includes file if it's a PHP or HTML file, then exit
	$FileDir = dirname($filewrap);
        if ($WrapDebug) echo " Include dir: ".$FileDir."<br /> Script dir: ".dirname($_SERVER['PHP_SELF'])."<br />"
                       ." Same directory? ".(dirname($filewrap) == dirname($_SERVER['PHP_SELF']) ? "Yes" : "No")."<br />\n"
                       ." Current Working Directory: ".getcwd()."<br />";
        // Need to change directory so as not to break paths in the included file
        $test = chdir($DocRoot.$FileDir); if ($WrapDebug) echo " New working directory: ".getcwd()." &nbsp;".($test?"Directory change successful":"Directory change failed").".<br />\n";
	$FixLinks = $FixLinks && (dirname($filewrap)!=dirname($_SERVER['PHP_SELF'])); // Don't parse links when in same dir
	$parse = ($FixLinks or $WrapLinks or $FixTransparency); // ($FixLinks and ($WrapLinks || (dirname($filewrap)!=dirname($_SERVER['PHP_SELF']))));
	if ($WrapDebug) echo " FixLinks: ".($FixLinks ? "On" : "Off")." &nbsp;WrapLinks: ".($WrapLinks ? "On" : "Off")." &nbsp;Parse: ".($parse ? "True" : "False")."<br />\n";
  	if ($ExtractMeta or $parse) { // Load file for parsing
 	  ob_start(); 
  	  $PHPself = $_SERVER['PHP_SELF']; $_SERVER['PHP_SELF'] = $filewrap; // temporarily change PHP_SELF for included file
  	  if ($WrapDebug) echo " PHP_SELF: $PHPself; &nbsp;Included file PHP_SELF: ".$_SERVER['PHP_SELF']."<br />\n"; 
	  include($DocRoot.$filewrap);
	  $file = ob_get_contents(); // if included PHP file does its own ob_end_clean, may not work
	  ob_end_clean(); // $file = ob_get_clean(); in PHP >= 4.3.0
  	  $_SERVER['PHP_SELF'] = $PHPself;
	}
	if ($ExtractMeta) { 
	  global $meta;
	  // Get title
	  $match = preg_match('|<title\s*>([^<]+)</title\s*>|misU',$file,$matches);
	  if ($match && $matches[1] !="") { $meta['title'] = $matches[1]; } // $NWtitle
	  else { $meta['title'] = basename(substr($filewrap, 0, strrpos($filewrap,"."))); // $NWtitle = 
  		$meta['title'] = str_replace("_", " ", $meta['title']); }
	  if ($WrapDebug)  echo " <br /><strong>Metatags in file:</strong><br /> ".($match ? "Title: ".$meta['title'] : "No <title> tag, using filename: ".$meta['title'])."<br />\n";
	  	
	  $match = preg_match('|<meta[^>]+keywords["\'\s]+content\s*=\s*["\']([^"\']+)["\']|misU', $file, $matches);
	  if ($match) $meta['keywords'] = $matches[1]; if ($WrapDebug) echo " keywords: ".($match? $matches[1]: "Not set")."<br />";

	  $match = preg_match('|<meta[^>]+description["\'\s]+content\s*=\s*["\']([^"\']+)["\']|misU', $file, $matches);
	  if ($match) $meta['description'] = $matches[1]; if ($WrapDebug) echo " description: ".($match? $matches[1]: "Not set")."<br />";
	  
	  $match = preg_match('|<meta[^>]+author["\'\s]+content\s*=\s*["\']([^"\']+)["\']|misU', $file, $matches);
	  if ($match) $meta['Author'] = $matches[1]; if ($WrapDebug) echo " Author: ".($match? $matches[1]: "Not set")."<br />";
	  
	  $match = preg_match('|<meta[^>]+Content-Type["\'\s]+content\s*=\s*["\']([^"\']+)["\']|misU', $file, $matches);
	  if ($match) $meta['Content-Type'] = $matches[1]; if ($WrapDebug) echo " Content-Type: ".($match? $matches[1]: "Not set")."<br />";

	  $match = preg_match('|<meta[^>]+Content-Language["\'\s]+content\s*=\s*["\']([^"\'\s]+)["\']|misU', $file, $matches);
	  if ($match) $meta['Content-Language'] = $matches[1]; if ($WrapDebug) echo " Content-Language: ".($match? $matches[1]: "Not set")."<br />";

	  $match = preg_match('|<meta[^>]+expires["\'\s]+content\s*=\s*["\']([^"\']+)["\']|misU', $file, $matches);
	  if ($match) $meta['Expires'] = $matches[1]; if ($WrapDebug) echo " Expires: ".($match? $matches[1]: "Not set")."<br />";

	  $match = preg_match('|<meta[^>]+Cache-Control["\'\s]+content\s*=\s*["\']([^"\'\s]+)["\']\s*|misU', $file, $matches);
	  if ($match) $meta['Cache-Control'] = $matches[1]; if ($WrapDebug) echo " Cache-Control: ".($match? $matches[1]: "Not set")."<br />";

	  $match = preg_match('|<meta[^>]+robots["\'\s]+content\s*=\s*["\']([^"\'\s]+)["\']\s*|misU', $file, $matches);
	  if ($match) $meta['Robots'] = $matches[1]; if ($WrapDebug) echo " Robots: ".($match? $matches[1]: "Not set")."<br />";
	} else {
	  $meta['title'] = basename(substr($filewrap, 0, strrpos($filewrap,"."))); // $NWtitle = 
  	  $meta['title'] = str_replace("_", " ", $meta['title']); 
	  if ($WrapDebug)  echo  " Title from filename: ".$meta['title']."<br />";
	}
  	  if ($WrapDebug && !isset($_REQUEST['WrapDebug'])) echo "PostnukeDir: $PostnukeDir <br />Current directory: ".getcwd();
	chdir($PostnukeDir); // Change back to the PostNuke directory
	wrap_opentable($opt);
 	if ($parse) {
 	  if ($FixLinks && $WebDir!='/') { 
	  	// look for src|href|background|CSS background| form Action, and @import relative links; only convert hyperlinks if not WrapLinks
	  	$pattern = array('[<'.($WrapLinks==false ? '' : '(?!a)(?!form)').'([^>]+)\s(src|href|background|action)\s*=\s*((["\'])?)(?!http)(?!ftp)(?!mailto)(?!javascript:)(?![/"\'\s#]+)]Ui',  
				'[>\s*@import\s+(url\s*\(["\']?|["\'])(?!http)(?![/"\'\s]+)]Ui', 
				'|<(style\s+type\s*=\s*["\']text/css["\']>)?([^>]+)(background(?:-image)?)\s*:([^(};]*)url\s*\((["\'])?(?!http)(?![/"\'\s]+)|Ui'); // '|<([^>]+)/\./([^>]+)>|U'
		$replace = array('<$1 $2=$3'.$WebDir.'/', '>@import $1'.$WebDir.'/', '<$1$2$3:$4url($5'.$WebDir.'/'); //, '<$1/$2>' $FileDir $HTMLroot
	  } else {
	  	$pattern = array(); $replace = array();
	  }
	  if ($WrapLinks) { // Redirect links through NW. Convert /link.html to page.php?file=link.html for valid file types
		$pattern[] = '%<a([^>]+)href\s*=\s*((["\'])?)(?!http)(?!ftp)(?!mailto)(?!javascript:)(?=[^\s#"\']*'.$ValidExpr.')%Ui'; // (?!page\.php.file=)
		$replace[] = '<a$1href=$2'.$nukeurl.'/index.php?module=Wrapper&file=';
		$pattern[] = '%<([^>]+'.$nukeurl.'/index.php?module=Wrapper&file=[^\s>]+)\?%Ui'; // Replace sub-query ? with &
		$replace[] = '<$1&'; 
		// Add hidden form field where file=filename.ext
		$pattern[] = '%<form([^>]+)action\s*=\s*(["\']?)(?!http)([^\s#"\']+'.$ValidExpr.')([^>]*)>%Ui';
		$replace[] = "<form$1action=$2".$nukeurl."/index.php$5>\n"
			."  <input type=\"hidden\" name=\"module\" value=\"Wrapper\">\n"
			."  <input type=\"hidden\" name=\"file\" value=\"$3\">";
	  }
	  $file = preg_replace($pattern, $replace, $file);
	  if ($FixTransparency) {
	  	$file = AddFilter($file, $SpacerPath, $PNGsuffix);
	  }
	  echo $file;
	  unset($file);
  	} else { 
	  chdir($DocRoot.$FileDir);
  	  $PHPself = $_SERVER['PHP_SELF']; $_SERVER['PHP_SELF'] = $filewrap; 
  	  if ($WrapDebug) echo " PHP_SELF: $PHPself; &nbsp;Included file PHP_SELF: ".$_SERVER['PHP_SELF']."<br />\n";
  	  include($DocRoot.$filewrap); 
  	  $_SERVER['PHP_SELF'] = $PHPself;
  	  if ($WrapDebug && !isset($_REQUEST['WrapDebug'])) echo "PostnukeDir: $PostnukeDir <br />Current directory: ".getcwd();
	  chdir($PostnukeDir);
  	}
  	if ($WrapDebug && !isset($_REQUEST['WrapDebug']))  echo "<br />New working directory: ".getcwd();
  	wrap_closetable($opt, $WrapDebug, true); exit;
  } elseif ($opens AND preg_match("/\.txt+$/i",$filewrap)) { // Checks if text file
  	// $GLOBALS['NWtitle'] can be referenced from theme template. 
  	$meta['title'] = basename(substr($filewrap, 0, strrpos($filewrap,".")));
  	$meta['title'] = str_replace("_", " ", $meta['title']);
	wrap_opentable($opt);
	$fcontents=file($DocRoot.$filewrap); // Puts file into array
	foreach ($fcontents as $value) {
		echo str_replace("\t",str_repeat("&nbsp;", 8),str_replace("  ","&nbsp;&nbsp;",nl2br(htmlspecialchars($value)))); // Outputs text file as-is, with no HTML markup
 	} // Replace 2 spaces with 2 non-breaking spaces, and tabs (\t) with 8, insert <br /> before newlines, and convert special characters for HTML output
	wrap_closetable($opt, $WrapDebug, true); 
	exit;
  } elseif (!$ValidFile) { // AND $ValidDir
  	$msg = _FileWrongType1.$filewrapname._FileWrongType2.($AllowPHP ? ", php, php3, asp, jsp, cfm, cgi, pl," : "")._FileWrongType3.($AllowPHP ? "" : _FileWrongType4)."<br />"._FileWrongType5;
//  	$msg="<strong>File <span style='color: red;'>$filewrap</span> wrong file type.<br />\nMust be html, shtml".($AllowPHP ? ", php, php3, asp, jsp, cfm, cgi, pl," : "")." or txt file. ".($AllowPHP ? "" : "PHP support disabled.")." <br />Please correct and try again.</strong>";
  	$checked=$opt; $opt="1"; $filewrap=$filewrapname;
  } else {
  	$msg = _FileNotFound1.$filewrapname._FileNotFound2; 
//  	$msg.="<strong>File <span style=\"color: red;\">$filewrap</span> not found or couldn't be opened. Please correct and try again.</strong>";
  	$checked=$opt; $opt="1"; $filewrap=$filewrapname;
  }
  wrap_opentable($opt); wrap_output($msg, $filewrap, $checked, $index); wrap_closetable($opt, $WrapDebug); 
  exit; // Output message and form, then exit
}


    // Return the output that has been generated by this function
//    return $output->GetOutput();
} // End Wrapper_user_main


/******************* Functions *******************/

/****************** Wrap Outout ******************/ 
function wrap_output($msg, $filewrap, $checked, $idx="0", $type="file") {
global $PHP_SELF; 

$idx = ($idx>=0 || $idx<=4) ? $idx : "0";
	$text[0] = _NWLayout0; // "Default view - Left column only";
	$text[1] = _NWLayout1; // "Home page (Left, Right and Center blocks with Admin message)";
	$text[2] = _NWLayout2; // "Left and Right blocks, no Center blocks or Admin message";
	$text[3] = _NWLayout3; // "Right blocks, no Left blocks, reverse of default";
	$text[4] = _NWLayout4; // "No side blocks, only wrapped page with Header and Footer";
echo "<script type='text/javascript'>
	var text = new Array(5);
	text[0] = '$text[0]';
	text[1] = '$text[1]';
	text[2] = '$text[2]';
	text[3] = '$text[3]';
	text[4] = '$text[4]'; 

function ChangeText() {
//	var elm = document.getElementById('idx');
	var elm = document.WrapLocation.idx; 
	var OptionValue = elm.options[elm.selectedIndex].value; 
	document.getElementById('descr').innerHTML = text[OptionValue];
}
</script>\n"; 
echo "<center>$msg</center><br /><br />\n"; ?>
  <table border="0" cellpadding="0" cellspacing="0" align="center">
    <tr>
      <td align="left">
        <form action="<?PHP echo $_SERVER['PHP_SELF'] ?>" method="get" name="WrapLocation" id="WrapLocation">
          &nbsp;Wrapper<br />
          <nobr>
          <input type="hidden" name="module" value="Wrapper">
          <input type="text" name="<?PHP echo $type ?>" size="35" value="<? echo $filewrap ?>">
          &nbsp;<?PHP echo _NWThemeTable."\n"; // Theme table ?>
          <input type="checkbox" name="opt" value="1" <?PHP if ($checked) echo "checked" ?>>
          <input type="submit" value="<?PHP echo _SUBMIT ?>"></nobr><br /><!-- style="margin-right: 6em;" -->
<?PHP if ($type=="file") echo "          "._PathRelToRoot; ?>
<!--          <p><?PHP echo _NWLayout ?> 
          <select name="idx" id="idx" onChange="ChangeText();">
            <option value="0"<?PHP echo ($idx==0 ? " selected":"") ?>>0</option>
            <option value="1"<?PHP echo ($idx==1 ? " selected":"") ?>>1</option>
            <option value="2"<?PHP echo ($idx==2 ? " selected":"") ?>>2</option>
            <option value="3"<?PHP echo ($idx==3 ? " selected":"") ?>>3</option>
            <option value="4"<?PHP echo ($idx==4 ? " selected":"") ?>>4</option>
          </select>
          <nobr> &nbsp;<span id="descr"><?PHP echo $text[$idx] ?></span></nobr></p> -->
        </form>
      </td>
    </tr>
  </table>
<?
}  //End wrap_output

/********** Wrap Opentable **********/ 
function wrap_opentable($opt) {
  include_once("header.php");
  if ($opt=="1") { OpenTable(); echo "\n<DIV id=\"Wrapper\">\n"; }
  else { 
    echo "<DIV id=\"Wrapper\" style=\"margin-bottom: 1em;\">\n"; // class=\"NWopentable\" 
  } 
} //End opentable function

/********** Wrap Closetable **********/ 
function wrap_closetable($opt, $WrapDebug=false, $CallHooks=false) {
global $HTTP_POST_VARS, $HTTP_GET_VARS, $HTTP_COOKIE_VARS, $HTTP_SESSION_VARS, 
	$PostnukeDir, $filewrapname, $FileBase, $Request;
  if ($opt=="1") { CloseTable(); }
  echo "</DIV><!-- End wrapped page -->\n"; 
//  else { echo "</DIV>\n"; }
  if ($CallHooks) { // Call hooks 
  // $pnRender->assign('hooks', ... ); // pnRender
  echo pnModCallHooks('item', 'display', $FileBase, 
  		pnModURL('Wrapper', 'user', 'main', array('file' => $filewrapname)))."<br /><br />"; 
  }
  include_once("footer.php");
// Debug table output
if ($WrapDebug 		// && !isset($_REQUEST['WrapDebug']) && !isset($_SESSION['WrapDebug']))  debugtable();
	&& !isset($HTTP_GET_VARS['WrapDebug']) 
	&& !isset($HTTP_POST_VARS['WrapDebug']) 
	&& !isset($HTTP_COOKIE_VARS['WrapDebug']) 
	&& !isset($HTTP_SESSION_VARS['WrapDebug']))  debugtable();
  exit; 
} // End closetable function

/********** Wrapper System and Web Paths **********/
function Wrapper_paths() {
  global $DocRoot, $FullPath, $RelDir, $WebRoot, $nukeurl, $nukeroot, $DocumentRoot;
// Determine if site outside Document Root, maybe due to site in User Dir ~user, if so make new DocRoot
$nukeurl = $nukeroot = pnGetBaseURI();
if (empty($_SERVER['DOCUMENT_ROOT']) && !empty($DocumentRoot))  $_SERVER['DOCUMENT_ROOT'] = $DocumentRoot; 
$DocRoot = $_SERVER['DOCUMENT_ROOT']; // Server file path to site root 
$WebRoot = "http".(isset($_SERVER['HTTPS']) && $_SERVER['HTTPS']=="on"?"s":"")."://".(empty($_SERVER['HTTP_HOST'])?getenv('HTTP_HOST'):$_SERVER['HTTP_HOST']);
if (isset($_SERVER['PATH_TRANSLATED'])) $_SERVER['SCRIPT_FILENAME'] = $_SERVER['PATH_TRANSLATED'];
if (!isset($_SERVER['SCRIPT_FILENAME']))  $_SERVER['SCRIPT_FILENAME'] = str_replace("\\", "/", __FILE__);
$FullPath = ($_SERVER['PATH_TRANSLATED'] ? dirname($_SERVER['PATH_TRANSLATED']) : dirname($_SERVER['SCRIPT_FILENAME']));
// translate IIS C:\\Apache2\\htdocs\ to C:/Apache2/htdocs/
$FullPath = str_replace("\\", "/", $FullPath);
$FullPath = str_replace("//", "/", $FullPath); 
$RelDir = dirname($_SERVER['PHP_SELF']); // $_SERVER['SCRIPT_NAME'];
// echo " Script filename: ".$_SERVER['SCRIPT_FILENAME']."<br /> Document root: ".$_SERVER['DOCUMENT_ROOT']."<br /> Script in root? ".(strstr($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT'])!=false ? "Yes" : "No")."<br />";
if (strstr($_SERVER['SCRIPT_FILENAME'], $_SERVER['DOCUMENT_ROOT'])==false) {
  $WebRoot .= $nukeurl; 
  $pos1 = strrpos($FullPath, "/");// last dir
  $pos2 = strrpos($RelDir, "/");
  do {
	$FP = substr($FullPath, strrpos($FullPath, "/")); 
	$RD = substr($RelDir, strrpos($RelDir, "/"));
	if (strcmp($FP, $RD)==0) { 
		$FullPath = substr($FullPath, 0, strrpos($FullPath, "/"));
		$RelDir = substr($RelDir, 0, strrpos($RelDir, "/"));
		$WebRoot = substr($WebRoot, 0, strrpos($WebRoot, "/")); // webroot
	} else {
		$DocRoot = realpath($FullPath);
		break;	
	}
  } while(true);
  if (strpos($nukeurl,"~")!==false)  // remove userdir /~user/nukeroot 
  	$nukeroot = preg_replace("|([^/]*)/~[^/]+(.*)|", "$1$2", $nukeurl);
}
  $DocRoot = str_replace("\\", "/", $DocRoot); // realpath($DocRoot)
  $DocRoot = str_replace("//", "/", $DocRoot); // MS IIS DocRoot returns double \ as in C:\\Apache2\\htdocs\
  if ($RelDir=='\\') $RelDir="";
}

/********** Wrapper_user_getip **********/
function Wrapper_user_getip() {
if (isset($_SERVER)) {
 if (isset($_SERVER["HTTP_X_FORWARDED_FOR"])) {
  $remoteIP = $_SERVER["HTTP_X_FORWARDED_FOR"];
 } elseif (isset($_SERVER["HTTP_CLIENT_IP"])) {
  $remoteIP = $_SERVER["HTTP_CLIENT_IP"];
 } else {
  $remoteIP = $_SERVER["REMOTE_ADDR"];
 }

} else {
 if ( getenv( 'HTTP_X_FORWARDED_FOR' ) ) {
  $remoteIP = getenv( 'HTTP_X_FORWARDED_FOR' );
 } elseif ( getenv( 'HTTP_CLIENT_IP' ) ) {
  $remoteIP = getenv( 'HTTP_CLIENT_IP' );
 } else {
  $remoteIP = getenv( 'REMOTE_ADDR' );
 }
}
// in case multiple IPs are returned
if (strstr($remoteIP, ', ')) {
   $ips = explode(', ', $remoteIP);
   $remoteIP = $ips[0];
} 
return $remoteIP;
}

/********** Wrapper_admin_checkurl **********/
function Wrapper_admin_checkurl($url, $WrapDebug=false) {
global $Request, $URLs, $URLkeys, $AllowURLs;
  $backbutton = "<FORM>\n<INPUT TYPE=\"Button\" VALUE=\""._Back."\" onClick=\"history.go(-1)\">\n</FORM>\n"; 
  if (!$AllowURLs) { 
    // session_write_close();
    // header("Location: ".$SiteRoot."index.php?URL_links_not_allowed."); exit(); 
    wrap_opentable(1); echo "<div style=\"padding: 30px 6px;\" align=\"center\">"._NWNoAuthorityForURL.$backbutton."</div>\n"; wrap_closetable(1, 0); exit;
  } 
  if (substr($url, 0, 1)=="/") $url = $GLOBALS['WebRoot'].$url; // allow url=/somedir/somefile.html for local files in frame
  $url = $URLwrap = strtok($url,"?&");
  $urlOK=false; 
  if ($WrapDebug)  echo "URL: $url<br /><br />\n<strong>Security check:</strong><br /> Component: Wrapper : : url<br /> Instance:  filename : keyword : extension<br />";
  if (is_array($URLkeys) && !empty($URLkeys)) {
      if (isset($URLkeys[$url])) { // Get URL if $url is keyword  & set $key // array_key_exists($URLwrap, $URLkeys) in PHP4.1.0
    		$URLwrap = $URLkeys[$url]; $key = $url; 
      } else {  $key = array_search($url, $URLkeys); } // check if whole URL has keyword
      $extension = substr($URLwrap, strrpos($URLwrap, ".")+1); 
      if ($key!==false) {
//      foreach($URLkeys as $key=>$value) {
  //        if (stristr($URLwrap, $key)!==false) {  
		// if ($WrapDebug)  echo "Matched <em>$key:</em> $value &nbsp;\n";
		if (!pnSecAuthAction(0, 'Wrapper::url', basename($URLwrap).":$key:$extension", ACCESS_READ)) { // basename($URLwrap)
			if ($WrapDebug)  echo "<span style=\"color: red;\">failed</span><br />";
    			wrap_opentable(1); 
    			echo "<div style=\"padding: 30px 6px;\" align=\"center\">"._NWNoAuthorityForThisURL.$backbutton."</div>\n"; 
    			wrap_closetable(1, 0); exit;
    		} else { $urlOK=true; if ($WrapDebug)  echo "<span style=\"color: green;\">passed</span><br />"; }
  //        } 
 //     }
 	}
  } 
  if ($urlOK==false && !pnSecAuthAction(0, 'Wrapper::url', basename($URLwrap)."::$extension", ACCESS_READ)) {
    	wrap_opentable(1); 
    	echo "<div style=\"padding: 30px 6px;\" align=\"center\">"._NWNoAuthorityForExt.$backbutton."</div>\n"; 
    	wrap_closetable(1, 0); exit;
  } // end file check

  if (is_array($URLs['allow']) && !empty($URLs['allow'])) {
//    if (array_search($URLwrap, $URLs['allow'])===false || array_search($URLwrap, $URLs['allow'])==NULL) {
    $urlOK = false;
    if ($WrapDebug)  echo " <strong>Allow filter:</strong><br />\n";
    foreach($URLs['allow'] as $good) {
    	if ($WrapDebug)  echo " $good".(stristr($URLwrap, $good)!==false ? " - <span style=\"color: green\">Matched $URLwrap</span>" : "")."; &nbsp;"; 
    	if (stristr($URLwrap, $good)!==false) { 
    		$urlOK = true; break; 
    	}
    }   if ($WrapDebug)  echo "<br />";
    if ($urlOK==false) {
      // session_write_close(); header("Location: ".$SiteRoot."index.php?URL_not_allowed."); exit(); 
    	wrap_opentable(1); 
    	echo "<div style=\"padding: 30px 6px;\" align=\"center\">\n"._NWNoAuthorityForThisURL.$backbutton."</div>\n"; 
    	wrap_closetable(1, 0); exit;
    }
  } // elseif
  if(is_array($URLs['deny']) && !empty($URLs['deny'])) {
    $urlOK = true;
    if ($WrapDebug)  echo " <strong>Deny filter:</strong><br />\n";
    foreach($URLs['deny'] as $bad) {
  	if ($WrapDebug)  echo " $bad".(stristr($URLwrap, $bad)!==false ? " - <span style=\"color: red\">Matched $URLwrap</span>" : "")."; &nbsp;"; 
  	if (stristr($URLwrap, $bad)!==false) { 
  		$urlOK = false; break; 
    	}
    }   if ($WrapDebug)  echo "<br />";
    if ($urlOK==false) {
    	// session_write_close();  header("Location: ".$SiteRoot."index.php?URL_with_".$url."_not_allowed."); exit(); 
      	wrap_opentable(1); 
    	echo "<div style=\"padding: 30px 6px;\" align=\"center\">\n"._NWNoAuthorityForThisURL.$backbutton."</div>\n"; 
    	wrap_closetable(1, 0); exit;	
    }
  }
  if (!stristr($URLwrap,"http://") && !stristr($URLwrap,"https://")) 
    $URLwrap="http://".$URLwrap;

  $query=$_SERVER['QUERY_STRING']; 
//  if ($WrapDebug) echo " Query: $query<br />\n";
  if (strstr($query, "?")) {
	$query=substr(strstr($query, "?"),1);
	$URLwrap.= "?".$query;
	if ($WrapDebug) echo " Sub-Query: $query<br />"; 
  }
  return $URLwrap;
}

/************** Check file permission against Allow/Deny rules **************/
function Wrapper_PermissionCheck($file, $NWFiles, $level, $WrapDebug=false) { // 'level' from listdir search dir level
  global $DocRoot;
  $padding = str_repeat("|&nbsp;&nbsp;&nbsp;", $level);
  if(is_array($NWFiles['allow']) && !empty($NWFiles['allow'])) {
      // $fileOK = false;
      foreach($NWFiles['allow'] as $good) {
          if (stristr($file, $good)!==false) { 
              $fileOK = true; 
              if ($WrapDebug)  echo $padding."<span style=\"color: green;\"><em>".substr($file, strlen($DocRoot))." - <strong>Matched Allow: $good</strong></em></span><br />\n"; 
              return true; 
          }
      }
      if ($WrapDebug) echo $padding."<span style=\"color: red;\"><em>".substr($file, strlen($DocRoot))." - <strong>Failed Allow: $good</strong></em></span><br />\n"; 
      return false;
  }
  if(is_array($NWFiles['deny']) && !empty($NWFiles['deny'])) {
      // $fileOK = true;
      foreach($NWFiles['deny'] as $bad) {
          if (stristr($file, $bad)!==false) { 
              // $fileOK = false; 
              if ($WrapDebug)  echo $padding."<span style=\"color: red;\"><em>".substr($file, strlen($DocRoot))." - <strong>Matched Deny: $bad</strong></em></span><br />\n"; 
              return false; 
          }
      }
  }
  return true;
}

/*
*   replacePngTags - Justin Koivisto [W.A. Fisher Interactive] 7/1/2003 10:45AM
*   Modified: 8/4/2004 4:57PM
*
*   Modifies IMG and INPUT tags for MSIE5+ browsers to ensure that PNG-24
*   transparencies are displayed correctly.  Replaces original SRC attribute
*   with a transparent GIF file (spacer.png) that is located in the same
*   directory as the orignal image, and adds the STYLE attribute needed to for
*   the browser. (Matching is case-insensitive. However, the width attribute
*   should come before height.
*
*   Also replaces code for PNG images specified as backgrounds via:
*   background-image: url('image.png'); When using PNG images in the background,
*   there is no need to use a spacer.png image. (Only supports inline CSS)
*
*   @param  $file           String containing the content to search and replace in.
*   @param  $SpacerPath     The path to the directory with the spacer image relative to
*                           the DOCUMENT_ROOT. If none is supplied, the spacer.png image
*                           should be in the same directory as PNG-24 image. When supplying
*                           a path, be sure it ends with a '/'.
*   @param  $sizingMethod   String containing the sizingMethod to be used in the
*                           Microsoft.AlphaImageLoader call. Possible values are:
*                   scale - Default. Stretches or shrinks the image to fill the borders of the object.
*                    crop - Clips the image to fit the dimensions of the object.
*                   image - Enlarges or reduces the border of the object to fit
*                           the dimensions of the image.
*                               
*    @param  $PNGsuffix     Only PNGs with this suffix will be processed. Default '-trans'
*
*   @result Returns the modified string.
*/
function AddFilter($file, $SpacerPath='images/spacer.gif', $PNGsuffix="-trans", $sizingMethod='scale'){ 
global $DocRoot;
    $msie='/msie\s(5\.5|[6-9]\.[0-9]*).*(win)/i';
    // echo $_SERVER['HTTP_USER_AGENT']."<br />\n";
    if( !isset($_SERVER['HTTP_USER_AGENT']) ||
        !preg_match($msie,$_SERVER['HTTP_USER_AGENT']) ||
        preg_match('/opera/i',$_SERVER['HTTP_USER_AGENT']))
        	return $file; 
    // Replace background images
    $file = preg_replace('[<(style\s+type\s*=\s*["\']text/css["\']>)?([^>]+)(background(?:-image)?)\s*:([^(};]*)url\s*\((?:["\'])?([^\"\'\s]+'.$PNGsuffix.'\.png)(?:["\'])?\)(^\d;)[;\"\']]Uis',
    		'<$1$2$4 filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src=\'$5\', enabled=\'true\', sizingMethod=\'crop\');', $file);

    // Extract URLs from IMG and INPUT tags with "$PNGsuffix.png" in them, eg "-trans.png"
    $pattern = '/<(img|input)([^\(>]+)src\s*=(?:\s*["\']?)([^\'"\s>]*'.$PNGsuffix.'\.png)(["\']?)([^>]*)>/Uis'; // $3=url $5 has width/height
    preg_match_all($pattern, $file, $images); // $images[3] has path
    // Go over each tag and extract dimensions if present, else get from image.
    foreach($images[0] as $key=>$img) {
    	$width = 0; $height = 0; $modified = $img;
  //  	echo htmlspecialchars($img)."<br />\n Image path: ".$DocRoot.$images[3][$key]."<br /><br />\n";
    	$imgsize = getimagesize($DocRoot.$images[3][$key]);
    	if (preg_match('/width\s*[:=][\s\'"]*(\d+)(%|\w*)[\'"]?/i', $img, $w)) 
    		$width = $w[1].(empty($w[2]) ? "px" : $w[2]);
    	else 	$width = $imgsize[0].'px';
    	if (preg_match('/height\s*[:=][\s\'"]*(\d+)(%|\w*)[\'"]?/i', $img, $h)) 
    		$height = $h[1].(empty($h[2]) ? "px" : $h[2]);
    	else 	$height = $imgsize[1].'px';  
    	$filter = "width: $width; height: $height; filter:progid:DXImageTransform.Microsoft.AlphaImageLoader(src='".$images[3][$key]."', sizingMethod='$sizingMethod', enabled='true');";
	$modified = str_replace($images[3][$key], $SpacerPath, $img); // Replace png image in src with Blank
	if (preg_match('/style\s*=\s*["\']/i', $img)) {
		$modified = preg_replace('/style\s*=\s*["\'](.+)["\']/Ui', 'style="'.$filter.' $1"', $modified);
	} else {
		$modified = str_replace('>', ' style="'.$filter.'">', $modified);
	}
   	// Replace the original tag with the new
   	$file = str_replace($img, $modified, $file);
    } 
    return $file;
}

include("Debug.php");
