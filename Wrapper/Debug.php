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
 */

/******************* Debugging *******************/
function debugtable() { 
if (!pnSecAuthAction(0, 'Wrapper::', '::', ACCESS_ADMIN)) {
	return;
} 

global $SiteRoot, $DocRoot, $nukeroot, $PostnukeDir, $PHPdir, $PHPdirs, $HTMLdir, $HTMLdirs, $HTMLroot, $FullPath, $WebDir, $AllowPHP, $AllowURLs, $extension, $URLwrap, $URLs, $filewrap, $filewrapname;
?>
<style type="text/css">
<!-- 
 DIV#DebugWrapper  { padding-left: 1.5em; padding-right: 1.5em; }
 DIV#DebugWrapper H2 {font-size: 18px; font-weight: bold; text-align: center; }
 DIV#DebugWrapper TABLE#Debug TD, 
 DIV#DebugWrapper TABLE#Debug td div, 
 DIV#DebugWrapper table#Debug td p { font-size: 12px; color: #000000; background-color: #FFFFEE; }
 DIV#DebugWrapper TABLE#Debug TD { width: 50%; border: 1px solid #CCCCFF;}
 DIV#DebugWrapper TABLE#Debug TH { border: 1px solid #CCCCFF; background: #DDDDFF;}
-->
</style>
<div id="DebugWrapper"> 
<?PHP OpenTable(); ?>
<H2>Debug Table</H2>
<table ID="Debug" style="border: 3px solid #CCCCFF;" cellpadding="5" align="center" cellspacing="0" width="95%" bgcolor="#FFFFEE" bordercolor="#CCCCFF">
  <tr><!----------------- POSTNUKE SECTION ----------------->
    <th style="color:black;" height="30" colspan="2" bgcolor="#DDDDFF">Postnuke</th>
  </tr>
  <tr>
    <td valign="top" align="right">Fullpath:<br />DocRoot:
    <?PHP if (!empty($GLOBALS['DocumentRoot'])) echo "<br />\$DocumentRoot:"; ?></td>
    <td width="50%"><?PHP echo "$FullPath<br />$DocRoot<br />"
    .(!empty($GLOBALS['DocumentRoot']) ? $GLOBALS['DocumentRoot'].'<br />' : '')
    .(strpos($FullPath, $DocRoot)===false 
    ? " <span style=\"color:red\">Filepath <strong>OUTSIDE</strong> document root!</span>" 
    : " <span style=\"color:green\"><strong>File in document root.</strong></span>") ?>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">$_SERVER['SCRIPT_FILENAME']:</td>
    <td width="50%"><?PHP echo $_SERVER['SCRIPT_FILENAME'] ?>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">__FILE__ (translated):</td>
    <td width="50%"><?PHP echo str_replace("\\", "/", __FILE__); ?>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">$PostnukeDir:</td>
    <td width="50%"><?PHP echo $GLOBALS['PostnukeDir'] ?> </td>
  </tr>
  <tr>
    <td valign="top" align="right">PostNuke $SiteRoot:<br /></td>
    <td><?PHP echo $GLOBALS['SiteRoot'] ?> </td>
  </tr>
  <tr>
    <td valign="top" align="right">$WebRoot:<br /></td>
    <td><?PHP echo $GLOBALS['WebRoot'] ?> </td>
  </tr>
  <tr>
    <td valign="top" align="right">$RelDir:<br /></td>
    <td><?PHP echo $GLOBALS['RelDir'] ?> </td>
  </tr>
  <tr>
    <td valign="top" align="right">$WebDir:<br /></td>
    <td><?PHP echo $GLOBALS['WebDir'] ?> </td>
  </tr>
  <tr>
    <td valign="top" align="right">Relative path to site $nukeroot:</td>
    <td width="50%"><?PHP echo $GLOBALS['nukeroot'] ?> &nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">pnGetBaseURI() ($nukeurl)</td>
    <td width="50%"><?PHP echo pnGetBaseURI() ?>&nbsp; </td>
  </tr>
  <tr>
    <td valign="top" align="right">pnGetBaseURL()</td>
    <td width="50%"><?PHP echo pnGetBaseURL() ?>&nbsp; </td>
  </tr>
  <tr>
    <td valign="top" align="right">Path to scriptfile:<br />(URL minus scriptfile)</td>
    <td><?PHP echo "http".($_SERVER['HTTPS']=="on"?"s":"")."://".$_SERVER['SERVER_NAME'].(dirname($_SERVER['PHP_SELF'])=="\\" ? "" : dirname($_SERVER['PHP_SELF']))."/"
    ."<br />http".($_SERVER['HTTPS']=="on"?"s":"")."://".$_SERVER['SERVER_NAME'].substr($_SERVER['PHP_SELF'], 0, strrpos($_SERVER['PHP_SELF'],"/"))."/"; // strrev(strstr(strrev($_SERVER['PHP_SELF']),"/"))?></td>
  </tr>
<?PHP if ($isPHP=strstr($GLOBALS['extension'],'php')) { ?>
  <tr>
    <td valign="top" align="right">PHP dir:<br />PHP root:</td>
    <td><?PHP echo $DocRoot.$PHPdir.(is_dir($DocRoot.$PHPdir) ? " &nbsp;&nbsp;is a valid directory<br />\n" : " &nbsp;&nbsp;is NOT a valid directory<br />\n");
        if ($isPHP and is_dir($DocRoot.$PHPdir)) { echo $GLOBALS['PHProot']."<br />"; } else { echo "PHP root not set<br />"; } 
	if ($AllowPHP) echo "PHP allowed"; else echo "PHP NOT allowed";  ?></td>
  </tr>
  <tr>
    <td valign="top" align="right">PHPdirs array:</td>
    <td><?PHP 
if (is_array($PHPdirs) && !empty($PHPdirs)){
    foreach($PHPdirs as $dir) {
	if (substr($dir, -1)=="/")  $dir=substr($dir, 0, strlen($dir)-1); 
	$dir=(substr($dir, 0, 1)=="/" ? "" : "/").$dir;
 	echo "<div style='margin-bottom: 0.5em;'>$dir<br />".$DocRoot.$dir." &nbsp;".(is_dir($DocRoot.$dir) ? "<span style='color: green;'>Valid directory</span>" : "<span style='color: red;'>Not valid directory</span>")."<br />"
	.$DocRoot.$dir.$filewrapname." &nbsp;".(is_file($DocRoot.$dir.$filewrapname) ? "<span style='color: green;'>File</span>" : "<span style='color: red;'>Not a file</span>")."<br />"
	.$DocRoot.$filewrapname." &nbsp;".(is_file($DocRoot.$filewrapname) && strstr($DocRoot.$filewrapname, $dir) ? "<span style='color: green;'>Valid File & in dir</span>" : "<span style='color: red;'>Not a file in valid dir</span>")."<br /></div>\n";
    }
  }  ?>
    </td>
  </tr>
<?PHP }
if ($isHTML=strstr($GLOBALS['extension'],'htm')) { ?>
  <tr>
    <td valign="top" align="right">HTML dir:<br />HTML root:</td>
    <td><?PHP echo $DocRoot.$HTMLdir.(is_dir($DocRoot.$HTMLdir) ? " &nbsp;&nbsp;is a valid directory<br />\n" : " &nbsp;&nbsp;is NOT a valid directory<br />\n");
        if ($isHTML and is_dir($DocRoot.$HTMLdir)) { echo $HTMLroot; } else { echo "HTML root not set"; } ?></td>
  </tr>
  <tr>
    <td valign="top" align="right">HTMLdirs array:</td>
    <td><?PHP 
  if (is_array($HTMLdirs) && !empty($HTMLdirs)){
    foreach($HTMLdirs as $dir) {
	if (substr($dir, -1)=="/")  $dir=substr($dir, 0, strlen($dir)-1); 
	$dir=(substr($dir, 0, 1)=="/" ? "" : "/").$dir; // .(substr($dir, -1)=="/" ? "" : "/");
	echo "<div style='margin-bottom: 0.5em;'>$dir<br />".$DocRoot.$dir." &nbsp;".(is_dir($DocRoot.$dir) ? "<span style='color: green;'>Valid directory</span>" : "<span style='color: red;'>Not valid directory</span>")."<br />"
	.$DocRoot.$dir.$filewrapname." &nbsp;".(is_file($DocRoot.$dir.$filewrapname) ? "<span style='color: green;'>File</span>" : "<span style='color: red;'>Not a file</span>")."<br />"
	.$DocRoot.$filewrapname." &nbsp;".(is_file($DocRoot.$filewrapname) && strstr($DocRoot.$filewrapname, $dir) ? "<span style='color: green;'>Valid File & in dir</span>" : "<span style='color: red;'>Not a file in valid dir</span>")."<br /></div>\n";
    }
  } ?>
    </td>
  </tr>
<?PHP } 
 if (!empty($URLwrap)) { ?>
  <tr>
    <td valign="top" align="right">Allow URLs?</td>
    <td width="50%"><?PHP echo ($AllowURLs ? "URLs allowed" : "URLs <strong>not</strong> allowed") ?></td>
  </tr>
  <tr>
    <td valign="top" align="right">Allowed URLs:</td>
    <td width="50%"><?PHP 
if (!is_array($URLs['allow']) or empty($URLs['allow'])) {
	echo "All"; 
} else {
   $urlOK = false;
    foreach($URLs['allow'] as $url) {
	echo "$url".(stristr($URLwrap, $url)!==false ? " - Matched $URLwrap" : "")."<br />\n"; 
	if (stristr($URLwrap, $url)!==false)  $urlOK = true;  
    }
    echo $urlOK ? "URL allowed" : "URL <strong>not</strong> allowed"; 
} ?>
    </td>
  </tr>
  <tr>
    <td valign="top" align="right">Disallowed URLs:</td>
    <td width="50%"><?PHP 
if (!is_array($URLs['deny']) or empty($URLs['deny'])) {
	echo "None";
} else {
   $urlOK = true;
     foreach($URLs['deny'] as $url) {
	echo "$url".(stristr($URLwrap, $url)!==false ? " - Matched $URLwrap" : "")."<br />\n"; 
	if (stristr($URLwrap, $url)!==false)  $urlOK = false;  
    }
    echo $urlOK ? "URL allowed" : "URL <strong>not</strong> allowed"; 
} ?>
    </td>
  </tr>
<?PHP } ?>
<!--  <tr>
    <td valign="top" align="right">URLencoded Filepath:</td>
    <td><?PHP echo htmlentities(urlencode($GLOBALS['filewrap'])) ?></td>
  </tr> -->
  <tr>
    <td valign="top" align="right"><?PHP echo (empty($GLOBALS['URLwrap']) ? "File (filewrap):" : "URL (URLwrap):") ?></td>
    <td><?PHP echo (empty($GLOBALS['URLwrap']) ? $GLOBALS['filewrap'] : $GLOBALS['URLwrap']) ?>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">Query string:</td>
    <td><?PHP echo $_SERVER['QUERY_STRING']; ?>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">First part of Query:</td>
    <td><?PHP echo strtok($_SERVER['QUERY_STRING'], '?&'); ?>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">Sub-Query:</td>
    <td><?PHP echo $GLOBALS['query']; ?>&nbsp;</td>
  </tr>
<?PHP if (empty($URLwrap)) { ?>  <tr>
    <td valign="top" align="right">realpath(<?PHP echo $filewrap ?>)</td>
    <td><?PHP echo $GLOBALS['FullPath']; // realpath($DocRoot.$filewrap) ?>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">Is file in Postnuke site?</td>
    <td><?PHP if (strpos($FullPath, $PostnukeDir)===false) { echo "No\n"; }
		else { echo "<span style=\"color:green\"><strong>File onsite</strong></span>\n";}
    echo "<p>Postnuke Site: &nbsp;".$PostnukeDir."<br />Filepath:".str_repeat("&nbsp;",11).$FullPath."\n" ?></td>
  </tr>
  <tr>
    <td valign="top" align="right">pathinfo(<?PHP echo $filewrap ?>):</div></td>
    <td><pre><?PHP DebugPrint(pathinfo($filewrap)) ?></pre></td>
  </tr><?PHP } ?>
  <tr>
    <td valign="top" align="right">parse_url($REQUEST_URI):</td>
    <td><pre><?PHP DebugPrint(parse_url($_SERVER['REQUEST_URI'])) ?></pre></td>
  </tr>
  <tr>
    <td valign="top" align="right">basename(request_uri):</td>
    <td><?PHP echo basename($_SERVER['REQUEST_URI']) ?></td>
  </tr>
<!--  <tr>
    <td valign="top" align="right">$_ENV:</td>
    <td><pre><?PHP DebugPrint($_ENV) ?></pre></td>
  </tr> -->
  <tr>
    <td valign="top" align="right">$PHP_SELF:</td>
    <td><?PHP echo $_SERVER['PHP_SELF'] ?>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">basename($PHP_SELF):</td>
    <td><?PHP echo basename($_SERVER['PHP_SELF']) ?></td>
  </tr> 
  <tr>
    <td valign="top" align="right">$REQUEST_METHOD:</td>
    <td><?PHP echo $_SERVER['REQUEST_METHOD'] ?>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">$_GET:</td>
    <td><pre><?PHP DebugPrint($_GET) ?></pre></td>
  </tr>
  <tr>
    <td valign="top" align="right">$_POST:</td>
    <td><pre><?PHP DebugPrint($_POST) ?></pre></td>
  </tr>
  <tr>
    <td valign="top" align="right">$_COOKIE:</td>
    <td><pre><?PHP DebugPrint($_COOKIE) ?></pre></td>
  </tr>
<!-- 
  <tr>  
    <td valign="top" align="right">$GLOBALS</td>
    <td><pre><?PHP /* DebugPrint($GLOBALS) */ ?></pre>&nbsp;</td>
  </tr>
--> 
  <tr>
    <td valign="top" align="right">$php_errormsg:</td>
    <td><pre><?PHP DebugPrint($php_errormsg) ?></pre>&nbsp;</td>
  </tr>

  <tr><!----------------- APACHE SECTION ----------------->
  <th style="color:black; background: #DDDDFF;" colspan="2" height="30" bgcolor="#DDDDFF">Server</th>
  <tr>
    <td valign="top" align="right">PHP version:</td>
    <td><?PHP echo phpversion(); ?></td>
  </tr>
  <tr>
    <td valign="top" align="right">$HTTP_HOST:</td>
    <td><?PHP echo $_SERVER['HTTP_HOST'] ?></td>
  </tr>
  <tr>
    <td align="right">Server Address:</td>
    <td><?PHP echo $_SERVER['SERVER_ADDR'] ?></td>
  </tr>
  <tr>
    <td valign="top" align="right">$REMOTE_HOST:</td>
    <td><?PHP echo $_SERVER['REMOTE_HOST'] ?>&nbsp;</td>
  </tr>
  <tr>
    <td valign="top" align="right">Remote Address:</td>
    <td><?PHP echo Wrapper_user_getip(); ?></td>
  </tr>
  <tr>
    <td valign="top" align="right">$PATH_TRANSLATED:</td>
    <td><?PHP echo $_SERVER['PATH_TRANSLATED'] ?></td>
  </tr>
  <tr>
    <td valign="top" align="right">$REQUEST_URI:</td>
    <td><?PHP echo $_SERVER['REQUEST_URI'] ?></td>
    <!-- If server can't use $REQUEST_URI (like IIS), use $location = $PHP_SELF."?".$HTTP_SERVER_VARS['QUERY_STRING']; -->
  </tr>
  <tr>
    <td valign="top" align="right">$_REQUEST:</td>
    <td><pre><?PHP DebugPrint($_REQUEST) ?></pre> </td>
  </tr>
  <tr>
    <td valign="top" align="right">$_FILES:</td>
    <td><pre><?PHP DebugPrint($_FILES) ?></pre> </td>
  </tr>
  <tr>
    <td valign="top" align="right">$_SESSION:</td>
    <td><pre><?PHP DebugPrint($_SESSION) ?></pre> </td>
  </tr>
  <tr><!-- Can choke on Apache 2; remove if problem -->
    <td valign="top" align="right">$_SERVER['HTTP_REFERER']<br />HTTP Referer host:</td>
    <td><?PHP echo $_SERVER['HTTP_REFERER']."<br />".dirname($_SERVER['HTTP_REFERER']) ?></td>
  </tr>
  <tr>
    <td valign="top" align="right">$_SERVER:</td>
    <td><pre><?PHP DebugPrint($_SERVER) ?></pre></td>
  </tr><?PHP if (function_exists('getallheaders')) { ?>
  <tr>
    <td valign="top" align="right">Headers (Apache):</td>
    <td><pre><?PHP DebugPrint(getallheaders()) ?></pre></td>
  </tr>
<?PHP 
} 
 if (function_exists('apache_lookup_uri')) { ?>  <tr>
    <td valign="top" align="right">apache_lookup_uri(<?PHP echo $filewrap ?>):</td>
    <td><pre><?PHP DebugPrint(apache_lookup_uri($filewrap)) ?></pre></td>
  </tr>
<?PHP } ?>
</table>
<?PHP 
CloseTable(); ?>
</div>
<? 
}

/********* Format Debug output *************/
function DebugPrint($array) {
    foreach ($array as $key=>$value) {
        echo "[$key] => ".wordwrap($value, 70, "\n           ", 1)."\n"; 
    }
}
?>
