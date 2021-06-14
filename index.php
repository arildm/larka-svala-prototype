<?php
$path = str_replace('arild/larkadb/', '', $_SERVER['REQUEST_URI']);
$args = explode('/', trim($path, '/'));

switch (count($args)) {
    case 1:
        // GET :key
        respond(get_essay($args[0]));
        break;
    case 2:
        // GET :key/status
        respond(get_status($args[0]));
        break;
    case 3:
        // POST :key/version/:version
        respond(save_version($args[0], $args[2]));
        break;
}

function respond($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
}

function get_essay($key) {
    $json = file_get_contents('g.json');
    return [
        'access_write' => TRUE,
        'done' => FALSE,
        'state' => $json,
        'version' => 1,
    ];
}

function get_status($key) {
    return ['done' => FALSE];
}

function save_version($key, $version) {
    return ['version' => $version];
}
