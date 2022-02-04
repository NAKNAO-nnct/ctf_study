<?php

session_start();

$user = new User();
$auth = $_SESSION['auth'] ?? null;

$path = $_GET['path'] ?? 'top';
if (empty($path)) {
    $path = 'top';
}

if ($path === 'top') {
    if (empty($_SESSION['auth'])) {
        print '<meta http-equiv="refresh" content="1; URL=./index.php?path=logout">';
        return;
    }
    if ($user->isAdmin($auth)) {
        include("./flag.txt");
    }

    ?>
    <h1>Welcome</h1>
    <a href="./index.php?path=logout" type="submit">LOGOUT</a>
    <?
    return;
} elseif ($path === 'login') {
    $is_do_login = $_SERVER['REQUEST_METHOD'] == 'POST';

    if(!$is_do_login) {
        ?>
        <h2>Login</h2>
        <form action="./index.php?path=login" method="post">
            <input type="text" name="username" placeholder="user name">
            <input type="password" name="password" placeholder="password">

            <input type="submit" value="login">
        </form>

        <h2>Register</h2>
        <form action="./index.php?path=register" method="post">
            <input type="text" name="username" placeholder="user name">
            <input type="password" name="password" placeholder="password">

            <input type="submit" value="register">
        </form>
        <?php
        return;
    }

    if (!empty($_SESSION['auth'])) {
        print '<meta http-equiv="refresh" content="1; URL=./index.php?path=top">';
        return;
    }

    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    $auth = $user->login($username, $password);
    $_SESSION['auth'] = $auth;

    print '<meta http-equiv="refresh" content="1; URL=./index.php?path=top">';
} elseif ($path === 'logout') {
    $user->logout();
    $_SESSION['auth'] = null;
    print '<meta http-equiv="refresh" content="1; URL=./index.php?path=login">';
    return;
} elseif ($path === 'register') {
    $_SESSION['auth'] = null;
    $username = $_POST['username'] ?? '';
    $password = $_POST['password'] ?? '';

    if (empty($username) || empty($password)) {
        print '<meta http-equiv="refresh" content="1; URL=./index.php?path=top">';
        return;
    }

    $auth = $user->register($username, $password);
    $_SESSION['auth'] = $auth;
    print '<meta http-equiv="refresh" content="1; URL=./index.php?path=top">';

    return;
} else {
    print '<meta http-equiv="refresh" content="1; URL=./index.php?path=top">';
    return;
}


class DB {
    private $db;

    function __construct()
    {
        $this->db = new SQLite3('./user.db');

        $this->initSql();
    }

    private function initSql()
    {
        $init_query = explode(';', implode(" ", file(__DIR__ . '/init.sql', FILE_IGNORE_NEW_LINES | FILE_SKIP_EMPTY_LINES)));
        
        foreach($init_query as $query) {
            $this->execQuery($query);
        }
    }

    public function execQuery($query)
    {
        if (empty($query)) {
            return;
        }
        return $this->db->query($query);
    }
}

class User {
    private $db;

    function __construct()
    {
        $this->db = new DB();;
    }

    public function login($user, $password) {
        $auth = $this->auth($user, $password);
        return $auth !== 'guest' ? $auth : null;
    }

    public function auth($user, $password)
    {
        $sql = "SELECT * from `user` where username = '" .  $user ."' and password = '" . $password . "' limit 1;";
        $result = $this->db->execQuery($sql)->fetchArray() ?? [];

        if (!empty($result) && $result[1] === 'admin') {
            return 'admin';
        } elseif (!empty($result)) {
            return 'user';
        }

        return 'guest';
    }

    public function logout()
    {
        return null;
    }

    public function register($user, $password)
    {
        $query = "INSERT INTO user (username, password) VALUES ('". $user . "', '" . $password . "');";
        $this->db->execQuery($query);
        return $this->login($user, $password);
    }

    public function isAdmin($auth)
    {
        return $auth === 'admin';
    }
}
