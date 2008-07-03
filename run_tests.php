<?php
/**
 * Test cases for GeSHi - Generic Syntax Highlighter
 *
 * This file highlights all code snippets in the samples folder with GeSHi. You can either
 * access it through a webserver & browser to check if highlighting is done correctly. Or
 * you access this file from CLI to check for code errors, to trace it or simply to measure
 * performance.
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
 * @subpackage core
 * @author     Milian Wolff <mail@milianw.de>
 * @copyright  (C) 2008 Milian Wolff
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */

// output as much as we can
error_reporting(E_ALL | E_STRICT | E_NOTICE);

/** path to the language snippets **/
define('CODEREPO_PATH', dirname(__FILE__) . '/coderepo/');

/** path to geshi **/
define('GESHI_PATH', dirname(__FILE__) . '/profiling/geshi-trunk/');

/** wether this file is accessed through CLI or not **/
define('CLI_MODE', defined('STDIN'));

/** we might want to trace this file **/
define('_TRACE_', (CLI_MODE && in_array('--trace', $_SERVER['argv'])));

/** only profile when we have PHP5 and don't trace **/
define('MAY_PROFILE', ! _TRACE_ && version_compare(PHP_VERSION, '5.0.0', '>'));

if (MAY_PROFILE) {
    include "profile.class.php";
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

if (_TRACE_) {
  xdebug_start_trace(__FILE__, XDEBUG_TRACE_COMPUTERIZED);
}

MAY_PROFILE && profile::start('overall');

MAY_PROFILE && profile::start('include GeSHi');
include GESHI_PATH . 'geshi.php';
MAY_PROFILE && profile::stop();

MAY_PROFILE && profile::start('setup GeSHi');
$GeSHi = new GeSHi("", "php");
$GeSHi->enable_strict_mode(true);
$GeSHi->set_header_type(GESHI_HEADER_DIV);
$GeSHi->enable_classes();
$GeSHi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
$GeSHi->set_line_style('background: #f0f0f0;', 'background: #fcfcfc;', true);
$GeSHi->set_header_type(GESHI_HEADER_DIV);
$GeSHi->set_highlight_lines_extra_style("background-color: #ccc;");
MAY_PROFILE && profile::stop();

MAY_PROFILE && profile::start('stylesheets');
$stylesheets = '';
foreach ($languages as $lang) {
    $GeSHi->set_language($lang);
    $stylesheets .= '<style type="text/css">' . $GeSHi->get_stylesheet() . "</style>\n";
}
MAY_PROFILE && profile::stop();

echo '<'.'?xml version="1.0" encoding="utf-8" ?'.'>'; ?>
<!DOCTYPE html PUBLIC "-//W3C//DTD XHTML 1.0 Strict//EN" "http://www.w3.org/TR/xhtml1/DTD/xhtml1-strict.dtd">
<html xmlns="http://www.w3.org/1999/xhtml" lang="en" xml:lang="en">
<head>
  <title>GeSHi v.<?php echo GESHI_VERSION ?> Test page</title>
  <meta http-equiv="Content-Type" content="text/html;charset=utf-8" />
  <style type="text/css">
  .de1, .de2 {border-left: 1px #888 solid; }
  * > ol {border: 1px black solid; background-color: #ddd; }
  li { margin-left: 10px; }
  </style>
  <?php echo $stylesheets; ?>
</head>
<body>
<?php

// highlight samples
$samples = opendir(CODEREPO_PATH);
$speeds = array();
$mem_peaks = array();
$x = 0;
while (false !== $file = readdir($samples)) {
    if ($file[0] == '.' || strpos($file, '.') === false) {
        continue;
    }
    $pkey = $file . '(file #'. $x .')';
    $lang = substr($file, 0, strrpos($file, '.'));
    MAY_PROFILE && profile::start($pkey);
    $GeSHi->set_language($lang);
    $GeSHi->set_source(file_get_contents(CODEREPO_PATH . $file));
    $src = $GeSHi->parse_code();
    MAY_PROFILE && profile::stop();

    echo "<hr /><p>" . $GeSHi->get_language_name();

    if (MAY_PROFILE) {
        // speed calculation
        $profile_results = profile::get_last_results();
        $speeds[$pkey] = profile::format_size(filesize(CODEREPO_PATH . $file) / ($profile_results[1] - $profile_results[0])) . '/s';
        // mem_peak
        $mem_peaks[$pkey] = profile::format_size(memory_get_peak_usage());
        echo " proccessed at ". $speeds[$pkey] ." | mem peak so far: ". $mem_peaks[$pkey];
    }
    echo '</p>';
    echo $src;
    ++$x;
}
unset($src, $profile_results);
MAY_PROFILE && profile::stop();

if (_TRACE_) {
  xdebug_stop_trace();
}

echo '<pre>';
global $calls;

if (!empty($__calls)) {
    echo 'calls: ' . $__calls . "\n";
}

if (MAY_PROFILE) {
    echo profile::print_results(profile::flush(true), true, array('Speeds' => $speeds, 'Mem Peaks' => $mem_peaks)) . "\n";
    echo "\n\n" . round(memory_get_peak_usage() / 1024, 2).' KB Memory Peak';
}

echo '</pre>';
?>
<p>
    <a href="http://validator.w3.org/check?uri=referer">
        <img src="http://www.w3.org/Icons/valid-xhtml10-blue" alt="Valid XHTML 1.0 Strict" height="31" width="88" />
    </a>
</p>
</body>
</html>

