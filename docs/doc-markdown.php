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
    public $toc_offset = 1;
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
        $languages[] = 'html';
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

        // don't let markdown put <toc /> inside <p> tags
        $text = str_replace('<toc />', '<div id="toc"></div>', $text);

        /** actual markdown parser */
        $text = parent::transform($text);

        /** replacements after */
        // get headers and produce table of contents (toc) and "prev up next" navigation
        preg_match_all('#(<h([0-6])[^>]*>)(.+)</h\2>#Us', $text, $headers, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        $toc = '<div id="toc">';
        $old_level = $this->toc_offset - 1;
        $counter = array();
        foreach ($headers as $header) {
            $level = $header[2][0];
            if ($this->toc_offset > $level) {
                continue;
            }
            $tag = $header[1][0];
            $content = $header[3][0];
            if ($level < $old_level) {
                $toc .= str_repeat("</li>\n</ul>", $old_level - $level) . "</li>\n";
                $counter = array_slice($counter, 0, $level);
            } elseif ($level > $old_level) {
                if ($level != $old_level + 1) {
                    trigger_error('incorrect header: '.$content, E_USER_ERROR);
                }
                $toc .= "<ul>\n";
                $counter[$level] = 1;
            } else {
                $toc .= "</li>\n";
                $counter[$level]++;
            }
            // counter
            $content = implode('.', $counter).' '.$content;
            // get ID
            if (preg_match('#id=("|\')([^>]+)\1#U', $tag, $id)) {
                $content = '<a href="#'.$id[2].'">'.$content.'</a>';
            }
            $toc .= '<li>'.$content;
            $old_level = $level;
        }
        $toc .= str_repeat("</li>\n</ul>", $level) . "\n</div>";

        $text = str_replace('<div id="toc"></div>', $toc, $text);

        return $text;
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
        if ($lang == 'html') {
            $lang = 'html4strict';
        }
        $is_block = $matches[2] == 'block';

        if (!isset($this->geshi_parsers[$lang])) {
            $this->geshi_parsers[$lang] = new GeSHi('', $lang);
            $geshi =& $this->geshi_parsers[$lang];
            $geshi->set_overall_class($geshi->overall_class . ' geshicode');
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
            $geshi->set_header_content('');
        }

        $geshi->set_source($matches[4]);

        $code = $geshi->parse_code();

        if (!$is_block) {
            $code = '<code class="'.$lang.'">'.$code.'</code>';
        }
        return $code;
    }
}