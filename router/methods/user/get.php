<?php
add_controller('user');

$core = CoreAPI::getCore();

list($user_id, $scopes) = $core->auth(true);

$fields = $core->getFields('GET', [], ['id']);

if ($scopes === 1 && isset($fields['id'])) {
    $user_id = intval($fields['id']);
}
$user = get_user_by_id($user_id);
$core->response($user);
