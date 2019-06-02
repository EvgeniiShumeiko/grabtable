<?php

/**
 * Функция проверки логина на корректность
 * @param string $login
 *
 * @return bool
 */
function check_login(string $login): bool
{
    return preg_match("/^[a-zA-Z0-9]{3,15}$/", $login);
}

function get_user_by_login(string $login): array
{
    $db = DbMysqlProvider::getConnection();
    $user = $db->getRow("select * from users where login=?s", $login);
    return $user ?? [];
}

function get_user_by_id(int $id): array
{
    $db = DbMysqlProvider::getConnection();
    $user = $db->getRow("select users.*, places.name as place_name, place_id from users left join places ON user_id IN (places.admin_ids) where user_id=?i", $id);
    return $user ?? [];
}

function create_token(int $user_id): string
{
    $db = DbMysqlProvider::getConnection();
    while (true){
        try {
            $token = generate_token(32);
            $data = ['access_token' => $token, "user_id" => $user_id];
            $db->query("insert into tokens SET ?u", $data);
            return $token;
        } catch (Exception $e) {
            continue;
        }
    }
}

function create_user(string $login, string $password, string $name): int
{
    $db = DbMysqlProvider::getConnection();
    $data = ['login' => $login, 'password' => $password, 'name' => $name];
    $db->query("insert into users SET ?u", $data);
    return $db->insertId();
}

function clear_tokens_by_user_id(int $user_id)
{
    $db = DbMysqlProvider::getConnection();
    $db->query("delete from tokens where user_id=?i", $user_id);
}

function delete_user(int $user_id)
{
    clear_tokens_by_user_id($user_id);
    $db = DbMysqlProvider::getConnection();
    $db->query("delete from users where user_id=?i", $user_id);
}

function get_admins()
{
    $db = DbMysqlProvider::getConnection();
    $user = $db->getAll("select user_id,login, users.name as user_name, users.date_create, places.name as place_name, place_id from users left join places on user_id IN (places.admin_ids) where scopes=0");
    return $user ?? [];
}

function add_user_to_place(int $user_id, int $place_id)
{
    $db =DbMysqlProvider::getConnection();
    $db->query("update places set admin_ids = CONCAT(admin_ids, ?s) where place_id=?i", $user_id.",", $place_id);
}