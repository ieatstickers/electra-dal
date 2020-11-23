<?php

namespace Electra\Dal\Data;

use Electra\Utility\Objects;

abstract class AbstractEntity
{
  /**
   * @param \stdClass | array | object $data
   * @return static
   * @throws \Exception
   */
  public static function create($data = []): ?self
  {
    if (is_null($data))
    {
      return null;
    }

    return Objects::hydrate(
      new static(),
      (object)$data
    );
  }

  /** @return mixed */
  protected static abstract function getModel();
}
