<?php

error_reporting(-1);
ini_set('display_errors', 'On');

/* print_r(fetch_videos()); */
$dink = json_decode(json_encode(fetch_videos()),true);

$time = $dink['contentDetails']['duration'];
$formated_stamp = str_replace(array("PT","M","S"), array("",":",""),$time);
$exploded_string = explode(":",$formated_stamp);
$new_formated_stamp = sprintf("%02d", $exploded_string[0]).":".sprintf("%02d", $exploded_string[1]);
 echo $new_formated_stamp;


function fetch_videos($query = null) {
    /* if(empty($query)) { */
    /*     header('HTTP/1.0 403 Forbidden'); */
    /*     return false; */
    /* } */
    require $_SERVER['DOCUMENT_ROOT'] . '/api/includes/youtube_api.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/api/vendor/autoload.php';
    $youtube = new Madcoda\Youtube\Youtube(array('key' => DEVELOPER_KEY));
    return $youtube->getVideoInfo("eUt5kzEoOp4");
    /* $params  = array( */
    /*     'q'          => $query, */
    /*     'type'       => 'video', */
    /*     'part'       => 'id, snippet', */
    /*     'maxResults' => 20 */
    /* ); */
    /* $results = $youtube->searchAdvanced($params, true); */
    /* return $results; */
}

?>
