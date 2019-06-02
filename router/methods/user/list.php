<?php

add_controller('user');

$core = CoreAPI::getCore();

$fields = $core->getFields('GET', [], []);

list($user_id, $user_scopes) = $core->auth(true, true);

$users = get_admins();
$core->response($users);