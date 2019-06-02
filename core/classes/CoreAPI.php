<?php


class CoreAPI
{

    private $needAuth;
    private $needParams, $optionParams;
    private $methods, $appDir;
    private $errorCodes, $errors, $fields, $token;

    protected static $core;
    public static function getCore()
    {
        if(self::$core === null) {
            self::$core = new CoreAPI();
            if(!self::$core) {
                exit('Ошибка core: not found');
            }
        }
        return self::$core;
    }

    public function __construct()
    {
        $this->needAuth = false;
        $this->needParams = array();
        $this->optionParams = array();
        $this->errors = array();
        $this->setErrors();
        return $this;
    }

    public function setAllowMethods(array $methods)
    {
        if (!is_array($methods)){
            throw new InvalidArgumentException("It is not array");
        }
        $this->methods = $methods;

    }

    public function setAppDirectory(string $directory)
    {
        if (!file_exists($directory)) {
            throw new InvalidArgumentException("Directory is wrong!");
        }
        $this->appDir = $directory;
    }

    public function needAuth(bool $auth = true)
    {
        $this->needAuth = $auth;
    }

    public function getRoutePath()
    {
        if (!isset($_GET['case']) || !isset($_GET['method'])) {
            $this->addError('needFields', true);
        }

        $case = $_GET['case'];
        $method = $_GET['method'];

        if (!isset($this->methods[$case]) || !isset($this->methods[$case][$method])) {
            $this->addError('methodNotFound', true, [$method]);
        }


        return $this->appDir . 'methods' . DIRECTORY_SEPARATOR . $case . DIRECTORY_SEPARATOR . "{$method}.php";
    }

    public function auth($need=true, $super_admin=false) : array
    {
        $headers = getallheaders();

        if ($need || isset($headers['Authorization'])) {
            if ((!isset($headers['Authorization']) || empty($headers['Authorization']))) {
                $this->addError('needAuth', true);
            }

            $auth = explode(' ', $headers['Authorization']);
            if (!isset($auth[1])) {
                $this->addError('needAuth', true);
            }


            $user = $this->get_user_by_token($auth[1]);
            if ($super_admin && $user['scopes'] != 1 ) {
                $this->addError('access_denied', true);
            }
            $this->token = $auth[1];
            return [(int)$user['user_id'], intval($user['scopes'])];
        }
        return [false, false];
    }

    public function logout()
    {
        $link = DbMysqlProvider::getConnection();
        $query = "UPDATE tokens SET active = 0 WHERE access_token=?s AND active=1";
        $link->query($query, $this->token);

    }
    public function getFields(string $requestMethod, array $required, array $optional): array
    {
        if ($_SERVER['REQUEST_METHOD'] != mb_strtoupper($requestMethod)) {
            if ($_SERVER['REQUEST_METHOD'] != 'OPTIONS') {
                $this->addError('only' . mb_strtoupper($requestMethod), true);
            }
        }

        $getInParam = array_diff($_GET, ['case' => $_GET['case'], 'method' => $_GET['method']]);
        $in = mb_strtoupper($requestMethod) == "POST" ? $_POST : $getInParam;
        if (is_array($required) && is_array($optional)) {
            if (!empty($required)) {
                foreach ($required as $key => $value) {
                    switch (gettype($value)) {
                        case 'array':
                            $leastOneFlag = false;
                            foreach ($value as $kk => $vv) {
                                $optional[] = $vv;
                                if (!isset($in[$vv]) || $in[$vv]) {
                                    $leastOneFlag = true;
                                }
                            }
                            if (!$leastOneFlag) {
                                $this->addError('needFields', false, join(" || ", $value));
                            }
                            break;
                        case 'string':
                            if (!isset($in[$value]) || $in[$value] == '') {
                                $fieldsError[] = $value;
                            }
                            break;
                    }
                }
                if (isset($fieldsError)) {
                    $this->addError('needFields', false, $fieldsError);
                    unset($fieldsError);
                }
            }

            if (is_array($in) && !empty($in)) {
                $merge = array_merge($required, $optional);
                foreach ($in as $key => $value) {
                    if (!in_array($key, $merge)) {
                        $fieldsError[] = $key;
                    }
                }
                if (isset($fieldsError)) {
                    $this->addError('unknownField', false, $fieldsError);
                    unset($fieldsError);
                }
            }
        }
        $this->throwErrors();
        $this->fields = $in;
        return $in;
    }
    public function isNum(array $fields)
    {
        $error_fields = array_values(array_filter($fields, function ($item){
            return !is_numeric($this->fields[$item]);
        }));

        if ($error_fields) {
            $this->addError('unacceptable_symbols', true, $error_fields);
        }
    }
    public function addError(string $code, bool $throw = false, array $fields = [])
    {

        $error = [
            'code' => $this->errorCodes[$code][0],
            'error' => $this->errorCodes[$code][1],
        ];

        if (count($fields)) {
            $error['fields'] = $fields;
        }
        array_push($this->errors, $error);

        if ($throw) {
            $this->throwErrors();
        }

    }

    public function response(array $data)
    {
        $response = ['data' => $data];
        if (count($this->errors)) {
            $response['errors'] = $this->errors;
        }
        $this->responseBuilder($response, !DEV);
    }


    private function get_user_by_token(string $token): array
    {
        $link = DbMysqlProvider::getConnection();
        $query = "SELECT users.user_id, scopes, active FROM tokens LEFT JOIN users ON tokens.user_id = users.user_id WHERE access_token=?s AND active=1";
        $user = $link->getRow($query, $token);
        if (!$user) {
            $this->addError('permission_denied', true);
        }

        return $user;
    }

    private function throwErrors()
    {
        if (count($this->errors)){
            http_response_code(405);
            $response = ['errors' => $this->errors];
            $this->responseBuilder($response, true);
        }
    }

    private function responseBuilder(array $response, $throw = false)
    {
        $json = json_encode($response, JSON_UNESCAPED_UNICODE);
        print_r($json);

        if ($throw) {
            exit(0);
        }

    }

    private function setErrors()
    {
        $this->errorCodes = [
            "unknownError"         => array(1, "Unknown Error"),
            "appDisable"           => array(2, "Application is disable"),
            "methodNotFound"       => array(3, "Method not found"),
            "onlyGET"              => array(4, "Only GET requests are supported"),
            "onlyPOST"             => array(5, "Only POST requests are supported"),
            "not_found"            => array(6, "Nothing found on your request"),
            "fileNotFound"         => array(7, "File not found"),
            "needFields"           => array(8, "Missing a required fields"),
            "access_denied"        => array(9, "Access denied. Invalid field"),
            "unacceptable_symbols" => array(10, "Invalid characters in the field"),
            "permission_denied"    => array(11, "Permission_denied"),
            "invalidField"         => array(12, "Invalid field"),
            "unknownField"         => array(13, "Unknown field"),
            "same"                 => array(14, "Field already exists"),
            "needAuth"             => array(15, "Authorization required"),
        ];
    }

}