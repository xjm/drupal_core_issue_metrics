<?php

namespace Drupal\core_metrics;

/**
 * Defines a value object of issue metadata for a database query.
 */
class ImmutableIssueMetadata {

  use ValidateBranchTrait;

  /**
   * The git branch names to select. '-dev' will be appended automatically.
   *
   * @var string[]
   */
  protected array $versions = [];

  /**
   * The issue category IDs to select.
   *
   * @var int[]
   */
  protected array $categories = [];

  /**
   * The integer issue priority values to select.
   *
   * @var int[]
   */
  protected array $priorities = [];

  /**
   * The integer issue status values to select.
   *
   * @var int[]
   */
  protected array $statuses = [];

  /**
   * Array of issue component labels to select.
   *
   * @var string[]
   */
  protected array $components = [];

  /**
   * The array of issue date range timestamps.
   *
   * The array will have associated keys with the following structure:
   * - changedStart
   *     The timestamp for the oldest updated issues to select. If 0, no lower
   *     limit is placed on the changed date.
   * - changedEnd
   *     The timestamp of the most recently updated issues to select. If 0, no
   *     upper limit is placed on the changed date.
   * - statusChangeStart
   *     The timestamp for the oldest status change to select. If 0, no lower
   *     limit is placed on the status changed date.
   * - statusChangeEnd
   *     The timestamp for the most recent status
   *     change to select. If 0, no upper limit is placed on the status changed
   *     date.
   *
   * @var int[]
   */
  protected array $timestamps = [];

  /**
   * Taxonomy term IDs to use in the filter.
   *
   * @var int[]
   */
  protected array $tids = [];

  /**
   * Whether to EXCLUDE issues with ANY of the tags (TRUE), or INCLUDE issues
   * with ALL of the tags (FALSE).
   */
  protected bool $excludeTerms = FALSE;

  /**
   * Constructs a new immutable issue metadata value object from another
   * metadata object.
   *
   * @param ImmutableIssueMetadata $metadata
   *   The metadata to upcast into the immutable base class.
   */
  public function __construct(ImmutableIssueMetadata $metadata) {
    // Initialize properties from the other object.
    foreach ($metadata as $property => $value) {
      $this->$property = $value;
    }
  }

  /**
   * Validates supplied metadata using the MagicIntMetadata values.
   *
   * @param string[]|int[] $data
   *   The issue data supplied by the caller, as either a list of integer IDs,
   *   or a list of user-friendly short strings depending on the context (like
   *   'nw' and 'postponed', or 'task' and 'feature', etc.).
   * @param string[] $valid
   *   The valid MagicIntMetadata strings accepted for the given context.
   *
   * @throws \UnexpectedValueException
   *   If the provided data format is not valid.
   */
  protected static function validateData(array $data, array $valid) {
    // If the user passed string labels, convert them to integer IDs.
    if (is_string($data[0])) {
      // Overwrite the data with known tid values for the short names.
      foreach ($data as $index => $string) {
        if (!is_string($string)) {
          throw new \UnexpectedValueException('Data in setters cannot mix string and integer values.');
        }
        if (!isset($valid[$string])) {
          throw new \UnexpectedValueException("$string is not an allowed short label for the specified issue metadata category. See the Drupal\core_metrics\MagicIntMetadata class for more information.");
        }
        $data[$index] = $valid[$string];
      }
    }

    return $data;
  }

  /**
   * Gets the issue categories.
   *
   * @return string[]
   *   The issue types to select.
   */
  public function getCategories(): array {
    return $this->categories;
  }

  /**
   * Gets the issue types. Alias of getCategories().
   *
   * @return string[]
   *   The issue types to select.
   */
  public function getTypes(): array {
    return $this->categories;
  }

  /**
   * Gets the issue branches.
   *
   * @return string[]
   *   The branches to select.
   */
  public function getVersions(): array {
    return $this->versions;
  }

  /**
   * Alias for getVersions().
   */
  public function getBranches(): array {
    return $this->getVersions();
  }

  /**
   * Gets the issue priorities.
   *
   * @return string[]
   *   The issue types to select.
   */
  public function getPriorities(): array {
    return $this->priorities;
  }

  /**
   * Gets the issue statuses.
   *
   * @return string[]
   *   The issue types to select.
   */
  public function getStatuses(): array {
    return $this->statuses;
  }

  /**
   * Gets the issue components.
   *
   * @return string[]
   *   The issue types to select.
   */
  public function getComponents(): array {
    return $this->components;
  }

  /**
   * Gets the array of potential timestamps start and end limits for the query.
   *
   * @return int[]
   *   The oldest updated dates for issues to select.
   */
  public function getTimestamps(): array {
      return $this->timestamps;
  }

/**
   * Gets the array of issue timestamp conditions.
   *
   * @return int[]
   *   The array of timestamps.
   */
  public function getChangedStartTimestamp(): array {
    return $this->timestamps['changedStart'];
  }

  /**
   * Gets the end date for the last issue changes.
   *
   * @return int
   *   The newest updated dates for issues to select. If 0, any issues after
   *   the start date will be selected.
   */
  public function getChangedEndTimestamp(): int {
    return $this->timestamps['changedEnd'];
  }

  /**
   * Gets the start date for the oldest issue status change.
   *
   * @return int
   *   The oldest updated dates for issues to select. If 0, no lower limit is
   *   placed on the issue last updated date.
   */
  public function getStatusChangeTimestamp(): int {
    return $this->timestamps['statusChangeStart'];
  }

  /**
   * Gets the end date for the newest issue status change.
   *
   * @return int
   *   The newest updated dates for issues to select. If 0, any issues after
   *   the start date will be selected.
   */
  public function getStatusChangeEndTimestamp(): int {
    return $this->timestamps['statusChangeEnd'];
  }

  /**
   * Gets the taxonomy term IDs.
   *
   * @return string[]
   *   The term IDs to use for filtering.
   */
  public function getTids(): array {
    return $this->tids;
  }

  /**
   * Gets the setting for whether the terms should be included or excluded.
   *
   * @return bool
   *   TRUE if the terms should all be excluded, or FALSE if they should all be
   *   included.
   */
  public function excludeTerms(): bool {
    return $this->excludeTerms;
  }

}
