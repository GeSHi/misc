<?php
/**
 * Library with helper functions for profiling
 *
 *   This file is part of GeSHi.
 *
 *  GeSHi is free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  GeSHi is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  You should have received a copy of the GNU General Public License
 *  along with GeSHi; if not, write to the Free Software
 *  Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @package    geshi
 * @subpackage tests
 * @author     Milian Wolff <mail@milianw.de>
 * @copyright  (C) 2008 Milian Wolff
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */

// output as much as we can
error_reporting(E_ALL | E_NOTICE);

/** path to the language snippets **/
define('CODEREPO_PATH', dirname(__FILE__) . '/../coderepo/');

/** path to geshi **/
define('GESHI_PATH', dirname(__FILE__) . '/geshi-trunk/');


/** wether this file is accessed through CLI or not **/
define('CLI_MODE', defined('STDIN'));

/** we might want to trace this file **/
define('_TRACE_', (CLI_MODE && in_array('--trace', $_SERVER['argv'])));

/** only profile when we have PHP5 and don't trace **/
define('MAY_PROFILE', ! _TRACE_ && version_compare(PHP_VERSION, '5.0.0', '>'));

if (version_compare(PHP_VERSION, '5.0.0', '>')) {
    require 'profile.class.php';
}
// get all supported languages
$dir = opendir(GESHI_PATH . 'geshi/');

$languages = array();
while (false !== $file = readdir($dir)) {
    if ( $file[0] == '.' || strpos($file, '.', 1) === false) {
        continue;
    }
    $lang = substr($file, 0,  strpos($file, '.'));
    $languages[] = $lang;
}
closedir($dir);
sort($languages);

if (_TRACE_ && empty($GLOBALS['dont_auto_trace'])) {
  xdebug_start_trace($_SERVER['argv'][0], XDEBUG_TRACE_COMPUTERIZED);
}