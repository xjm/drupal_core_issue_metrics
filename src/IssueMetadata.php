<?php

namespace Drupal\core_metrics;

/**
 * Defines a value object of issue metadata for a database query.
 */
class IssueMetadata {

  /**
   * The static issue metadata.
   */
  protected static MagicIntMetadata $metadata;

  /**
   * The issue categories to select, as an array of human-readable short names
   * (e.g. 'bug' or 'task'). All issue types are included by default.
   *
   * @var string[]
   */
  protected array $categories = ['bug', 'task', 'feature', 'plan'];

  /**
   * The issue priorities to select, as an array of human-readable short names
   * (e.g. 'critical' or 'major'). By default, all priorities are included.
   *
   * @var string[]
   */
  protected array $priorities = ['critical', 'major', 'normal', 'minor'];

  /**
   * The issue statuses to select, as an array of human-readable short names
   * (e.g. 'postponed' or 'nr'). If this is empty, the constructor will
   * automatically select all issues in "relevant" open statuses (everything
   * except 'Fixed' and PMNMI).
   *
   * @var string[]
   */
  protected array $statuses = [];

  /**
   * Array of issue components to select. If empty, issues in all components
   * are returned.
   *
   * @var string[]
   */
  protected array $components;

  /**
   * Taxonomy term IDs to use in the filter.
   *
   * @var int[]
   */
  protected array $tids = [];

  /**
   * Whether to EXCLUDE issues with ANY of the tags (TRUE), or INCLUDE issues
   * with ALL of the tags (FALSE).
   *
   * @var bool
   */
  protected bool $excludeTerms = FALSE;

  /**
   * Constructs a new issue metadata value object.
   */
  public function __construct() {
    static::$metadata = new MagicIntMetadata();
    $this->statuses = static::$metadata::$open;
  }

  /**
   * Sets the issue categories.
   *
   * @param string[] $categories
   *   The issue types to select.
   */
  public function setCategories(array $categories) {
    $this->categories = $categories;
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
   * Sets the priorities.
   *
   * @param string[] $priorities
   *   The issue priorities to select, e.g. 'nw' or 'postponed'.
   */
  public function setPriorities(array $priorities) {
    $this->priorities = $priorities;
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
   * Sets the statuses.
   *
   * @param string[] $statuses
   *   The issue statuses to select, e.g. 'nw' or 'postponed'.
   */
  public function setStatuses(array $statuses) {
    $this->statuses = $statuses;
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
   * Sets the components.
   *
   * @param string[] $components
   *   The issue components to select, e.g. 'nw' or 'postponed'.
   */
  public function setComponents(array $components) {
    $this->components = $components;
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
   * Sets the taxonomy term query data.
   *
   * @param array $terms
   *   If the values of terms are integers, they are assumed to be term IDs. If
   *   they are strings, they are assumed to be shorthand labels as defined in
   *   the magic metadata.
   * @param bool $exclude
   *   Whether to search for issues that include ALL the given tags (FALSE), or
   *   NONE OF the given tags (TRUE). Defaults to FALSE.
   */
  public function setTaxonomyData(array $terms, $exclude = FALSE) {
    if (is_string($terms[0])) {
      // Overwrite the data with known tid values for the short names.
      foreach ($terms as $index => $term) {
        $terms[$index] = static::$metadata::$tids[$term];
      }
    }
    $this->tids = $terms;
    $this->excludeTerms = $exclude;
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
  public function excludeTids() {
    return $this->excludeTids;
  }

}
