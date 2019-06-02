<?php

add_controller('booking');
add_controller('place');

$core = CoreAPI::getCore();
list($user_id, $user_scopes) = $core->auth(true, false);

$need_field = $user_scopes === 1 ? [] : ['place_id'];

$optional_field = $user_scopes === 1 ? ['place_id'] : [];

$fields = $core->getFields('GET', $need_field, $optional_field);

if (isset($fields['place_id']) && !is_numeric($fields['place_id'])) {
    $core->addError('invalidField', true, ['place_id']);
}


if ($user_scopes === 1 && !isset($fields['place_id'])){
    $booking = get_all_bookings();
} else {
    $place = get_place_by_id($fields['place_id'], $user_id, $user_scopes === 1);

    if (!count($place)) {
        $core->addError('not_found', true, ['place_id']);
    }
    $booking = get_booking_by_place($fields['place_id']);
}

$core->response($booking);