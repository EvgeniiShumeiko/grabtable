<?php

function check_ability_to_clocks(array $place, string $date): array
{
    $date_booking_start = strtotime($date);
    $date__booking_day = date("Y-m-d", $date_booking_start);
    $date_booking_end = $date_booking_start + 60*60*4;

    if ($date_booking_start < time()) {
        return [];
    }

    $day_of_week = intval(date("N",  $date_booking_start)) - 1;
    $now_clocks = $place['clocks'][$day_of_week] ?? ["start" => "", "end" => ""];

    if (empty($now_clocks['start']) || empty($now_clocks['end'])) {
        return [];
    }
    $now_clocks = [
        "start" => strtotime("{$date__booking_day} {$now_clocks['start']}"),
        "end" => strtotime("{$date__booking_day} {$now_clocks['end']}")
    ];


    if ($now_clocks["start"] > $date_booking_start || ($now_clocks["end"] - 60*60) < $date_booking_start) {
        return [];
    }

    $may_be_end_date = (intval($now_clocks["end"]) - intval($date_booking_start));

    if ($may_be_end_date < 60*60*4){
        $date_booking_end = $date_booking_start +  $may_be_end_date;
    }
    return [$date_booking_start, $date_booking_end];
}

function check_ability_to_booking(array $place, array $clocks, int $table_id): array
{
    $db = DbMysqlProvider::getConnection();
    $date_start = date("Y-m-d H:i:s", $clocks[0]);
    $date_end = date("Y-m-d H:i:s", $clocks[1]);
    $result = $db->getAll(
        "select booking_id from booking WHERE table_id=?i AND place_id=?i AND active=1 AND ((date_booking < ?s AND date_booking_end > ?s))",
        $table_id, intval($place['place_id']), $date_end, $date_start
    );
    if ($result) {
        return [];
    }
    return [$date_start, $date_end];
}

function create_booking(int $table_id, int $place_id, array $fields, array $clocks, bool $is_admin = false)
{
    $db = DbMysqlProvider::getConnection();
    $data = [
        'table_id' => $table_id,
        'place_id' => $place_id,
        'client_name' => $fields["name"] ?? '',
        'client_phone' => $fields["phone"] ?? '',
        'client_comment' => $fields["comment"] ?? '',
        'date_booking' => $clocks[0],
        'date_booking_end' => $clocks[1],
        'active' => 1,
        'is_admin' => intval($is_admin),
    ];
    $db->query("insert into booking SET ?u", $data);
    return $db->insertId();
}

function get_all_bookings(): array
{
    $db = DbMysqlProvider::getConnection();
    $bookings = $db->getAll("select * from booking WHERE  active=1 AND date_booking_end >= NOW()");
    return $bookings ?? [];
}

function get_booking_by_place(int $place_id): array
{
    $db = DbMysqlProvider::getConnection();
    $bookings = $db->getAll("select * from booking WHERE place_id=?i AND active=1 AND date_booking_end >= NOW() ORDER BY date_booking", $place_id);
    return $bookings ?? [];
}

function get_booking_by_id(int $booking_id): array
{
    $db = DbMysqlProvider::getConnection();
    $bookings = $db->getRow("select * from booking WHERE booking_id=?i AND active=1", $booking_id);
    return $bookings ?? [];
}

function approve_booking(int $booking_id)
{
    $db = DbMysqlProvider::getConnection();
    $db->query("UPDATE booking SET active=3, date_booking_end=NOW() WHERE booking_id=?i", $booking_id);
}

function decline_booking(int $booking_id)
{
    $db = DbMysqlProvider::getConnection();
    $db->query("UPDATE booking SET active=0 WHERE booking_id=?i", $booking_id);
}