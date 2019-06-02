<?php
add_controller('user');
add_controller('place');

$core = CoreAPI::getCore();


$fields = $core->getFields('POST', ['login', 'password', 'name'], ['place_id']);

list($user_id, $user_scopes) = $core->auth(true,true);

if (isset($fields['place_id']) &&  !is_numeric($fields['place_id'])) {
    $core->addError('invalidField', true, ['place_id']);
}

$login = $fields['login'];

$user = get_user_by_login($login);

if (count($user)) {
    $core->addError('same', true, ['login']);
}

$password = password_hash($fields['password'], PASSWORD_DEFAULT);

$user_id = create_user($login, $password, $fields['name']);

if (isset($fields['place_id'])) {
    $place = get_place_by_id($fields['place_id']);
    $place_id = intval($fields['place_id']);

    if (!count($place)) {
        $core->addError('not_found', false, ['place_id']);
    }
    add_user_to_place($user_id, $place_id);
}


$core->response(['user_id' => $user_id]);