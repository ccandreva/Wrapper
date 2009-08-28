<?php
// $Id: pninit.php,v 1.1.1.1 2002/09/15 22:26:15 root Exp $
// ----------------------------------------------------------------------
// POST-NUKE Content Management System
// Copyright (C) 2002 by the PostNuke Development Team.
// http://www.postnuke.com/
// ----------------------------------------------------------------------
// Based on:
// PHP-NUKE Web Portal System - http://phpnuke.org/
// Thatware - http://thatware.org/
// ----------------------------------------------------------------------
// LICENSE
//
// This program is free software; you can redistribute it and/or
// modify it under the terms of the GNU General Public License (GPL)
// as published by the Free Software Foundation; either version 2
// of the License, or (at your option) any later version.
//
// This program is distributed in the hope that it will be useful,
// but WIthOUT ANY WARRANTY; without even the implied warranty of
// MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
// GNU General Public License for more details.
//
// To read the license please visit http://www.gnu.org/copyleft/gpl.html
// ----------------------------------------------------------------------
// Original Author of file: Jim McDonald
// Purpose of file:  Initialisation functions for template
// ----------------------------------------------------------------------

/****************************************************************************
 * initialise the NukeWrapper module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function NukeWrapper_init() {
return true; // temporarily disabled
    // Get old configuration in configuration file
    if (is_file("NukeWrapper.conf.php")) include("NukeWrapper.conf.php"); 
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
    $NukeWrappertable  = &$pntable['NukeWrapper_settings'];
    $NukeWrappercolumn = &$pntable['NukeWrapper_settings_column'];

    $sql = "CREATE TABLE $NukeWrappertable (
            $NukeWrappercolumn[AllowPHP] tinyint(1) NOT NULL default '".(isset($AllowPHP)?$AllowPHP:"1")."', 
            $NukeWrappercolumn[AllowExtLink] tinyint(1) NOT NULL default '".(isset($AllowExtLink)?$AllowExtLink:"1")."', 
            $NukeWrappercolumn[AllowURLs] tinyint(1) NOT NULL default '".(isset($AllowURLs)?$AllowURLs:"1")."', 
            $NukeWrappercolumn[FixLinks] tinyint(1) NOT NULL default '".(isset($FixLinks)?$FixLinks:"1")."', 
            $NukeWrappercolumn[WrapLinks] tinyint(1) NOT NULL default '".(isset($WrapLinks)?$WrapLinks:"1")."',  
            $NukeWrappercolumn[UseTables] tinyint(1) NOT NULL default '".(isset($UseTables)?$UseTables:"1")."', 
            $NukeWrappercolumn[Layout] tinyint(1) NOT NULL default '".(isset($Layout)?$Layout:"0")."',   
            $NukeWrappercolumn[ShowLink] tinyint(1) NOT NULL default '".(isset($ShowLink)?(int)$ShowLink:"1")."',  
            $NukeWrappercolumn[AutoResize] tinyint(1) NOT NULL default '".(isset($AutoResize)?$AutoResize:"1")."',  
            $NukeWrappercolumn[StartPage] varchar(100) NOT NULL default '".(isset($StartPage)?pnVarPrepForStore($StartPage):"")."',  
            $NukeWrappercolumn[ValidExp1] varchar(100) NOT NULL default '".(isset($ValidExp1)?pnVarPrepForStore($ValidExp1):pnVarPrepForStore("\.htm|\.shtml|\.txt"))."',
            $NukeWrappercolumn[ValidExp2] varchar(100) NOT NULL default '".(isset($ValidExp2)?pnVarPrepForStore($ValidExp2):pnVarPrepForStore("\.php|\.phtml|\.cgi|\.asp|\.iasp|\.jsp|\.cfm|\.pl|\.adp"))."',
            $NukeWrappercolumn[Debug] tinyint(1) NOT NULL default '".(isset($WrapDebug)?$WrapDebug:"0")."')";    
            
    $dbconn->Execute($sql);
/* Need to have INSERT too */
    // Check for an error with the database code, and if so set an
    // appropriate error message and return
    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg());
        return false;
    }
////////// HTMLdirs allowed static page directories //////////
    $NukeWrappertable = $pntable['NukeWrapper_htmldirs'];
    $NukeWrappercolumn = &$pntable['NukeWrapper_htmldirs_column'];

    $sql = "CREATE TABLE $NukeWrappertable (
            $NukeWrappercolumn[id] int unsigned NOT NULL auto_increment,    
            $NukeWrappercolumn[directory] varchar(255) NOT NULL default '',   
            PRIMARY KEY(id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." HTMLdirs. Query: $sql");
        return false;
    }
    if (is_array($HTMLdirs) && !empty($HTMLdirs)) { // Insert $HTMLdirs array into database
    	$sql="";
    	foreach($HTMLdirs as $dir) {
    		$sql .= "INSERT INTO $NukeWrappertable 
    		        VALUES ( NULL, '".pnVarPrepForStore($dir)."'); ";
        }
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." HTMLdirs. Query: $sql");
    	    return false;
    	}
    }
