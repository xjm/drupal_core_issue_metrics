<?php

namespace Drupal\core_metrics\Fetcher;

use Drupal\core_metrics\Request\RequestInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Fetches results for a given Drupal.org issue request.
 */
class FixedIssueListFetcher extends FetcherBase {

  public function __construct(protected RequestInterface $requestGroup, protected Client $client, protected int $oldestDataTimestamp = 0) {

    if (empty($this->oldestDataTimestamp)) {
      // Fetch at least three months of data by default so we don't miss items
      // if we run reports for specific timeframes.
      $this->oldestDataTimestamp = strtotime('3 months ago');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function isFetchComplete($page): bool {

    // Get the last issue on the page.
    $lastIssue = $page->list[sizeof($page->list) - 1];

    // The fetch is complete if the last issue is older than the timestamp, or
    // incomplete otherwise.
    return $this->oldestDataTimestamp >= $lastIssue->field_issue_last_status_change;
  }

}
