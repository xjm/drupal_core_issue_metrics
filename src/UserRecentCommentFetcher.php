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

    // Fetch at least two weeks of data so we don't miss items if we run the
    // report later in the week.
    $fortnightAgo = strtotime('14 days ago');

    // The fetch is complete if the last comment is older than 14 days, or
    // incomplete otherwise.
    return $fortnightAgo >= $lastComment->created;
  }

}
