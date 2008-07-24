<?php
/**
 * An extended markdown extra parser which is used to parse the geshi-doc.text file
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

require 'php-markdown-extra/markdown.php';

class DocMarkdown extends MarkdownExtra_Parser {
    public function __construct() {
        parent::MarkdownExtra_Parser();
    }
    public function transform($text) {
        // various replacements before
        $text = preg_replace('#<note>(.+)</note>#Us',
                          '<div class="note" markdown="1"><div class="note-header">Note:</div>\1</div>', $text);
        $text = preg_replace('#<caution>(.+)</caution>#Us',
                          '<div class="caution" markdown="1"><div class="caution-header">Caution:</div>\1</div>', $text);

        // actual markdown parser
        $text = parent::transform($text);

        // replacements after
        $text = str_replace('<toc />', $this->get_toc($text), $text);

        return $text;
    }
    /**
     *
     * @param string $text HTML markup
     * @return string the table of contents in HTML markup
     */
    public function get_toc($text) {
        return '<strong style="color:red;">TOC TODO</strong>';
    }
}