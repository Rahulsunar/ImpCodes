<?php
if (empty($path['options'][1])) {
    header("Location: $site_url/404");
    exit();
}
$audio_id = secure($path['options'][1]);

$getIDAudio = $db->where('audio_id', $audio_id)->getValue(T_SONGS, 'id');

if (empty($getIDAudio)) {
    header("Location: $site_url/404");
    exit();
}

$songData = songData($getIDAudio);
//if ($songData->IsOwner == true || IsAdmin()) {
//
//}else{
//    header("Location: $site_url");
//    exit();
//}

$songData->owner  = false;

if (IS_LOGGED == true) {
    $songData->owner  = ($user->id == $songData->publisher->id) ? true : false;
}

$total_rate = 0;
$total_review = 0;
$reviews_html = '<div class="no-track-found bg_light"><svg xmlns="http://www.w3.org/2000/svg" width="24" height="24" viewBox="0 0 24 24"><path fill="currentColor" d="M15,6H3V8H15V6M15,10H3V12H15V10M3,16H11V14H3V16M17,6V14.18C16.69,14.07 16.35,14 16,14A3,3 0 0,0 13,17A3,3 0 0,0 16,20A3,3 0 0,0 19,17V8H22V6H17Z" /></svg>' . lang("No reviews on this track yet.") . '</div>';
$reviews = $db->objectbuilder()->where('track_id',$songData->id)->orderBy('id', 'DESC')->get(T_REVIEWS);
if (!empty($reviews)) {
    $reviews_html = '';
    foreach ($reviews as $key => $value) {
        $user = userData($value->user_id);
        $reviews_html .= loadPage('track/review', ['SONG_DATA' => $songData, 'USER_DATA' => $user, 'TM' => $value->time, 'DESC' => $value->description, 'ID' => $value->id]);
        $total_review++;
        $total_rate += $value->rate;
    }
}


$music->albumData = [];
if (!empty($songData->album_id)) {
    $music->albumData = $db->where('id', $songData->album_id)->getOne(T_ALBUMS);
}




$autoPlay = false;
if (!empty($path['options'][2])) {
    if ($path['options'][2] == 'play') {
        $autoPlay = true;
    }
}



$t_desc = $songData->description;

$music->autoPlay = $autoPlay;



$music->site_title = html_entity_decode($songData->title);
$music->site_description = $songData->description;
$music->site_pagename = "track_reviews";
$music->site_content = loadPage("track/reviews", [
    'USER_DATA' => $songData->publisher,
    't_thumbnail' => $songData->thumbnail,
    't_song' => $songData->secure_url,
    't_title' => $songData->title,
    't_description' => $t_desc,
    't_lyrics' => $songData->lyrics,
    't_time' => time_Elapsed_String($songData->time),
    'ts_time' => date('c',$songData->time),
    't_audio_id' => $songData->audio_id,
    't_id' => $songData->id,
    'category_name' => $songData->category_name,
    't_shares' => number_format_mm($songData->shares),
    'COUNT_LIKES' => number_format_mm(countLikes($songData->id)),
    'COUNT_DISLIKES' => number_format_mm(countDisLikes($songData->id)),
    'COUNT_VIEWS' => number_format_mm($db->where('track_id', $songData->id)->getValue(T_VIEWS, 'count(*)')),
    'COUNT_USER_SONGS' => $db->where('user_id', $songData->publisher->id)->getValue(T_SONGS, 'count(*)'),
    'COUNT_USER_FOLLOWERS' => number_format_mm($db->where('following_id', $songData->publisher->id)->getValue(T_FOLLOWERS, 'COUNT(*)')),
    'fav_count' => number_format_mm($db->where('track_id', $songData->id)->getValue(T_FOV, 'count(*)')),
    'recentPlays' => $recentUserPlays_html,
    'reviews_html' => $reviews_html,
    'total_review' => $total_review
]);