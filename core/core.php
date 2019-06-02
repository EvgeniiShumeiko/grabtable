<?php

require_once(CORE_DIR . '/classes/DbMysqlProvider.php');
require_once(CORE_DIR . '/classes/CoreAPI.php');

$core = CoreAPI::getCore();

$core->setAllowMethods(
    [
        'user'    => [
            'auth' => [],
            'create' => [],
            'logout' => [],
            'delete' => [],
            'list' => [],
            'get' => [],
            'edit' => []
        ],
        'booking' => [
            'new'     => [],
            'approve' => [],
            'decline'  => [],
            'edit'    => [],
            'list'     => [],
        ],
        'sudo'    => [
           'createAdmin'     => [],
           'addPlaceToAdmin' => [],
           'getAdmins'       => [],
        ],
        'place'   => [
            'edit'   => [],
            'create' => [],
            'delete' => [],
            'get'    => [],
            'getAll' => []
        ],
        'stat' => [
            'countPeople' => [], // за день, за месяц, за год, за неделю
            'middleOnWeek' => [], // статистика по дням недели средняя за весь период
            'regularCustomer' => [], //постоянные клиенты (3 раза за 2 месяца)
        ]
    ]
);

$core->setAppDirectory(ROUTER_DIR);