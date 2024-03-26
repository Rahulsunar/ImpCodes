<?php 

$final_data = [];
$interests = [];
if (IS_LOGGED) {
    $interests = getUserInterest();

    $db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}
$getOnePlaylist = $db->where("id IN (SELECT playlist_id FROM " . T_PLAYLIST_SONGS . ")")->where('privacy', 0)->orderby('RAND()')->getOne(T_PLAYLISTS);

if (!empty($getOnePlaylist)) {
	$final_data[] = [
		'title' => $getOnePlaylist->name,
		'thumbnail' => getMedia($getOnePlaylist->thumbnail),
		'url' => getLink('playlist/' . $getOnePlaylist->uid),
		'ajax_url' => 'playlist/' . $getOnePlaylist->uid
	];
}

if (!empty($interests)) {
    $db->where('category_id',array_keys($interests),'IN');
}
$getOneSong = $db->where('availability', 0)->orderby('RAND()')->getOne(T_SONGS);
if (!empty($getOneSong)) {
	$final_data[] = [
		'title' => $getOneSong->title,
		'thumbnail' => getMedia($getOneSong->thumbnail),
		'url' => getLink('track/' . $getOneSong->audio_id),
		'ajax_url' => 'track/' . $getOneSong->audio_id
	];
}

if (IS_LOGGED) {
	$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}
$db->where("id IN (SELECT playlist_id FROM " . T_PLAYLIST_SONGS . ")")->where('privacy', 0);

if (!empty($getOnePlaylist)) {
	$db->where('id', $getOnePlaylist->id,'<>');
}

$getAnotherPlaylist = $db->orderby('RAND()')->getOne(T_PLAYLISTS);

if (!empty($getAnotherPlaylist)) {
	$final_data[] = [
		'title' => $getAnotherPlaylist->name,
		'thumbnail' => getMedia($getAnotherPlaylist->thumbnail),
		'url' => getLink('playlist/' . $getAnotherPlaylist->uid),
		'ajax_url' => 'playlist/' . $getAnotherPlaylist->uid
	];
}

if (IS_LOGGED) {
	$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}
$db->where('availability', 0);
if (!empty($getOneSong)) {
	$db->where('id', $getOneSong->id,'<>');
}


    if (!empty($interests)) {
        $db->where('category_id',array_keys($interests),'IN');
    }
$getAnotherSong = $db->orderby('RAND()')->getOne(T_SONGS);

if (!empty($getAnotherSong)) {
	$final_data[] = [
		'title' => $getAnotherSong->title,
		'thumbnail' => getMedia($getAnotherSong->thumbnail),
		'url' => getLink('track/' . $getAnotherSong->audio_id),
		'ajax_url' => 'track/' . $getAnotherSong->audio_id
	];
}

$top_slider_list = "";
if (!empty($final_data)) {

	foreach ($final_data as $key => $topList) {
		$top_slider_list .= loadPage("discover/top_slider_list", [
			'url' => $topList['url'],
			'title' => $topList['title'],
			'thumbnail' => $topList['thumbnail'],
			'ajax_url' => $topList['ajax_url'],
		]);
	}
}

if (!empty($_SESSION['fingerPrint'])) {
	$db->where('fingerprint', secure($_SESSION['fingerPrint']));
} else if (IS_LOGGED) {
	$db->where('user_id', secure($user->id));
}

$getRecentPlay = $db->groupBy('track_id')->orderBy('id', 'DESC')->get(T_VIEWS, 10);

$recent_plays = '';

if (!empty($getRecentPlay)) {
	foreach ($getRecentPlay as $key => $list) {
		$songData = songData($list->track_id);
		if (!empty($songData)) {
			$recent_plays .= loadPage("discover/recently-list", [
				'url' => $songData->url,
				'title' => $songData->title,
				'thumbnail' => $songData->thumbnail,
				'id' => $songData->id,
				'audio_id' => $songData->audio_id,
				'USER_DATA' => $songData->publisher
			]);
		}
	}
}

if (IS_LOGGED) {
	$db->where("user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)");
}
if (IS_LOGGED) {
    if (!empty($interests)) {
        $db->where('category_id',array_keys($interests),'IN');
    }
}
$getNewRelease = $db->where('availability', 0)->orderBy('id', 'DESC')->get(T_SONGS, 12);

$newReleases = '';

if (!empty($getNewRelease)) {
	foreach ($getNewRelease as $key => $song) {
		$songData = songData($song, false, false);
		$newReleases .= loadPage("discover/recently-list", [
			'url' => $songData->url,
			'title' => $songData->title,
			'thumbnail' => $songData->thumbnail,
			'id' => $songData->id,
			'audio_id' => $songData->audio_id,
			'USER_DATA' => $songData->publisher
		]);
	}
}

