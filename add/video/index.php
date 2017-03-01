<?php

if(isset($_POST['addVideo'])) {
    echo json_encode(addVideo($_POST['video'], $_POST['user']));
}

function addVideo($video=null, $user=null){
    if(empty($video)) {
        header("HTTP/1.1 403 Forbidden");
        return 'Invalid video';
    }
    @include('../../includes/db_connection.php');
    if(!$mysqli) {
        http_response_code(500);
        return 'Database Error';
    }

    $now = date("Y-m-d H:i:s");
    $stmt = $mysqli->prepare('INSERT INTO video (video_id, title, duration, added_by, timestamp) VALUES(?,?,?,?,?)');
    $stmt->bind_param('sssss', $video['id'], $video['snippet']['title'], $video['contentDetails']['duration'], $user, $now);
    if(!$stmt->execute()) {
        http_response_code(500);
        $stmt->close();
        $mysqli->close();
        return $stmt->error;
    }
    $stmt->close();
    $mysqli->close();
    return true;
}

?>
