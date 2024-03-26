<?php
$track_id = 0;
if (!empty($_POST['id'])) {
    $track_id = secure($_POST['id']);
}
if (empty($track_id)) {
    $data = array('status' => 400, 'error' => 'Invalid Track ID');
    echo json_encode($data);
    exit();
}

$getSong = $db->where('id', $track_id)->getOne(T_SONGS);
if (empty($getSong)) {
    $data = array('status' => 400, 'error' => 'Invalid Track ID.');
    echo json_encode($data);
    exit();
}


$data['status'] = 200;
$data['data'] = songData($track_id);
