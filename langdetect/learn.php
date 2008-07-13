<?
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'profiling' . DIRECTORY_SEPARATOR . 'lib.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'markov.php';

header("Content-Type: text/plain; charset=utf-8");

foreach($languages as $lang) {
    echo "Learning language $lang ... ";

    if(!file_exists(CODEREPO_PATH . $lang) || !is_dir(CODEREPO_PATH . $lang)) {
        echo "failed (Nothing to learn)\n\n";
        continue;
    }

    echo "\n";
    $was_error = false;

    $m = new Markov();
    $dir_toc = opendir(CODEREPO_PATH . $lang);
    while($lang_example = readdir($dir_toc)) {
        $lang_file = CODEREPO_PATH . $lang . DIRECTORY_SEPARATOR . $lang_example;
        if('.' == $lang_example[0] || !is_file($lang_file) || !is_readable($lang_file)) {
            continue;
        }

        echo "- Training file $lang_example ... ";
        $m->analyze(file_get_contents($lang_file));
        echo "done\n";
    }
    closedir($dir_toc);
    $was_error |= !$m->save_to_file(dirname(__FILE__) . DIRECTORY_SEPARATOR . $lang . '.slid');
    echo "==> Completed training ".($was_error ? "with errors" : "successfully")."!\n\n";
}

?>
