<?php

$file = $_GET['file'];
if (empty($file)) {
    $file = 'default.txt';
}

include("./template/" . $file);
