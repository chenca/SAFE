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

namespace FacebookAds;

use FacebookAdsTest\AbstractTestCase;

error_reporting(E_ALL | E_STRICT);
chdir(__DIR__);

abstract class Bootstrap {

  /**
   * @string
   */
  const DEFAULT_TIMEZONE = 'UTC';

  private static $loader;

  /**
   * @var array
   */
  private static $config = array();

  public static function init() {
    self::initAutoloader();
    self::initConfig();
  }

  /**
   * @throws \RuntimeException
   */
  private static function initAutoloader() {
    $vendor_path = static::findParentPath('vendor');
    if (!$vendor_path || !is_readable($vendor_path . '/autoload.php')) {
      throw new \RuntimeException("Could not read autoload.php");
    }
    self::$loader = include $vendor_path . '/autoload.php';
    self::$loader->addPsr4(
      'FacebookAdsTest\\', __DIR__.'/FacebookAdsTest/');
  }

  /**
   * @throws \RuntimeException
   */
  private static function initConfig() {
    $config_path = __DIR__.'/config.php';
    if (!is_readable($config_path)) {
      throw new \RuntimeException("Could not read config.php");
    }

    self::$config = include $config_path;
    AbstractTestCase::$appId = self::$config['app_id'];
    AbstractTestCase::$appSecret = self::$config['app_secret'];
    AbstractTestCase::$accessToken = self::$config['access_token'];
    AbstractTestCase::$actId = self::$config['act_id'];
    AbstractTestCase::$testRunId = md5(
      (isset($_SERVER['LOGNAME']) ? $_SERVER['LOGNAME'] : uniqid(true))
      .microtime(true));

    $timezone = self::DEFAULT_TIMEZONE;
    if (isset(self::$config['act_timezone'])
      && !empty(self::$config['act_timezone'])) {

      $timezone = self::$config['act_timezone'];
    }

    if (!date_default_timezone_set($timezone)) {
      exit();
    }
  }

  /**
   * @param string $path
   * @return string
   */
  protected static function findParentPath($path) {
    $dir = __DIR__;
    $previous = '.';
    while (!is_dir($dir.'/'.$path)) {
      $dir = dirname($dir);
      if ($previous === $dir) {
        return false;
      }
      $previous = $dir;
    }
    return $dir.'/'.$path;
  }
}

Bootstrap::init();
