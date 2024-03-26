<?php
if ($option == 'general' || $option == 'profile' || $option == 'password' || $option == 'delete' || $option == 'get-profile' || $option == 'update_notification_setting') {
    if (empty($_POST['user_id']) || !is_numeric($_POST['user_id']) || $_POST['user_id'] == 0) {
        $errors[] = "Invalid user ID";
    } else {
        $userData = userData($_POST['user_id']);
    }
}

        if (empty($errors)) {
            $count                      = [];
            
            if( isset( $_POST['access_token'] ) ) {
                $request_uid = getUserFromSessionID($_POST['access_token'], 'mobile');
            }else{
                $request_uid = secure($_POST['user_id']);
            }

            $id          = secure($_POST['user_id']);
            $music->user = userData($id);
            unset($music->user->password);
            $count['followers']         = $db->where('following_id', $id)->where("follower_id NOT IN (SELECT blocked_id FROM blocks WHERE user_id = $id)")->getValue(T_FOLLOWERS, 'COUNT(*)');
            $count['following']         = $db->where('follower_id', $id)->where("following_id NOT IN (SELECT blocked_id FROM blocks WHERE user_id = $id)")->getValue(T_FOLLOWERS, 'COUNT(*)');
            $count['albums']            = $db->where('user_id', $id)->getValue(T_ALBUMS, 'COUNT(*)');
            $count['playlists']         = $db->where('user_id', $id)->getValue(T_PLAYLISTS, 'COUNT(*)');
            
            $count['favourites']        = $db->where('user_id', $id)->getValue(T_FOV, 'COUNT(*)');
            $count['recently_played']   = $db->where('user_id', $id)->getValue(T_VIEWS, 'COUNT(*)');;
            $count['liked']             = $db->where('user_id', $id)->where('track_id', 0,"<>")->getValue(T_LIKES, 'COUNT(*)');
            $count['activities']        = $db->where('user_id', $id)->getValue(T_ACTIVITIES, 'COUNT(*)');
            $count['latest_songs']      = $db->where('user_id', $id)->getValue(T_SONGS, 'COUNT(*)');
            $count['top_songs']         = count($db->rawQuery("
                                            SELECT " . T_SONGS . ".*, COUNT(" . T_VIEWS . ".id) AS " . T_VIEWS . "
                                            FROM " . T_SONGS . " LEFT JOIN " . T_VIEWS . " ON " . T_SONGS . ".id = " . T_VIEWS . ".track_id
                                            WHERE " . T_SONGS . ".user_id = ".$id."
                                            GROUP BY " . T_SONGS . ".id
                                            ORDER BY " . T_VIEWS . " DESC"));
            $count['store']             = $db->where('user_id', $id)->where('price', '0', '<>')->getValue(T_SONGS, 'COUNT(*)');
            $count['stations']             = $db->where('user_id', $id)->where('src', 'radio')->getValue(T_SONGS, 'COUNT(*)');

            $user_data = $music->user;
            $user_data->IsFollowing = isFollowing($request_uid, $id);
            $user_data->IsBloked = isBlocked($id);

            $fetch = explode(',',secure($_POST['fetch']));
            if(in_array('followers',$fetch)){
                $followers = GetFollowers($id);
                $user_data->followers = $followers['data'];
                //$count['followers'] = $followers['count'];
            }
            if(in_array('following',$fetch)){
                $following = GetFollowing($id);
                $user_data->following = $following['data'];
                //$count['following'] = $following['count'];
            }
            if(in_array('albums',$fetch)){
                $albums = GetAlbums($id);
                $user_data->albums = $albums['data'];
                //$count['albums'] = $albums['count'];
            }
            if(in_array('playlists',$fetch)){
                $playlists = GetPlaylists($id);
                $user_data->playlists = $playlists['data'];
                //$count['playlists'] = $playlists['count'];
            }
            if(in_array('blocks',$fetch)){
                $blocks = GetBlocks($id);
                $user_data->blocks = $blocks['data'];
                //$count['blocks'] = $blocks['count'];
            }
            if(in_array('favourites',$fetch)){
                $favourites = GetFavourites($id);
                $user_data->favourites = $favourites['data'];
                //$count['favourites'] = $favourites['count'];
            }
            if(in_array('recently_played',$fetch)){
                $recently_played = GetRecentlyPlayed($id);
                $user_data->recently_played = $recently_played['data'];
                //$count['recently_played'] = $recently_played['count'];
            }
            if(in_array('liked',$fetch)){
                $liked = GetLiked($id);
                $user_data->liked = $liked['data'];
                //$count['liked'] = $liked['count'];
            }
            if(in_array('activities',$fetch)){
                $activities = GetActivities($id);
                $user_data->activities = $activities['data'];
                //$count['activities'] = $activities['count'];
            }
            if(in_array('latest_songs',$fetch)){
                $latestsongs = GetLatestSongs($id);
                $user_data->latestsongs = $latestsongs['data'];
                //$count['latest_songs'] = $latestsongs['count'];
            }
            if(in_array('top_songs',$fetch)){
                $top_songs = GetTopSongs($id);
                $user_data->top_songs = $top_songs['data'];
                //$count['top_songs'] = $top_songs['count'];
            }
            if(in_array('store',$fetch)){
                $store = GetStore($id);
                $user_data->store = $store['data'];
                //$count['store'] = $store['count'];
            }
            if(in_array('stations',$fetch)){
                $sql = 'SELECT * FROM `'.T_SONGS.'` WHERE `src` = "radio" AND `user_id` = "'.$id.'" limit 20';
                $getUserStations            = $db->objectBuilder()->rawQuery($sql);
                $songs_data = array();
                if (!empty($getUserStations)) {

                    foreach ($getUserStations as $key => $song) {
                        $userSong = songData($song->id);
                        unset($userSong->publisher->password);
                        foreach ($userSong->songArray as $key => $value) {
                            unset($userSong->songArray->{$key}->USER_DATA->password);
                        }
                        $songs_data[] = $userSong;
                    }
                }
                $user_data->stations = $songs_data;
            }
            if(secure($_POST['fetch']) == "all"){
                $followers = GetFollowers($id);
                $user_data->followers = $followers['data'];
                //$count['followers'] = $followers['count'];

                $following = GetFollowing($id);
                $user_data->following = $following['data'];
                //$count['following'] = $following['count'];

                $albums = GetAlbums($id);
                $user_data->albums = $albums['data'];
                //$count['albums'] = $albums['count'];

                $playlists = GetPlaylists($id);
                $user_data->playlists = $playlists['data'];
                //$count['playlists'] = $playlists['count'];

                

                $favourites = GetFavourites($id);
                $user_data->favourites = $favourites['data'];
                //$count['favourites'] = $favourites['count'];

                $recently_played = GetRecentlyPlayed($id);
                $user_data->recently_played = $recently_played['data'];
                //$count['recently_played'] = $recently_played['count'];

                $liked = GetLiked($id);
                $user_data->liked = $liked['data'];
                //$count['liked'] = $liked['count'];

                $activities = GetActivities($id);
                $user_data->activities = $activities['data'];
                //$count['activities'] = $activities['count'];

                $latestsongs = GetLatestSongs($id);
                $user_data->latestsongs = $latestsongs['data'];
                //$count['latest_songs'] = $latestsongs['count'];

                $top_songs = GetTopSongs($id);
                $user_data->top_songs = $top_songs['data'];
                //$count['top_songs'] = $top_songs['count'];

                

                $sql = 'SELECT * FROM `'.T_SONGS.'` WHERE `src` = "radio" AND `user_id` = "'.$id.'" limit 20';
                $getUserStations            = $db->objectBuilder()->rawQuery($sql);
                $songs_data = array();
                if (!empty($getUserStations)) {

                    foreach ($getUserStations as $key => $song) {
                        $userSong = songData($song->id);
                        unset($userSong->publisher->password);
                        foreach ($userSong->songArray as $key => $value) {
                            unset($userSong->songArray->{$key}->USER_DATA->password);
                        }
                        $songs_data[] = $userSong;
                    }
                }
                $user_data->stations = $songs_data;
            }

            $data = [
                'status' => 200,
                'data' => $user_data,
                'details' => $count
            ];
        }
    }
}


