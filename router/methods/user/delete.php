<?php

add_controller('user');

$core = CoreAPI::getCore();

$fields = $core->getFields('POST', ['user_id'], []);

list($user_id, $user_scopes) = $core->auth(true, true);

$core->isNum(['user_id']);

$user_id = intval($fields['user_id']);

$user = get_user_by_id($user_id);

if (!count($user)) {
    $core->addError('not_found', true, ['user_id']);
}

delete_user($user_id);

$core->response(['status' => 'success']);