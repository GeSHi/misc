######
## ATTENTION: this is a _template_ whith embedded PHP
##            !!! DONT CALL DIRECTLY WITH GNUPLOT !!!
######

set term png interlace tiny font "/usr/share/fonts/truetype/ttf-dejavu/DejaVuSans.ttf" size 1200, 800
set grid xtics mxtics ytics mytics lt 49 lw 1, lt 0 lw 1
set key inside left top

###
# time over loc
###
<?php foreach ($files as $data): ?>
set output '<?php echo $data['base']; ?>_loc_vs_time.png'
set title '<?php echo $data['title']; ?>  time over linenumbers'
set ylabel 'time in s'
set xlabel 'linenumber'
set xtics 100 out rotate by 90
set mxtics 4
set ytics autofreq
set mytics 10
plot "<?php echo $data['trace']; ?>" using 7:1 notitle with points

###
# memory differences over loc
###
set output '<?php echo $data['base']; ?>_memdiff_vs_loc.png'
set title '<?php echo $data['title']; ?> Memory differences over linenumber'
set xlabel 'line number'
set xtics 100 out rotate by 90
set mxtics 4
# memory diff ordinate
set ylabel 'memory difference in KB'
set ytics autofreq
set mytics 10
set logscale y
plot "<?php echo $data['trace']; ?>" using 7:($6 > 0 ? $6/1024 : 1/0 ) title "alloc" with points lt 1,\
     "<?php echo $data['trace']; ?>" using 7:($6 < 0 ? -$6 / 1024 : 1/0) title "dealloc" with points lt 2
unset logscale y

###
# memory over loc
###
set output '<?php echo $data['base']; ?>_mem_vs_loc.png'
set title '<?php echo $data['title']; ?> Memory over LinesOfCode'
# loc
set xlabel 'line of code'
set xtics 100 out rotate by 90
set mxtics 4
# memory ordinate
set ylabel 'memory in KB'
set ytics 500
set mytics 5
plot "<?php echo $data['trace']; ?>" using 7:($4/1024) title "entry" with points, "<?php echo $data['trace']; ?>" using 7:($5/1024) title "exit"

###
# timediff over loc
###
set output '<?php echo $data['base']; ?>_loc_vs_timediff.png'
set title '<?php echo $data['title']; ?> Time differences over LinesOfCode'
# loc
set xlabel 'line number'
set xtics 100 out rotate by 90
set mxtics 4
# time
set ylabel 'timediff in sec'
set logscale y
set ytics autofreq
set mytics 10
set format y "%.1e"
plot "<?php echo $data['trace']; ?>" using 7:3 notitle with points
unset format
unset logscale y

###
# code coverage : number of times a given loc is touched
###
set output '<?php echo $data['base']; ?>_coverage_vs_loc.png'
set title '<?php echo $data['title']; ?> Coverage of linenumbers (i.e. how often got function X get called in line Y)'
# loc
set xlabel 'line number'
set xtics 100 out rotate by 90
set mxtics 4
# coverage
set ylabel 'coverage (number of calls)'
set logscale y
set ytics autofreq
set mytics 10
plot "<?php echo $data['coverage']; ?>" using 1:2 notitle with impulses
unset logscale y

###
# code coverage : accumulated timediffs per loc
###
set output '<?php echo $data['base']; ?>_accumulated_time_vs_loc.png'
set title '<?php echo $data['title']; ?> Accumulated timediff over linenumbers (i.e. how much time is spent in total on loc X)'
# loc
set xlabel 'line number'
set xtics 100 out rotate by 90
set mxtics 4
# timediff
set ylabel 'accumulated timediff in sec'
set logscale y
set ytics autofreq
set mytics 10
set format y "%.1e"
plot "<?php echo $data['coverage']; ?>" using 1:3 notitle with impulses
unset logscale y
unset format

<?php endforeach; ?>

###
# all files together: memory over time
###
set output '<?php echo $base; ?>_mem_vs_time.png'
set title '<?php echo $title; ?> Memory over Time'
# time
set xlabel 'time in sec'
set xtics autofreq
set mxtics 5
# memory ordinate
set ylabel 'memory in KB'
set ytics 500
set mytics 5
plot <?php foreach ($files as $k => $data) { echo (($k != 0) ? ', ' : '').'"'.$data['trace'].'" using 1:($4/1024) with points lt '.($k+1).' title "'.$data['id'].'.'.$data['script_name'].'", '
    .'"'.$data['trace'].'" using 1:($5/1024) with points lt '.($k+1).' notitle'; } ?>