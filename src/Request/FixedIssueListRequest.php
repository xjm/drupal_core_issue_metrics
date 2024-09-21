<?php

namespace Drupal\core_metrics\Request;

use Drupal\core_metrics\MagicIntMetadata;

/**
 * Builds a REST API query for Drupal.org issues.
 *
 * @see https://www.drupal.org/drupalorg/docs/apis/rest-and-other-apis
 */
class FixedIssueListRequest extends IssueListRequest {

  /**
   * Constructs a new issue query URL.
   *
   * Since the issue query API does not support arrays for issue metadata, we
   * must construct multiple query URLs in order to filter the data.
   */
  public function __construct(protected array $branches, public string $type = '') {
    static::$metadata = new MagicIntMetadata();

    $baseUrl = $this->getBaseUrl();

    if (!empty($type)) {
      $baseUrl .= '&field_issue_category=' . static::$metadata::$type[$type];
    }

    foreach (static::$metadata::$fixed as $status) {
      foreach ($branches as $branch) {
        $this->urls[$branch] = $baseUrl . '&field_issue_version='. $branch . '-dev' . '&field_issue_status=' . $status;
      }
    }
  }

}
