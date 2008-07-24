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
    private $geshi_parsers = array();
    private $geshi_language_rx = false;
    public $styles = '';
    /**
     * simple constructor which calls the old MarkdownExtra_Parser
     * and sets some GeSHi related stuff up
     *
     * @param void
     */
    public function __construct() {
        parent::MarkdownExtra_Parser();

        $dir = opendir('../profiling/geshi-trunk/geshi/');
        $languages = array();
        while (false !== $file = readdir($dir)) {
            if ( $file[0] == '.' || strpos($file, '.', 1) === false) {
                continue;
            }
            $lang = substr($file, 0,  strpos($file, '.'));
            $languages[] = $lang;
        }
        closedir($dir);
        $temp = new GeSHi('', '');
        $this->geshi_language_rx = implode('|', $temp->optimize_regexp_list($languages, '#'));
    }
    public function transform($text) {
        /** various replacements before */
        // <note> and <caution>
        $text = preg_replace('#<note>(.+)</note>#Us',
                          '<div class="note" markdown="1"><div class="note-header">Note:</div>\1</div>', $text);
        $text = preg_replace('#<caution>(.+)</caution>#Us',
                          '<div class="caution" markdown="1"><div class="caution-header">Caution:</div>\1</div>', $text);

        // language highlighting
        $text = preg_replace_callback('#<((block)?('.$this->geshi_language_rx.'))>(.+)</\1>#Us', array($this, 'highlight'), $text);

        /** actual markdown parser */
        $text = parent::transform($text);

        /** replacements after */
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
    /**
     * highlight <html>...</html> or <php>...</php> stuff.
     *
     * this is a callback - don't call it manually!
     *
     * @param array $matches
     * @return string highlighted code
     */
    private function highlight($matches) {
        $lang = $matches[3];
        $is_block = $matches[2] == 'block';

        if (!isset($this->geshi_parsers[$lang])) {
            $this->geshi_parsers[$lang] = new GeSHi('', $lang);
            $geshi =& $this->geshi_parsers[$lang];
            $geshi->enable_classes();
            $this->styles .= $geshi->get_stylesheet(false) . "\n";;
        } else {
            $geshi =& $this->geshi_parsers[$lang];
        }
        if ($is_block) {
            $geshi->set_header_type(GESHI_HEADER_PRE_VALID);
            $geshi->enable_line_numbers(GESHI_FANCY_LINE_NUMBERS, 2);
            $geshi->set_header_content('<LANGUAGE> code');
        } else {
            $geshi->set_header_type(GESHI_HEADER_NONE);
            $geshi->enable_line_numbers(GESHI_NO_LINE_NUMBERS);
        }

        $geshi->set_source($matches[4]);

        return $geshi->parse_code();
    }
}