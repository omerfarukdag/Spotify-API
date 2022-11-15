<?php
require_once '../db.php';
$client_id = client('client_id');
$client_secret = client('client_secret');
$url = "https://accounts.spotify.com/api/token";

function getAccessToken($client_id, $client_secret, $url)
{
    $grant_type = "refresh_token";
    $refresh_token = defined('REFRESH_TOKEN') ? REFRESH_TOKEN : die(json_encode(['error' => 'Refresh token not found.']));
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_POST, 1);
    curl_setopt($ch, CURLOPT_POSTFIELDS, "grant_type=$grant_type&refresh_token=$refresh_token");
    $headers = array();
    $headers[] = "Content-Type: application/x-www-form-urlencoded";
    $headers[] = "Authorization: Basic " . base64_encode($client_id . ":" . $client_secret);
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    $data = json_decode($result);
    update('access_token', $data->access_token, time() + $data->expires_in);
    return $data->access_token;
}

function getUpdatedData($client_id, $client_secret, $url)
{
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, "https://api.spotify.com/v1/me/player/currently-playing");
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_CUSTOMREQUEST, "GET");
    $headers = array();
    $headers[] = "Accept: application/json";
    $headers[] = "Content-Type: application/json";
    $headers[] = "Authorization: Bearer " . (defined('ACCESS_TOKEN') ? ACCESS_TOKEN : getAccessToken($client_id, $client_secret, $url));
    curl_setopt($ch, CURLOPT_HTTPHEADER, $headers);
    $result = curl_exec($ch);
    if (curl_errno($ch)) {
        echo 'Error:' . curl_error($ch);
    }
    curl_close($ch);
    return json_decode($result, true);
}

$data = getUpdatedData($client_id, $client_secret, $url);

if (isset($data['error'])) {
    echo json_encode(['error' => $data['error']['message']]);
} elseif (is_null($data)) {
    echo json_encode(['error' => 'Currently not playing.']);
} else {
    $album = $data['item']['album']['name'];
    $artists = array_column($data['item']['album']['artists'], 'name');
    $name = $data['item']['name'];
    $image = $data['item']['album']['images'][0]['url'];
    $is_playing = $data['is_playing'];
    echo json_encode([
        'name' => $name,
        'artists' => $artists,
        'album' => $album,
        'image' => $image,
        'is_playing' => $is_playing
    ]);
}