////////// PHPdirs allowed dynamic script directories //////////
    $NukeWrappertable = $pntable['NukeWrapper_phpdirs'];
    $NukeWrappercolumn = &$pntable['NukeWrapper_phpdirs_column'];

    $sql = "CREATE TABLE $NukeWrappertable (
            $NukeWrappercolumn[id] int unsigned NOT NULL auto_increment,    
            $NukeWrappercolumn[directory] varchar(255) NOT NULL default '',   
            PRIMARY KEY(id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." PHPdirs. Query: $sql");
        return false;
    }
    if (is_array($PHPdirs) && !empty($PHPdirs)) { // Insert $PHPdirs array into database
    	$sql="";
    	foreach($PHPdirs as $dir) {
    		$sql .= "INSERT INTO $NukeWrappertable 
    		        VALUES ( NULL, '".pnVarPrepForStore($dir)."'); ";
        }
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." PHPdirs. Query: $sql");
    	    return false;
    	}
    } 
    else { 
    	$sql = "INSERT INTO $NukeWrappertable 
    		        VALUES ( NULL, '/PHPpages')";
  	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." PHPdirs Query: $sql");
    	    return false;
    	}
    }
////////// Allow list //////////
    $NukeWrappertable = $pntable['NukeWrapper_allow']; // URL allow rules
    $NukeWrappercolumn = &$pntable['NukeWrapper_allow_column'];

    $sql = "CREATE TABLE $NukeWrappertable (
            $NukeWrappercolumn[id] int unsigned NOT NULL auto_increment,    
            $NukeWrappercolumn[allow] varchar(40) NOT NULL default '',   
            PRIMARY KEY(id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." Allow rules. Query: $sql");
        return false;
    }
    if (is_array($URLs['allow']) && !empty($URLs['allow'])) { // Insert $URLs['allow'] array into database
    	$sql="";
    	foreach($URLs['allow'] as $allow) {
    		$sql .= "INSERT INTO $NukeWrappertable 
    		        VALUES ( NULL, '".pnVarPrepForStore($allow)."'); ";
        }
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." Allow rules. Query: $sql");
    	    return false;
    	}
    } 
////////// Deny list //////////
    $NukeWrappertable = $pntable['NukeWrapper_deny']; // URL deny rules
    $NukeWrappercolumn = &$pntable['NukeWrapper_deny_column'];

    $sql = "CREATE TABLE $NukeWrappertable (
            $NukeWrappercolumn[id] int unsigned NOT NULL auto_increment,    
            $NukeWrappercolumn[deny] varchar(40) NOT NULL default '',   
            PRIMARY KEY(id))";
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." Deny rules. Query: $sql");
        return false;
    }
    if (is_array($URLs['deny']) && !empty($URLs['deny'])) { // Insert $URLs['deny'] array into database
    	$sql="";
    	foreach($URLs['allow'] as $deny) {
    		$sql .= "INSERT INTO $NukeWrappertable 
    		        VALUES ( NULL, '".pnVarPrepForStore($deny)."'); ";
        }
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." Deny rules. Query: $sql");
    	    return false;
    	}
    } 
////////// URL keywords/shortcuts: array('key' => 'url'); //////////
    $NukeWrappertable = $pntable['NukeWrapper_wrapurl']; 
    $NukeWrappercolumn = &$pntable['NukeWrapper_wrapurl_column'];

    $sql = "CREATE TABLE $NukeWrappertable (   
            $NukeWrappercolumn[key] varchar(30) NOT NULL default '' unique,    
            $NukeWrappercolumn[url] varchar(100) NOT NULL default '', 
            PRIMARY KEY(key))";
       //     $NukeWrappercolumn[id] int unsigned NOT NULL auto_increment, 
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." WrapURL. Query: $sql");
        return false;
    }
    if (is_array($wrapUrl) && !empty($wrapUrl)) { // Insert $$wrapUrl array into database
    	$sql="";
    	foreach($wrapUrl as $key => $url) {
    		$sql .= "INSERT INTO $NukeWrappertable 
    		        VALUES (  '".pnVarPrepForStore($key)."', '".pnVarPrepForStore($url)."'); ";
        } // NULL,
    	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." WrapURL. Query: $sql");
    	    return false;
    	}
    } 
