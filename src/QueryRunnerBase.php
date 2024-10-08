<?php

namespace Drupal\core_metrics;

use \PDO;

/**
 * Queries issue data.
 */
abstract class QueryRunnerBase {

  /**
   * The relative path to the SQLite database file.
   */
  protected static string $dbPath = '../data/issue_data.sqlite';

  /**
   * The assembled query string.
   */
  protected string $queryString;

  /**
   * The query parameters.
   */

  protected array $queryParameters = [];

  /**
   * The query.
   */
  protected string|IssueQuery $query;

  /**
   * The query metadata.
   */
  protected ImmutableIssueMetadata $metadata;

  /**
   * Constructs a new issue query.
   *
   * @param string|IssueQuery $query
   *   The query string or issue query object
   * @param \PDO $db
   *  (optional) The database connection. If NULL, a new connection to the SQLite
   *  database at the default path is opened.
   */
  public function __construct(string|IssueQuery $query, protected ?PDO $db = NULL) {

    // Initialize the database connection if none was passed.
    $this->initializeDatabase();
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
   * Initializes the database.
   */
  public function initializeDatabase(): void {
    if ($this->db === NULL) {
      $this->db = new PDO('sqlite:' . __DIR__ . '/' . static::$dbPath);
    }
  }
}
