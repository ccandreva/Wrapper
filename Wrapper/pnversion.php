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

$modversion['name'] = 'Wrapper';
$modversion['version'] = '0.0';
$modversion['description'] = 'Wraps local and external pages in Zikula site';
$modversion['credits'] = 'documents/credits.txt';
$modversion['help'] = 'documents/Wrapper.html';
$modversion['changelog'] = 'documents/changelog.txt';
$modversion['license'] = 'documents/license.txt';
$modversion['official'] = 0;
$modversion['author'] = 'Martin Andersen';
$modversion['contact'] = 'msandersen at tpg.com.au';
$modversion['admin'] = 1;
$modversion['securityschema'] = array('Wrapper::file|url' => 'filename.ext:keyword:extension');
?>