////////// WrapIn/WrapOut Replace rules //////////
    $NukeWrappertable = $pntable['NukeWrapper_replace']; // 
    $NukeWrappercolumn = &$pntable['NukeWrapper_replace_column'];

    $sql = "CREATE TABLE $NukeWrappertable (   
            $NukeWrappercolumn[key] varchar(30) NOT NULL default '' unique,    
            $NukeWrappercolumn[in] varchar(255) NOT NULL default '',    
            $NukeWrappercolumn[out] varchar(255) NOT NULL default '', 
            PRIMARY KEY(key))"; // key
        //    $NukeWrappercolumn[id] int unsigned NOT NULL auto_increment, 
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." Replace column. Query: $sql");
        return false;
    }
    if (is_array($wrapIn) && !empty($wrapIn) && is_array($wrapOut) && !empty($wrapOut)) { 
    	$sql="";
    	foreach($wrapIn as $key => $value) {
    		foreach($value as $value2) {
    			$sql .= "INSERT INTO $NukeWrappertable (key, in, out)
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
    	$sql = "INSERT INTO $NukeWrappertable (key, in, out)
    	        VALUES (NULL, 'all', '', '')"; // id, 
  	$dbconn->Execute($sql);
    	if ($dbconn->ErrorNo() != 0) {
    	    pnSessionSetVar('errormsg', _INSERTVALUEFAILED.$dbconn->ErrorMsg()." Replace rules. Query: $sql");
    	    return false;
    	}
    }
////////// WrapIn2/WrapOut2 Regular Expression replace rules //////////
    $NukeWrappertable = $pntable['NukeWrapper_RegEx_replace']; // 
    $NukeWrappercolumn = &$pntable['NukeWrapper_RegEx_replace_column'];

    $sql = "CREATE TABLE $NukeWrappertable ( 
            $NukeWrappercolumn[key] varchar(20) NOT NULL default '' unique,    
            $NukeWrappercolumn[in] varchar(255) NOT NULL default '',    
            $NukeWrappercolumn[out] varchar(255) NOT NULL default '', 
            PRIMARY KEY(key))"; 
        //    $NukeWrappercolumn[id] int unsigned NOT NULL auto_increment,   // key id
    $dbconn->Execute($sql);

    if ($dbconn->ErrorNo() != 0) {
        pnSessionSetVar('errormsg', _CREATETABLEFAILED.$dbconn->ErrorMsg()." RegEx replace rules. Query: $sql");
        return false;
    }
    if (is_array($wrapIn2) && !empty($wrapIn2) && is_array($wrapOut2) && !empty($wrapOut2)) { 
    	$sql="";
    	foreach($wrapIn2 as $key => $value) {
    		foreach($value as $value2) {
    			$sql .= "INSERT INTO $NukeWrappertable (key, in, out)
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
    	$sql = "INSERT INTO $NukeWrappertable (key, in, out)
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
    pnModSetVar('NukeWrapper', 'StartPage', '');

    // Initialisation successful
    return true;
}

/****************************************************
 * upgrade the NukeWrapper module from an old version
 * This function can be called multiple times
 */
function NukeWrapper_upgrade($oldversion) {
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
 //           $NukeWrappertable = $pntable['NukeWrapper'];
 //           $NukeWrappercolumn = &$pntable['NukeWrapper_column'];

            // Add a column to the table - the formatting here is not
            // mandatory, but it does make the SQL statement relatively easy
            // to read.  Also, separating out the SQL statement from the
            // Execute() command allows for simpler debug operation if it is
            // ever needed
 //           $sql = "ALTER TABLE $NukeWrappertable
 //                   ADD $NukeWrappercolumn[number] int(5) NOT NULL default 0";
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
//            return NukeWrapper_upgrade(1.0);
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
 * Delete the NukeWrapper module
 * This function is only ever called once during the lifetime of a particular
 * module instance
 */
function NukeWrapper_delete() { 
return true; // temporarily disabled
    // Get datbase setup - note that both pnDBGetConn() and pnDBGetTables()
    // return arrays but we handle them differently.  For pnDBGetConn()
    // we currently just want the first item, which is the official
    // database handle.  For pnDBGetTables() we want to keep the entire
    // tables array together for easy reference later on
    list($dbconn) = pnDBGetConn();
    $pntable = pnDBGetTables();

    $dbconn->Execute("DROP TABLE $pntable[NukeWrapper_settings]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[NukeWrapper_htmldirs]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[NukeWrapper_phpdirs]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[NukeWrapper_allow]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[NukeWrapper_deny]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[NukeWrapper_wrapurl]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[NukeWrapper_replace]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }
   $dbconn->Execute("DROP TABLE $pntable[NukeWrapper_RegEx_replace]");
    if ($dbconn->ErrorNo() != 0) {
        return false;
    }

    // Delete any module variables
    pnModDelVar('NukeWrapper', 'StartPage');


    // Deletion successful
    return true;
}
?>