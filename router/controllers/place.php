<?php

function get_all_place(int $offset, int $count): array
{

    $db = DbMysqlProvider::getConnection();
    $places = $db->getAll(
        "SELECT place_id, name, logo_url, images, phone, middle_check, clocks, address, map_id, description, links 
                 FROM places WHERE active=1 LIMIT ?i, ?i", $offset, $count
    );
    $places = array_map(
        function ($item) {

            $item['clocks'] = !empty($item['clocks']) ? json_decode(
                    $item['clocks'], true
                ) ?? [] : [];

            $item['links'] = !empty($item['links']) ? json_decode(
                    $item['links'], true
                ) ?? [] : [];

            $item['images'] = !empty($item['images']) ? json_decode(
                    $item['images'], true
                ) ?? [] : [];

            $item['vk'] = $item['links']['vk'] ?? '';
            $item['fb'] = $item['links']['fb'] ?? '';
            $item['inst'] = $item['links']['inst'] ?? '';
            $item['site'] = $item['links']['site'] ?? '';

            unset($item['links']);
            return $item;
        }, $places
    );
    return $places ?? [];
}

function get_place_by_id(int $place_id, int $user_id = 0, bool $is_sudo = false): array
{
    $db = DbMysqlProvider::getConnection();
    $where = !$is_sudo && $user_id !== 0
        ? "AND FIND_IN_SET({$user_id}, places.admin_ids)" : '';
    $query = "SELECT place_id, name, logo_url, images, phone, middle_check, clocks, address, map, tables, description, active, links, admin_ids
                 FROM places join maps on places.map_id = maps.map_id WHERE place_id=?i {$where}";
    $places = $db->getRow($query, $place_id);

    if ($places) {
        $places['clocks'] = !empty($places['clocks']) ? json_decode(
                $places['clocks'], true
            ) ?? [] : [];

        $places['links'] = !empty($places['links']) ? json_decode(
                $places['links'], true
            ) ?? [] : [];

        $places['vk'] = $places['links']['vk'] ?? '';
        $places['fb'] = $places['links']['fb'] ?? '';
        $places['inst'] = $places['links']['inst'] ?? '';
        $places['site'] = $places['links']['site'] ?? '';

        unset($places['links']);
        $places['tables'] = !empty($places['tables']) ? json_decode(
                $places['tables'], true
            ) ?? [] : [];
        $places['images'] = !empty($places['images']) ? json_decode(
                $places['images'], true
            ) ?? [] : [];
    }
    return $places ?? [];
}

function create_place(array $place): int
{
    $db = DbMysqlProvider::getConnection();
    $data = [
        'name'         => $place['name'],
        'logo_url'    => $place['logo'],
        'phone'        => $place['phone'],
        'middle_check' => $place['middle_check'],
        'clocks'       => $place['clocks'],
        'address'      => $place['address'],
        'map_id'       => $place['map_id'],
        'description'  => $place['description'],
        'links'       => $place['links'],
        'active'       => 1,
        'images'       => $place['images'] ?? ''
    ];
    $db->query("insert into places SET ?u", $data);
    return $db->insertId();
}

function update_place(array $place): bool
{
    $db = DbMysqlProvider::getConnection();
    $place_id = $place['place_id'];
    unset($place['place_id']);
    $result = $db->query("update places SET ?u WHERE place_id=?i", $place, $place_id);
    return $result;
}

function delete_place(int $place_id): bool
{
    $db = DbMysqlProvider::getConnection();
    $result = $db->query("UPDATE places SET active=0 WHERE place_id=?i",$place_id);
    return $result;
}

function get_maps(): array
{
    $db = DbMysqlProvider::getConnection();
    $maps = $db->getAll("select * from maps");
    return empty($maps) ? [] : $maps;
}
