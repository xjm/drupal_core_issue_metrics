<?php

namespace Drupal\core_metrics;

use \PDO;

/**
 * Queries issue data.
 */
class QueryRunner {

  /**
   * The relative path to the SQLite database file.
   */
  protected static string $dbPath = '../data/issue_data.sqlite';

  /**
   * The assembled query string.
   */
  protected string $queryString;

  /**
   * The query parameters
   */
  protected array $queryParameters;

  /**
   * Constructs a new issue query.
   *
   * @param IssueQuery $query
   *   The issue query object
   * @param \PDO $db
   *  (optional) The database connection. If NULL, a new connection to the SQLite
   *  database at the default path is opened.
   */
  public function __construct(protected IssueQuery $query, PDO $db = NULL) {

    // Initialize the database connection if none was passed.
    if ($db === NULL) {
      $db = new PDO('sqlite:' . __DIR__ . '/' . static::$dbPath);
    }
    $this->db = $db;

    // Get the query metadata.
    $this->metadata = $query->getMetadata();

    $this->assembleQueryString();
  }

  /**
   * Executes the query and returns all results.
   *
   * @return array
   *   The results from the database.
   */
  public function getResults(): array {
    $statement = $this->db->prepare($this->queryString);
    $statement->execute($this->queryParameters);
    return $statement->fetchAll();
  }

  /**
   * Assembles the query based on the metadata.
   */
  protected function assembleQueryString(): void {
    $queryParameters = [];

    $filters = [];
    $filters['category'] = $this->metadata->getTypes();
    $filters['version'] = $this->metadata->getBranches();
    $filters['priority'] = $this->metadata->getPriorities();
    $filters['status'] = $this->metadata->getStatuses();
    $filters['component'] = $this->metadata->getComponents();
    $terms = $this->metadata->getTids();

    $query = " SELECT * FROM issue_data \n";

    if (!empty($terms) && !$this->metadata->excludeTerms()) {
      $query .= " LEFT JOIN nid_tid \n"
        . " ON issue_data.nid = nid_tid.nid AND nid_tid.tid ";
      if (sizeof($terms) > 1) {
        $query .= " IN ( \n"
          . implode(', ' , array_fill(0, sizeof($filter), '?'))
          . " ) \n";
      }
      else {
        $query .= " = ? \n";
      }
      $queryParameters = array_merge($queryParameters, array_values($terms));
    }

    $conditions = [];
    $conditionParameters = [];

    foreach ($filters as $key => $filter) {
      if (empty($filter)) {
        continue;
      }
      if (sizeof($filter) > 1) {
        $conditions[$key] = " `$key` IN ("
          . implode(', ' , array_fill(0, sizeof($filter), '?'))
          . ') ';
      }
      else {
        $conditions[$key] = " `$key` = ?";
      }
      $conditionParameters = array_merge($conditionParameters, array_values($filter));
    }
    $queryParameters = array_merge($queryParameters, $conditionParameters);

    $query .= " WHERE \n" . implode("\n AND ", $conditions) . " \n";

    if (!empty($terms) && $this->metadata->excludeTerms()) {
      foreach ($terms as $tid) {
        $query .= " AND issue_data.nid NOT IN ( \n"
          . "   SELECT id.nid FROM issue_data id \n"
          . "   INNER JOIN nid_tid nt \n"
          . "   ON id.nid = nt.nid AND nt.tid = ?\n"
          . "   WHERE \n" . implode("\n   AND ", $conditions) . " \n"
          . " ) \n";
        $queryParameters = array_merge($queryParameters, [$tid]);
        $queryParameters = array_merge($queryParameters, $conditionParameters);
      }
    }
    $this->queryString = $query;
    $this->queryParameters = $queryParameters;
  }

}
