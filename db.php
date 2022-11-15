<?php
error_reporting(E_ALL);
$env = 'development';
if ($env == 'production') {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=dbname', 'user', 'pw');
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Connection failed: ' . $e->getMessage()]));
    }
    define('CALLBACK_URI', '');
} elseif ($env == 'development') {
    try {
        $pdo = new PDO('mysql:host=localhost;dbname=spotify', 'root', '');
    } catch (PDOException $e) {
        die(json_encode(['error' => 'Connection failed: ' . $e->getMessage()]));
    }
    define('CALLBACK_URI', 'http://localhost/spotify/oauth/callback.php');
} else {
    die(json_encode(['error' => 'Environment not set']));
}

$result = $pdo->query("SELECT * FROM tokens")->fetchAll(PDO::FETCH_ASSOC);
foreach ($result as $token) {
    if ($token['type'] == 'refresh_token') {
        if (!is_null($token['value']) && !empty($token['value'])) {
            define('REFRESH_TOKEN', $token['value']);
        }
    }
    if ($token['type'] == 'access_token') {
        if ($token['expire'] > time() && !is_null($token['value']) && !empty($token['value'])) {
            define('ACCESS_TOKEN', $token['value']);
        }
    }
}

function update(string $type, string $value, int $expire = 0)
{
    global $pdo;
    $pdo->query("UPDATE tokens SET value = '$value', expire = '$expire' WHERE type = '$type'");
}

function client(string $column)
{
    global $pdo;
    $result = $pdo->query("SELECT $column FROM user WHERE id = '1'")->fetch(PDO::FETCH_ASSOC);
    return $result[$column];
}