if ($option == 'get-genres') {
    $genres = [];
    $categories = getCategories(false);
    foreach ($categories as $key => $value) {
        $value->background_thumb = (empty($value->background_thumb)) ? $music->config->theme_url . '/img/crowd.jpg' : getMedia($value->background_thumb);
        $genres[] = $value;
    }
    $data = [
        'status' => 200,
        'data' => $genres
    ];
}
if ($option == 'get-following') {
    if (empty($_POST['id'])) {
        $errors[] = "Please check your details";
    } else {

        if (!filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT)) {
            $errors[] = "Invalid id";
        }
        if ($_POST['id'] == 0) {
            $errors[] = "Invalid id";
        }
        if (empty($errors)) {
            $id                 = secure($_POST['id']);
            $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
            $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
            $data = [
                'status' => 200,
                'data' => GetFollowing($id,$limit,$offset)
            ];
        }
    }
}
if ($option == 'get-follower') {
    if (empty($_POST['id'])) {
        $errors[] = "Please check your details";
    } else {

        if (!filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT)) {
            $errors[] = "Invalid id";
        }
        if ($_POST['id'] == 0) {
            $errors[] = "Invalid id";
        }
        if (empty($errors)) {
            $id                 = secure($_POST['id']);
            $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
            $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
            $data = [
                'status' => 200,
                'data' => GetFollowers($id,$limit,$offset)
            ];
        }
    }
}
if ($option == 'get-artists') {
    $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
    $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
    $data = [
        'status' => 200,
        'data' => GetArtists($limit,$offset)
    ];
}

