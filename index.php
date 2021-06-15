<?php
$path = str_replace('arild/larkadb/', '', $_SERVER['REQUEST_URI']);
$args = array_filter(explode('/', trim($path, '/')));
$method = $_SERVER['REQUEST_METHOD'];

switch (count($args)) {
    case 0:
        // Probably the pseudonymizer service proxy.
        // TODO Doesn't work, need to login etc.
        respond_error('Cannot use pseudonymization service in the prototype.', 406);
        // $data = file_get_contents("php://input");
        // $pseuws_url = 'https://spraakbanken.gu.se/swell/portal/annotation/pseuws';
        // $response = post_request($pseuws_url, $data);
        // respond(json_decode($response));
        break;
    case 1:
        if ($method == 'POST'):
            // POST :key
            $data = file_get_contents("php://input");
            respond(save_version($args[0], $data, 1));
        elseif ($method == 'DELETE'):
            respond(delete_essay($args[0]));
        else:
            // GET :key
            respond(get_essay($args[0]));
        endif;
        break;
    case 2:
        // GET :key/status
        respond(get_status($args[0]));
        break;
    case 3:
        // POST :key/version/:version
        $data = file_get_contents("php://input");
        respond(save_version($args[0], $data, (int) $args[2]));
        break;
}

function respond($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
}

function respond_error($reason, $code=500) {
    http_response_code($code);
    respond(['error' => $reason]);
}

function get_essay($key) {
    // Open the edited file if it exists, otherwise the default one.
    $json = file_get_contents("essays/$key.json");
    if (!$json)
        return [ 'error' => "No essay named '$key'" ];
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

function save_version($key, $data, $version) {
    $result = file_put_contents("essays/$key.json", $data);
    return $result !== FALSE
        ? ['version' => $version]
        : ['error' => 'Could not save'];
}

function delete_essay($key) {
    $result = unlink("essays/$key.json");
    return $result ? ['deleted' => $key] : ['error' => 'Could not delete'];
}

function post_request($url, $data) {
    $options = [
        'http' => [
            'header'  => "Content-type: application/x-www-form-urlencoded\r\n",
            'method'  => 'POST',
            'content' => http_build_query($data),
        ],
    ];
    $context = stream_context_create($options);
    return file_get_contents($url, false, $context);
}
