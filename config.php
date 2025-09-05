<?php
function load_credentials($service) {
    static $credentials = null;

    if ($credentials === null) {
        $jsonPath = __DIR__ . "/config/credentials.json";
        if (!file_exists($jsonPath)) {
            die("ERROR: credentials.json not found\n");
        }
        $json = file_get_contents($jsonPath);
        $credentials = json_decode($json, true);

        if ($credentials === null) {
            die("ERROR: Failed to parse credentials.json\n");
        }
    }

    if (!isset($credentials[$service])) {
        die("ERROR: Service '$service' not found in credentials.json\n");
    }

    return $credentials[$service];
}
?>