<?php

namespace Drupal\core_metrics;

/**
 * Builds a REST API query for Drupal.org issues.
 *
 * @see https://www.drupal.org/drupalorg/docs/apis/rest-and-other-apis
 */
interface RequestInterface {

  /**
   * Returns the base URL for querying core issue metadata.
   *
   * @return string
   *   The base URL for the request query.
   */
  public function getBaseUrl(): string;

  /**
   * Gets the array of API request URLs.
   *
   * @return string[]
   *   An array of URL strings.
   */
  public function getUrls(): array;

  /**
   * Gets the type for the requests.
   *
   * @return string
   *   The issue type string.
   */
  public function getType(): string;

}
