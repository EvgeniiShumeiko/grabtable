<?php
add_controller('place');
add_controller('booking');

$core = CoreAPI::getCore();

$fields = $core->getFields('GET', ['id'], []);

if (!is_numeric($fields['id'])) {
    $core->addError('invalidField', true, ['id']);
}

$place = get_place_by_id($fields['id']);

if (!count($place)) {
    $core->addError('not_found', true);
}

$bookings = get_booking_by_place($place['place_id']);

foreach ($bookings as $booking) {
    $place['bookings'][] = [
        'table_id' => intval($booking['table_id']),
        'date_booking' => $booking['date_booking'],
        'date_booking_end' => $booking['date_booking_end']
    ];
}

$core->response($place);