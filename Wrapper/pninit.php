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

/****************************************************************************
 * initialise the Wrapper module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function Wrapper_init() {
return true; // temporarily disabled
    // Get old configuration in configuration file
    if (is_file("Wrapper.conf.php")) include("Wrapper.conf.php"); 
    // If array list from standalone wrap.php file, convert to regular expression syntax. 
    if (is_array($ValidExp1)) {
	$ValidExp1 = str_replace(".", "\.", implode("|", $ValidExp1)); 
    }
    if (is_array($ValidExp2)) {
	$ValidExp2 = str_replace(".", "\.", implode("|", $ValidExp2)); 
    }
    if (is_array($wrapUrl2))
        $wrapUrl = array_merge($wrapUrl, $wrapUrl2);

    // Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
    // return arrays but we handle them differently.  For pnDBGetConn()
    // we currently just want the first item, which is the official
    // database handle.  For pnDBGetTables() we want to keep the entire
    // tables array together for easy reference later on
    $dbconn =& pnDBGetConn(true);
    $pntable =& pnDBGetTables();

////////// General global settings //////////
/* Can make config data module vars */
    $Wrappertable  = &$pntable['Wrapper_settings'];
    $Wrappercolumn = &$pntable['Wrapper_settings_column'];

    $sql = "CREATE TABLE $Wrappertable (
            $Wrappercolumn[AllowPHP] tinyint(1) NOT NULL default '".(isset($AllowPHP)?$AllowPHP:"1")."', 
            $Wrappercolumn[AllowExtLink] tinyint(1) NOT NULL default '".(isset($AllowExtLink)?$AllowExtLink:"1")."', 
            $Wrappercolumn[AllowURLs] tinyint(1) NOT NULL default '".(isset($AllowURLs)?$AllowURLs:"1")."', 
            $Wrappercolumn[FixLinks] tinyint(1) NOT NULL default '".(isset($FixLinks)?$FixLinks:"1")."', 
            $Wrappercolumn[WrapLinks] tinyint(1) NOT NULL default '".(isset($WrapLinks)?$WrapLinks:"1")."',  
            $Wrappercolumn[UseTables] tinyint(1) NOT NULL default '".(isset($UseTables)?$UseTables:"1")."', 
            $Wrappercolumn[Layout] tinyint(1) NOT NULL default '".(isset($Layout)?$Layout:"0")."',   
            $Wrappercolumn[ShowLink] tinyint(1) NOT NULL default '".(isset($ShowLink)?(int)$ShowLink:"1")."',  
            $Wrappercolumn[AutoResize] tinyint(1) NOT NULL default '".(isset($AutoResize)?$AutoResize:"1")."',  
            $Wrappercolumn[StartPage] varchar(100) NOT NULL default '".(isset($StartPage)?pnVarPrepForStore($StartPage):"")."',  
            $Wrappercolumn[ValidExp1] varchar(100) NOT NULL default '".(isset($ValidExp1)?pnVarPrepForStore($ValidExp1):pnVarPrepForStore("\.htm|\.shtml|\.txt"))."',
            $Wrappercolumn[ValidExp2] varchar(100) NOT NULL default '".(isset($ValidExp2)?pnVarPrepForStore($ValidExp2):pnVarPrepForStore("\.php|\.phtml|\.cgi|\.asp|\.iasp|\.jsp|\.cfm|\.pl|\.adp"))."',
            $Wrappercolumn[Debug] tinyint(1) NOT NULL default '".(isset($WrapDebug)?$WrapDebug:"0")."')";    
            
    $dbconn->Execute($sql);
/* Need to have INSERT too */
    // Check for an error with the database code, and if so set an
    // appropriate error message and return
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg());
        return false;
    }
////////// HTMLdirs allowed static page directories //////////
    $Wrappertable = $pntable['Wrapper_htmldirs'];
    $Wrappercolumn = &$pntable['Wrapper_htmldirs_column'];

    $sql = "CREATE TABLE $Wrappertable (
            $Wrappercolumn[id] int unsigned NOT NULL auto_increment,    
            $Wrappercolumn[directory] varchar(255) NOT NULL default '',   
            PRIMARY KEY(id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." HTMLdirs. Query: $sql");
        return false;
    }
    if (is_array($HTMLdirs) && !empty($HTMLdirs)) { // Insert $HTMLdirs array into database
    	$sql="";
    	foreach($HTMLdirs as $dir) {
    		$sql .= "INSERT INTO $Wrappertable 
    		        VALUES ( NULL, '".pnVarPrepForStore($dir)."'); ";
        }
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." HTMLdirs. Query: $sql");
    	    return false;
    	}
    }
