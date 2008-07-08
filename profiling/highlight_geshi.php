<?php
/**
 * highlight geshi.php with geshi, optionally trace or do some profiling
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
error_reporting(E_ALL | E_NOTICE);

if (isset($_SERVER['argv'])) {
  // all paths are relative to this file
  chdir(dirname($_SERVER['argv'][0]));
}

/**
 * path to the geshi file we include & highlight
 * TODO: make it settable via CLI to make tracing of older releases possible
 */
define('GESHI_FILE', 'geshi-trunk/geshi.php');

if (isset($_SERVER['argv']) && $key = array_search('--iterations', $_SERVER['argv'])) {
  $iterations = intval($_SERVER['argv'][$key + 1]);
} else {
  $iterations = 5;
}

$GLOBALS['dont_auto_trace'] = true;
include 'lib.php';

if (_TRACE_) {
  // when tracing, restrict to one iteration
  $iterations = 1;
}

include 'geshi-trunk/geshi.php';

profile::start('overall');

echo 'Memory usage before:  '. profile::format_size(xdebug_memory_usage(), 2) . "\n";

for ($i = 1; $i <= $iterations; ++$i) {
  profile::start('run #'.$i);
  if (_TRACE_) {
      xdebug_start_trace(__FILE__);
  }

  $G = new GeSHi("", "php");
  $G->enable_strict_mode(false);
  $G->enable_classes();
  $G->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
  $G->set_header_type(GESHI_HEADER_PRE_VALID);
  $style = $G->get_stylesheet(true);
  $G->load_from_file(GESHI_FILE);
  $src = $G->parse_code();

  if (_TRACE_) {
      xdebug_stop_trace();
  }
  profile::stop();

  unset($G, $src, $style);
}

profile::stop();

$results = profile::flush(true);
array_push($results, array(
  'name' => 'average',
  'deviation' => '-/-',
  'start' => 0,
  'end' => 0,
  'diff' => $results[0]['diff'] / $iterations,
  'mem_deviation' => '-/-',
  'mem_start' => 0,
  'mem_end' => 0,
  'mem_diff' => 0
));

profile::print_results($results);
echo "Peak memory usage:   " . profile::format_size(memory_get_peak_usage(), 2) . "\n";
echo "Average speed:       " . profile::format_size(filesize(GESHI_FILE) / ($results[0]['diff'] / $iterations), 2) ."/s\n";
echo "Memory usage after:  " . profile::format_size(memory_get_usage(), 2) . "\n";
