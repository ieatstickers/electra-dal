<?php

namespace Electra\Dal\Database\Mysql;

use Electra\Utility\Arrays;
use Electra\Utility\Objects;
use Illuminate\Database\Capsule\Manager as Capsule;

class Mysql extends Capsule
{
  /** @var array */
  private static $dbConnections;

  /**
   * @param null $connection
   * @return \Illuminate\Database\Connection
   * @throws \Exception
   */
  public static function connection($connection = null)
  {
    self::registerDbConnections();
    return parent::connection($connection);
  }

  /**
   * @param string $dbName
   * @throws \Exception
   */
  public static function createDatabase(string $dbName)
  {
    Mysql::connection()->statement(
      "CREATE DATABASE IF NOT EXISTS `{$dbName}` CHARACTER SET UTF8 COLLATE utf8_bin"
    );
  }

  /**
   * Get a schema builder instance.
   *
   * @param  string|null $connection
   * @return \Illuminate\Database\Schema\Builder
   * @throws \Exception
   */
  public static function schema($connection = null)
  {
    static::createDatabase($connection);
    return parent::schema($connection);
  }

  /**
   * @param array $dbConnections
   * @throws \Exception
   *
   * This method stores the connection config in a static property. Connections
   * aren't actually registered until an instance of Model is instantiated.
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
  public static function registerDbConnections()
  {
    if (!self::$dbConnections)
    {
      throw new \Exception("Cannot register database connections - no connections set");
    }

    $mysqlCapsule = new self();

    foreach (self::$dbConnections as $name => $connectionConfig)
    {
      // Create DB if not exists
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
}
