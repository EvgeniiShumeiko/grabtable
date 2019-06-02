<?php
add_controller('user');

$core = CoreAPI::getCore();

$fields = $core->getFields('GET', ['login', 'password'], []);


$login = $fields['login'];
if (!check_login($login)) {
    $core->addError('invalidField', true, ['login']);
}

$user = get_user_by_login($login);

if (!count($user)) {
    $core->addError('access_denied', true, ['login']);
}

if (!password_verify($fields['password'], $user['password'])) {
    $core->addError('access_denied', true, ['login', 'password']);
}

$token = create_token($user['user_id']);

$core->response(['token' => $token, 'user_id' => $user["user_id"], 'user_login' => $user["login"], 'user_scopes' => $user['scopes']]);

