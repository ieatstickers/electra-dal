<?php

namespace Electra\Dal\Data;

use Electra\Dal\Database\Mysql\Model;
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
   * @param bool  $skipHydration
   *
   * @return Collection
   * @throws \Exception
   */
  public static function toCollection(iterable $data = [], $skipHydration = false): Collection
  {
    $entityCollection = new Collection();

    if(is_null($data))
    {
      return $entityCollection;
    }

    foreach($data as $item)
    {
      $itemData = $item instanceof Model ? (object)($item->getAttributes()) : $data;
      $entity = $skipHydration ? $itemData : self::create($itemData);
      $entityCollection->add($entity);
    }

    return $entityCollection;
  }

  /** @return mixed */
  protected static abstract function getModel();
}
