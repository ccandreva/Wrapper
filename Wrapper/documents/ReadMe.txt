

Wrapper 1.0 devel snapshot

This snapshot is an update of NukeWrapper for Zikula. The changes are:
1) Rename module from NukeWrapper to Wrapper
2) Rename config file to Wrapper.conf.php, and have it looked for in the
   Zikula /config/ directory
3) An a type=file function for wrapping files without modifying them. This
   is intended to allow Zikula permissions to protect  any file that is
   available for download. 
4) Add a FileDirs array to control what directories files may be downloaded
   from.

To truely protect files, they must be blocked for direct access via the
system .htaccess file

Everything else should be the same.


This package contains: 
1. The NukeWrapper standalone script and module; 
2. Optional htaccess files for ShortURLs (one for redirecting any HTML file in a PostNuke 
   site root to NukeWrapper, the other for ShortURL enabled themes with the addition of 
   the NW rewrite rules). Rename desired htaccess file to .htaccess and place in the 
   PostNuke root. If an htaccess file already exists, the content may be combined, 
   though the order may be important.
3. The Titlehack for Search Engine Optimisation, which gives PostNuke more descriptive page titles 
   and can be customised on a per-module basis(no titlefiles are supplied though).
   Location: includes/pnTitle.php
   See http://lottasophie.sourceforge.net
4. The IE7 Javascript library by Dean Edwards for bringing better web standards to 
   Internet Explorer users, in particular CSS1, CSS2, some CSS3 compliance, and PNG transparency. 
   If you don't plan to use any CSS which might give IE users trouble, such as positioning, 
   hover effects etc, you may choose to disable it by either removing or renaming the script. 
   If you merely want to use transparency with PNGs, you may prefer to use the inbuilt NukeWrapper parser, 
   which applies the Microsoft AlphaImageLoader filter to images on the page (but not to linked stylesheets).
   See http://dean.edwards.name/IE7/
   Location: includes/ie7/ie7-standard-p.js
5. 3 Xanthia plugins for PostNuke 0.75 and up for applying the TitleHack, NukeWrapper metatags, 
   and IE7 JavaScript library. To disable any of these 
   fatures in Xanthia themes, such as IE7, simply don't put the tag in.
   See tech notes below for how to install in a Xanthia theme.
   Location: modules/Xanthia/plugins 


NukeWrapper 2 notes
===================

Basically, NukeWrapper is an HTML wrapper, which means it will wrap any webpage you point it at into a PostNuke site, convenient for when you design pages in an HTML editor like Dreamweaver with tables, layers, images, Flash etc, and want to have them integrated into PostNuke, or for a community site where contributors are nontechnical but know enough to use Frontpage or even (Gahh!) Word to produce an online newsletter with images etc. This where HTML wrappers come in. 

Description:
============
NukeWrapper embeds HTML, PHP, other user-allowed script filetypes, text files and external webpages into PostNuke by "wrapping" PostNuke around them.  Local files are incorporated so as to use the site's themes and stylesheets, without the double-scrollbars you get from some modules using iFrames. Document-relative links (images, hyperlinks, stylesheets, and Javscript links) in files not in the PostNuke root are changed to Root-relative links so as not to break them.
Text files are converted to HTML with all special characters escaped to prevent code execution, and hard spaces are put in to maintain indentation and formatting. This makes it convenient for having code listings of, say, HTML. 
External URLs are placed in an iFrame, but unlike any other script available anywhere (literally), NukeWrapper will resize the iFrame to fit the contents of the page. 
NukeWrapper also allows for the content to be parsed (see below) and will attempt to thwart frame-breaking Javascript code (only works if in the actual page, not a linked Javascript file). 

You can set whether to use the theme tables or a blank background with the 'opt' (option) flag in the URL.
It also allows you to set a page layout for older ("Classic" PN 0.7x) themes with the 'idx' (index) flag. For Xanthia or AutoThemes, use the inbuilt templating system by creating a template in the desired layout and activate it for NukeWrapper. AuthTheme allows for different templates for different URL parameters, eg for different wrapped files/URLs. 


Usage:
======
Place page.php in you PostNuke root, alongside the main index.php file, and point to it in links or type an address in your browser's URL field like so:
     http://your.site.com/page.php?file=filename.html
  or http://your.site.com/page.php?filename.php
     http://your.site.com/page.php?HTMLcodeExample.txt 

