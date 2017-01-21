<?php

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
    require '/api/includes/youtube_api.php';
    require '/api/vendor/autoload.php';
    $youtube = new Madcoda\Youtube\Youtube(array('key' => DEVELOPER_KEY));
    $results = $youtube->search($query);
    return $results;
}

?>
