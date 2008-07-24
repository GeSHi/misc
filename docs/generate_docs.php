<?php
/**
 * Generate the geshi-doc.html and geshi-doc.txt files for GeSHi
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
 * @subpackage docs
 * @author     Milian Wolff <mail@milianw.de>
 * @copyright  (C) 2008 Milian Wolff
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 *
 */

chdir(dirname(__FILE__));

require 'doc-markdown.php';
require '../profiling/geshi-trunk/geshi.php'; // for version...

$parser = new DocMarkdown;

$documentation = file_get_contents('geshi-doc.text');

$documentation = $parser->transform($documentation);

ob_start();
require 'template.php';
$documentation = ob_get_contents();
ob_end_clean();

file_put_contents('geshi-doc.html', $documentation);
