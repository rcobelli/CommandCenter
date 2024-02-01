<?php

function getUrl() {
    return "https://$_SERVER[HTTP_HOST]/";
}

enum Mode
{
    case new;
    case edit;
    case delete;
}