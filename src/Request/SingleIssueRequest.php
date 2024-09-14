<?php

namespace Drupal\core_metrics\Request;

use Drupal\core_metrics\MagicIntMetadata;

/**
 * Builds a REST API query for a single Drupal.org node.
 *
 * @see https://www.drupal.org/drupalorg/docs/apis/rest-and-other-apis
 */
class SingleIssueRequest implements RequestInterface {

  /**
   * The static issue metadata.
   */
  protected static MagicIntMetadata $metadata;

  /**
   * The constructed URLs for the queries.
   */
  protected array $urls = [];

  /**
   * Constructs a new issue query URL.
   */
  public function __construct(protected array $nodeIds) {
    static::$metadata = new MagicIntMetadata();
    $baseUrl = $this->getBaseUrl();

    foreach ($nodeIds as $nodeId) {
      $this->urls[$nodeId] = $baseUrl . $nodeId . '.json?drupalorg_extra_credit=1';
    }
  }

  /**
   * {@inheritdoc}
   */
  public function getBaseUrl(): string {
    return 'https://www.drupal.org/api-d7/node/';
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
    return '';
  }

}
