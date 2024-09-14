<?php

namespace Drupal\core_metrics\Fetcher;

use Drupal\core_metrics\Request\RequestInterface;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Fetches results for a given Drupal.org comment request.
 */
class UserRecentCommentFetcher extends FetcherBase {

  public function __construct(protected RequestInterface $requestGroup, protected Client $client, protected int $oldestCommentData = 0) {

    if (empty($this->oldestCommentData)) {
      // Fetch at least three months of data by default so we don't miss items
      // if we run reports for specific timeframes.
      $this->oldestCommentData = strtotime('3 months ago');
    }
  }

  /**
   * {@inheritdoc}
   */
  protected function isFetchComplete($page): bool {

    // Get the last comment on the page.
    $lastComment = $page->list[sizeof($page->list) - 1];

    // The fetch is complete if the last comment is older than 3 months, or
    // incomplete otherwise.
    return $this->oldestCommentData >= $lastComment->created;
  }

}
