<?php

$file = $_GET['file'];
if (empty($file)) {
    $file = 'default.html';
}

include("./template/" . $file);
