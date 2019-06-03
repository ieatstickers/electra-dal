<?php

namespace Electra\Dal\Database\Mysql\Model;

use Electra\Dal\Database\Mysql\Mysql;
use Electra\Utility\Objects;
use Illuminate\Database\Eloquent\Model as EloquentModel;
use Illuminate\Database\Query\Builder;
use Electra\Utility\Arrays;

class Model extends EloquentModel
{
  private static $dbConnectionsSetup = false;
  private static $dbConnections;
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
      self::registerDbConnections();
      self::$dbConnectionsSetup = true;
    }
  }

  /**
   * @param array $dbConnections
   * @throws \Exception
   *
   * This method stores the connection config in a static property. Connections
   * aren't actually registered until the model is instantiated.
   */
  public static function setDbConnections(array $dbConnections)
  {
    $propertyTypeMap = [
      "name" => "string",
      "database" => "string",
      "host" => "string",
      "username" => "string",
      "password" => "string"
    ];

    foreach ($dbConnections as $dbConnection)
    {
      $dbConnection = (object)$dbConnection;
      Objects::validatePropertyTypes($dbConnection, $propertyTypeMap);
    }

    self::$dbConnections = $dbConnections;
  }

  /**
   * @throws \Exception
   */
  private static function registerDbConnections()
  {
    if (!self::$dbConnections)
    {
      throw new \Exception("Cannot register database connections - no connections set");
    }

    $mysqlCapsule = new Mysql();

    foreach (self::$dbConnections as $connectionConfig)
    {
      $connectionName = Arrays::getByKey('name', $connectionConfig);

      $mysqlCapsule->addConnection(
        [
          "driver" => "mysql",
          "host" => Arrays::getByKey('host', $connectionConfig),
          "database" => Arrays::getByKey('database', $connectionConfig),
          "username" => Arrays::getByKey('username', $connectionConfig),
          "password" => Arrays::getByKey('password', $connectionConfig)
        ],
        $connectionName
      );

      if ($connectionName == 'default')
      {
        $mysqlCapsule->getDatabaseManager()->setDefaultConnection($connectionName);
      }
    }

    $mysqlCapsule->setAsGlobal();
    $mysqlCapsule->bootEloquent();
  }


  /**
   * @return Builder
   */
  public function getQueryBuilder()
  {
    return $this->getConnection()->table($this->getTable());
  }
}