<?php
if (realpath(__FILE__) == realpath($_SERVER['SCRIPT_FILENAME'])) {
  exit;
} // current file not the executing script
//    if ($NWrap!==true) {
//        echo "No access allowed.";
//        exit;
//    }

// Use $nukeroot to indicate a path relative to the root of the PostNuke site
// either as $nukeroot.'/path' or as "$nukeroot/path" (variables don't work inside single-quotes)

$HTMLdirs = Array($nukeroot.'/HTML', $nukeroot, "$nukeroot/PHPpages");     
                    // If all HTML pages are placed in the same folder other than the PostNuke root, you can specify it here. Else leave empty.
                    // NukeWrapper will search each directory in turn for a specified file. Default is PostNuke directory.
                    // Example: Array($nukeroot.'/HTML', $nukeroot, "$nukeroot/PHPpages", '/albums', '/stuff');

$AllowPHP="1";      // To allow inclusion of internal PHP pages, set to "1", else "0". Will be disabled if no valid PHP directory set.

$PHPdirs = Array("$nukeroot/PHPpages", $nukeroot.'/HTML'); 
                    // If allowing PHP, create a directory for your PHP pages under the Postnuke site root, eg "PHPpages"
                    // and set in this array relative to server root, eg "/nuke/PHPpages" for www.example.com/nuke/PHPpages
                    // or "/someotherdir" for www.example.com/someotherdir/ Default is "PHPpages" under the PostNuke root.
                    // It's a good idea to place an empty index.html page in each directory to avoid indexing of content.
                    // Example: Array('/nuke/PHPpages', '/albums');

$NWkeywords = Array('admin', 'user', 'mem', 'me', 'who', 'members'); // '-', 'shtml'
                    // Keywords for use in the PostNuke security schema. They will be matched against the URI, so for instance
                    // filename-admin.php will match 'admin', or /HTML/path/filename-user.html could be matched against 'HTML', 'path', 'user' or just '-'.

$NWFiles['allow'] = Array(); 
                    // List of keywords/filepaths. ONLY allow files with these keywords. 
                    // If empty - Array(); - ALL files will be allowed subject to valid filetypes set in $ValidExp arrays. 
                    // Will match partial filepaths, eg '-doc' will match all files like 'manual-doc.html'
                    // Example: Array($nukeurl.'HTML', '/Sci-Fi/DrWho', '-'); 

$NWFiles['deny'] = Array('htaccess', 'config', 'includes', 'backend', 'header', 'footer', 'phpinfo', 'wrap',
                          $nukeurl.'/index', $nukeurl.'/modules', $nukeurl.'/mainfile'); 
                    // Disallow all URLs with these keywords. 
                    // Example: Array('includes', 'private', 'xxx', 'index', 'header');

$ExtractMeta="1";   // Whether to extract certain metadata from the HEAD of local files, which can then be used
                    // for customised page titles and Search Engine Optimisation.
                    // Returns an array of metadata. Values are only set when found in the file. 
                    // Available values (when set) are: 
                    // $meta['title']            the page title between the <title> tags
                    // $meta['keywords']         Meta keywords for search engines. Should be short list of relevant comma-separated words or phrases.
                    // $meta['description']      Short description of the page content.
                    // $meta['Content-Type']     As well as text/html, may contain the page character set to use.
                    // $meta['Author']           Author of the included page
                    // $meta['Content-Language'] The language for the site
                    // $meta['Expires']          The page Expiry date, useful for static content that doesn't change.
                    // $meta['Cache-Control']    Whether the page can be cached. May not be homoured by some browsers or proxy caches.
                    // $meta['Robots']           Whether and how Search Engine robots index a site. A robots.txt file may be better.

$FixLinks="1";      // 1=on, 0=off. Corrects relative filepaths of local files not in the same directory as NukeWrapper. 
                    // Leave off if not using document-relative links, maybe change the paths in your HTML documents to use root-relative paths (from the Document Root), 
                    // eg src="/sci-fi/drwho/images/TomBaker.jpg" rather than src="images/TomBaker.jpg"

$WrapLinks="1";     // If FixLinks is set, this option will further convert links to open in PostNuke site through NukeWrapper.

                    // -------------- IE PNG transparency fix ----------------
$FixTransparency = false; // This will add the AlphaImageLoader filter for Internet Explorer users
                    // to fix transparency in PNG images in this inferior browser. 
                    // Will only be applied to PNG images with the suffix specified in $PNGimages.
                    // Off by default since if you use the supplied IE7 Javascript library 
                    // (enabled by provided Header.php files), it will conflict. true/false.
                    
