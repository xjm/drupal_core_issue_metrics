<?php

namespace Drupal\core_metrics;

/**
 * Builds a REST API query for Drupal.org issues.
 *
 * @see https://www.drupal.org/drupalorg/docs/apis/rest-and-other-apis
 */
class IssueRequest implements RequestInterface {

  /**
   * The static issue metadata.
   */
  protected static MagicIntMetadata $metadata;

  /**
   * The constructed URLs for the queries.
   */
  protected array $urls;

  /**
   * Constructs a new user comment query URL.
   */
  public function __construct(protected int $uid) {
    static::$metadata = new MagicIntMetadata();
  }

  /**
   * {@inheritdoc}
   */
  public static function getBaseUrl(): string {
    return 'https://www.drupal.org/api-d7/comment.json?author=' . $this->uid . . '&sort=created&direction=DESC';
  }

  /**
   * {@inheritdoc}
   */
  public function getUrls(): array {
    return ['recent comments' => $this->getBaseUrl()];
  }

  /**
   * {@inheritdoc}
   */
  public function getType(): string {
    return '';
  }

}
