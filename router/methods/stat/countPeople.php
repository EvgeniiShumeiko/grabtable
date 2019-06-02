<?php
add_controller('stat');
add_controller('place');

$core = CoreAPI::getCore();

$fields = $core->getFields("GET", ['place_id'], ["date"]);

list($user_id, $user_scopes) = $core->auth(true, false);

$core->isNum(['place_id']);

$place_id = intval($fields['place_id']);

$place = get_place_by_id($place_id, $user_id, $user_scopes == 1);

if (!count($place)) {
    $core->addError('not_found', true, ['place_id']);
}

$result = [
    "day" => get_count_of_day($place_id),
    "week" => get_count_of_week($place_id),
    "month" => get_count_of_month($place_id),
    "year" => get_count_of_year($place_id),
    "clients_from" => get_all_count_where($place_id)
];

$core->response($result);