$PNGsuffix = "-trans"; // If using the IE transparency fix, only PNG images with this suffix will have the filter applied,
                    // eg "logo-trans.png"
         
$SpacerPath = "images/spacer.gif"; // For use by the IE transparency fix. A path to a valid transparent dummy image.
                    // One is supplied with NukeWrapper, and placed in the main images folder.
                    // -------------------------------------------------------
                    
$AllowExtLink="0";  // "1" on, "0" off. Whether to allow external linking to pages, whether via a hyperlink or entered as a URL in a browser.
                    // If set to "0", it will only allow site linking of pages, and they cannot be bookmarked or linked to from an external site.

$AllowURLs="1";     // Allow embedding of URLs with url=www.somesite.com - 1=yes, 0=no

$URLs['allow'] = Array(); 
                    // List of hosts/URLs. ONLY allow URLs with these keywords. If empty - Array(); - ALL URLs will be allowed. 
                    // Will match partial URLs, eg 'yahoo' will match all Yahoo domains.
                    // Example: Array('www.yahoo.com', 'slashdot', 'google');

$URLs['deny'] = Array('sex', 'hentai', 'cartoon-x', 'mangax', 'xxx'); 
                    // Disallow all URLs with these keywords. 
                    // Example: Array('sex', 'hentai', 'cartoon-x', 'mangax', 'xxx');

$URLkeys = Array('postnuke' => 'www.postnuke.com', 'google' => 'www.google.com'); // array('key' => 'value');
                    // Optional array for URL shortcuts, so long URLs can be specified as wrap.php?url=example 
                    // and be translated to wrap.php?url=http://www.example.com/somedir/ by using the format in the example below.
                    // Also used in the PostNuke security schema for Keyword: 
                    // component: NukeWrapper::url   Instance: $URLkeys:keyword:extension 
                    // Example: array=('example' => 'http://www.someurl.com/somedir/', 'postnuke' => 'www.postnuke.com', 
                    //        'nukewrapper' => 'http://forums.postnuke.com/index.php?name=PNphpBB2&file=viewtopic&t=27256&postdays=0&postorder=asc&start=0&sid=86a3095c426da96c33082788f824f2df')

$UseTables="1";     // Whether to use theme tables and borders around page by default. 1=yes, 0=no 

$Layout="0";        // Only for non-Xanthia themes that have been fixed. 
                    // Default for idx option above. 0, 1, 2, 3 or 4. 
                    // Options 2,3 and 4 requires editing of the theme.
                    //       0 = Default; left blocks, no right blocks.
                    //       1 = Home page (with left, right and center blocks with Admin message)
                    //       2 = display left and right blocks, no center blocks or Admin message.
                    //       3 = No left blocks, display right blocks.
                    //       4 = No left or right blocks, only HTML page with Header and Footer.

$ShowLink="1";      // For external pages, whether to show "Open in new window" link in top of frame. 1=yes, 0=no

$OpenInNewWindow="1"; // Whether to open external webpage links in new window, or open in the existing frame.
                    // URLs on the same server will not open in new window.
                    // The frame cannot resize to fit pages when browsing in the frame. This is only possible on page refresh.  
                    
$AutoResize="1";    // Resizes the iFrame when wrapping URLs. 1=on, 0=off
                    // If off, defaults to 600px (set below), but can be set in the URL with "height=xxx", 
                    // where xxx is an integer pixel value. 
                    // If you use url2= instead of url= then NW will turn off AutoResize and use a non-resizing iFrame.

$FrameHeight = 600; // Integer value in pixels for the default height of the iframe used with external urls.

$StartPage = ""; // who.html
                    // Page to be loaded if no location/file supplied. For URLs, put http:// first, 
                    // or it will be assumed to be a local file. 
                    // Examples: "who.html" "nuke/AboutUs.html" "http://www.google.com/"
                    
$DocumentRoot = ""; // For linking to local files. Leave empty if you don't have any problems with local files.
                    // If NukeWrapper can't detect the correct Document Root, then you may be 
                    // denied access to the file with the "Who's A Naughty Boy" message 
                    // as it may be an attempt to hack the server. The correct Document Root may fix this.
                    // Example: "/home/s/jsmith/somedir";
                    // See the Debug setting below.

$ValidExp1 = array('.htm', '.shtml', '.txt', '.pdf');
                    // Array with allowed extensions and keywords for static non-script pages, eg .html, .txt.
                    
