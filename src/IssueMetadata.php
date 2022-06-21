<?php

namespace Drupal\core_metrics;

/**
 * Defines a value object of issue metadata for a database query.
 */
class IssueMetadata extends ImmutableIssueMetadata {

  /**
   * The static issue metadata.
   */
  protected static MagicIntMetadata $metadata;

  /**
   * Constructs a new issue metadata value object.
   */
  public function __construct() {
    static::$metadata = new MagicIntMetadata();

    // By default, select open issues in "relevant" statuses.
    $this->statuses = static::$metadata::$open;

    // By default, select the actively supported branches.
    $this->versions = array_unique(static::$metadata::$activeBranches);
  }

  /**
   * Sets the issue branches.
   *
   * @param string[] $branches
   *   The branch names. '-dev' will be appended automatically if needed for
   *   the issue query.
   */
  public function setVersions(array $branches) {
    foreach ($branches as $index => $branch) {
      $branches[$index] = static::validateBranch($branch, TRUE);
    }
    $this->versions = $branches;
  }

  /**
   * Alias for setVersions().
   */
  public function setBranches(array $branches) {
    $this->setVersions();
  }

  /**
   * Sets the issue categories.
   *
   * @param string[]|int[] $categories
   *   If the values of terms are integers, they are assumed to be category
   *   IDs. If they are strings, they are assumed to be shorthand labels as
   *   defined in the magic metadata (like 'bug' or 'task').
   */
  public function setCategories(array $categories) {
    $data = static::validateData($categories, static::$metadata::$type);
    $this->categories = $data;
  }

  /**
   * Sets the issue types. Alias of setCategories().
   *
   * @param string[]|int[] $categories
   *   If the values of terms are integers, they are assumed to be category
   *   IDs. If they are strings, they are assumed to be shorthand labels as
   *   defined in the magic metadata (like 'bug' or 'task').
   */
  public function setTypes(array $categories) {
    $this->setCategories($categories);
  }

  /**
   * Sets the issue priorities.
   *
   * @param string[]|int[] $priorities
   *   If the values of terms are integers, they are assumed to be priority
   *   IDs. If they are strings, they are assumed to be shorthand labels as
   *   defined in the magic metadata (like 'critical' or 'major').
   */
  public function setPriorities(array $priorities) {
    $data = static::validateData($priorities, static::$metadata::$priority);
    $this->priorities = $data;
  }

  /**
   * Sets the statuses.
   *
   * @param string[]|int[] $statuses
   *   If the values of terms are integers, they are assumed to be category
   *   IDs. If they are strings, they are assumed to be shorthand labels as
   *   defined in the magic metadata (like 'critical' or 'major').
   */
  public function setStatuses(array $statuses) {
    $data = static::validateData($statuses, static::$metadata::$status);
    $this->statuses = $data;
  }

  /**
   * Sets the taxonomy term query data.
   *
   * @param string[]|int[] $terms
   *   If the values of terms are integers, they are assumed to be term IDs. If
   *   they are strings, they are assumed to be shorthand labels as defined in
   *   the magic metadata.
   * @param bool $exclude
   *   Whether to search for issues that include ALL the given tags (FALSE), or
   *   NONE OF the given tags (TRUE). Defaults to FALSE.
   */
  public function setTaxonomyData(array $terms, $exclude = FALSE) {
    $data = static::validateData($terms, static::$metadata::$tids);
    $this->tids = $data;
    $this->excludeTerms = $exclude;
  }

  /**
   * Sets the components.
   *
   * @param string[] $components
   *   The issue components to select, e.g. 'views.module' or 'media system'.
   */
  public function setComponents(array $components) {
    $this->components = $components;
  }

}
