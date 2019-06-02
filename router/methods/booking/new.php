<?php
add_controller('booking');
add_controller('place');

$core = CoreAPI::getCore();


list($user_id, $scopes) = $core->auth(false);

if ($user_id) {
    $need_fields = ['place_id', 'date', 'table_id'];
    $optional_fields = ['comment', 'phone', 'name'];
} else {
    $need_fields = ['place_id', 'date', 'table_id', 'name', 'phone'];
    $optional_fields = ['comment'];
}

$fields = $core->getFields('POST', $need_fields, $optional_fields);

$core->isNum(['place_id', 'table_id']);

$place_id = intval($fields['place_id']);
$table_id = intval($fields['table_id']);


if (isset($fields['phone'])) {
    // Проверка телефона
    $phone = checkNumber($fields['phone']);
    if ($phone === 0) {
        $core->addError('invalidField', true, ['phone']);
    }
    $fields['phone'] = $phone;

}

$user_for_place = $user_id && !isset($fields['phone']) && !isset($fields['name']) ? $user_id : 0;
$place = get_place_by_id($place_id, $user_for_place);

if (!count($place)) {
    $core->addError('not_found', true, ['place_id']);
}

$table_ids = array_column($place['tables'], 'id');

if (!in_array($table_id, $table_ids)) {
    $core->addError('not_found', true, ['table_id']);
}

//проверить часы работы, проверить что не залазит на текущее бронирование
$clocks = check_ability_to_clocks($place, $fields['date']);

if (!count($clocks)) {
    $core->addError('permission_denied', true, ['date']);
}

$bookings = check_ability_to_booking($place, $clocks, $table_id);

if (!count($bookings)) {
    $core->addError('permission_denied', true, ['date']);
}
$admin_ids = array_map("trim", explode(',', $place["admin_ids"]));


$booking_id = create_booking($table_id, $place_id, $fields, $bookings, $user_id && in_array($user_id, $admin_ids));

if (!$booking_id) {
    $core->addError('unknownError', true, ['date']);
}

$core->response(["booking_id" => $booking_id, "table_id" => $table_id, "date" => $bookings, "name"=> $fields["name"], "phone" => $fields['phone']]);