////////// PHPdirs allowed dynamic script directories //////////
    $Wrappertable = $pntable['Wrapper_phpdirs'];
    $Wrappercolumn = &$pntable['Wrapper_phpdirs_column'];

    $sql = "CREATE TABLE $Wrappertable (
            $Wrappercolumn[id] int unsigned NOT NULL auto_increment,    
            $Wrappercolumn[directory] varchar(255) NOT NULL default '',   
            PRIMARY KEY(id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." PHPdirs. Query: $sql");
        return false;
    }
    if (is_array($PHPdirs) && !empty($PHPdirs)) { // Insert $PHPdirs array into database
    	$sql="";
    	foreach($PHPdirs as $dir) {
    		$sql .= "INSERT INTO $Wrappertable 
    		        VALUES ( NULL, '".pnVarPrepForStore($dir)."'); ";
        }
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." PHPdirs. Query: $sql");
    	    return false;
    	}
    } 
    else { 
    	$sql = "INSERT INTO $Wrappertable 
    		        VALUES ( NULL, '/PHPpages')";
  	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." PHPdirs Query: $sql");
    	    return false;
    	}
    }
////////// Allow list //////////
    $Wrappertable = $pntable['Wrapper_allow']; // URL allow rules
    $Wrappercolumn = &$pntable['Wrapper_allow_column'];

    $sql = "CREATE TABLE $Wrappertable (
            $Wrappercolumn[id] int unsigned NOT NULL auto_increment,    
            $Wrappercolumn[allow] varchar(40) NOT NULL default '',   
            PRIMARY KEY(id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." Allow rules. Query: $sql");
        return false;
    }
    if (is_array($URLs['allow']) && !empty($URLs['allow'])) { // Insert $URLs['allow'] array into database
    	$sql="";
    	foreach($URLs['allow'] as $allow) {
    		$sql .= "INSERT INTO $Wrappertable 
    		        VALUES ( NULL, '".pnVarPrepForStore($allow)."'); ";
        }
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." Allow rules. Query: $sql");
    	    return false;
    	}
    } 
////////// Deny list //////////
    $Wrappertable = $pntable['Wrapper_deny']; // URL deny rules
    $Wrappercolumn = &$pntable['Wrapper_deny_column'];

    $sql = "CREATE TABLE $Wrappertable (
            $Wrappercolumn[id] int unsigned NOT NULL auto_increment,    
            $Wrappercolumn[deny] varchar(40) NOT NULL default '',   
            PRIMARY KEY(id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." Deny rules. Query: $sql");
        return false;
    }
    if (is_array($URLs['deny']) && !empty($URLs['deny'])) { // Insert $URLs['deny'] array into database
    	$sql="";
    	foreach($URLs['allow'] as $deny) {
    		$sql .= "INSERT INTO $Wrappertable 
    		        VALUES ( NULL, '".pnVarPrepForStore($deny)."'); ";
        }
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." Deny rules. Query: $sql");
    	    return false;
    	}
    } 
////////// URL keywords/shortcuts: array('key' => 'url'); //////////
    $Wrappertable = $pntable['Wrapper_wrapurl']; 
    $Wrappercolumn = &$pntable['Wrapper_wrapurl_column'];

    $sql = "CREATE TABLE $Wrappertable (   
            $Wrappercolumn[key] varchar(30) NOT NULL default '' unique,    
            $Wrappercolumn[url] varchar(100) NOT NULL default '', 
            PRIMARY KEY(key))";
       //     $Wrappercolumn[id] int unsigned NOT NULL auto_increment, 
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." WrapURL. Query: $sql");
        return false;
    }
    if (is_array($wrapUrl) && !empty($wrapUrl)) { // Insert $$wrapUrl array into database
    	$sql="";
    	foreach($wrapUrl as $key => $url) {
    		$sql .= "INSERT INTO $Wrappertable 
    		        VALUES (  '".pnVarPrepForStore($key)."', '".pnVarPrepForStore($url)."'); ";
        } // NULL,
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." WrapURL. Query: $sql");
    	    return false;
    	}
    } 
////////// WrapIn/WrapOut Replace rules //////////
    $Wrappertable = $pntable['Wrapper_replace']; // 
    $Wrappercolumn = &$pntable['Wrapper_replace_column'];

    $sql = "CREATE TABLE $Wrappertable (   
            $Wrappercolumn[key] varchar(30) NOT NULL default '' unique,    
            $Wrappercolumn[in] varchar(255) NOT NULL default '',    
            $Wrappercolumn[out] varchar(255) NOT NULL default '', 
            PRIMARY KEY(key))"; // key
        //    $Wrappercolumn[id] int unsigned NOT NULL auto_increment, 
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." Replace column. Query: $sql");
        return false;
    }
    if (is_array($wrapIn) && !empty($wrapIn) && is_array($wrapOut) && !empty($wrapOut)) { 
    	$sql="";
    	foreach($wrapIn as $key => $value) {
    		foreach($value as $value2) {
    			$sql .= "INSERT INTO $Wrappertable (key, in, out)
    		       		VALUES (
     					'".pnVarPrepForStore($key)."',
     					'".pnVarPrepForStore($value2)."',
     					'".pnVarPrepForStore($wrapOut[$key][$value2])."'); 
      				";// NULL, // id, 
    		}
        }
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _CREATEFAILED.$dbconn->ErrorMsg()." Replace rules. Query: $sql");
    	    return false;
    	}
    } 
    else { 
    	$sql = "INSERT INTO $Wrappertable (key, in, out)
    	        VALUES (NULL, 'all', '', '')"; // id, 
  	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." Replace rules. Query: $sql");
    	    return false;
    	}
    }
