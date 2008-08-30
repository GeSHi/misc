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
 * what this script does. It generates a folder which contains several tracefiles
 * (one for each included script) with a new format (@see TRACE_FORMAT)
 *
 * time_before    time_after    timediff    memory_before    \
 * memory_after    memorydiff    loc    function
 *
 * -------------
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
 * we use a line buffer and write only after we have processed
 * LINE_BUFFER lines. HDD access is generally slow and memory cheap.
 */
define('LINE_BUFFER', 1000);

/**
 * this is the format for the new tracefile
 *   time_before    time_after    timediff    memory_before    \
 *   memory_after    memorydiff    loc    function
 */
define('TRACE_FORMAT', '%f    %f    %f    %d    %d    %+d    %d    %s');

// validate user input
// @todo: rewrite this part and use proper GetOpt style flags to handle
//        outputdir, removefromtrace, etc. etc.
if (!isset($_SERVER['argv'][1])) {
    die("usage: ". basename(__FILE__) ." TRACEFILE\n".
        "or:    ". basename(__FILE__) ." TRACEFILE REMOVEFROMTRACE ...\n");
}

$tracefile = $_SERVER['argv'][1];

if (!is_readable($tracefile) || !($input = fopen($tracefile, 'r'))) {
    die("cannot read tracefile `$tracefile`\n");
}

// get output path
// @todo: make it user definable via -outputdir DIR
$outputdir = $tracefile.'_preprocessed';

if (!is_dir($outputdir)) {
    if (file_exists($outputdir)) {
        trigger_error('Potential name-clash: File exists with name of output directoy "'.$outputdir.'"', E_USER_ERROR);
    } elseif (!mkdir($outputdir, 0755)) {
        trigger_error('Could not create output directoy "'.$outputdir.'"', E_USER_ERROR);
    }
} else {
    do {
        echo "Outputdir '$outputdir' exists and is possibly not empty. Purge contents and continue?\n[y/N]:  ";
        $answer = strtolower(trim(fgets(STDIN)));
        if (empty($answer)) {
            $answer = 'n';
        }
    } while (!in_array($answer, array('y', 'n')));
    if ($answer == 'n') {
        die("...aborting\n");
    }
    $dir = opendir($outputdir);
    while (false !== ($file = readdir($dir))) {
        if ($file[0] != '.' && is_file($outputdir.'/'.$file)) {
            unlink($outputdir .'/'. $file);
        }
    }
    closedir($dir);
}

// one file handle per included file in the trace
$output = array();

$line_costs = array();
$x = 0;
$lines = array(); // buffer per output file
$start_stack = array();
$level_time_costs = array(); // we don't want parent functions to include the timediffs of their children

$paths = array();
$path_key = 0;

// @todo: properly sort the output
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
        if ($line[9] === 0) {
          // assume this is a callback thus set the linenumber to the last remembered
          $line[9] = $start_stack[$line[0] - 1][9];
        }
        if (!$line[9] && $line[9] !== 0) {
            fclose($input);
            foreach ($output as &$handle) {
                fclose($handle);
            }
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

        $timediff = $line[3] - $start[3];

        // simple code coverage and total time cost for given lines
        if (!isset($line_costs[$start[8]][$start[9]])) {
            $line_costs[$start[8]][$start[9]] = array(
              'hits' => 1,
              'time' => $timediff
            );
        } else {
            ++$line_costs[$start[8]][$start[9]]['hits'];
            $line_costs[$start[8]][$start[9]]['time'] += $timediff;
        }

        // cumulate timediff for this level
        if (!isset($level_time_costs[$line[0]])) {
            $level_time_costs[$line[0]] = 0;
        }
        $level_time_costs[$line[0]] += $timediff;

        // reduce timediff by cumulated timediff of child level
        if (isset($level_time_costs[$line[0] + 1])) {
          $timediff -= $level_time_costs[$line[0] + 1];
        }
        // the child level must be resetted now
        $level_time_costs[$line[0] + 1] = 0;

        if (!isset($lines[$start[8]])) {
            $lines[$start[8]] = '';
            // when we are at it, also setup the filehandle
            // we don't want horribly long file names, what we do is group by dirname:
            // /foo/bar/etc/file.php => 1.file.php
            // /foo/asdfasd/file.php => 2.file.php
            $dirname = dirname($start[8]);
            if (!isset($paths[$dirname])) {
                $path_key++;
                $paths[$dirname] = sprintf('%\'03d', $path_key);
            }
            $key = $paths[$dirname];

            $output_file = $outputdir . '/' . $key . '.'. basename($start[8]) .'.trace';
            $output[$start[8]] = fopen($output_file , 'w');
            if (!$output[$start[8]]) {
                trigger_error('Failed to open file "'.$output_file.'" for writing', E_USER_ERROR);
            }
        }
        // time_before    time_after    timediff    memory_before    memory_after    memorydiff    loc    function    path
        $lines[$start[8]] .= sprintf(TRACE_FORMAT,
            $start[3],
            $line[3],
            $timediff,
            $start[4],
            $line[4],
            $line[4] - $start[4],
            $start[9],
            $start[5],
            $start[8]
        ) . "\n";
        unset($start_stack[$line[0]], $start);

        // buffer lines and write LINE_BUFFER lines in one go
        ++$x;
        if ($x == LINE_BUFFER) {
            foreach ($lines as $file => &$sub_lines) {
                fwrite($output[$file], $sub_lines);
                $sub_lines = '';
            }
            $x = 0;
        }
    }
}
unset($level_time_costs);
// clean up this mess
fclose($input);
foreach ($lines as $file => &$sub_lines) {
    fwrite($output[$file], $sub_lines);
    fclose($output[$file]);
}
unset($lines, $output);

foreach ($line_costs as $file => &$per_file_line_costs) {
    // write iterations to file
    $outputfile = $outputdir .'/' . $paths[dirname($file)] . '.' . basename($file) . '.coverage';
    $output = fopen($outputfile, 'w');
    if (!$output) {
        trigger_error('Could not open file "'.$outputfile.'" for writing', E_USER_ERROR);
    }

    ksort($per_file_line_costs);
    foreach ($per_file_line_costs as $line => &$cost) {
        fprintf($output, "%-6d    %-6d    %f\n", $line, $cost['hits'], $cost['time']);
    }
    fclose($output);
}
unset($line_costs);

$path_file = fopen($outputdir.'/paths.txt', 'w');
foreach ($paths as $path => $key) {
    fwrite($path_file, $key."\t".$path."\n");
}
fclose($path_file);
