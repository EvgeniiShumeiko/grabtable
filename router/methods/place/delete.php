<?php
add_controller('place');

$core = CoreAPI::getCore();

$fields = $core->getFields('POST', ['place_id'], []);

list($user_id, $user_scopes) = $core->auth(true,true);

$core->isNum(['place_id']);

$place_id = intval($fields['place_id']);

$place = get_place_by_id($place_id);

if (!count($place)) {
    $core->addError('not_found', true);
}

$result = delete_place($place_id);


if (!$result) {
    $core->addError('unknownError', true);
}

$core->response(['status' => 'success']);