////////// WrapIn2/WrapOut2 Regular Expression replace rules //////////
    $Wrappertable = $pntable['Wrapper_RegEx_replace']; // 
    $Wrappercolumn = &$pntable['Wrapper_RegEx_replace_column'];

    $sql = "CREATE TABLE $Wrappertable ( 
            $Wrappercolumn[key] varchar(20) NOT NULL default '' unique,    
            $Wrappercolumn[in] varchar(255) NOT NULL default '',    
            $Wrappercolumn[out] varchar(255) NOT NULL default '', 
            PRIMARY KEY(key))"; 
        //    $Wrappercolumn[id] int unsigned NOT NULL auto_increment,   // key id
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." RegEx replace rules. Query: $sql");
        return false;
    }
    if (is_array($wrapIn2) && !empty($wrapIn2) && is_array($wrapOut2) && !empty($wrapOut2)) { 
    	$sql="";
    	foreach($wrapIn2 as $key => $value) {
    		foreach($value as $value2) {
    			$sql .= "INSERT INTO $Wrappertable (key, in, out)
    		       		VALUES (  
     					'".pnVarPrepForStore($key)."',
     					'".pnVarPrepForStore($value2)."',
     					'".pnVarPrepForStore($wrapOut2[$key][$value2])."'); 
      				"; // id, // NULL,
    		}
        }
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." RegEx replace rules. Query: $sql");
    	    return false;
    	}
    } 
    else { 
    	$sql = "INSERT INTO $Wrappertable (key, in, out)
    	        VALUES ('all', '', '')"; // id, NULL, 
  	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." RegEx replace rules. Query: $sql");
    	    return false;
    	}
    }

    // Set up an initial value for a module variable.  Note that all module
    // variables should be initialised with some value in this way rather
    // than just left blank, this helps the user-side code and means that
    // there doesn't need to be a check to see if the variable is set in
    // the rest of the code as it always will be
    pnModSetVar('Wrapper', 'StartPage', '');

    // Initialisation successful
    return true;
}

/****************************************************
 * upgrade the Wrapper module from an old version
 * This function can be called multiple times
 */
function Wrapper_upgrade($oldversion) {
return true; // temporarily disabled
echo "Old version: $oldversion  ";
    // Upgrade dependent on old version number
//    switch($oldversion) {
//        case 0.5:
            // Version 0.5 didn't have a 'number' field, it was added
            // in version 1.0

            // Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
            // return arrays but we handle them differently.  For pnDBGetConn()
            // we currently just want the first item, which is the official
            // database handle.  For pnDBGetTables() we want to keep the entire
            // tables array together for easy reference later on
            // This code could be moved outside of the switch statement if
            // multiple upgrades need it
//            list($dbconn) = pnDBGetConn();
//            $pntable = pnDBGetTables();

            // It's good practice to name the table and column definitions you
            // are getting - $table and $column don't cut it in more complex
            // modules
            // This code could be moved outside of the switch statement if
            // multiple upgrades need it
 //           $Wrappertable = $pntable['Wrapper'];
 //           $Wrappercolumn = &$pntable['Wrapper_column'];

            // Add a column to the table - the formatting here is not
            // mandatory, but it does make the SQL statement relatively easy
            // to read.  Also, separating out the SQL statement from the
            // Execute() command allows for simpler debug operation if it is
            // ever needed
 //           $sql = "ALTER TABLE $Wrappertable
 //                   ADD $Wrappercolumn[number] int(5) NOT NULL default 0";
 //           $dbconn->Execute($sql);

            // Check for an error with the database code, and if so set an
            // appropriate error message and return
 //           if ($dbconn->ErrorNo() != 0) {
 //               pnSessionSetVar('errormsg', _UPDATETABLEFAILED);
 //               return false;
 //           }

            // At the end of the successful completion of this function we
            // recurse the upgrade to handle any other upgrades that need
            // to be done.  This allows us to upgrade from any version to
            // the current version with ease
//            return Wrapper_upgrade(1.0);
//        case 1.0:
            // Code to upgrade from version 1.0 goes here
//            break;
//        case 2.0:
            // Code to upgrade from version 2.0 goes here
//            break;
//    }

    // Update successful
    return true;
}

/****************************************************************************
 * Delete the Wrapper module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function Wrapper_delete() { 
return true; // temporarily disabled
    // Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
    // return arrays but we handle them differently.  For pnDBGetConn()
    // we currently just want the first item, which is the official
    // database handle.  For pnDBGetTables() we want to keep the entire
    // tables array together for easy reference later on
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $dbconn->Execute("DROP TABLE $pntable[Wrapper_settings]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[Wrapper_htmldirs]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[Wrapper_phpdirs]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[Wrapper_allow]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[Wrapper_deny]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[Wrapper_wrapurl]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[Wrapper_replace]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[Wrapper_RegEx_replace]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }

    // Delete any module variables
    pnModDelVar('Wrapper', 'StartPage');


    // Deletion successful
    return true;
}
?>