If the 'file=' prefix is excluded, it will be assumed that the first part of the Query string (after the '?') is the filename, unless it is one of the optional parameters.
The file path is by default relative to the Postnuke root (where index.php and NukeWrapper is located) for HTML and txt pages, and a subdirectory called 'PHPpages' for PHP pages, but as described below it may be from any location.

For external web pages:
    http://your.site.com/page.php?url=www.somesite.com
    http://your.site.com/page.php?url=http://www.somesite.com 

url2 uses a plain frame with no resizing (default 600 pixels high):
    http://your.site.com/page.php?url2=www.somesite.com

Turning off Auto-Resizing for fixed-height pages and specifying a height:
    http://your.site.com/page.php?height=800&url2=http://www.somesite.com

url = The URL or webpage you wish to wrap; if you omit the http://, it will be added automatically.
      This method can parse external page content by opening the page in PHP, which is how pageheight is determined.
url2 = Same as url, except url2 is a shorthand way of telling it not to resize the frame, 
      which will default to a height of 600px. It uses a straightforward frame link in HTML, 
      and so cannot parse the content.
height = When autoresizing is off, such as by using url2, this will then set the height.

When wrapping external URLs, clicked links will open in a new window, unless the page is on the same host. 

If the supplied .htaccess file is installed in the Postnuke directory on an Apache server with mod_rewrite enabled and allowing per-directory overrides (use of htaccess files):

    http://your.site.com/filename.html
(this assumes all HTML files in your site root is to be wrapped). 
    http://your.site.com/url=www.somesite.com 

Because of how PostNuke works, it cannot take 
http://your.site.com/subdir/filename.html to mean page.php?file=/subdir/filename.html, as PN will get confused as to where the site root is.

  The opt and idx variables are optional, they can be excluded and will use their defaults.
  opt = use themed borders, where X is 0 or 1 (0=off, 1=on, default 1)
  idx = Column layout, determines which blocks are displayed. X is 0, 1, 2, 3 or 4.
       Options 2,3 and 4 requires editing of your theme. See Technical Notes below.
       NOTE: This only works with "Classic" PostNuke themes, not Xanthia themes.
       0 = Default; left blocks, no right blocks.
       1 = Home page (with left, right and center blocks with Admin message)
       2 = display left and right blocks, no center blocks or Admin message.
       3 = No left blocks, display right blocks.
       4 = No left or right blocks, only HTML page with Header and Footer.

Example: 
    http://your.site.com/page.php?file=filename.html&opt=0&idx=4

A default layout can be set in the script, for instance if you want wrapped pages full width (no side blocks), you'd set the variable 
$Layout="4";

By default, you can link to local HTML and txt files under the PostNuke root. For instance, if you've installed Postnuke in 
www.example.com/nuke/ 
and if you specify the filepath file=AboutUs.html, NukeWrapper will look for /nuke/AboutUs.html, and file=HTML/AboutUs.html will point to 
www.example.com/nuke/HTML/AboutUs.html
Similarly, by default for security it will look for PHP pages in a subdirectory called PHPpages under the PostNuke root (which you must create to use). Ie, as per previous example, file=AboutUs.php would point to
www.example.com/nuke/PHPpages/AboutUs.php
This is to keep the PHP pages separate so you don't allow someone to link to PostNuke system files.

However, you can specify any location you choose, even outside the Postnuke root, provided it's inside the server Document root, and the location is listed in one of two arrays supplied in the file for this purpose; one for HTML and txt files, $HTMLdirs, and one for PHP, $PHPdirs. They take a comma-delimited and quoted list in the Array(); construct.

For example:
$HTMLdirs = Array('/nuke/HTML', '/albums', '/nuke/holiday/Australia'); 
$PHPdirs  = Array('/nuke/PHPpages', '/albums/family', '/cult/sci-fi/drwho', 
                  '/stuff', '/erotica/japan');

The path here is relative to the Site root, not the Postnuke install directory, hence in the above example the PostNuke root is '/nuke', and hence you can list parallel directories outside the Postnuke root. If you simply link to file=DrWho.php or drwho/DrWho.php, then NukeWrapper will search each directory in the list in turn as they appear, so if there are two files with the same name, the file in the directory appearing first in the list will be linked to. If not in any of the listed directories, it will look in the default location last, and if not there, will inform the user the file wasn't found and prompt for the correct path.


