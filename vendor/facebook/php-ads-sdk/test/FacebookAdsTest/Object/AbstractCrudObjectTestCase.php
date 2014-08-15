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

namespace FacebookAdsTest\Object;

use FacebookAds\Cursor;
use FacebookAds\Object\AbstractCrudObject;
use FacebookAds\Object\AbstractObject;
use FacebookAdsTest\AbstractTestCase;

abstract class AbstractCrudObjectTestCase extends AbstractTestCase {

  /**
   * @param AbstractCrudObject $subject
   */
  public function assertCanCreate(AbstractCrudObject $subject) {
    $this->assertEmpty($subject->{AbstractCrudObject::FIELD_ID});
    $subject->create();
    $this->assertNotEmpty($subject->{AbstractCrudObject::FIELD_ID});
  }

  /**
   * @param AbstractCrudObject $object
   * @return AbstractCrudObject
   */
  protected function getEmptyClone(AbstractCrudObject $object) {
    $fqn = get_class($object);
    return new $fqn(
      $object->{AbstractCrudObject::FIELD_ID},
      $object->getParentId());
  }

  /**
   * @param AbstractCrudObject $subject
   */
  public function assertCannotCreate(AbstractCrudObject $subject) {
    $has_throw_exception = false;
    try {
      $subject->create();
    } catch (\Exception $e) {
      $has_throw_exception = true;
    }
    $this->assertTrue($has_throw_exception);
  }

  /**
   * @param AbstractCrudObject $subject
   */
  public function assertCanRead(AbstractCrudObject $subject) {
    $this->assertNotEmpty($subject->{AbstractCrudObject::FIELD_ID});
    $mirror = $this->getEmptyClone($subject);
    $mirror->read();
    $this->assertEquals(
      $subject->{AbstractCrudObject::FIELD_ID},
      $mirror->{AbstractCrudObject::FIELD_ID});
  }

  /**
   * @param AbstractCrudObject $subject
   * @param string $connection_name
   * @param array $fields
   * @param array $params
   */
  public function assertCanFetchConnection(
    AbstractCrudObject $subject,
    $connection_name,
    array $fields = array(),
    array $params = array()) {

    $this->assertNotEmpty($subject->{AbstractCrudObject::FIELD_ID});
    $mirror = $this->getEmptyClone($subject);
    $result = call_user_func(
      array($mirror, $connection_name), $fields, $params);
    $this->assertTrue(
      $result instanceof Cursor
      || $result instanceof AbstractObject);
  }  

  /**
   * @param AbstractCrudObject $object
   * @param array $diff
   * @return array
   */
  private function extractDiffFromObject(
    AbstractCrudObject $object,
    array $diff) {

    $sub_diff = array();
    foreach ($object->getData() as $key => $val) {
      if (array_key_exists($key, $diff)) {
        $sub_diff[$key] = $val;
      }
    }

    return $sub_diff + array_combine(
      array_keys($diff),
      array_fill(0, count($diff), null));
  }

  /**
   * @param AbstractCrudObject $subject
   * @param array $diff Data to be changed in the object
   */
  public function assertCanUpdate(
    AbstractCrudObject $subject,
    array $diff) {

    $this->assertNotEmpty($subject->{AbstractCrudObject::FIELD_ID});
    $mirror = $this->getEmptyClone($subject);
    $mirror->read();
    $mirror->setData($diff);
    $mirror->update();
    $this->assertNotEmpty($subject->id);
    $mirror2 = $this->getEmptyClone($subject);
    $mirror2->read(array_keys($diff));
    $this->assertEquals(
      $this->extractDiffFromObject($mirror, $diff),
      $this->extractDiffFromObject($mirror2, $diff));
  }

  /**
   * @param AbstractCrudObject $subject
   */
  public function assertCannotUpdate(AbstractCrudObject $subject) {
    $has_throw_exception = false;
    try {
      $subject->update();
    } catch (\Exception $e) {
      $has_throw_exception = true;
    }
    $this->assertTrue($has_throw_exception);
  }

  /**
   * @param AbstractCrudObject $subject
   */
  public function assertCanDelete(AbstractCrudObject $subject) {
    $this->assertNotEmpty($subject->{AbstractCrudObject::FIELD_ID});
    $subject->delete();
    $this->assertEmpty($subject->{AbstractCrudObject::FIELD_ID});
  }

  /**
   * @param AbstractCrudObject $subject
   */
  public function assertCannotDelete(AbstractCrudObject $subject) {
    $has_throw_exception = false;
    try {
      $subject->delete();
    } catch (\Exception $e) {
      $has_throw_exception = true;
    }
    $this->assertTrue($has_throw_exception);
  }
}
