<?php

namespace Drupal\core_metrics;

/**
 * Fetches results for a given Drupal.org issue request.
 */
class SingleIssueFetcher extends FetcherBase {

  /**
   * {@inheritdoc}
   */
  protected function isFetchComplete($page): bool {
    return empty($page->next);
  }

}
