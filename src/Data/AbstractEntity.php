<?php

namespace Electra\Dal\Data;

abstract class AbstractEntity
{
  /**
   * @param $data
   * @return mixed
   */
  public static abstract function create($data);

  /** @return mixed */
  protected static abstract function getModel();
}