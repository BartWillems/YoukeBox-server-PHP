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
    require $_SERVER['DOCUMENT_ROOT'] . '/api/includes/youtube_api.php';
    require $_SERVER['DOCUMENT_ROOT'] . '/api/vendor/autoload.php';
    $youtube = new Madcoda\Youtube\Youtube(array('key' => DEVELOPER_KEY));
    $params  = array(
        'q'          => $query,
        'type'       => 'video',
        'part'       => 'id, snippet',
        'maxResults' => 20
    );
    $results = $youtube->searchAdvanced($params, true);
    return $results;
}

?>
