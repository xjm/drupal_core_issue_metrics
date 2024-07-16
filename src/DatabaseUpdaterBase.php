<?php

namespace Drupal\core_metrics;

use \PDO;
use \SQLite3;

/**
 * Updates the local SQLite issue database with the latest issue data.
 */
abstract class DatabaseUpdaterBase {

  /**
   * The database object.
   *
   * @var \SQLite3
   */
  protected \SQLite3 $db;

  /**
   * The list of database tables.
   *
   * @var string[]
   */
  protected static array $dbTables = [];

  /**
   * Constructs a new database, or returns the current one if it exists.
   *
   * @param \SQLite3 $db = NULL
   *   The database.
   */
  public function __construct(?SQLite3 $db = NULL) {
    if ($db === NULL) {
      $db = new SQLite3(__DIR__ . '/' . static::getDbPath());
    }
    $this->db = $db;
  }

  /**
   * Writes data from a branch/type result set to the database.
   *
   * @param mixed $data
   *   The decoded JSON data.
   */
  abstract public function writeData($data): void;

  /**
   * Recreates the tables.
   */
  abstract function createTables(): void;

  /**
   * The relative path to the SQLite database file.
   */
  abstract protected static function getDbPath(): string;

  /**
   * Escapes a table name for the SQL query.
   *
   * @param string $string
   *   The table name string.
   *
   * @return string
   *   Sanitized table name.
   */
  protected static function escapeTableName($string): string {
    return preg_replace('/[^A-Za-z0-9_.]+/', '', $string);
  }

  /**
   * Drops the tables.
   */
  public function dropTables(): void {
    foreach (static::$dbTables as $table) {
      $this->db->exec('DROP TABLE ' . static::escapeTableName($table));
    }
  }

  /**
   * Truncates the tables.
   */
  public function truncateTables(): void {
    foreach (static::$dbTables as $table) {
      $this->db->exec('DELETE FROM ' . static::escapeTableName($table));
    }
  }

}
