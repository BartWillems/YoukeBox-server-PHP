<?php
error_reporting(-1);
ini_set('display_errors', 'On');

if(isset($_GET['videos'])) {
    if(isset($_GET['query'])) {
        echo json_encode(fetch_videos($_GET['query']));
    }
}

function fetch_videos($query = null) {
    if(empty($query)) {
        header('HTTP/1.0 403 Forbidden');
        return false;
    }
    require '../includes/youtube_api.php';
    require '../vendor/autoload.php';
    $youtube = new Madcoda\Youtube\Youtube(array('key' => DEVELOPER_KEY));
    $params  = array(
        'q'          => $query,
        'type'       => 'video',
        'part'       => 'id, snippet',
        'maxResults' => 20
    );
    $results = $youtube->searchAdvanced($params, true);

    $videos = json_decode(json_encode($results),true);
    $ids = array();
    foreach($videos['results'] as $video) {
        array_push($ids, $video['id']['videoId']);
    }

    $results = $youtube->getVideosInfo($ids);
    return $results;
}

function getVideoDurations($videos) {
    $videos = json_decode(json_encode($videos),true);
    $ids = array();
    foreach($videos['results'] as $video) {
        array_push($ids, $video['id']);
    }

    foreach($videos as &$video) {
        $time = $video['contentDetails']['duration'];
        $formated_stamp = str_replace(array("PT","M","S"), array("",":",""),$time);
        $exploded_string = explode(":",$formated_stamp);
        $new_formated_stamp = sprintf("%02d", $exploded_string[0]).":".sprintf("%02d", $exploded_string[1]);
        $video['contentDetails']['duration'] = $new_formated_stamp;
    }
    return json_encode($videos);
}

?>
