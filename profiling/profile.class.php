<?php
/**
 * profile.class.php - A timer class for qualitative profiling
 *
 * This is a static class which you can use to qualitatively compare and
 * profile parts of your PHP code. It is as simple as
 *
 * <?php ...
 *   require 'profile.class.php';
 *   ...
 *   profile::start('some name');
 *   ...
 *   profile::stop();
 *   ...
 *   profile::print_results(profile::flush());
 * ?>
 *
 * The class itself should be self explaining and well documented. Take a look below!
 *
 *
 *
 * profile.class.php is free software; you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation; either version 2 of the License, or
 * (at your option) any later version.
 *
 * profile.class.php is distributed in the hope that it will be useful,
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

class profile {
  // list of start and end timestamps
  static private $start = array();
  static private $end = array();
  // list of start and end memory footprints
  static private $mem_before = array();
  static private $mem_after = array();
  // current names
  static private $running_profiles = array();
  static private $cur_name;
  static private $last_name;
  // only used for sorting
  static private $sort_by_key;
  // custom measurements
  static private $measurements = array();
  static private $measurement_keys = array();
  /**
   * start the profile timer
   *
   * @param $name optional name for this profile
   */
  static public function start($name = 0) {
    if (!is_null(self::$cur_name)) {
      // nested timer
      // add old name to running profiles
      array_push(self::$running_profiles, self::$cur_name);
      if (self::$cur_name === $name) {
        if (is_int($name)) {
          $name++;
        } else {
          $name .= '-SUB';
        }
      }
    }
    if (!isset(self::$start[$name])) {
      // init
      self::$start[$name] = array();
      self::$end[$name] = array();
      self::$mem_before[$name] = array();
      self::$mem_after[$name] = array();
    }
    self::$cur_name = $name;
    // remember current memory footprint
    array_push(self::$mem_before[$name], memory_get_usage());
    // start timer
    array_push(self::$start[$name], microtime(true));
  }
  /**
   * stop the profile timer
   */
  static public function stop() {
    // stop timer
    array_push(self::$end[self::$cur_name], microtime(true));
    // remember current memory footprint
    array_push(self::$mem_after[self::$cur_name], memory_get_usage());

    self::$last_name = self::$cur_name;
    if (!empty(self::$running_profiles)) {
      // got a parent timer (nested timer)
      self::$cur_name = array_pop(self::$running_profiles);
    } else {
      self::$cur_name = null;
    }
  }
  /**
   * get last start/end values
   *
   * @return array[start, end, mem_before, mem_after]
   */
  static public function get_last_results() {
      if (is_null(self::$last_name)) {
        trigger_error('No timer was started yet.', E_USER_ERROR);
        return;
      }
      return array(
        end(self::$start[self::$last_name]),
        end(self::$end[self::$last_name]),
        end(self::$mem_before[self::$last_name]),
        end(self::$mem_after[self::$last_name])
      );
  }
  /**
   * get the results and reset internal result cache
   *
   * @param $reverse boolean; calculate deviation to longest diff, defaults to false (shortest diff)
   * @return array of results
   */
  static public function flush($reverse = false) {
    if (!is_null(self::$cur_name)) {
      trigger_error('A timer is still running. Stop it before flushing the results by calling profile::stop().', E_USER_ERROR);
      return;
    }
    if (empty(self::$start)) {
      return array();
    }

    $results = array();
    // reset vars
    $start = self::$start;
    $end = self::$end;
    $mem_after = self::$mem_after;
    $mem_before = self::$mem_before;
    $measurement_keys = self::$measurement_keys;
    $measurements = self::$measurements;
    self::$start = array();
    self::$end = array();
    self::$mem_after = array();
    self::$mem_before = array();
    self::$cur_name = null;
    self::$measurement_keys = array();
    self::$measurements = array();

    $results = array();
    $diffs = array();
    $mem_diffs = array();

    // get runtimes
    $names = array_keys($start);
    $deviate_key = $names[0];

    foreach ($names as $key) {
      $diffs[$key] = array_sum($end[$key]) - array_sum($start[$key]);
      if (($reverse && $diffs[$key] > $diffs[$deviate_key])
          || (!$reverse && $diffs[$key] < $diffs[$deviate_key])) {
        // remember which run we take as reference point to calculate deviations
        $deviate_key = $key;
      }
      $mem_diffs[$key] = array_sum($mem_after[$key]) - array_sum($mem_before[$key]);
    }

    if ($reverse) {
      arsort($diffs);
    } else {
      asort($diffs);
    }

    // calculate percental deviations and build up return array
    foreach ($diffs as $name => $diff) {
      $result = array(
        'name' => $name,
        'diff' => $diff,
        'start' => $start[$name],
        'end' => $end[$name],
        'deviation' => ($diffs[$name] / $diffs[$deviate_key]) * 100,
        'mem_diff' => $mem_diffs[$name],
        'mem_before' => $mem_before[$name],
        'mem_after' => $mem_after[$name],
        'mem_deviation' => ($mem_diffs[$name] * count($mem_before[$name]) / array_sum($mem_before[$name])) * 100,
      );
      // add custom measurements
      foreach (array_keys($measurement_keys) as $key) {
        if (isset($measurements[$name][$key])) {
            $result[$key] = $measurements[$name][$key];
        } else {
            $result[$key] = false;
        }
      }
      array_push($results, $result);
    }

    return $results;
  }
  /**
   * add custom measurement to this profile entry
   *
   * @param $key   string
   * @param $value mixed
   */
  function add_measurement($key, $value, $name = null) {
      if (is_null($name)) {
          if (is_null(self::$cur_name)) {
              trigger_error('No timer is currently running.', E_USER_ERROR);
          }
          $name = self::$cur_name;
      }
      self::$measurements[$name][$key] = $value;
      self::$measurement_keys[$key] = true;
  }
  /**
   * sort result set by entries in col $key
   *
   * @param &$results  the results
   * @param $key       the assoc key by which the results will be sorted
   * @return sorted results
   */
  function sort_results($results, $key, $revert = false) {
      self::$sort_by_key = $key;
      usort($results, array('profile', '_sort_callback'));
      if ($revert) {
          $results = array_reverse($results);
      }
      self::$sort_by_key = null;
      return $results;
  }
  /**
   * the usort callback for sort_results
   *
   * do not call manually!
   */
  function _sort_callback($a, $b) {
      if (is_string($a)) {
          return strcmp($a[self::$sort_by_key], $b[self::$sort_by_key]);
      } elseif (is_array($a[self::$sort_by_key])) {
          return ($a[self::$sort_by_key][0] < $b[self::$sort_by_key][0]) ? -1 : 1;
      } else {
          return ($a[self::$sort_by_key] < $b[self::$sort_by_key]) ? -1 : 1;
      }
      return 0;
  }
  /**
   * default implementation as to how one could present the results, optimized for CLI usage
   *
   * @param $results       an array as returned by profile::flush()
   * @param $dont_print    optionally; only return the result and dont print it
   * @param $merge_titles  an array of measurement key => title
   * @return string
   */
  static public function print_results($results, $dont_print = false, $merge_titles = array()) {
    $output = "\n=== profile results ===\n";
    if (empty($results)) {
      $output = "\tno code was profiled, empty resultset\n";
    } else {
      // get maximum col-width:
      $max_col_width = 0;
      $n_results = count($results);
      for ($key = 0, $n_results; $key < $n_results; ++$key) {
        $max_col_width = max($max_col_width, strlen($results[$key]['name']));
      }
      $columns = array(
        'Timer' => $max_col_width,
        'Time Diff' => 14,
        'Time Deviation' => 14,
        'Mem Diff' => 14,
        'Mem Deviation' => 14
      );
      if (!empty($merge_titles)) {
          // get merge titles
          foreach($merge_titles as $merge_key => $title) {
              // get max_width for this value
              $_max_width = strlen($title);
              for ($key = 0, $n_results; $key < $n_results; ++$key) {
                  $_max_width = max($_max_width, strlen($results[$key][$merge_key]));
              }
              $columns[$title] = $_max_width;
          }
      }
      // output table header
      $header = "  ";
      foreach ($columns as $title => $width) {
          $header .= '  '. $title . str_repeat(' ', $width - strlen($title)) . '  |';
      }
      $header = substr($header, 0, -1);
      $output .= "\n" . $header . "\n";
      $separator = str_repeat('-', strlen($header)) ."\n";
      $output .= $separator;
      foreach ($results as $profile) {
        $output .= sprintf("    %-". $max_col_width ."s  |    %9Fs    |     %8.2F%%    |    %11s   |    %+10.2F%% ",
                      $profile['name'], $profile['diff'], $profile['deviation'],
                      self::format_size($profile['mem_diff']), $profile['mem_deviation']);

        foreach ($merge_titles as $key => $title) {
            $output .= sprintf("  |  %". $columns[$title] . "s", $profile[$key]);
        }
        $output .= "\n";
      }
      $output .= $separator;
    }
    if (!$dont_print) {
      echo $output;
    }
    return $output;
  }
  /**
   * helper function to properly format byte sizes with appropriate suffixes
   *
   * @param
   * @return string
   */
  static public function format_size($size, $round = 4) {
    //Size must be bytes!
    $sizes = array('B', 'kB', 'MB', 'GB', 'TB', 'PB', 'EB', 'ZB', 'YB');
    for ($i = 0; $size > 1024 && isset($sizes[$i+1]); ++$i) {
      $size /= 1024;
    }
    return round($size, $round) . ' '. $sizes[$i];
  }
  /**
   * define code blocks and run them in random order, profiling each
   * this is great when writing little scripts to see which implementation of a given feature is faster
   *
   * @param $code_blocks an array with strings of php-code (just like eval or create_function accepts).
   *                     don't forget keys to give those codeblocks a name
   * @param $vars assoc array with global values which are used in the code blocks (i.e. varname => value)
   * @param $iterations number of times each block gets run
   * @return void
   */
  static public function codeblocks($code_blocks, $vars = array(), $iterations = 200) {
    if (!empty($vars)) {
      $vars_keys = '$'. implode(', $', array_keys($vars));
      $vars_values = array_values($vars);
    } else {
      $vars_keys = '';
      $vars_values = array();
    }
    $blocks = array();
    // pita to get random order
    foreach ($code_blocks as $name => $block) {
      $block = trim($block);
      if ($block[strlen($block) - 1] != ';') {
        $block .= ';';
      }
      array_push($blocks, array('name' => $name, 'code' => $block));
    }
    unset($code_blocks);
    shuffle($blocks);
    foreach ($blocks as $block) {
      $func = create_function($vars_keys, $block['code']);
      self::start($block['name']);
      for ($i = 0; $i < $iterations; ++$i) {
        call_user_func_array($func, $vars_values);
      }
      self::stop();
    }
  }
}