<?php

function get_regular_customers(int $place_id): array
{
    $db = DbMysqlProvider::getConnection();
    $result = $db->getAll("SELECT client_phone, client_name, count(client_phone) as cnt FROM booking where place_id=?i AND active=3 AND DATE(date_booking) >= DATE(NOW() - interval 2 MONTH) group by client_phone order by cnt DESC", $place_id);

    if (count($result) === 0){
        return [];
    }

    $regular = array_filter($result,
        function($item) {
            return $item['cnt'] > 2;
        }
    );

    return $regular;

}
function get_middle_on_week(int $place_id): array
{
    $db = DbMysqlProvider::getConnection();
    $result = $db->getAll("SELECT DAYOFWEEK(date_booking) as dw, count(booking_id) as cnt FROM booking where place_id=?i group by DAYOFWEEK(date_booking) order by DAYOFWEEK(date_booking)", $place_id);

    if (count($result) === 0){
        return [];
    }

    $days = [0 => 0, 1 => 0, 2 => 0, 3 => 0, 4 => 0, 5 => 0, 6 => 0];
    foreach ($result as $day) {
        $days[intval($day['dw']) - 1] = intval($day['cnt']);
    }
    shift_in_left($days);

    return $days;
}

function get_count_of_day(int $place_id): int
{
    $db = DbMysqlProvider::getConnection();
    $result = $db->getRow("SELECT count(booking_id) as cnt FROM booking where place_id=?i AND active=3 AND DATE(date_booking) = CURRENT_DATE", $place_id);

    return $result['cnt'] ?? 0;
}

function get_count_of_week(int $place_id): int
{
    $db = DbMysqlProvider::getConnection();
    $result = $db->getRow("SELECT count(booking_id) as cnt FROM booking where place_id=?i AND active=3 AND WEEK(date_booking, 1) = WEEK(NOW(), 1) AND YEAR(date_booking) = YEAR(NOW())", $place_id);

    return $result['cnt'] ?? 0;
}

function get_count_of_month(int $place_id): int
{
    $db = DbMysqlProvider::getConnection();
    $result = $db->getRow("SELECT count(booking_id) as cnt FROM booking where place_id=?i AND active=3 AND MONTH(date_booking) = MONTH(NOW()) AND YEAR(date_booking) = YEAR(NOW())", $place_id);

    return $result['cnt'] ?? 0;
}

function get_count_of_year(int $place_id): int
{
    $db = DbMysqlProvider::getConnection();
    $result = $db->getRow("SELECT count(booking_id) as cnt FROM booking where place_id=?i AND active=3 AND YEAR(date_booking) = YEAR(NOW())", $place_id);

    return $result['cnt'] ?? 0;
}

function get_all_count_where(int $place_id): array
{
    $db = DbMysqlProvider::getConnection();
    $result = $db->getAll("SELECT count(booking_id) as cnt, is_admin FROM booking where place_id=?i AND active=3 GROUP BY is_admin", $place_id);
    $types = ['admin' => 0, 'client' => 0];

    foreach ($result as $type) {

        if ((int) $type["is_admin"] === 1){
            $types["admin"] = (int) $type["cnt"];
            continue;
        }
        $types["client"] = (int) $type["cnt"];

    }
    return $types;
}