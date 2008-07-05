<?php
/**
 * make sure stylesheet generators behave correctly in regard to speed & memory
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

require 'lib.php';

require GESHI_PATH . 'geshi.php';


$GeSHi = new GeSHi("", "php");
$GeSHi->enable_strict_mode(true);
$GeSHi->set_header_type(GESHI_HEADER_DIV);
$GeSHi->enable_classes();
$GeSHi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
$GeSHi->set_line_style('background: #f0f0f0;', 'background: #fcfcfc;', true);
$GeSHi->set_header_type(GESHI_HEADER_DIV);
$GeSHi->set_highlight_lines_extra_style("background-color: #ccc;");

MAY_PROFILE && profile::start('overall');

foreach ($languages as $lang) {
    MAY_PROFILE && profile::start($lang);
    $GeSHi->set_language($lang);
    $GeSHi->get_stylesheet();
    if (MAY_PROFILE) {
        profile::add_measurement('mem_peak', profile::format_size(memory_get_peak_usage()));
    }
    MAY_PROFILE && profile::stop();
}

MAY_PROFILE && profile::stop();

if (_TRACE_) {
  xdebug_stop_trace();
}

if (MAY_PROFILE) {
    echo profile::print_results(profile::sort_results(profile::flush(true), 'mem_peak'), true, array('mem_peak' => 'Mem Peaks')) . "\n";
    echo "\n\n" . profile::format_size(memory_get_peak_usage(), 2) . ' Memory Peak';
    echo "\n" . profile::format_size(memory_get_usage(), 2) . ' Current Memory Consumption' . "\n";
}