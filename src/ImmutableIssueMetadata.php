<?php

namespace Drupal\core_metrics;

/**
 * Defines a value object of issue metadata for a database query.
 */
class ImmutableIssueMetadata {

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
   * Validates a branch name.
   *
   * @param string $branch
   *   The name of the branch.
   * @param bool $issueFormat
   *   Casts the branch to issue format (e.g. 9.4.x-dev) if TRUE, or git format
   *   (e.g. 9.4.x). Defaults to FALSE.
   */
  public static function validateBranch(string $branch, bool $issueFormat = FALSE) {
    // We allow either git branch format, e.g. 9.4.x, or issue queue format,
    // e.g. 9.4.x-dev.
    $regex = '/([0-9])+\.([0-9])+\.x(\-dev)?\z/';
    $matches = [];
    if (!preg_match($regex, $branch, $matches)) {
      throw new \UnexpectedValueException("Branch $branch must be in one of the following formats, using 9.4 as an example: 9.4.x or 9.4.x-dev.");
    }

    // If the branch string has the -dev suffix.
    if (!empty($matches[3])) {
      // Return the git-formatted version of the branch if requested instead.
      if ($issueFormat === FALSE) {
        return $matches[1] . '.' . $matches[2];
      }
    }
    // Otherewise, there's no -dev suffix.
    else {
      // Return the issue-formatted version of the branch if requested instead.
      if ($issueFormat === TRUE) {
        return $branch . '-dev';
      }
    }

    // If we get to here, the version of the branch submitted is the one
    // requested.
    return $branch;
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
  public function getCategories() {
    return $this->categories;
  }

  /**
   * Gets the issue types. Alias of getCategories().
   *
   * @return string[]
   *   The issue types to select.
   */
  public function getTypes() {
    return $this->categories;
  }

  /**
   * Gets the issue branches.
   *
   * @return string[]
   *   The branches to select.
   */
  public function getVersions() {
    return $this->versions;
  }

  /**
   * Alias for getVersions().
   */
  public function getBranches() {
    return $this->getVersions();
  }

  /**
   * Gets the issue priorities.
   *
   * @return string[]
   *   The issue types to select.
   */
  public function getPriorities() {
    return $this->priorities;
  }

  /**
   * Gets the issue statuses.
   *
   * @return string[]
   *   The issue types to select.
   */
  public function getStatuses() {
    return $this->statuses;
  }

  /**
   * Gets the issue components.
   *
   * @return string[]
   *   The issue types to select.
   */
  public function getComponents() {
    return $this->components;
  }

  /**
   * Gets the taxonomy term IDs.
   *
   * @return string[]
   *   The term IDs to use for filtering.
   */
  public function getTids() {
    return $this->tids;
  }

  /**
   * Gets the setting for whether the terms should be included or excluded.
   *
   * @return bool
   *   TRUE if the terms should all be excluded, or FALSE if they should all be
   *   included.
   */
  public function excludeTerms() {
    return $this->excludeTerms;
  }

}
