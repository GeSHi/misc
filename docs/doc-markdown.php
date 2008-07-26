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
    public $toc_only_after = false;
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
        $toc_pos = strpos($text, '<toc />');
        if ($toc_pos !== false) {
            $text = substr_replace($text, '<div id="toc"></div>', $toc_pos, 7);
        }

        /** actual markdown parser */
        $text = parent::transform($text);

        /** replacements after */
        // get headers and produce table of contents (toc) and "prev up next" navigation
        preg_match_all('#(<h([0-6])[^>]*>)(.+)</h\2>#Us', $text, $headers, PREG_SET_ORDER | PREG_OFFSET_CAPTURE);

        if ($toc_pos !== false) {
            $toc_pos = strpos($text, '<div id="toc"></div>');
            $toc = '<div id="toc">';
            $old_level = $this->toc_offset - 1;
            $counter = array();
            $offset = 0;
            $last_k = -1;
            $top_ks = array();
            foreach ($headers as $k => $header) {
                $level = $header[2][0];
                if ($this->toc_offset > $level || ($this->toc_only_after && $header[0][1] < $toc_pos)) {
                    continue;
                }
                $tag = $header[1][0];
                $content = $header[3][0];

                if ($level < $old_level) {
                    $toc .= str_repeat("</li>\n</ul>", $old_level - $level) . "</li>\n";
                    $counter = array_slice($counter, 0, $level);
                    array_pop($top_ks);
                } elseif ($level > $old_level) {
                    if ($level != $old_level + 1) {
                        trigger_error('incorrect header: '.$content, E_USER_ERROR);
                    }
                    $toc .= "<ul>\n";
                    $counter[$level] = 1;
                    $top_ks[] = $last_k;
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

                // prev top next navigation
                // this is quick'n'dirty code, speed should not be a concern for this script...
                $nav = array();
                // prev
                if (isset($headers[$last_k])) {
                    $prev = $headers[$last_k];
                    $tag = $prev[1][0];
                    $content = $prev[3][0];
                    if (preg_match('#id=("|\')([^>]+)\1#U', $tag, $id)) {
                        $nav[] = '<a href="#'.$id[2].'">Previous</a>';
                    }
                }
                // top
                $top_k = end($top_ks);
                if ($top_k !== false) {
                    if (preg_match('#id=("|\')([^>]+)\1#U', $headers[$top_k][1][0], $id)) {
                        $nav[] = '<a href="#'.$id[2].'">Top</a>';
                    }
                }
                // next
                $next_k = $k + 1;
                while (isset($headers[$next_k])) {
                    if ($this->toc_offset > $headers[$next_k][2][0] ||
                        ($this->toc_only_after && $headers[$next_k][0][1] < $toc_pos)) {
                        ++$next_k;
                        continue;
                    }
                    if (preg_match('#id=("|\')([^>]+)\1#U', $headers[$next_k][1][0], $id)) {
                        $nav[] = '<a href="#'.$id[2].'">Next</a>';
                    }
                    break;
                }
                if (!empty($nav)) {
                    $nav = '<div class="nav">'.implode(' | ', $nav).'</div>';
                    $text = substr_replace($text, $nav, $offset + $header[0][1] + strlen($header[0][0]), 0);
                    $offset += strlen($nav);
                }
                $last_k = $k;
            }
            $toc .= str_repeat("</li>\n</ul>", $level - $this->toc_offset + 1) . "\n</div>";

            $text = str_replace('<div id="toc"></div>', $toc, $text);
        }

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
            $geshi->enable_classes();
            $this->styles .= $geshi->get_stylesheet(false) . "\n";
            $geshi->set_overall_class('geshicode');
        } else {
            $geshi =& $this->geshi_parsers[$lang];
        }
        if ($is_block) {
            $geshi->set_header_type(GESHI_HEADER_PRE_TABLE);
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
            $code = '<code class="highlighted '.$lang.'">'.$code.'</code>';
        }
        return $code;
    }
}