if(!in_array($option, $whitelist)) {
    if (IS_LOGGED == false) {
        $errors[] = "You ain't logged in!";
    }
}

if (empty($errors)) {

    if ($option == 'profile') {
        if (empty($_POST['name'])) {
            $errors[] = "Please check your details";
        } else {
            $name                 = secure($_POST['name']);
            $about_me             = secure($_POST['about_me']);
           
            if (empty($errors)) {
                $update_data = [
                    'name' => $name,
                    'about' => $about_me,
                    
                ];

                if (isAdmin() || $userData->id == $user->id) {
                    $update = $db->where('id', $userData->id)->update(T_USERS, $update_data);
                    if ($update) {
                        $data = [
                            'status' => 200,
                            'message' => "Profile successfully updated!"
                        ];
                    }
                }
            }
        }
    }

    if ($option == 'delete') {
        if (empty($_POST['c_pass'])) {
            $errors[] = "Please check your details";
        } else {
            $c_pass      = secure($_POST['c_pass']);

            if (!password_verify($c_pass, $db->where('id', $userData->id)->getValue(T_USERS, 'password'))) {
                $errors[] = "Your current password is invalid";
            }
            if (empty($errors)) {
                if (isAdmin() || $userData->id == $user->id) {
                    $delete = deleteUser($userData->id);
                    if ($delete) {
                        $data = [
                            'status' => 200,
                            'message' => "Your account was successfully deleted, please wait.."
                        ];
                    }
                }
            }
        }
    }

    if ($option == 'password') {
        if (empty($_POST['c_pass']) || empty($_POST['n_pass']) || empty($_POST['rn_pass'])) {
            $errors[] = "Please check your details";
        } else {
            $c_pass      = secure($_POST['c_pass']);
            $n_pass      = secure($_POST['n_pass']);
            $rn_pass     = secure($_POST['rn_pass']);
            if (!password_verify($c_pass, $db->where('id', $userData->id)->getValue(T_USERS, 'password'))) {
                $errors[] = "Your current password is invalid";
            } else if ($n_pass != $rn_pass) {
                $errors[] = "Passwords don't match";
            } else if (strlen($n_pass) < 4 || strlen($n_pass) > 32) {
                $errors[] = "New password is too short";
            }
            if (empty($errors)) {
                $update_data = [
                    'password' => password_hash($n_pass, PASSWORD_DEFAULT),
                ];

                if (isAdmin() || $userData->id == $user->id) {
                    $update = $db->where('id', $userData->id)->update(T_USERS, $update_data);
                    if ($update) {
                        $data = [
                            'status' => 200,
                            'message' => "Your password was successfully updated!"
                        ];
                    }
                }
            }
        }
    }



    if ($option == 'get-recently-played') {
        if (empty($_POST['id'])) {
            $errors[] = "Please check your details";
        } else {

            if (!filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT)) {
                $errors[] = "Invalid id";
            }
            if ($_POST['id'] == 0) {
                $errors[] = "Invalid id";
            }
            if (empty($errors)) {
                $id                 = secure($_POST['id']);
                $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
                $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
                $data = [
                    'status' => 200,
                    'data' => GetRecentlyPlayed($id,$limit,$offset)
                ];
            }
        }
    }

    if ($option == 'get-favourites') {
        if (empty($_POST['id'])) {
            $errors[] = "Please check your details";
        } else {

            if (!filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT)) {
                $errors[] = "Invalid id";
            }
            if ($_POST['id'] == 0) {
                $errors[] = "Invalid id";
            }
            if (empty($errors)) {
                $id                 = secure($_POST['id']);
                $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
                $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
                $data = [
                    'status' => 200,
                    'data' => GetFavourites($id,$limit,$offset)
                ];
            }
        }
    }

    if ($option == 'get-blocks') {
        if (empty($_POST['id'])) {
            $errors[] = "Please check your details";
        } else {

            if (!filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT)) {
                $errors[] = "Invalid id";
            }
            if ($_POST['id'] == 0) {
                $errors[] = "Invalid id";
            }
            if (empty($errors)) {
                $id                 = secure($_POST['id']);
                $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
                $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
                $data = [
                    'status' => 200,
                    'data' => GetBlocks($id,$limit,$offset)
                ];
            }
        }
    }

    if ($option == 'get-liked') {
        if (empty($_POST['id'])) {
            $errors[] = "Please check your details";
        } else {

            if (!filter_var($_POST['id'], FILTER_SANITIZE_NUMBER_INT)) {
                $errors[] = "Invalid id";
            }
            if ($_POST['id'] == 0) {
                $errors[] = "Invalid id";
            }
            if (empty($errors)) {
                $id                 = secure($_POST['id']);
                $limit              = (isset($_POST['limit'])) ? secure($_POST['limit']) : 20;
                $offset             = (isset($_POST['offset'])) ? secure($_POST['offset']) : 0;
                $data = [
                    'status' => 200,
                    'data' => GetLiked($id,$limit,$offset)
                ];
            }
        }
    }



    if ($option == 'general') {
        if (empty($_POST['username']) || empty($_POST['email'])) {
            $errors[] = "Please check your details";
        } else {
            $username          = secure($_POST['username']);
            $email             = secure($_POST['email']);
            if (UsernameExits($_POST['username']) && $_POST['username'] != $userData->username) {
                $errors[] = "This username is already taken";
            }
            if (strlen($_POST['username']) < 4 || strlen($_POST['username']) > 32) {
                $errors[] = "Username length must be between 5 / 32";
            }
            if (!preg_match('/^[\w]+$/', $_POST['username'])) {
                $errors[] = "Invalid username characters";
            }
            if (EmailExists($_POST['email']) && $_POST['email'] != $userData->email) {
                $errors[] = "This e-mail is already taken";
            }
            if (!filter_var($_POST['email'], FILTER_VALIDATE_EMAIL)) {
                $errors[] = "This e-mail is invalid";
            }
            $country = $userData->country_id;
            if (in_array($_POST['country'], array_keys($countries_name))) {
                $country = secure($_POST['country']);
            }

            $gender = $userData->gender;
            if (in_array($_POST['gender'], ['male', 'female'])) {
                $gender = secure($_POST['gender']);
            }

            $age = $userData->age;
            if (is_numeric($_POST['age']) && ($_POST['age'] <= 100 || $_POST['age'] >= 0)) {
                $age = secure($_POST['age']);
            }

            
            $verified = $userData->verified;
            if (!empty($_POST['verified']) && IsAdmin()) {
                if ($_POST['verified'] == 'yes') {
                    $verified = 1;
                } else if ($_POST['verified'] == 'no') {
                    $verified = 0;
                }
                if ($verified == $userData->verified) {
                    $verified = $userData->verified;
                }
            }

            if (empty($errors)) {
                $update_data = [
                    'username' => $username,
                    'email' => $email,
                    'gender' => $gender,
                    'age' => $age,
                    'verified' => $verified
                ];

                if (isAdmin() || $userData->id == $user->id) {
                    $update = $db->where('id', $userData->id)->update(T_USERS, $update_data);
                    if ($update) {
                        $data = [
                            'status' => 200,
                            'message' => "Settings successfully updated!"
                        ];
                    }
                }
            }
        }
    }

    if ($option == 'update-profile-cover') {
        if (empty($_FILES)) {
            $errors[] = "Please check your details";
        }
        if (empty($errors)) {
            if (!empty($_FILES['cover']['tmp_name'])) {
                $type = (!empty($_REQUEST['type'])) ? secure($_REQUEST['type']) : "";
                $file_info = array(
                    'file' => $_FILES['cover']['tmp_name'],
                    'size' => $_FILES['cover']['size'],
                    'name' => $_FILES['cover']['name'],
                    'type' => $_FILES['cover']['type'],
                    'crop' => array('width' => 1600, 'height' => 400),
                    'allowed' => 'jpg,png,jpeg,gif'
                );
                if ($type == 'artist') {
                    $file_info['crop'] = array('width' => 1400, 'height' => 800);
                }
                $file_upload = shareFile($file_info);
                if (!empty($file_upload['filename'])) {
                    $update_data['cover'] = $file_upload['filename'];
                    $db->where('id', $user->id)->update(T_USERS, $update_data);
                    $data['status'] = 200;
                    $data['img'] = getMedia($file_upload['filename']);
                }
            }
        }
    }

    if ($option == 'update-profile-picture') {
        if (empty($_FILES)) {
            $errors[] = "Please check your details";
        }
        if (empty($errors)) {
            if (!empty($_FILES['avatar']['tmp_name'])) {
                $file_info = array(
                    'file' => $_FILES['avatar']['tmp_name'],
                    'size' => $_FILES['avatar']['size'],
                    'name' => $_FILES['avatar']['name'],
                    'type' => $_FILES['avatar']['type'],
                    'crop' => array('width' => 400, 'height' => 400),
                    'allowed' => 'jpg,png,jpeg,gif'
                );
                $file_upload = shareFile($file_info);
                if (!empty($file_upload['filename'])) {
                    $update_data['avatar'] = $file_upload['filename'];
                    $db->where('id', $user->id)->update(T_USERS, $update_data);
                    $data['status'] = 200;
                    $data['img'] = getMedia($file_upload['filename']);
                }
            }
        }
    }

   
   
    if ($option == 'update_notification_setting') {
        $array = array('email_on_follow_user' => 0,
                      'email_on_liked_track' => 0,
                      'email_on_reviewed_track' => 0,
                      'email_on_artist_status_changed' => 0,
                      'email_on_receipt_status_changed' => 0,
                      'email_on_new_track' => 0);
        if (!empty($_POST)) {
            foreach ($_POST as $key => $value) {
                if (in_array($key, array_keys($array)) && is_numeric($value) && $value == 1) {
                    $array[$key] = '1';
                }
            }
            if (isAdmin() || $userData->id == $user->id) {
                $update = $db->where('id', $userData->id)->update(T_USERS, $array);
                if ($update) {
                    $data = [
                        'status' => 200,
                        'message' => "Settings successfully updated!"
                    ];
                }
                else{
                    $data = [
                        'status' => 400,
                        'error' => 'something went wrong'
                    ];
                }
            }
            else{
                $data = [
                    'status' => 400,
                    'error' => 'you are not the profile owner'
                ];
            }
        }
        else{
            $data = [
                    'status' => 400,
                    'error' => 'check your details'
                ];
        }
    }
}
?>