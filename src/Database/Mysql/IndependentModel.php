<?php

namespace Electra\Dal\Database\Mysql;

use Electra\Dal\Database\Mysql\Mysql;
use Illuminate\Database\Schema\Blueprint;

/**
 * Class UserTable
 * @package Data\Mysql\App\User
 */
abstract class IndependentModel extends Model
{
  /**
   * IndependentModel constructor.
   * @throws \Exception
   */
  public function __construct()
  {
    parent::__construct();

    // Create DB if not exists
    Mysql::createDatabase($this->connection);;

    // Check if table exists
    $tableExists = Mysql::schema($this->connection)->hasTable($this->table);

    // If migration table doesn't exist
    if (!$tableExists)
    {
      // Create it
      Mysql::schema($this->connection)->create($this->table, function(Blueprint $table)
      {
        $this->createTable($table);
      });
    }
  }

  /**
   * @param Blueprint $table
   * @return mixed
   */
  public abstract function createTable(Blueprint $table);
}