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
    $requiredProperties = [ "database", "host", "username", "password" ];

    $propertyTypeMap = [
      "database" => "string",
      "host" => "string",
      "username" => "string",
      "password" => "string",
      "default" => "bool"
    ];

    foreach ($dbConnections as $dbConnection)
    {
      $dbConnection = (object)$dbConnection;
      Objects::validatePropertiesExist((object)$dbConnection, $requiredProperties, true);
      Objects::validatePropertyTypes((object)$dbConnection, $propertyTypeMap, true);
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

    foreach (self::$dbConnections as $name => $connectionConfig)
    {
      $mysqlCapsule->addConnection(
        [
          "driver" => "mysql",
          "host" => Arrays::getByKey('host', $connectionConfig),
          "database" => Arrays::getByKey('database', $connectionConfig),
          "username" => Arrays::getByKey('username', $connectionConfig),
          "password" => Arrays::getByKey('password', $connectionConfig)
        ],
        $name
      );

      $isDefaultConnection = Arrays::getByKey('default', $connectionConfig);

      if ($isDefaultConnection)
      {
        $mysqlCapsule->getDatabaseManager()->setDefaultConnection($name);
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