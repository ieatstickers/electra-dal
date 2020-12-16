<?php

namespace Electra\Dal\Data;

use Electra\Utility\Collection;
use Electra\Utility\Objects;

abstract class AbstractEntity
{
  /**
   * @param \stdClass | array | object $data
   * @return static
   * @throws \Exception
   */
  public static function create($data = [])
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

  /**
   * @param array $data
   *
   * @return Collection
   * @throws \Exception
   */
  public static function toCollection($data = []): Collection
  {
    $entityCollection = new Collection();

    if(is_null($data))
    {
      return $entityCollection;
    }

    foreach($data as $item)
    {
      $entityCollection->add(self::create($item));
    }

    return $entityCollection;
  }

  /** @return mixed */
  protected static abstract function getModel();
}
