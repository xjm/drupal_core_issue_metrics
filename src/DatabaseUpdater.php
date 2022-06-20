<?php

namespace Drupal\core_metrics;

use \PDO;
use \SQLite3;

/**
 * Updates the local SQLite issue database with the latest issue data.
 */
class DatabaseUpdater {

  /**
   * The relative path to the SQLite database file.
   */
  protected static string $dbPath = '../data/issue_data.sqlite';

  /**
   * Constructs a new database updater.
   */
  public function __construct(SQLite3 $db = NULL) {
    if ($db === NULL) {
      $db = new SQLite3(__DIR__ . '/' . static::$dbPath);
    }
    $this->db = $db;
  }

  /**
   * Writes data from a branch/type result set to the database.
   */
  public function writeData($data) {
    print "Writing data for up to " . sizeof($data) . " issues...\n";
    $pdo = new PDO('sqlite:' . __DIR__ . '/' . static::$dbPath);
    foreach ($data as $datum) {
      $queries[] = 'INSERT OR IGNORE INTO issue_data '
        . '(nid, created, changed, status, priority, category, version, title, component) '
        . 'VALUES('
        . (int) $datum->nid . ', '
        . (int) $datum->created . ', '
        . (int) $datum->changed . ', '
        . (int) $datum->field_issue_status . ', '
        . (int) $datum->field_issue_priority . ', '
        . (int) $datum->field_issue_category . ', '
        . $pdo->quote($datum->field_issue_version) . ', '
        . $pdo->quote($datum->title) . ', '
        . $pdo->quote($datum->field_issue_component)
        . ');';

      foreach ($datum->taxonomy_vocabulary_9 as $term) {
        $queries[] = 'INSERT OR IGNORE INTO nid_tid (nid, tid) VALUES('
          . (int) $datum->nid . ', '
          . (int) $term->id
          . ');';
      }

      foreach ($queries as $query) {
        $this->db->exec($query);
      }
    }
  }

  /**
   * Drops the tables.
   */
  public function dropTables() {
    $this->db->exec('DROP TABLE issue_data;');
    $this->db->exec('DROP TABLE nid_tid;');
  }

  /**
   * Truncates the tables.
   */
  public function truncateTables() {
    $this->db->exec('DELETE FROM issue_data;');
    $this->db->exec('DELETE FROM nid_tid;');
  }

  /**
   * Recreates the tables.
   */
  public function createTables() {
    $create['issue_table'] = 'CREATE TABLE issue_data('
      . 'nid INTEGER PRIMARY KEY, '
      . 'created INTEGER, '
      . 'changed INTEGER, '
      . 'status INTEGER, '
      . 'priority INTEGER, '
      . 'category INTEGER, '
      . 'version TEXT, '
      . 'title TEXT, '
      . 'component TEXT'
      . ');';

    $create['nid_tid'] = 'CREATE TABLE nid_tid(id INTEGER PRIMARY KEY, nid INTEGER, tid INTEGER, CONSTRAINT u UNIQUE(nid, tid));';

    foreach ($create as $name => $query) {
      $this->db->exec($query);
    }
  }

}
