<?php

echo json_encode(getPlaylist());

function getPlaylist(){
    @include('../../includes/db_connection.php');
    if(!$mysqli) {
        http_response_code(500);
        return 'Database Error';
    }

    // Fetch playlist
    $stmt = $mysqli->prepare('SELECT id, title, video_id, duration, added_by, timestamp FROM video WHERE played = 0 ORDER BY timestamp ASC');
    if(!$stmt->execute()) {
        http_response_code(500);
        $stmt->close();
        $mysqli->close();
        return 'Database Execution Error';
    }
    $stmt->bind_result($id, $title, $video_id, $duration, $username, $timestamp);
    $videos = array();
    while($stmt->fetch()) {
        $o = null;
        $o['id']        = $id;
        $o['title']     = $title;
        $o['video_id']  = $video_id;
        $o['duration']  = $duration;
        $o['username']  = $username;
        $o['timestamp'] = $timestamp;
        array_push($videos, $o);
    }
    $stmt->close();

    $totalPlayTime  = 0;
    $now            = time();
    $check          = true;
    if(count($videos) > 0) {
        $initialPlayTime = strtotime($videos[0]['timestamp']);
    }
    while(count($videos) > 0 && $check) {
        // Check if the first sonk from the array is already done playing
        $currentVideo   = $videos[0];
        $duration       = getHumanTime($currentVideo['duration']);
        $duration       = explode(':',$duration);
        $duration       = $duration[0] * 3600 + $duration[1] * 60 + $duration[2];
        $totalPlayTime += $duration;

        if($now - $initialPlayTime > $totalPlayTime) {
            $playedID = $currentVideo['id'];
            $playedID = mysqli_real_escape_string($mysqli, $playedID);
            array_shift($videos);
            $mysqli->query("UPDATE video SET played = 1 WHERE id = $playedID");
        } else {
            // We should now update a video play time somewhere
            // It should say something like: $video started playing on XX:XX:XX
            $stmt = $mysqli->prepare('UPDATE video SET timestamp = UNIX_TIMESTAMP(?) WHERE id = ?');
            $videoCount = count($videos);
            for($i = 1; $i < $videoCount; $i++) {
                $time = $now + $totalPlayTime;
                $stmt->bind_param('si', $time, $videos[$i]['id']);
                $stmt->execute();
            }
            $stmt->close();
            $timeDifference     = $now - $initialPlayTime;
            $videos[0]['time']  = $timeDifference;
            $check = false;
        }
    }
    $mysqli->close();
    return $videos;
}

function getHumanTime($youtube_time){
    $start = new DateTime('@0'); // Unix epoch
    $start->add(new DateInterval($youtube_time));
    return $start->format('H:i:s');
}


?>
