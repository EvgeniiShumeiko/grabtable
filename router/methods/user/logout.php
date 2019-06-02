<?php
add_controller('user');

$core = CoreAPI::getCore();
list($user_id, $user_scopes) = $core->auth(true, false);
$core->logout();

$core->response(['status' => 'success']);

