<?php

namespace Electra\Dal\Database\Mysql;

use Electra\Dal\Database\Mysql\Mysql;
use Electra\Utility\Objects;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder;
use Electra\Utility\Arrays;

class Model extends EloquentModel
{
  private static $dbConnectionsSetup = false;
  const CREATED_AT = 'created';
  const UPDATED_AT = 'updated';

  /**
   * MysqlModel constructor.
   * @throws \Exception
   */
  public function __construct()
  {
    if (!self::$dbConnectionsSetup)
    {
      parent::__construct();
      Mysql::registerDbConnections();
      self::$dbConnectionsSetup = true;
    }
  }

  /**
   * @return Builder
   */
  public function getQueryBuilder()
  {
    return $this->getConnection()->table($this->getTable());
  }
}