Security:
=============
NukeWrapper will not allow any linking to files appearing outside the Document Root or outside any of the listed or default directories. Even if the file exists, if trying to link to unauthorised locations, the user will be redirected to the Postnuke site's home page.
For instance, if the server has a system file at C:/Apache2/conf/httpd.txt (assuming the system file had a .txt extension), a hacker might try the old trick of supplying the path file=../../conf/httpd.txt to produce the server path
C:/Apache2/htdocs/albums/../../conf/httpd.txt (which match one of the listed paths in the above example), which in turn translates to 
C:/Apache2/conf/httpd.txt
This will fail the hacker test, as the file is outside the absolute path of the document root and any of the listed directories, and the hacker will be redirected.
Consequently, if your site's Document Root is not correctly detected due to server settings, you may have trouble wrapping your local files. You can explicitly set your site's Document Root in the $DocumentRoot option below. 
The Debug mode can be helpful in determining what the problem is. 


Options:
==============
These options are set in the Script. The defaults may suffice, but you may need to change the allowed directories in HTMLdirs (for static HTML pages) or PHPdirs (for dynamic content such as PHP).

$HTMLdirs = Array('/nuke/HTML', '/albums', '/nuke/holiday/Australia');  // Example     
                    If all HTML pages are placed in the same folder other than the PostNuke root, you can specify it here.
                    NukeWrapper will search each directory in turn for a specified file. Default is PostNuke directory.
                    Else leave empty with Array(); 
  
$AllowPHP="1";      To allow linking to internal PHP pages, set to "1", else "0". Will be disabled if no valid 
                    PHP directory set, either by making a directory called PHPpages under the PostNuke root, 
                    or listing in the $PHPdirs array.

