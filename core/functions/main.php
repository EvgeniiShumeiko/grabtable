<?php

function add_controller(string $name)
{
    $core = CoreAPI::getCore();
    $file = ROUTER_DIR . DIRECTORY_SEPARATOR . 'controllers' . DIRECTORY_SEPARATOR . "{$name}.php";

    if (!file_exists($file)) {
        throw new InvalidArgumentException('Controller Not Found');
    }
    require_once($file);
}

function generate_token($n)
{
    $key = '';
    $pattern = '1234567890abcdefghijklmnopqrstuvwxyz';
    $counter = strlen($pattern)-1;

    for ($i=0; $i<$n; $i++) {
        $key.= $pattern{rand(0, $counter)};
    }
    return $key;
}

if (!function_exists('getallheaders')) {
    function getallheaders()
    {
        $headers = array();
        foreach ($_SERVER as $name => $value) {
            if (substr($name, 0, 5) == 'HTTP_') {
                $headers[str_replace(' ', '-', ucwords(strtolower(str_replace('_', ' ', substr($name, 5)))))] = $value;
            }
        }
        return $headers;
    }
}

function checkNumber(string $number): int
{
    $length = strlen(intval($number));
    if ($length === 11) {
        $number[0] = (strlen($number) === 11 && $number[0] == 8) ? 7 : $number[0];
        return intval($number);
    }
    return 0;
}

function shift_in_left (&$arr) {
    $item = array_shift($arr);
    array_push ($arr,$item);
}

function shift_in_right (&$arr) {
    $item = array_pop($arr);
    array_unshift ($arr,$item);
}

function get_env(): array
{
    try {
        $env = json_decode(file_get_contents(ROOT_DIR . ".env"), true);
        if (isset($env["database"])) {
            return $env;
        }
        return [];
    } catch (Exception $e){
        return [];
    }


}