<?php

namespace Drupal\core_metrics\Fetcher;

use Drupal\core_metrics\Request\RequestInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Fetches results for a given Drupal.org comment request.
 */
class UserRecentCommentFetcher extends FetcherBase {

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

    // Get the last comment on the page.
    $lastComment = $page->list[sizeof($page->list) - 1];

    // The fetch is complete if the last comment is older than the timestamp,
    // or incomplete otherwise.
    return $this->oldestDataTimestamp >= $lastComment->created;
  }

}
