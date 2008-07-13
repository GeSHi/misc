<?
/**
 * markov.php
 * ----------
 *
 * Author: Benny Baumann <BenBE@omorphia.de>
 * Based on an idea by: Andreas Unterweger <support@dustsigns.de>
 * Read more on: http://www.dustsigns.de/cgi-bin/index.cgi?submenu=FH;content=Projekte;lang=
 * Copyright: (c) 2008 Benny Baumann
 * Date started: 2008/07/12
 *
 * Markov Chain Implementation for Source File Language Recognition
 *
 * CHANGES
 * -------
 * 2008/07/12
 *   -  Initial Release
 *
 * TODO
 * ----
 * * Test this stuff on real-world source *G*
 *
 *******************************************************************************
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
 * @subpackage langdetect
 * @author     Benny Baumann <BenBE@omorphia.de>, Andreas Unterweger <support@dustsigns.de>
 * @copyright  (C) 2008 Benny Baumann
 * @license    http://gnu.org/copyleft/gpl.html GNU GPL
 */

class Markov {

    //Character Count (Order 0)
    var $ci = 0;

    //Initial probabilities (Order 0 Probabilities)
    var $i = array();

    //Character Count (Order 2)
    var $cp = 0;

    //Sequence Probabilities (Order 2 Probabilities)
    var $p = array();

    function load_from_file($filename) {
        $lang_file = unserialize(file_get_contents($filename));

        $this->ci = $lang_file['ci'];
        $this->i = $lang_file['i'];
        $this->cp = $lang_file['cp'];
        $this->p = $lang_file['p'];
    }

    function save_to_file($filename) {
        ksort($this->i);
        ksort($this->p);

        file_put_contents($filename, serialize(
            array(
                "t" => date("d.m.Y H:i:s"), //Time of last store operation
                "ci" => $this->ci,          //Number of trained chars (Order 0)
                "i" => $this->i,            //Initial Probabilities
                "cp" => $this->cp,          //Number of trained steps (Order 2)
                "p" => $this->p             //Sequence Probabilities
            )));

        return true;
    }

    function update_ip($s, $chg=1) {
        if(!isset($this->i[$s])) {
            $this->i[$s] = $chg;
        } else {
            $this->i[$s] += $chg;
        }

        $this->ci++;
    }

    function update_sp($prev, $curr, $chg=1) {
        if(!isset($this->p[$prev])) {
            $this->p[$prev] = array();
        }

        if(!isset($this->p[$prev][$curr])) {
            $this->p[$prev][$curr] = $chg;
        } else {
            $this->p[$prev][$curr] += $chg;
        }

        $this->cp++;
    }

    function analyze($text) {
        $text = str_replace("\r\n", "\n", $text);
        $text = str_replace("\r", "\n", $text);
        $text = preg_replace('/[\x00-\x1F\x80-\xFF]/m', '', $text);

        $len = strlen($text);
        for($c = 0; $c < $len; $c++) {
            $this->update_ip($text[$c]);
        }

        for($c = 0; $c < $len - 3; $c++) {
            $this->update_sp($text[$c].$text[$c+1], $text[$c+3].$text[$c+3]);
        }
    }

    function mean_square_error($markov) {
        $mse = 0;

        for($a = 0; $a < 128; $a++) {
            //Calculate Order 0 probability errors
            if(isset($this->i[chr($a)])) {
                $t_i_a = $this->i[chr($a)] / $this->ci;
            } else {
                $t_i_a = 0;
            }
            if(isset($markov->i[chr($a)])) {
                $m_i_a = $markov->i[chr($a)] / $markov->ci;
            } else {
                $m_i_a = 0;
            }
            $err_a = $t_i_a - $m_i_a;
            $mse += $err_a * $err_a;

            for($b = 0; $b < 128; $b++) {
                $__ab = chr($a).chr($b);

                //Noone has something for this probability --> so no error!
                if(!isset($this->p[$__ab]) && !isset($markov->p[$__ab])) {
                    continue;
                }

                for($c = 0; $c < 128; $c++) {
                    for($d = 0; $d < 128; $d++) {
                        $__cd = chr($c).chr($d);

                        //Calculate Order 2 probability errors
                        if(isset($this->p[$__ab]) &&
                            isset($this->p[$__ab][$__cd])) {
                            $t_p_ab_cd = $this->p[$__ab][$__cd] / $this->cp;
                        } else {
                            $t_p_ab_cd = 0;
                        }
                        if(isset($markov->p[$__ab]) &&
                            isset($markov->p[$__ab][$__cd])) {
                            $m_p_ab_cd = $markov->p[$__ab][$__cd] / $markov->cp;
                        } else {
                            $m_p_ab_cd = 0;
                        }
                        $err_ab_cd = ($t_p_ab_cd - $m_p_ab_cd);
                        $mse += $err_ab_cd * $err_ab_cd;
                    }
                }
            }
        }

        return $mse;
    }

    function detect_lang($lang_arr) {
        $result = array(
            "lang" => false,
            "err" => 1E100
            );

        foreach($lang_arr as $lang => $model) {
            $lang_err = $this->mean_square_error($model);
            if($lang_err < $result["err"]) {
                $result = array(
                    "lang" => $lang,
                    "err" => $lang_err
                    );
            }
        }

        return $result;
    }
}

?>
