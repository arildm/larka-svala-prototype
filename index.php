<?php
/**
 * @file A very simple "database" implementation.
 */

// Get parameters from path
$path = str_replace('arild/larkadb/', '', $_SERVER['REQUEST_URI']);
$args = array_filter(explode('/', trim($path, '/')));
$method = $_SERVER['REQUEST_METHOD'];

// Determine which endpoint/method is being used.
switch (count($args)) {
    case 1: // Path: /:key
        if ($method == 'POST'):
            // Save essay graph.
            $data = file_get_contents("php://input");
            respond(save_version($args[0], $data, 1));
        elseif ($method == 'DELETE'):
            // Delete essay.
            // Only for the prototype. In the real SweLL portal, deletion happens only within Django.
            respond(delete_essay($args[0]));
        else:
            // Get essay graph.
            respond(get_essay($args[0]));
        endif;
        break;
    case 2: // Path: /:key/status
        // Get status.
        respond(get_status($args[0]));
        break;
    case 3: // Path: /:key/version/:version (should be POST)
        // Auto-save new version of the essay.
        $data = file_get_contents("php://input");
        respond(save_version($args[0], $data, (int) $args[2]));
        break;
    case 0:
        // Probably the pseudonymizer service proxy.
        // TODO Doesn't work, need to login etc.
        respond_error('Cannot use pseudonymization service in the prototype.', 406);
        break;
    }

/** Send JSON response. */
function respond($data) {
    header('Content-Type: application/json');
    echo json_encode($data);
}

/** Send error response. */
function respond_error($reason, $code=500) {
    http_response_code($code);
    respond(['error' => $reason]);
}

/** Load the essay file. */
function get_essay($key) {
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

/** Get status (fake, never "done"). */
function get_status($key) {
    return ['done' => FALSE];
}

/** Save essay to file (skips version check). */
function save_version($key, $data, $version) {
    // Note: This assumes that the directory and file are writable
    $result = file_put_contents("essays/$key.json", $data);
    return $result !== FALSE
        ? ['version' => $version]
        : ['error' => 'Could not save'];
}

/** Delete essay file. */
function delete_essay($key) {
    $result = unlink("essays/$key.json");
    return $result ? ['deleted' => $key] : ['error' => 'Could not delete'];
}
