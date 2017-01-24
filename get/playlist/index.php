<?php

echo json_encode(getPlaylist());

function getPlaylist(){
    @include('../../includes/db_connection.php');
    if(!$mysqli) {
        http_response_code(500);
        return 'Database Error';
    }

    $stmt = $mysqli->prepare('SELECT title, duration, added_by, timestamp FROM video ORDER BY timestamp ASC LIMIT 20');
    if(!$stmt->execute()) {
        http_response_code(500);
        $stmt->close();
        $mysqli->close();
        return 'Database Execution Error';
    }
    $stmt->bind_result($title, $duration, $username, $timestamp);
    $videos = array();
    while($stmt->fetch()) {
        $o = null;
        $o['title']     = $title;
        $o['duration']  = $duration;
        $o['username']  = $username;
        $o['timestamp'] = $timestamp;
        array_push($videos, $o);
    }
    $stmt->close();
    $mysqli->close();
    return $videos;
}

?>