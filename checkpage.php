<?php

include 'utils.php';

use Facebook\FacebookSession;
use Facebook\FacebookRequest;

function encrypt_decrypt($action, $string) {
    $output = false;

    $encrypt_method = "AES-256-CBC";
    $secret_key = 'tijolo22';
    $secret_iv = 'tijolo22';

    // hash
    $key = hash('sha256', $secret_key);
    $iv = substr(hash('sha256', $secret_iv), 0, 16);

    if( $action == 'encrypt' ) {
        $output = openssl_encrypt($string, $encrypt_method, $key, 0, $iv);
        $output = base64_encode($output);
    }
    else if( $action == 'decrypt' ){
        $output = openssl_decrypt(base64_decode($string), $encrypt_method, $key, 0, $iv);
    }

    return $output;
}

function createURL($pageID) {
  return Utils::BASE_INVITATION_URL . encrypt_decrypt('encrypt', $pageID);
}

function decode($code) {
  return encrypt_decrypt('decrypt', $code);
}

function validateURL($code, $access_token) {
  $utils = Utils();

  $app_id = $utils->getFacebookAppId();
  $app_secret = $utils->getFacebookAppSecret();

  define('SDK_DIR', __DIR__ ); // Path to the SDK directory
  $loader = include SDK_DIR.'/vendor/autoload.php';

  FacebookSession::setDefaultApplication($app_id, $app_secret);
  $session = new FacebookSession($access_token);

  $request = new FacebookRequest(
    $session,
    'GET',
    '/me/accounts?fields=id,name,access_token'
  );

  $response = $request->execute();
  $obj = $response->getGraphObject();

  $pageID = decode($code);
  foreach ($obj->getPropertyAsArray('data') as $account) {
    if ($account->getProperty('id')  == $pageID)
      return $account;
  }

  return null;
}

/*
$code = encrypt_decrypt('encrypt', '254904217904566');
$access_token = 'CAAB3rQQzTFABAHWbfX9tHuIcNcRSoeZBQdTHF2gQKnLy2AQe4bAhZAJCxuSbNBQsW4GOZC6FEcBrTiu6oZBSPuXIO0EZBHMVoNfahrnn3U9EZC6lXA8ZAShhi2DoNWthhDVgySUwizsyupmu6ILRKEWo9bLER92szdJfXwdmhirdtfGKwIUAQshW6GzSUWXX5Vgi2NlqC8V45p6JaZA8kBZCZBkuRgJYml3T8ZD';

echo $code . "\n";

$acc = validateURL($code, $access_token);

if ($acc) {
  echo $acc->getProperty('name')  . "\n";
  echo $acc->getProperty('access_token')  . "\n";
} else {
  echo "NONE\n";
}
*/

?>
