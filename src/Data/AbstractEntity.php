<?php

namespace Electra\Dal\Data;

abstract class AbstractEntity
{
  /**
   * @param $data
   *
   * @return object
   */
  public static function create($data)
  {
    return (object)$data;
  }

  /** @return mixed */
  protected static abstract function getModel();
}
