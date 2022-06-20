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
   * The git branch names to select. '-dev' will be appended automatically.
   *
   * @var string[]
   */
  protected array $branches;

  /**
   * The issue category IDs to select.
   *
   * @var int[]
   */
  protected array $categories;

  /**
   * The issue category IDs to select.
   *
   * @var int[]
   */
  protected array $categories;

  /**
   * The integer issue priority values to select.
   *
   * @var int[]
   */
  protected array $priorities;

  /**
   * The integer issue status values to select.
   *
   * @var int[]
   */
  protected array $statuses;

  /**
   * Array of issue component labels to select.
   *
   * @var string[]
   */
  protected array $components;

  /**
   * Taxonomy term IDs to use in the filter.
   *
   * @var int[]
   */
  protected array $tids;

  /**
   * Whether to EXCLUDE issues with ANY of the tags (TRUE), or INCLUDE issues
   * with ALL of the tags (FALSE).
   */
  protected bool $excludeTerms = FALSE;

  /**
   * Constructs a new issue metadata value object.
   */
  public function __construct() {
    static::$metadata = new MagicIntMetadata();

    // By default, select open issues in "relevant" statuses.
    $this->statuses = static::$metadata::$open;

    // By default, select the actively supported branches.
    $this->branches = array_unique(static::$metadata::$activeBranches);
  }

  /**
   * Sets the issue branches.
   *
   * @param string[] $branches
   *   The branch names. '-dev' will be appended automatically if needed for
   *   the issue query.
   */
  public function setBranches(array $branches) {
    foreach ($branches as $index => $branch) {
      $branches[$index] = static::validateBranch($branch, TRUE);
    }
    $this->branches = $branches;
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
    if (!empty($matches[3]) {
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
   * Sets the issue categories.
   *
   * @param string[]|int[] $categories
   *   If the values of terms are integers, they are assumed to be category
   *   IDs. If they are strings, they are assumed to be shorthand labels as
   *   defined in the magic metadata (like 'bug' or 'task').
   */
  public function setCategories(array $categories) {
    $data = $this->validateData($categories, static::$metadata::$type);
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
    $data = $this->validateData($priorities, static::$metadata::$priority);
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
    $data = $this->validateData($statuses, static::$metadata::$status);
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
    $data = $this->validateData($categories, static::$metadata::$tids);
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
  protected function validateData(array $data, array $valid) {
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
   * Gets the issue branches.
   *
   * @return string[]
   *   The branches to select.
   */
  public function getCategories() {
    return $this->branches;
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
  public function excludeTids() {
    return $this->excludeTids;
  }

}
