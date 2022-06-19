<?php

namespace Drupal\core_metrics;

/**
 * Builds a REST API query for Drupal.org issues.
 *
 * @see https://www.drupal.org/drupalorg/docs/apis/rest-and-other-apis
 */
class IssueRequest {

  /**
   * The base URL for querying core issue metadata.
   */
  const URL = 'https://www.drupal.org/api-d7/node.json?type=project_issue&field_project=3060';

  /**
   * The static issue metadata.
   */
  protected static IssueMetadata $metadata;

  /**
   * The constructed URLs for the queries.
   */
  protected array $urls;

  /**
   * Constructs a new issue query URL.
   *
   * Since the issue query API does not support arrays for issue metadata, we
   * must construct multiple query URLs in order to filter the data.
   */
  public function __construct(protected array $branches, public string $type) {
    static::$metadata = new IssueMetadata();

    $url_base = static::URL
      . '&field_issue_category='
      . static::$metadata::$type[$type];

    foreach ($branches as $branch) {
      $this->urls[$branch] = $url_base . '&field_issue_version='. $branch . '-dev';
    }
  }

  /**
   * Gets the array of API request URLs.
   */
  public function getUrls() {
    return $this->urls;
  }

  /**
   * Gets the array of branches
   */
  public function getBranches() {
    return $this->branches;
  }

  /**
   * Gets the issue type.
   */
  public function getType() {
    return $this->type;
  }

}
