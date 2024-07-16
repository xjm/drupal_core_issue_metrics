<?php

namespace Drupal\core_metrics;

/**
 * Builds a REST API query for Drupal.org issues.
 *
 * @see https://www.drupal.org/drupalorg/docs/apis/rest-and-other-apis
 */
class IssueListRequest implements RequestInterface {

  /**
   * The static issue metadata.
   */
  protected static MagicIntMetadata $metadata;

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
    static::$metadata = new MagicIntMetadata();

    $url_base = static::getBaseUrl();
      . '&field_issue_category='
      . static::$metadata::$type[$type];

    foreach ($branches as $branch) {
      $this->urls[$branch] = $url_base . '&field_issue_version='. $branch . '-dev';
    }
  }

  /**
   * {@inheritdoc}
   */
  public static function getBaseUrl(): string {
    return 'https://www.drupal.org/api-d7/node.json?type=project_issue&field_project=3060';
  }

  /**
   * {@inheritdoc}
   */
  public function getUrls(): array {
    return $this->urls;
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): string {
    return $this->type;
  }


  /**
   * Gets the array of branches.
   *
   * @return string[]
   *   An array of branch strings.
   */
  public function getBranches(): array {
    return $this->branches;
  }

}
