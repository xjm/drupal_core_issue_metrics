<?php

namespace Drupal\core_metrics;

use \PDO;

/**
 * Queries issue data with a query string.
 *
 * WARNING: This object does NO sanitization. Callers must sanitize the string
 * if necessary.
 */
class StringQueryRunner extends QueryRunnerBase {

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

    $this->query = $query;
    if (is_string($query)) {
      $this->queryString = $query;
    }

  }

}
