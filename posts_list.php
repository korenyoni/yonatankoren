<?php
$i = 0;
$dir = 'posts/';
$a = scandir($dir);
$ops = [];
$dev = [];
$art = [];
foreach ($a as &$file) {
    if ($file != '.' && $file != '..') {

        $file_text = file_get_contents(__DIR__ . '/posts/' . $file);

        $date_regex = "/Date:.*\n/";
        preg_match($date_regex,$file_text,$date_line_matches);
        $date = preg_replace("/Date:/", '', $date_line_matches[0]);
        $date = trim($date);
        $file_text = preg_replace($date_regex,'',$file_text);

        $title_regex = "/Title:.*\n/";
        preg_match($title_regex,$file_text,$title_line_matches);
        $title = preg_replace("/Title:/", '', $title_line_matches[0]);
        $title = trim($title);
        $file_text = preg_replace($title_regex,'',$file_text);

        $cat_regex = "/cat:.*\n/";
        preg_match($cat_regex,$file_text,$cat_line_matches);
        $cat = preg_replace("/cat:/", '', $cat_line_matches[0]);
        $cat = trim($cat);
        $cat = explode(',',$cat);
        $file_text = preg_replace($cat_regex,'',$file_text);

        foreach ($cat as &$c) {
            $post->date = $date;
            $post->title = $title;
            $post->cat = $c;
            $post->id = pathinfo($file)["filename"];
            $post->index = $i;
            if ($c == "ops") {
                array_unshift($ops, $post);
            }
            if ($c == "dev") {
                array_unshift($dev, $post);
            }
            if ($c == "art") {
                array_unshift($art, $post);
            }
        }

        unset($post);
        $i = $i + 1;
    }
}
$res->ops = $ops;
$res->dev = $dev;
$res->art = $art;
echo json_encode($res);
?>
