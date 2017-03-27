<?php

session_start();

echo json_encode(getPlaylist());

function getPlaylist(){
    @include('../../includes/db_connection.php');
    if(!$mysqli) {
        http_response_code(500);
        return 'Database Error';
    }

    // Fetch playlist
    $stmt = $mysqli->prepare('SELECT id, title, video_id, duration, added_by, timestamp FROM video WHERE played = 0 ORDER BY id ASC');
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
    $videoPurged    = false;
    $now            = time();
    $check          = true;
    $result = array();

    // Only refresh the queue every 2 seconds
    // Store the updateTime in the memcache object
    $m = new Memcached();
    $m->addServer('localhost', 11211);

    // Fetch the current list of users
    $session['id']      = session_id();
    $session['time']    = time();

    $connectedUsers = (array)$m->get('connectedUsers');
    if($m->getResultCode() == Memcached::RES_NOTFOUND || $connectedUsers === FALSE) {
        $users = array();
        array_push($users, $session);
        $m->set('connectedUsers', $users);
        $userCount = 1;
    } else {
        $userExists = false;
        foreach($connectedUsers as $key => &$user) {
            // Update the current user's timestamp
            if($user['id'] === session_id()) {

                $user['time']   = time();
                $userExists     = true;

            }
            // Remove vacant users
            if(time() - $user['time'] > 60) {
                unset($connectedUsers[$key]);
            }
        }
        // If the current user didn't have an entry, add him to the list
        if(!$userExists) {
            $user = array('id' => session_id(), 'time' => time());
            array_push($connectedUsers, $user);
        }
        $m->set('connectedUsers', $connectedUsers);
        $userCount = count($connectedUsers);
    }

    if(count($videos) > 0) {
        $initialPlayTime = strtotime($videos[0]['timestamp']);
    }
    while(count($videos) > 0 && $check) {
        // Check if the first sonk from the array is already done playing
        $currentVideo   = $videos[0];
        $duration       = getHumanTime($currentVideo['duration']);
        $duration       = explode(':',$duration);
        $duration       = $duration[0] * 3600 + $duration[1] * 60 + $duration[2];
        $tmpDuration    = $duration;
        $totalPlayTime += $duration;

        if($now - $initialPlayTime > $totalPlayTime) {
            $playedID = $currentVideo['id'];
            $playedID = mysqli_real_escape_string($mysqli, $playedID);
            array_shift($videos);
            $mysqli->query("UPDATE video SET played = 1 WHERE id = $playedID");
            $videoPurged = true;
        } else {
            // We should now update a video play time somewhere
            // It should say something like: $video started playing on XX:XX:XX
            $stmt = $mysqli->prepare('UPDATE video SET timestamp = UNIX_TIMESTAMP(?) WHERE id > ?');
            $videoCount = count($videos);

            $time = $now + $totalPlayTime - $tmpDuration;

            $stmt->bind_param('si', $time, $videos[0]['id']);
            $stmt->execute();
            $stmt->close();
            $timeDifference     = $now - $initialPlayTime;
            if($videoPurged) {
                $videos[0]['time']  = 0;
            } else {
                $videos[0]['time']  = $timeDifference;
            }
            $check = false;
        }
    }
    $mysqli->close();

    $result['videos'] = $videos;
    $result['meta'] = array('userCount' => $userCount);
    return $result;
}

function getHumanTime($youtube_time){
    $start = new DateTime('@0'); // Unix epoch
    $start->add(new DateInterval($youtube_time));
    return $start->format('H:i:s');
}


?>
