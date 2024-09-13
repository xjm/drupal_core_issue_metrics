<?php

namespace Drupal\core_metrics;

use GuzzleHttp\Client;
use GuzzleHttp\Exception\BadResponseException;

/**
 * Fetches results for a given Drupal.org comment request.
 */
class UserRecentCommentFetcher extends FetcherBase {

  /**
   * {@inheritdoc}
   */
  protected function isFetchComplete($page): bool {

    // Get the last comment on the page.
    $lastComment = $page->list[sizeof($page->list) - 1];

    // Fetch at least three months of data so we don't miss items if we run
    // reports for specific timeframes.
    $threeMonthsAgo = strtotime('3 months ago');

    // The fetch is complete if the last comment is older than 3 months, or
    // incomplete otherwise.
    return $threeMonthsAgo >= $lastComment->created;
  }

}
