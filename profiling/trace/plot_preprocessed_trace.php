#!/usr/bin/php
<?php
/**
 * plot_preprocessed_trace.php - plot the results of a preprocessed Xdebug Tracefile
 *                               using GnuPlot
 *
 * @note You must preprocess the .xt tracefile Xdebug generates prior to plotting
 *       it with this function. @see preprocess_xt.php
 *
 * This PHP script reads a folder with preprocessed Xdebug trace data and populates
 * various PHP variables which are then used by the plot_xt.plt template in this
 * directory.
 * 
 * -------------
 *
 * plot_preprocessed_trace.php is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * plot_preprocessed_trace.php is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with profile.class.php; if not, write to the Free Software
 * Foundation, Inc., 59 Temple Place, Suite 330, Boston, MA  02111-1307  USA
 *
 * @author     Milian Wolff <mail@milianw.de>
 * @copyright  (C) 2008 Milian Wolff
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 */

// validate user input
// @todo: support user defined gnuplot templates
if (!isset($_SERVER['argv'][1])) {
    die("Usage:  ./plot_preprocessed_trace.php /path/to/script.php.xt_preprocessed/\n"
       ."  Note: the trace directory must be called .../SCRIPT.xt_preprocessed\n");
}

$trace_dir = rtrim($_SERVER['argv'][1], '/');


if (substr($trace_dir, strrpos($trace_dir, '.') + 1) != 'xt_preprocessed') {
    trigger_error('Trace directory must be called SCRIPT.xt_preprocessed', E_USER_ERROR);
}

if (!is_dir($trace_dir) || !is_readable($trace_dir)) {
    trigger_error('Trace directory "'.$trace_dir.'" is not readable.', E_USER_ERROR);
}

$main_script = substr(basename($trace_dir), 0, - strlen('.xt_preprocessed'));
$title = 'Trace results for "'.$main_script.'"';
$base = $trace_dir .'/all';

if (!is_readable($trace_dir.'/paths.txt')) {
    trigger_error('Path information file "'.$trace_dir.'/paths.txt" not readable.', E_USER_ERROR);
}

// get paths
$paths_tmp = file($trace_dir.'/paths.txt');
$paths = array();
foreach ($paths_tmp as $line) {
    list($id, $path) = explode("\t", trim($line), 2);
    $paths[$id] = $path;
}
unset($paths_tmp);

// get trace- and coverage files
$dir = opendir($trace_dir);

$files = array();
while (false !== ($file = readdir($dir))) {
    if ($file[0] == '.' || !is_file($trace_dir.'/'.$file) || !is_numeric(substr($file, 0, strpos($file, '.'))) ||
        substr($file, strrpos($file, '.') + 1) != 'trace') {
        // not a valid trace file
        continue;
    }
    $path_id = substr($file, 0, strpos($file, '.'));
    $script_path = $paths[$path_id];
    $script_name = substr($file, strpos($file, '.') + 1);
    $script_name = substr($script_name, 0, strrpos($script_name, '.'));
    
    $coverage_file = $path_id .'.'. $script_name . '.coverage';
    if (!is_readable($trace_dir .'/'. $coverage_file)) {
        trigger_error('Coverage file "'.$trace_dir .'/'. $coverage_file.'" not readable.', E_USER_ERROR);
    }
    
    $files[] = array(
        'trace' => $trace_dir .'/'. $file,
        'coverage' => $trace_dir .'/'. $coverage_file,
        'script_name' => $script_name,
        'title' => $title .' | File: "'. $script_path .'/'. $script_name .'"',
        'base' => $trace_dir .'/'. $path_id .'.'. $script_name,
        'id' => $path_id,
    );
}

closedir($dir);

ob_start();
require dirname(__FILE__) . '/' . 'plot_xt.plt';
$gnuplot_script = ob_get_contents();
ob_end_clean();

$tmp = tempnam(dirname(__FILE__), $main_script.'.plt');

file_put_contents($tmp, $gnuplot_script);

shell_exec('gnuplot "'.$tmp.'"');
unlink($tmp);
