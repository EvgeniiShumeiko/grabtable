<?php
add_controller('place');

$core = CoreAPI::getCore();

$fields = $core->getFields('POST', ['place_id'], ['vk', 'fb', 'inst', 'site', 'name', 'logo', 'images', 'phone', 'middle_check', 'address', 'map_id', 'tables', 'description', 'clocks']);

list($user_id, $user_scopes) = $core->auth(true,true);

$core->isNum(['place_id']);

$place_id = intval($fields['place_id']);

$place = get_place_by_id($place_id);

if (!count($place)) {
    $core->addError('not_found', true);
}

if (isset($fields['phone'])) {
    // Проверка телефона
    $phone = checkNumber($fields['phone']);
    if ($phone === 0) {
        $core->addError('invalidField', true, ['phone']);
    }
    $fields['phone'] = $phone;
}


if (isset($fields['logo'])) {
    //Проверка логотипа, что это ссылка
    if (!filter_var($fields['logo'], FILTER_VALIDATE_URL)) {
        $core->addError('invalidField', true, ['logo']);
    }

    $fields['logo_url'] = $fields['logo'];
    unset($fields['logo']);
}

if (isset($fields['images'])) {

    try {
        $images = json_decode($fields['images'], true);
    } catch (Exception $e) {
        $core->addError('invalidField', true, ['images']);

    }
    foreach ($images as $img)
    {
        if (!filter_var($img, FILTER_VALIDATE_URL)) {
            $core->addError('invalidField', true, ['images']);
        }

    }
}

if (isset($fields['map_id'])) {
    $maps = get_maps();
    $map_ids = array_column($maps, 'map_id');

    if (!in_array($fields['map_id'], $map_ids)) {
        $core->addError('invalidField', true, ['map_id']);
    }
}

if (isset($fields['middle_check'])) {
    $core->isNum(['middle_check']);
}
$social_name = ['vk', 'fb', 'inst', 'site'];
$social = [];
foreach ($social_name as $link)
{
    if (isset($fields[$link]))
    {
        if (filter_var($fields[$link], FILTER_VALIDATE_URL)) {
            $social[$link] = $fields[$link];
        }
        unset($fields[$link]);
    }
}

$fields['links'] = json_encode($social);

$result = update_place($fields);

if (!$result) {
    $core->addError('unknownError', true);
}

$core->response(['status' => 'success']);
