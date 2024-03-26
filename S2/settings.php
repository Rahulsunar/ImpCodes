<?php 
if (IS_LOGGED == false) {
	header("Location: $site_url/discover");
	exit();
}
if (empty($path['options'][1])) {
	header("Location: $site_url/discover");
	exit();
}
$settings_page = 'general';
if (empty($path['options'][2])) {
	$settings_page = 'general';
} else {
	if (in_array($path['options'][2], ['general', 'profile', 'delete', 'password', 'blocked','interest','balance','withdrawals','notification', 'manage-sessions', 'two-factor', 'affiliates','my_info'])) {
		$settings_page = secure($path['options'][2]);
        if ($settings_page == 'affiliates' && $music->config->affiliate_system != '1') {
            $settings_page = 'general';
        }
	}
}

if( $settings_page == 'delete' ){
    if( $music->config->delete_account == 'off' ){
        header("Location: $site_url/feed");
        exit();
    }
}

if( $settings_page == 'balance' || $settings_page == 'withdrawals' ){
    if( $music->user->artist !== 1 && $music->config->affiliate_system != '1' && $music->config->point_system != 'on'){
        header("Location: $site_url/feed");
        exit();
    }
}


$username = secure($path['options'][1]);

$getIDfromUser = $db->where('username', $username)->getValue(T_USERS, 'id');
if (empty($getIDfromUser) || !isAdmin()) {
	$getIDfromUser = $user->id;
}

$userData = userData($getIDfromUser);

$userData->owner  = false;

if ($music->loggedin == true) {
    $userData->owner  = ($user->id == $userData->id) ? true : false;
}



$music->setting_fields = UserFieldsData($userData->id);

$music->settings_page = $settings_page;
$music->userData = $userData;
$music->site_title = lang("Settings");
$music->site_description = $music->config->description;
$music->site_pagename = "settings";
$music->site_content = loadPage("settings/$settings_page", [
	'USER_DATA' => $userData,
	
]);