<?
require dirname(dirname(__FILE__)) . DIRECTORY_SEPARATOR . 'profiling' . DIRECTORY_SEPARATOR . 'lib.php';
require dirname(__FILE__) . DIRECTORY_SEPARATOR . 'markov.php';

header("Content-Type: text/plain; charset=utf-8");
set_time_limit(300);

$lang_models = array();

foreach($languages as $lang) {
    echo "Loading language $lang ... ";

    $lang_path = dirname(__FILE__) . DIRECTORY_SEPARATOR . $lang . '.slid';
    if(!file_exists($lang_path)) {
        echo "failed (No Model Info)\n";
        continue;
    }

    $lang_models[$lang] = new Markov();
    $lang_models[$lang]->load_from_file($lang_path);

    echo "done\n";
}

foreach($lang_models as $lang => $tmp) {
    echo "Verifying language $lang ... ";

    echo "\n";

    $was_error = false;

    $dir_toc = opendir(CODEREPO_PATH . $lang);
    while($lang_example = readdir($dir_toc)) {
        $lang_file = CODEREPO_PATH . $lang . DIRECTORY_SEPARATOR . $lang_example;
        if('.' == $lang_example[0] || !is_file($lang_file) || !is_readable($lang_file)) {
            continue;
        }

        echo "- Testing with file $lang_example ... ";
        $m = new Markov();
        $m->analyze(file_get_contents($lang_file));

        echo "detecting language ... ";

        $ld_res = $m->detect_lang($lang_models);

        echo ($was_error = ($ld_res['lang'] != $lang) ? "fail" : "ok").
            "(".$ld_res['lang']."@ ".$ld_res['err'].")\n";
    }
    closedir($dir_toc);
    echo "==> Completed training ".($was_error ? "with errors" : "successfully")."!\n\n";
}

?>