// $ValidExp1 = "\.htm|\.shtml|\.txt";
                    // Regular Expression list with allowed extensions and keywords for non-script pages, eg html, txt.
                    // Full stops must be escaped with a baskslash \. and terms separated by the "pipe" character
                    // (shift-\ in Windows)

$ValidExp2 = array('.php', '.phtml', '.cgi', '.asp', '.iasp', '.jsp', '.cfm', '.pl', '.adp', '.orm'); 
                    // Allowed dynamic page (script) extensions and keywords, 
                    // eg .php, .jsp, .asp, cgi 

// $ValidExp2 = "\.php|\.phtml|\.cgi|\.asp|\.iasp|\.jsp|\.cfm|\.pl|\.adp|.orm"; // $ValidExpr="(".$ValidExp1.($AllowPHP ? "|".$ValidExp2 : "" ).")";
                    // Allowed dynamic page (script) extensions and keywords when AllowPHP is set above; 
                    // eg .php, .jsp, .asp, cgi 
                    // In Admin, have semi-colon separated input: .asp; .jsp; .php parse and add slash to dots, replace ;\s* with |  

$WrapDebug="0";     // "1" to output Debugging info, "0" to turn off.
                    // If you have trouble with site security and your pages being redirected 
                    // to the index page with "Who's A Naughty Boy Then?", try this satting to discover your 
                    // PostNuke install directory from the Server root or your site's Document Root, 
                    // or if something else fails. It will output a number of variables, 
                    // paths and directories as the script executes, and at the bottom of the page
                    // will output a large table of PostNuke and Server settings and variables.
                    // The output is only visible to site admins. 


/****************************************************************************************/
// The In and Out arrays below can be used to replace elements on a web page, be it words, images or HTML.
// They take a comma-delimited and quoted list, one search string for each replacement string. 
// Since double-quotes are more common in HTML, I use single-quotes for each array element. If matching a single-quote (as in Don't), 
// you must 'escape' it with a backslash \' if you use double-quotes in the array, they must be escaped instead \"
// Note all HTML on the page is affected, including filepaths, so be careful what you replace. 
// It is case-sensitive, so 'Example' is not the same as 'example'. Space elements in the array to suit, even across multiple lines.

// $URLkeys2: Array matching user-defined keynames used in In/Out arrays to URL path to be parsed in the format array('key' => 'value'); 
//           Path is without filename (eg index.html) but with slash on the end (see example below for syntax)
//           Can also be used for URL shortcuts, see above. 
// $wrapIn['key']: Pattern/words to match. Key restricts matching to URL in $URLkeys.
// $wrapOut['key']: Replacement string. Key must match $wrapIn key. As above, only URLs matching the key is affected.
// $wrapIn['all'] / $wrapOut['all']: 'all' is a special key which match all URLs, so all pages are parsed. It is overriden by unique keys.
// Uncomment (remove slashes before statements) below to use.  

// $URLkeys2 = Array('example' => 'http://www.someurl.com/somedir/', 'whitehouse' => 'http://www.whitehouse.gov/president/');
// $wrapIn['example']  = Array('pattern', 'this');     $wrapIn['all'] = Array('Microsoft', 'Bill Gates', 'Windows');
// $wrapOut['example'] = Array('replacement', 'that'); $wrapOut['all'] = Array('Micro$uck', 'The Antichrist', 'Windoze');

$URLkeys2 = array('whitehouse' => 'http://www.whitehouse.gov/president/');
$wrapIn['all'] = Array('Microsoft', 'Bill Gates', 'Windows'); 
$wrapOut['all'] = Array('Micro$uck', 'The Antichrist', 'Windoze');
// Example for US weather service at http://absreport.aghost.net/ replacing background colour and javascript link
//$wrapIn['weather']  = Array('FFFFFF', 'window.open("/');
//$wrapOut['weather'] = Array('E6E2C4', 'window.open("http://absreport.aghost.net/');

// Example for Google replacing background colour and javascript link
//$wrapIn['google']  = Array("//-->\n</style>", 'color:#0000cc;', 'bgcolor=#ffffff', 
//		'text=#000000 link=#0000cc vlink=#551a8b alink=#ff0000');
//$wrapOut['google'] = Array("BODY {\n background-color: black;\n}\n//-->\n</style>", 
//		'color:#FFFFFF;', 'bgcolor="#000000"', 
//		'text="#FFFFFF" link=#FFFFFF" vlink="#FF9900" alink=#FF9900');
//$wrapIn2['google'] = Array('|src=".+/logo.gif|U');
//$wrapOut2['google'] = Array('src="http://www.abc.net.au/children/bananas/img/nav_test/nav_navy_stories.gif');

