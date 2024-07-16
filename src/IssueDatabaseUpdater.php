<?php

namespace Drupal\core_metrics;

use \PDO;
use \SQLite3;

/**
 * Updates the local SQLite issue database with the latest issue data.
 */
class IssueDatabaseUpdater extends DatabaseUpdaterBase {

  /**
   * The relative path to the SQLite database file.
   */
  protected static function getDbPath(): string {
    return '../data/issue_data.sqlite';
  }

  /**
   * Writes data from a branch/type result set to the database.
   *
   * @param mixed $data
   *   The decoded JSON data.
   */
  public function writeData($data): void {
    print "Writing data for up to " . sizeof($data) . " issues...\n";
    $pdo = new PDO('sqlite:' . __DIR__ . '/' . static::getDbPath());
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
   * Recreates the tables.
   */
  public function createTables(): void {
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
