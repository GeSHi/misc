#!/usr/bin/php
<?php
/**
 * preprocess_xt.php - prepare your xdebug tracefile (machine readable format!)
 *                     for further evaluation, e.g. plotting
 *
 * Xdebug can generate trace files of your PHP scripts, e.g. with
 * xdebug_start_trace(__FILE__) you will get a file called "__FILE__".xt
 * with quite some interesting numbers in it. When you set xdebug.trace_format
 * to 1 (i.e. computer readable format) you'll need a script to preprocess
 * the tracefile before it can be evaluated by plotting or similar. This is
 * what this script does. Your old trace file will be replaced with the
 * preprocessed data in the following order @see TRACE_FORMAT:
 *
 * time_before    time_after    timediff    memory_before    memory_after    memorydiff    loc    function    path
 *
 * @attention your old tracefile will be replaced!
 *
 * To simply calculate memory and time differences do
 * @example ./preprocess_xt.php foobar.php.xt
 * @note This also generates a second file with code coverage data
 *       i.e. how often got line X called
 *
 * To additionally exclude a given file do something like this:
 * @example ./preprocess_xt.php foobar.php.xt foobar.php
 *
 * preprocess_xt.php is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * preprocess_xt.php is distributed in the hope that it will be useful,
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

/**
 * we use a line buffer and write only when we have processed
 * LINE_BUFFER lines.
 */
define('LINE_BUFFER', 1000);

/**
 * this is the format for the new tracefile
 */
define('TRACE_FORMAT', '%f    %f    %f    %d    %d    %+d    %d    %s    %s');

// validate user input
if (!isset($_SERVER['argv'][1])) {
    die("usage: ". basename(__FILE__) ." TRACEFILE\n".
        "or:    ". basename(__FILE__) ." TRACEFILE REMOVEFROMTRACE ...\n");
}

$tracefile = $_SERVER['argv'][1];

if (!is_readable($tracefile)) {
    die("cannot read tracefile `$tracefile`\n");
}

// we might want to remove a set of files from the trace
$remove_paths = array();
if (!empty($_SERVER['argv'][2])) {
    $remove_paths = $_SERVER['argv'];
    unset($remove_paths[0], $remove_paths[1]);
}

$input = fopen($tracefile, 'r');
$tmp = tempnam(dirname($tracefile), $tracefile . '_');
$output = fopen($tmp, 'w');

$lines_touched = array();
$x = 0;
$lines = '';
$start_stack = array();

while ($line = fscanf($input, '%d %d %d %f %d %s %d %s %s %d')) {
    if (is_null($line[1])) {
        // this seems to be junk
        continue;
    }
    /*
     0 => level
     1 => function #
     2 => bool: entry or exit
     3 => time index
     4 => memory usage
     -- below only for when entering a function --
     5 => function name
     6 => bool: user-defined or interal function
     7 => name of the include/require file
     8 => filename
     9 => line number
     */
    if (!$line[2]) { // enter function
        // lambda functions (anonymous functions) need special handling
        // their syntax is: file.php(LINE) in key 7
        if ($line[5] == '__lambda_func') {
            $pos = strrpos($line[7], '(');
            $line[9] = (int) substr($line[7], $pos + 1, -1);
            $line[8] = substr($line[7], 0, $pos);
            $line[7] = '';
        } elseif (is_null($line[9])) {
            // bullshit format... require/include functions use column 7 for the included file
            // all other functions will have an _nothing_ there, that's why fscanf()
            // cannot set the keys appropriatly...
            $line[9] = (int) $line[8];
            $line[8] = $line[7];
            $line[7] = null;
            if ($line[9] == 0 && ($pos = strrpos($line[8], '(')) !== false) {
                // dunno why, but _sometimes_ this has a different syntax
                $line[9] = (int) substr($line[8], $pos + 1, -1);
                $line[8] = substr($line[8], 0, $pos);
            }
        }
        if (in_array(basename($line[8]), $remove_paths)) {
            // we don't like this path, skip it
            continue;
        }
        // simple code coverage
        if (!isset($lines_touched[$line[9]])) {
            $lines_touched[$line[9]] = 1;
        } else {
            ++$lines_touched[$line[9]];
        }
        if (!$line[9]) {
            fclose($input);
            fclose($output);
            unlink($tmp);
            trigger_error("Bad line number given, that should not happen:\n". print_r($line, true), E_USER_ERROR);
            die();
        }
        // now add it this to the stack
        $start_stack[$line[0]] = $line;
    }
    else { // exit function
        if (!isset($start_stack[$line[0]])) {
            // path got skipped
            continue;
        }
        $start = $start_stack[$line[0]];
        // time_before    time_after    timediff    memory_before    memory_after    memorydiff    loc    function    path
        $lines .= vsprintf(TRACE_FORMAT, array(
            $start[3],
            $line[3],
            $line[3] - $start[3],
            $start[4],
            $line[4],
            $line[4] - $start[4],
            $start[9],
            $start[5],
            $start[8]
        )) . "\n";
        unset($start_stack[$line[0]], $start);
        // buffer lines and only write $x lines in one go
        ++$x;
        if ($x == LINE_BUFFER) {
            fwrite($output, $lines);
            $x = 0;
            $lines = '';
        }
    }
}

// clean up this mess
if (!empty($lines)) {
    fwrite($output, $lines);
}
unset($lines);

fclose($input);
fclose($output);

// we don't need the old file any longer
rename($tmp, $tracefile);

// write iterations to file
$outputfile = substr($tracefile, 0, strrpos($tracefile, '.')) . '_coverage.data';
$output = fopen($outputfile, 'w');

$keys = array_keys($lines_touched);

for ($i = 0, $n = count($keys); $i < $n; ++$i) {
  fwrite($output, $keys[$i] ."\t". $lines_touched[$keys[$i]] . "\n");
}