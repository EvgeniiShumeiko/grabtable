<?php
add_controller('place');

$core = CoreAPI::getCore();
$option = ['vk', 'fb', 'inst', 'site'];

$fields = $core->getFields('POST', ['name', 'logo', 'phone', 'middle_check', 'address', 'map_id', 'description', 'clocks'], array_merge($option, ['images']));

list($user_id, $user_scopes) = $core->auth(true,true);

$core->isNum(['map_id', 'middle_check']);

// Проверка телефона
$phone = checkNumber($fields['phone']);
if ($phone === 0) {
    $core->addError('invalidField', true, ['phone']);
}
$fields['phone'] = $phone;


//Проверка логотипа, что это ссылка
if (!filter_var($fields['logo'], FILTER_VALIDATE_URL)) {
    $core->addError('invalidField', true, ['logo']);
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



$maps = get_maps();
$map_ids = array_column($maps, 'map_id');

if (!in_array($fields['map_id'], $map_ids)) {
    $core->addError('invalidField', true, ['map_id']);
}
$social = [];
foreach ($option as $link)
{
    if (isset($fields[$link]))
    {
        if (filter_var($fields[$link], FILTER_VALIDATE_URL)) {
            $social[$link] = $fields[$link];
            unset($fields[$link]);
        }
    }
}
$fields['links'] = json_encode($social);


$result = create_place($fields);
if (!$result) {
    $core->addError('unknownError', true);
}
$core->response(['status' => 'success', 'place_id' => $result]);
