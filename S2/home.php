<?php 
if (IS_LOGGED == true) {
	header("Location: $site_url/feed");
	exit();
}

$result_artists = $db->rawQuery("SELECT * FROM `".T_USERS."` WHERE `artist` = 1 ORDER BY rand() DESC LIMIT 14;");
$artists_html = '';
foreach ($result_artists as $artists) {
    $pagedata = [
        'ARTIST_DATA' => userData( $artists->id )
    ];
    $artists_html .= loadPage("user/artist-item", $pagedata);
}
$music->artists_html = $artists_html;

$time_week = time() - 604800;
$query = "SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id
WHERE " . T_VIEWS . ".time > $time_week AND " . T_SONGS . ".availability = '0'";

if (IS_LOGGED) {
    $query .= " AND " . T_SONGS . ".user_id NOT IN (SELECT user_id FROM blocks WHERE blocked_id = $user->id)";
}

$limit_theme = 10;
if( $config['theme'] == 'volcano' ){
    $limit_theme = 8;
}
$query .= " GROUP BY " . T_SONGS . ".id
ORDER BY " . T_VIEWS . " DESC LIMIT ".$limit_theme;





$music->site_title = 'Home';
$music->site_description = $music->config->description;
$music->site_pagename = "home";
