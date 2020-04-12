<?php

namespace Electra\Dal\Database\Mysql;

use Electra\Utility\Arrays;
use Electra\Utility\Objects;
use Illuminate\Database\Capsule\Manager as Capsule;
use Illuminate\Database\Connection;
use Illuminate\Database\Schema\Builder;

class Mysql extends Capsule
{
  /** @var array */
  private static $dbConnections;
  /** @var array */
  private static $dbUsers;
  /** @var string */
  private static $dbUser;
  /** @var bool */
  private static $fallBackToDefaultUser = true;

  /**
   * @param null $connection
   * @return Connection
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
   * @return Builder
   * @throws \Exception
   */
  public static function schema($connection = null)
  {
    if (!$connection)
    {
      $connection = self::connection()->getName();
    }

    $connectionConfig = Arrays::getByKey($connection, self::$dbConnections);

    if (!$connectionConfig)
    {
      throw new \Exception("No connection config found for: $connection");
    }

    $dbName = Arrays::getByKey('database', $connectionConfig);

    static::createDatabase($dbName);
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
    $requiredProperties = [ "database", "host" ];

    $propertyTypeMap = [
      "database" => "string",
      "host" => "string",
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
   * @param array $dbUsers
   * @throws \Exception
   *
   * This method stores the MySQL users in a static property. These users
   * are used when connections are registered.
   */
  public static function registerDbUsers(array $dbUsers)
  {
    $requiredProperties = [ "username", "password" ];

    $propertyTypeMap = [
      "username" => "string",
      "password" => "string",
      "default" => "bool"
    ];

    foreach ($dbUsers as $dbUser)
    {
      $dbUser = (object)$dbUser;
      Objects::validatePropertiesExist((object)$dbUser, $requiredProperties, true);
      Objects::validatePropertyTypes((object)$dbUser, $propertyTypeMap, true);
    }

    self::$dbUsers = $dbUsers;
  }

  /**
   * @param string $dbUser
   * @param bool $fallBackToDefaultUser
   */
  public static function setUser(string $dbUser, $fallBackToDefaultUser = false)
  {
    self::$dbUser = $dbUser;
    self::$fallBackToDefaultUser = $fallBackToDefaultUser;
  }

  /** @throws \Exception */
  public static function registerDbConnections()
  {
    if (!self::$dbConnections)
    {
      throw new \Exception("Cannot register database connections - no connections set");
    }
    if (!self::$dbUsers)
    {
      throw new \Exception("Cannot register database connections - no users registered");
    }

    $dbUser = self::getDbUser();

    $mysqlCapsule = new self();

    foreach (self::$dbConnections as $name => $connectionConfig)
    {
      // Create DB if not exists
      $mysqlCapsule->addConnection(
        [
          "driver" => "mysql",
          "host" => Arrays::getByKey('host', $connectionConfig),
          "database" => Arrays::getByKey('database', $connectionConfig),
          "username" => Arrays::getByKey('username', $dbUser),
          "password" => Arrays::getByKey('password', $dbUser)
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
   * @return mixed|null
   * @throws \Exception
   */
  private static function getDbUser()
  {
    $selectedDbUser = null;
    $defaultDbUser = null;

    foreach (self::$dbUsers as $key => $dbUser)
    {
      // Set default
      if (Arrays::getByKey('default', $dbUser))
      {
        $defaultDbUser = $dbUser;
      }

      // Set selected
      if (self::$dbUser && self::$dbUser == $key)
      {
        $selectedDbUser = $dbUser;
      }
    }

    if (self::$dbUser && $selectedDbUser)
    {
      return $selectedDbUser;
    }

    if (self::$fallBackToDefaultUser && $defaultDbUser)
    {
      return $defaultDbUser;
    }

    throw new \Exception('DB user not found');
  }
}