$wrapIn['whitehouse']  = Array('in Focus',    'State', 'Union', 'Nation', 'address', 'serious',    'Violence', 'Oval',       'rosperous',    'ambitious',  'ecisive', 'More', 'more', 'ompassionate', 'forward',  'etter', 'Iraqi',   'Iraq',   'Middle', 'East', 'done',      'a personal',    'Americans', 'American', 'America',    'United',  'businesses', 'of state', 'Condoleezza Rice', 'hopeful',  'Strength', 'estimony', 'Terrorist', 'Terror', 'Independence', 'evolv', 'dignitaries', 'staff', 'uture', 'Military', 'Office', 'White', 'House', 'the Commander-in-Chief', 
	'images/president_bio.jpg', 
	'images/presidential_cabinet.jpg', 
	'images/essay.gif', 
	'images/marineone.gif', 
	'images/previous_presidents.jpg', 
	'images/ask.gif', 
	'images/oval_office.jpg');
$wrapOut['whitehouse'] = Array('Out of Whack', 'Fate', 'Onion', 'Notion', 'undress', 'ridiculous', 'Vasectomy', 'Egg-Shaped', 'reposterous', 'amphibious', 'erisive', 'Less', 'less', 'ompetent',    'backwards', 'itter', 'Canadian', 'Ironing', 'Piddle', 'West', 'unravelled', 'an impersonal', 'Martians', 'Venusian', 'Disneyland', 'Divided', 'children',  'of weed',   'Doolittle',      'hopeless',  'Weak',    'esticles', 'Turtle',    'Error',  'Incompetence', 'devolv',  'dickheads',  'stiff', 'uton', 'Millinary', 'Orifice', 'Purple', 'Meanie', 'Commander Keen', 
	'http://www.abc.net.au/children/bananas/img/nav_test/nav_navy_stories.gif', 
	'http://www.abc.net.au/children/bananas/img/nav_test/nav_navy_goodies.gif', 
	'http://www.abc.net.au/children/bananas/img/nav_test/nav_navy_makedo.gif', 
	'http://www.geocities.com/TimesSquare/Castle/1561/keenani.gif', 
	'http://www.abc.net.au/children/bananas/img/nav_test/nav_navy_gallery_ro.gif', 
	'http://www.abc.net.au/children/bananas/img/nav_test/nav_navy_guestbook.gif', 
	'http://www.abc.net.au/children/bananas/img/characters/smlnav_cow.gif');

// For more advanced Regular Expression (RegEx) replace rules, use this array. Both can be used simultaneously.
// See http://www.php.net/manual/en/pcre.pattern.syntax.php

/// Weather example, replacing all relative links to wrap through NukeWrapper, except HTTP, mailto, and javascript links
 $wrapIn2['weather']  = Array('%<a([^>]+)href\s*=\s*(["\'])(?!http)(?!mailto)(?!java)(?=[^\s#"\']*'.$ValidExpr.')%Ui');
 $wrapOut2['weather'] = Array('<a$1 target="_top" href=$2http://nuke/page.php?url=http://absreport.aghost.net/');

// Example for http://www.whitehouse.gov/president/ replacing some words conditionally and image links
 $wrapIn2['whitehouse']  = Array('/(George )?(W\. )?Bush/', 
	'/(?<!(\/|_))President/i', 
	'|/news/releases/200\d/\d+/images/[\d\w_-]+\.jpg|', 
	'|/images/porticop[\d\w-]+\.jpg|', 
	'|/president/images/400-\w\d{5}-\d{2}\w\.jpg|', 
 	'|president_page_header\.gif(.*)</tr>|Us');
 $wrapOut2['whitehouse'] = Array('<b>The Great Imbecile</b>', 
	'<b>Grand Poobah</b>', 
	'http://www.abc.net.au/children/bananas/img/home/nav_characters_home.gif', 
	'http://www.abc.net.au/children/bananas/img/home/nav_games_home.gif', 
	'http://politicalhumor.about.com/library/graphics/bush_gollum.jpg', 
//  "http://www.geocities.com/Heartland/Valley/3429/bananainpyjamabr.jpg\\1</tr>",
 	'president_page_header.gif$1</tr><tr><td colspan="5"><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="630" height="90"><param name="movie" value="http://m2.doubleclick.net/922051/728x90_02.swf"><param name="quality" value="high"><embed src="http://m2.doubleclick.net/922051/728x90_02.swf" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="630" height="90"></embed></object></td></tr>');
?>