$PHPdirs = Array('/nuke/PHPpages', '/albums', '/cult/sci-fi/drwho'); // Example 
                    As mentioned above, this array lists allowed directories under the Server document root for PHP pages, 
                    relative to the Document Root, not the Postnuke root (unless they're the same), eg "/nuke/PHPpages" 
                    for www.example.com/nuke/PHPpages or "/somedir" for www.example.com/somedir/ 
                    Default is "PHPpages" under PostNuke root.
                    If you leave the array empty with Array(); or requested file isn't in any of the listed directories, 
                    NukeWrapper will look in the default location. 
                    It's a good idea to place an empty index.html page in each directory to avoid indexing of content.

$NWkeywords = Array('admin'); 
                    Keywords for use in the PostNuke security schema. They will be matched against the URI, so for instance
                    filename-admin.php will match 'admin', or /HTML/path/filename-user.html could be matched against 
                    'HTML', 'path', 'user' or just '-'. Access rules can then be set in PostNuke Permissions.
                    Used in the permissions Instance as keyword:    filename.ext:keyword:extension
                    Example: Array('admin', 'user', 'member', 'personal');

$ExtractMeta="1";   Whether to extract certain metadata from the HEAD of local files, which can then be used
                    for customised page titles and Search Engine Optimisation. 
                    Used with the supplied metatag plugin for Xanthia and header_footer module templates.
                    Returns an array of metadata. Values are only set when found in the file. 
                    Available values (when set) are: 
                    $meta['title']            the page title between the <title> tags
                    $meta['keywords']         Meta keywords for search engines
                    $meta['description']      Short description of the page content.
                    $meta['Author']           Author of the included page
                    $meta['Content-Type']     As well as text/html, may contain the page character set to use.
                    $meta['Content-Language'] The language for the site
                    $meta['Expires']          The page Expiry date, useful for static content that doesn't change.
                    $meta['Cache-Control']    Whether the page can be cached. May not be homoured by some browsers or proxy caches.
                    $meta['Robots']           Whether and how Search Engine robots index a site. A robots.txt file may be better.

$FixLinks="1";      1=on, 0=off. Corrects relative filepaths of local files not in the same directory as NukeWrapper. 
                    Switch off if not using document-relative links, maybe change the paths in your HTML documents 
                    to use root-relative paths (from the Document Root), 
                    eg src="/sci-fi/drwho/images/TomBaker.jpg" rather than src="images/TomBaker.jpg"

$WrapLinks = "1";   If FixLinks is set, this option will further convert links to open in PostNuke site 
                    through NukeWrapper, provided they're of a valid type in the $ValidExp lists below.

                    -------------- IE PNG transparency fix ----------------
$FixTransparency = false; 
                    This will add the AlphaImageLoader filter for Internet Explorer users
                    to fix transparency in PNG images in this inferior browser. 
                    Will only be applied to PNG images with the suffix specified in $PNGimages.
                    Off by default since if you use the supplied IE7 Javascript library 
                    (enabled by the provided IE7 Xanthia plugin), it will conflict. true/false.
                    
$PNGsuffix = "-trans"; 
                    If using the IE transparency fix, only PNG images with this suffix will have the filter applied,
                    eg "logo-trans.png"
         
$SpacerPath = "images/spacer.gif"; 
                    For use by the IE transparency fix. A path to a valid transparent dummy image.
                    One is supplied with NukeWrapper, and placed in the main images folder.
                    -------------- End PNG Transparency Fix ---------------

$AllowExtLink="1";  "1" on, "0" off. Whether to allow external linking to the page (ie other than from your own site), 
                    whether via a hyperlink or entered in the URL field of a browser.
                    If set to "0", it will only allow local linking to pages, and they cannot be bookmarked or 
                    linked to from an external site.

$AllowURLs="1";     Allow embedding of URLs with url=www.somesite.com - 1=yes, 0=no

$URLs['allow'] = Array(); // Array('url1', word1', 'url2');
                    List of hosts/URLs. ONLY allow URLs with these keywords. 
                    If empty - Array(); - ALL URLs will be allowed. 
                    Will match partial URLs, eg 'yahoo' will match all Yahoo domains.
                    Example: Array('www.yahoo.com', 'slashdot', 'google');

$URLs['deny'] = Array(); 
                    Disallow all URLs with these keywords. 
                    Example: Array('sex', 'hentai', 'cartoon-x', 'mangax', 'xxx');

$URLkeys = Array('postnuke' => 'www.postnuke.com', 'google' => 'www.google.com'); // array('key' => 'value');
                    Optional array for URL shortcuts, so long URLs can be specified as page.php?url=example 
                    and be translated to page.php?url=http://www.example.com/somedir/ by using the format 
                    in the example below:
                    Example: array=('example' => 'http://www.someurl.com/somedir/', 'postnuke' => 'www.postnuke.com', 
                           'nukewrapper' => 'http://forums.postnuke.com/index.php?name=PNphpBB2&file=viewtopic&t=27256&postdays=0&postorder=asc&start=0&sid=86a3095c426da96c33082788f824f2df');

$UseTables="1";     Whether to use theme tables and borders around page by default. 1=yes, 0=no 

$Layout="0";        Only for non-Xanthia themes that have been fixed. See technotes below.
                    Default for idx option above. 0, 1, 2, 3 or 4. 
                    Options 2,3 and 4 requires editing of the theme.
                          0 = Default; left blocks, no right blocks.
                          1 = Home page (with left, right and center blocks with Admin message)
                          2 = display left and right blocks, no center blocks or Admin message.
                          3 = No left blocks, display right blocks.
                          4 = No left or right blocks, only HTML page with Header and Footer.

$ShowLink="1";      For external pages, whether to show "Open in new window" link in top of frame. 1=yes, 0=no

$OpenInNewWindow="1"; 
                    Whether to open external webpage links in new window, or open in the existing frame.
                    URLs on the same server will always open in the same window regardless.  

$AutoResize="1";    Resizes the iFrame when wrapping URLs. 1=on, 0=off
                    If off, defaults to 600px, but can be set in the URL with "height=xxx", 
                    where xxx is an integer pixel value. 
                    If you use url2= instead of url= then NW will turn off AutoResize and use non-resizing iFrames.

$StartPage = "http://www.google.com/";    
                    Page to be loaded if no location/file supplied. For URLs, put http:// first, 
                    or it will be assumed to be a local file. 
                    Examples: "who.html" "nuke/AboutUs.html" "http://www.google.com/"
                    
$DocumentRoot = ""; For linking to local files. Leave empty if you don't have any problems with local files.
                    If NukeWrapper can't detect the correct Document Root, then you may be 
                    denied access to the file with the "Who's A Naughty Boy" message 
                    as it may be an attempt to hack the server. The correct Document Root may fix this.
                    Example: "/home/s/jsmith/somedir";
                    See the Debug setting below.

$ValidExp1 = array('.htm', '.shtml', '.txt', '.pdf');
                    Array with allowed extensions and keywords for static non-script pages, eg .html, .txt.

$ValidExp2 = array('.php', '.phtml', '.cgi', '.asp', '.iasp', '.jsp', '.cfm', '.pl', '.adp', '.orm'); 
                    Allowed dynamic page (script) extensions and keywords when AllowPHP is set above;
                    eg .php, .jsp, .asp, cgi 

$WrapDebug="0";     "1" to output Debugging info, "0" to turn off.
                    If you have trouble with site security and your pages being redirected 
                    to the index page with "Who's A Naughty Boy Then?", try this function to discover your 
                    PostNuke install directory from the Server root, or if something else fails. It will output 
                    a number of variables, paths and directories as the script executes, and at the bottom of the page 
                    will output a large table of PostNuke and Server settings and variables. 

Page Parsing for Fun and Political Satire
=========================================
As if all this is not enough, I've also included a nifty feature that's just a bit of fun, good for satire, hell it may even have a practical application. It is disabled by default. Uncomment (remove slashes in front of) the arrays as noted to use.
NukeWrapper can parse external web pages and replace contents as you see fit, for instance if you hate Microsoft (who outside MS doesn't?), you can replace all instances of "Microsoft" with "Micro$uck" and "Bill Gates" with "The Antichrist", or you can replace HTML like image links.
There are two ways to do this:

Firstly using a set of In and Out arrays for simple case-sensitive string replacing using the $wrapIn and $wrapOut arrays, indexed by a key referencing the URL to be parsed. They take a comma-delimited and quoted list, one search string for each replacement string. 

$wrapUrl2: Array matching user-defined keynames (key) used in In/Out arrays to URL path to be parsed. Path is without filename (eg index.html) but with slash on the end (see example below for syntax)
$wrapIn['key']: Pattern/words to match. Key is defined in $wrapUrl and restricts matching to this URL.
$wrapOut['key']: Replacement string. Key must match $wrapIn key. As above, only URLs matching the key is affected.
$wrapIn['all'] / $wrapOut['all']: 'all' is a special key which matches all URLs, so all pages are parsed. It is overriden by unique keys.

Example:
$wrapUrl2 = Array('example' => 'http://www.someurl.com/somedir/', 'whitehouse' => 'http://www.whitehouse.gov/president/');
$wrapIn['example']  = Array('pattern', 'this');     
$wrapOut['example'] = Array('replacement', 'that'); 
$wrapIn['all']  = Array('Microsoft', 'Bill Gates', 'Windows');
$wrapOut['all'] = Array('Micro$uck', 'The Antichrist', 'Windoze');

Secondly, there's a second set of Arrays that allows for more advanced Regular Expression (RegEx) replace rules, called $wrapIn2[] and $wrapOut2[]. The format is the same, except the In array take Regular Expressions with pattern delimiters before and after. 
See http://www.php.net/manual/en/pcre.pattern.syntax.php
Both sets of arrays can be used simultaneously.


Example for http://www.whitehouse.gov/president/ using both types of arrays. 
Copy and paste into the NukeWrapper script to see the results with the URL 
page.php?url=http://www.whitehouse.gov/president/&idx=4

$wrapUrl2 = array('whitehouse' => 'http://www.whitehouse.gov/president/');
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

// Regular Expression arrays, which allows complex pattern-matching replacement rules, like images with a certain naming pattern that are changed regularly, or has conditional patterns.
 $wrapIn2['whitehouse']  = Array('/(George )?(W\. )?Bush/', '/(?<!(\/|_))President/i', 
	'|/news/releases/200\d/\d+/images/[\d\w_-]+\.jpg|', 
	'|/images/porticop[\d\w-]+\.jpg|', 
	'|/president/images/400-\w\d{5}-\d{2}\w\.jpg|', 
 	'|president_page_header\.gif(.*)</tr>|Us');
 $wrapOut2['whitehouse'] = Array('<b>The Great Imbecile</b>', '<b>Grand Poobah</b>',
	'http://www.abc.net.au/children/bananas/img/home/nav_characters_home.gif', 
	'http://www.abc.net.au/children/bananas/img/home/nav_games_home.gif', 
	'http://politicalhumor.about.com/library/graphics/bush_gollum.jpg', 
 	'president_page_header.gif$1</tr><tr><td colspan="5"><object classid="clsid:D27CDB6E-AE6D-11cf-96B8-444553540000" codebase="http://download.macromedia.com/pub/shockwave/cabs/flash/swflash.cab#version=6,0,29,0" width="630" height="90"><param name="movie" value="http://m2.doubleclick.net/922051/728x90_02.swf"><param name="quality" value="high"><embed src="http://m2.doubleclick.net/922051/728x90_02.swf" quality="high" pluginspage="http://www.macromedia.com/go/getflashplayer" type="application/x-shockwave-flash" width="630" height="90"></embed></object></td></tr>');

// This last one inserts a whole chunk of HTML, a Flash banner, under the Oval Office banner.



Technical Notes:
================
The link fixing function, which is on by default, uses 3 Regular Expression rules to fix all the relative links in local documents. This includes hyperlinks, image links (all 'src', 'href' and 'background' links),  stylesheet image links (background, background-image), and links to external stylesheets and Javascripts. Any links in the external stylesheets are unaffected, as they are relative to the stylesheet location. The limitation is links dynamically created by Javascript, as there is no way to check for them. Paths in external Javascript should be fine. If you don't use relative links at all in your documents, turn off this function by setting $FixLinks="0"; so the ducument won't be unnecessarily parsed.

Resizing a frame to fit the contents would seem to be a trivial task, but is in fact a major pain in HTML and Javasript due to HTML having no provision for it, and the Javascript DOM security model which normally blocks Javascript from reading the properties of external pages. So instead the page is loaded via the server and is embedded in the main page just like local pages are, and placed in the iFrame with Javascript. Not a perfect solution, but the only practical way of resizing frames with external pages to fit. 
How well the frame is resized depends on the browser; Internet Explorer always seem to gets it right, resizing to the size of the scrollbars as it should. Mozilla/Netscape and Opera rely in W3C web standards, and so it varies according to the web page. Some pages are resized slightly short, and so get scrollbars. Mozilla will sometimes display scrollbars on the frame even when correctly sized; this is a Mozilla bug. 

Some sites require a valid User-Agent string (www.microsoft.com, for instance), so if the script cannot get a valid user-agent from the browser, it will identify itself as "Mozilla/4.0 (compatible; MSIE 6.0; Windows NT 5.1)". Other sites have trouble in Mozilla, seeming to get stuck loading, but working in IE and Opera (ie www.microsoft.com/billgates/bio.asp). Clicking the Back button in Mozilla clears it, but the page is then not resized.
There is a bug in the Opera browser prior to version 7.50, where it cannot use onLoad events on iFrames, hence it can't resize. This has been fixed in Opera 7.5. 

The script uses a body onload event to load the external pages due to another bug in Opera (it refuses to load the page otherwise, IE and Moz have no such trouble), so if you need to use a Body onLoad event in your themes, and want to use NukeWrapper to display external pages, add
writeiframe(); before your other function calls:

    <BODY onLoad="writeiframe(); someOtherFunction();">


USING THE TITLEHACK, NW METATAGS, AND IE7 IN “CLASSIC” THEMES
=============================================================
You can make your site more Search Engine friendly by applying the supplied Titlehack (for more meaningful titles in PN modules), using metadata from wrapped pages (keywords, page description etc), and apply the IE7 Internet Explorer web standards patch, simply by placing the supplied files in their appropriate folders the way they appear in the package:

    images/spacer.gif
    includes/pnTitle.php
    includes/ie7/ie7-standard-p.js

Then rename the appropriate header file (a version for PN0.72x and PN0.75 is supplied) to header.php and place in your PostNuke root, remembering to back up your original header file.

The Titlehack checks the current module's directory for a file called pntitle.php to produce a module-specific title. If none is found, a different hack in the supplied header files adds the name of the current module (eg News) to the title. This package doesn't supply any pntitle files, whereas some are provided in the default download from the developer's site, such as for ContentExpress, FAQ, Downloads, Reviews and others, others can be found around the place made by various people. 
See http://lottasophie.sourceforge.net for sample titlefiles.
NukeWrapper creates its own metadata, including title, from the wrapped page and hence doesn't use it. 

IE7 is a Javascript library by Dean Edwards which attempts to patch Internet Explorer's poor standards compliance, such as proper CSS1, CSS2 and some CSS3 support, fixing the IE box model (how things like height, width, padding, margin and border on objects is understood) and transparency in PNG (pronounced 'ping') images. 
See http://dean.edwards.name/IE7/

Examples:
View in IE, then click the Remove button to see what IE users normally see.
Pure CSS Menus      http://dean.edwards.name/IE7/compatibility/Pure CSS Menus.html
Complex Spiral demo http://dean.edwards.name/IE7/compatibility/complexspiral/demo.html
(fixed background attachment example) 

To disable IE7, simply remove or rename the script ie7-standard-p.js. To disable the Titlehack, simply don't use any pntitle.php files in your module folders, or rename or delete the main pnTitle.php file above.


USING THE TITLEHACK, NW METATAGS, AND IE7 IN XANTHIA THEMES
===========================================================
To fix Xanthia themes to use the TitleHack, show metadata from the wrapped page, 
and apply the IE7 Internet Explorer patch:

First install the three supplied Xanthia plugins function.title, function.meta and function.ie7 
in "modules/Xanthia/plugins", the TitleHack pnTitle.php in "includes", IE7 ie7-standard-p.js in "includes/ie7", 
and a spacer image used by the Transparency filter in "images/spacer.gif". The package already has this folder structure, so if it was unpackaged in the PostNuke root, things should be in their proper place:

  images/spacer.gif
  includes/pnTitle.php
  includes/ie7/ie7-standard-p.js
  modules/Xanthia/plugins/
      function.ie7.php
      function.meta.php
      function.title.php

Then change your Xanthia theme templates to use metatags like these: 
[code]
    <title><!--[title]--></title>
    <meta name="Description" content="<!--[meta name="description"]-->">
    <meta name="Keywords" content="<!--[meta name="keywords"]-->">
    <meta name="Author" content="<!--[meta name="author"]-->">
    <meta name="Copyright" content="Copyright (c) 2004 by <!--[meta name="author"]-->">
    <meta http-equiv="Content-Type" content="<!--[meta name="content-type"]-->">
    <meta http-equiv="Content-Language" content="<!--[meta name="content-language"]-->">
    <meta http-equiv="Cache-Control" content="<!--[meta name="cache-control"]-->">
    <meta http-equiv="expires" content="<!--[meta name="expires"]-->">
    <meta name="Robots" content="<!--[meta name="robots"]-->">
    <meta name="Revisit-After" content="1 days">
    <meta name="Distribution" content="Global">
    <meta name="Generator" content="PostNuke - http://postnuke.com">
    <meta name="Rating" content="General">
    <link rel="icon" href="<!--[$imagepath]-->/icon.png" type="image/png">
    <link rel="shortcut icon" href="<!--[$imagepath]-->/favicon.ico">
    <link rel="StyleSheet" href="<!--[$themepath]-->/style/styleNN.css" type="text/css">
    <!--[ie7]-->
[/code]
All the available Metatags are listed above in the Options section.
There may be additional tags for links, styles, and scripts below this in the theme, be sure to leave those in.

The IE7 plugin supports the option suffix, which sets a suffix for PNG files which are to have 
the transparency filter applied to them. The default if none is supplied is "-trans", or by setting 
the variable $PNGsuffix in NukeWrapper. Because it uses a proprietary MicroSoft filter called 
AlphaImageLoader to enable the transparency in PNG files, which has its limitations, using a suffix 
only with those PNG files that has transparency means the other PNG files are unchanged. 

Example:
    <!--[ie7 suffix='-trans']-->
    applies transparency filter to PNG images with the suffix '-trans', as in image-trans.png


LAYOUT OPTIONS
==============
To use the extra layout options (idx 2-4) above, you must edit the themes you use.

For conventional PostNuke themes:
At the top of the "themefooter" function in the theme,  
the Right blocks are displayed if $index==1 (note double '=').  
Change the line
if ($index==1) {     to
if ($index==1 || $index==2 || $index==3) {
This should surround the TD table cell code.
Now you can use idx=2 in the URL, and the HTML page will be rendered with left
and right blocks without centre blocks or Admin message. 
To use the idx=3 or 4 option to allow for no left column, the table cell for the 
left column (where blocks('left'); appears, just BEFORE the themefooter function)
must be wrapped in an IF statement. This requires a bit more understanding of 
the table layout, but basically the leftmost table cell should be surrounded with 
if ($index != 3 && $index != 4) {
... {left column HTML here} ...
}
so it displays UNLESS $index is 3 or 4.
Example:
    <?PHP if ($index != 3 && $index != 4) { ?> 
    <TD id="LeftCol" width="150" bgcolor="#D9DCC2" align="center" valign="top">
    <!-- Begin left block --> 
    <?PHP    blocks('left'); ?>
    <!-- End left block -->
    </TD>
    <?PHP } ?>              

and the Right column code would look something like this:
    if ($index==1 || $index==2 || $index==3) { ?>
    <TD id="RightCol" align="center" valign="top" width="150">
    <!-- Begin right block -->
    <?PHP   blocks('right'); ?>
    <!-- End right block -->
    </TD>
    <?PHP } ?>

FOR AUTOTHEME-LITE 0.6 THEMES:
Edit the theme.php file in the theme directory (in AT 0.7, it's modules/AutoTheme/autotheme.php), and find the line that reads
    include($ThemePath."/theme.cfg");

and add below it (AT 0.7: substitute $block_display with $blockdisplay):
    if ($index==1 || $index==2 || $index==3) $block_display["right"]=true;
    if ($index != 3 && $index != 4) $block_display["left"]=true;
    if ($index==4) { $block_display["right"]=false; $block_display["left"]=false; }

This alters the column settings in the theme config file (theme.cfg) based on the idx variable set in the URL.

Now, editing the main HTML template theme.html, find the left and right table cells, and wrap them in IF statements checking for $block_display["left"] and $block_display["right"] respectively 
(AT-0.7: $blockdisplay["left"] and $blockdisplay["right"]):

Left column:
    <?PHP if ($block_display["left"]) { ?> 
    <TD id="LeftCol" width="180" valign="top" bgcolor="#EEEEEE"> 
    <!-- [left-blocks] -->
    </TD>
    <?PHP } ?>

Right column:
    <?PHP if ($block_display["right"]) { ?>
    <TD id="RightCol" width="180" valign="top" bgcolor="#EEEEEE"> 
    <!-- [right-blocks] -->
    </TD>
    <?PHP } // End right blocks ?>

Examples of themes that have been fixed to support the layout options:
    http://users.tpg.com.au/staer/Downloads/PostNukeBlue100.zip
    http://users.tpg.com.au/staer/Downloads/SeaBreeze-V.zip
    http://users.tpg.com.au/staer/Downloads/PostNuke-V.zip
    http://users.tpg.com.au/staer/Downloads/Bitrate2.zip
    http://users.tpg.com.au/staer/Downloads/Helius.zip
    http://users.tpg.com.au/staer/Downloads/Icey-V.zip
    http://users.tpg.com.au/staer/Downloads/i-geom.zip (AutoTheme)

They also support the layout options on their own without the use of NukeWrapper. Pass the idx variable in the URL the same as with NukeWrapper.

To further convert a theme to support the layout options on their own, independent of NukeWrapper like the above themes, as well as including the ability to make presets for certain modules, add the following at the top of the theme.php file, before the beginning of any functions (same for AutoTheme Lite 0.6, in AT 0.7 you must edit the module itself in modules/AutoTheme/autotheme.php at the same place):

    global $index;
    $CurrentMod = strtolower(pnModGetName('currentMod'));
    // To default certain modules to certain layouts, uncomment the line bolow and set $index at the end.
    // if ($CurrentMod == "pnphpbb2" or $CurrentMod == "xforum" or $CurrentMod == "gallery")  $index = 4; 
    if (phpversion() < "4.1.0") { $_REQUEST = array_merge($HTTP_GET_VARS, $HTTP_POST_VARS); }
    if (isset($_REQUEST['idx'])) $index=$_REQUEST['idx'];
