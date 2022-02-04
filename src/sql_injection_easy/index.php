<?php

session_start();
$is_do_login = $_SERVER['REQUEST_METHOD'] == 'POST';

if ($is_do_login) {
    $username = $_POST['username'];
    $password = $_POST['password'];
}
