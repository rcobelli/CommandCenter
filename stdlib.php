<?php

function getUrl() {
    $output = "https://$_SERVER[HTTP_HOST]";
    if ($_SERVER['SERVER_PORT'] != 443) {
        return $output . ":$_SERVER[SERVER_PORT]/";
    } else {
        return $output . "/";
    }
}

enum Mode
{
    case new;
    case edit;
    case delete;
}