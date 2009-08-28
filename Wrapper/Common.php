<?php

/* Wrapper_getdir
   Find a file in the given list of directories
*/

function Wrapper_getdir ($Dirs, $filewrap, $DocRoot)
{
  foreach ($Dirs as $dir) {
    
    // Remove trailing slash
    if (substr($dir, -1)=="/")  $dir=substr($dir, 0, strlen($dir)-1);
    
    // add leading slash if missing
    if ( substr($dir, 0, 1)!="/" ) $dir = '/' . $dir;
    
    if (is_dir($DocRoot.$dir) && (is_file($DocRoot.$dir.$filewrap) || (is_file($DocRoot.$filewrap) && strstr($filewrap, $dir)))) {
      return $dir;
    }
  }
  
  return false;
}



/* Wrapper_getfullpath
   Find a file in the given list of directories
   Return the full path to the file.
   Return false if not found.
*/

function Wrapper_getfullpath ($Dirs, $filewrap, $DocRoot)
{

  // Make sure $filewrap has a leading slash
  if (substr($filewrap, 0, 1) != "/") $filewrap = '/' . $filewrap;
  
  foreach ($Dirs as $dir) {
    
    // Remove trailing slash
    if (substr($dir, -1)=="/")  $dir=substr($dir, 0, strlen($dir)-1);
    
    // add leading slash if missing
    if ( substr($dir, 0, 1)!="/" ) $dir = '/' . $dir;
    
    $FullPath = $DocRoot.$dir.$filewrap;
    if ($FullPath == realpath($FullPath)) return $FullPath;

  }
  
  return false;
}

/* Wrapper_checkperm
   Check permissions
*/

function Wrapper_checkperm($filewrap, $NWkeywords)
{

  $filebase = basename($filewrap);
  $extension = pathinfo($filewrap, 'extension');
  $fileOK=false;

  if (is_array($NWkeywords) && !empty($NWkeywords)) {
      foreach($NWkeywords as $key) {
          if (stristr($filewrap, $key)!==false) { 
		if (!pnSecAuthAction(0, 'Wrapper::file', $filebase.":$key:$extension", ACCESS_READ)) { 
			if ($WrapDebug)  echo "<span style=\"color: red;\">failed</span><br />";
                        return false; //Wrapper_errorpage('403', 'Forbidden', _NWNoAuthorityForFile);
    		} 
    		$fileOK=true;
          } 
      }
  } 

  // If we haven't passed yet, check extension permissions.
  if ($fileOK==false) { 
    $fileOK = pnSecAuthAction(0, 'Wrapper::file', basename($filewrap)."::$extension", ACCESS_READ);
  }
  
  return $fileOK;

}


function Wrapper_errorpage( $code, $string, $message )
{

  // If not logged in, redirect to login screen on permissions error.
  if ( ($code == '403') && (pnUserGetVar('uid') <= 1)) {
    return pnRedirect(pnModUrl('users', 'user', 'loginscreen'));
  }


  header("HTTP 1.0 $code $string");
  $render = pnRender::getInstance('Wrapper');
  $render->assign('code', $code);
  $render->assign('string', $string);
  $render->assign('message', $message);
  return  $render->fetch('wrapper_file_error.html');
}


