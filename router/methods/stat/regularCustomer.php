<?php
add_controller('stat');
add_controller('place');

$core = CoreAPI::getCore();

$fields = $core->getFields("GET", ['place_id'], []);
list($user_id, $user_scopes) = $core->auth(true, false);

$core->isNum(['place_id']);

$place_id = intval($fields['place_id']);

$place = get_place_by_id($place_id, $user_id, $user_scopes == 1);

if (!count($place)) {
    $core->addError('not_found', true, ['place_id']);
}

$regular = get_regular_customers($place_id);

$core->response($regular);