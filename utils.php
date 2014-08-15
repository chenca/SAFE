<?php

class Utils {
    const BASE_DOMAIN = 'safe.parseapp.com'
    const BASE_INVITATION_URL = 'https://'.$BASE_DOMAIN.'/?id=';
    const FACEBOOK_APP_SETTINGS_ID = 'mLqPDXPEYf';

    function getFacebookAppId() {
      $query = new ParseQuery('FacebookAppSettings');
      $obj = $query->get($self::FACEBOOK_APP_SETTINGS_ID);
      $app_id = $obj->get("app_id");
      return $app_id;
    }

    function getFacebookAppSecret() {
      $query = new ParseQuery('FacebookAppSettings');
      $obj = $query->get($self::FACEBOOK_APP_SETTINGS_ID);
      $app_id = $obj->get("app_secret");
      return $app_secret;
    }
}

?>
