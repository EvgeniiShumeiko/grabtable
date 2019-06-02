<?php
add_controller('booking');
add_controller('place');

$core = CoreAPI::getCore();

list($user_id, $user_scopes) = $core->auth(true, false);

$fields = $core->getFields('POST', ['booking_id'], []);
$core->isNum(['booking_id']);

$booking_id = intval($fields['booking_id']);
$booking = get_booking_by_id($booking_id);

if (!count($booking)) {
    $core->addError('not_found', true, ['booking_id']);
}

$place = get_place_by_id($booking['place_id'], $user_id, $user_scopes);

if (!count($place)) {
    $core->addError('access_denied', true, ['booking_id']);
}

approve_booking($booking_id);

$core->response(['status' => 'success']);