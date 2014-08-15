<?php
/**
 * Copyright 2014 Facebook, Inc.
 *
 * You are hereby granted a non-exclusive, worldwide, royalty-free license to
 * use, copy, modify, and distribute this software in source code or binary
 * form for use in connection with the web services and APIs provided by
 * Facebook.
 *
 * As with any software that integrates with the Facebook platform, your use
 * of this software is subject to the Facebook Developer Principles and
 * Policies [http://developers.facebook.com/policy/]. This copyright notice
 * shall be included in all copies or substantial portions of the software.
 *
 * THE SOFTWARE IS PROVIDED "AS IS", WITHOUT WARRANTY OF ANY KIND, EXPRESS OR
 * IMPLIED, INCLUDING BUT NOT LIMITED TO THE WARRANTIES OF MERCHANTABILITY,
 * FITNESS FOR A PARTICULAR PURPOSE AND NONINFRINGEMENT. IN NO EVENT SHALL
 * THE AUTHORS OR COPYRIGHT HOLDERS BE LIABLE FOR ANY CLAIM, DAMAGES OR OTHER
 * LIABILITY, WHETHER IN AN ACTION OF CONTRACT, TORT OR OTHERWISE, ARISING
 * FROM, OUT OF OR IN CONNECTION WITH THE SOFTWARE OR THE USE OR OTHER
 * DEALINGS IN THE SOFTWARE.
 *
 */

// Set your access token here:
$access_token = null;
$app_id = null;
$app_secret = null;

if(is_null($access_token) || is_null($app_id) || is_null($app_secret)) {
  throw new \Exception(
    'You must set your access token, app id and app secret before executing'
  );
}

define('SDK_DIR', __DIR__ . '/..'); // Path to the SDK directory
$loader = include SDK_DIR.'/vendor/autoload.php';

use FacebookAds\Api;
use Facebook\FacebookSession;

FacebookSession::setDefaultApplication($app_id, $app_secret);
$session = new FacebookSession($access_token);
$api = new Api($session);


/**
 * Step 1 Read user and their AdAccounts
 */
use FacebookAds\Object\AdUser;
use FacebookAds\Object\Fields\AdUserFields;
use FacebookAds\Object\AdAccount;
use FacebookAds\Object\Fields\AdAccountFields;

$user = new AdUser('me');
$user->read(array(AdUserFields::ID));

$accounts = $user->getAdAccounts(array(
  AdAccountFields::ID,
  AdAccountFields::NAME,
));

// Print out the accounts
echo "Accounts:\n";
foreach($accounts as $account) {
  echo $account->id . ' - ' .$account->name."\n";
}

// Grab the first account for next steps (you should probably choose one)
$account = (count($accounts)) ? $accounts->getObjects()[0] : null;
echo "\nUsing this account: ";
echo $account->id."\n";

/**
 * Step 2 Create the AdCampaign
 */
use FacebookAds\Object\AdCampaign;
use FacebookAds\Object\Fields\AdCampaignFields;
use FacebookAds\Object\Values\AdObjectives;
use FacebookAds\Object\Values\AdBuyingTypes;

$campaign  = new AdCampaign(null, $account->id);
$campaign->setData(array(
  AdCampaignFields::NAME => 'My First Campaign',
  AdCampaignFields::OBJECTIVE => AdObjectives::WEBSITE_CLICKS,
  AdCampaignFields::STATUS => AdCampaign::STATUS_PAUSED,
));

$campaign->create();
echo "Campaign ID:" . $campaign->id . "\n";


/**
 * Step 3 Create the AdSet
 */
use FacebookAds\Object\AdSet;
use FacebookAds\Object\Fields\AdSetFields;

$adset = new AdSet(null, $account->id);
$adset->setData(array(
  AdSetFields::NAME => 'My First AdSet',
  AdSetFields::CAMPAIGN_GROUP_ID => $campaign->id,
  AdSetFields::CAMPAIGN_STATUS => AdSet::STATUS_ACTIVE,
  AdSetFields::DAILY_BUDGET => '150',
  AdSetFields::START_TIME =>
    (new \DateTime("+1 week"))->format(\DateTime::ISO8601),
  AdSetFields::END_TIME =>
    (new \DateTime("+2 week"))->format(\DateTime::ISO8601),
));

$adset->create();
echo 'AdSet  ID: '. $adset->id . "\n";

/**
 * Step 4 Create an AdImage
 */
use FacebookAds\Object\AdImage;
use FacebookAds\Object\Fields\AdImageFields;

$image = new AdImage(null, $account->id);
$image->filename = SDK_DIR.'/test/misc/FB-f-Logo__blue_512.png';

$image->create();
echo 'Image Hash: '.$image->hash . "\n";

/**
 * Step 5 Create an AdCreative
 */
use FacebookAds\Object\AdCreative;
use FacebookAds\Object\Fields\AdCreativeFields;

$creative = new AdCreative(null, $account->id);
$creative->setData(array(
  AdCreativeFields::NAME => 'Sample Creative',
  AdCreativeFields::TITLE => 'Welcome to the Jungle',
  AdCreativeFields::BODY => 'We\'ve got fun \'n\' games',
  AdCreativeFields::IMAGE_HASH => $image->hash,
  AdCreativeFields::OBJECT_URL => 'http://www.example.com/',
));

$creative->create();
echo 'Creative ID: '.$creative->id . "\n";


/**
 * Step 6 Search Targeting
 */
use FacebookAds\Object\TargetingSearch;
use FacebookAds\Object\Search\TargetingSearchTypes;

$results = TargetingSearch::search(
  $type = TargetingSearchTypes::INTEREST,
  $class = null,
  $query = 'facebook london'
);

// we'll take the top result for now
$target = (count($results)) ? $results->getObjects()[0] : null;
echo "Using target: ".$target->name."\n";

$targeting = array(
  'geo_locations' => array(
    'countries' => array('GB'),
  ),
  'interests' => array(
    array(
      'id' => $target->id,
      'name'=>$target->name
    )
  )
);

/**
 * Step 7 Create an AdGroup
 */
use FacebookAds\Object\AdGroup;
use FacebookAds\Object\Fields\AdGroupFields;
use FacebookAds\Object\Fields\AdGroupBidInfoFields;

$adgroup = new AdGroup(null, $account->id);
$adgroup->setData(array(
  AdGroupFields::CREATIVE =>
    array('creative_id' => $creative->id),
  AdGroupFields::NAME => 'My First AdGroup',
  AdGroupFields::BID_TYPE => AdGroup::BID_TYPE_CPM,
  AdGroupFields::BID_INFO =>
    array(AdGroupBidInfoFields::IMPRESSIONS => '2'),
  AdGroupFields::CAMPAIGN_ID => $adset->id,
  AdGroupFields::TARGETING => $targeting,
));

$adgroup->create();
echo 'AdGroup ID:' . $adgroup->id . "\n";
