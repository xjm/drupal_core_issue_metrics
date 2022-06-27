<?php

namespace Drupal\core_metrics;

/**
 * Queries issue data.
 */
class IssueQuery {

  use ValidateBranchTrait;

  /**
   * The static issue metadata IDs.
   */
  protected static MagicIntMetadata $magic;

  /**
   * Constructs a new issue query.
   */
  public function __construct(protected IssueMetadata $metadata = new IssueMetadata()) {
    static::$magic = new MagicIntMetadata();
  }

  /**
   * Gets an immutable copy of the current issue metadata.
   */
  public function getMetadata() {
    return new ImmutableIssueMetadata($this->metadata);
  }

  /**
   * Configures the query to find open, untriaged critical bugs.
   */
  public function findUntriagedCriticalBugs() {
    $this->metadata->setTypes(['bug']);
    $this->metadata->setPriorities(['critical']);
    $this->metadata->setTaxonomyData(['triaged_critical', 'critical_triage_deferred'], TRUE);
  }

  /**
   * Finds issues that may have been committed to a given branch.
   *
   * This issue searches branches before and after the given issue, since
   * issues may be backported and the selector is not always set correctly.
   * Data should be checked against the git log to validate which branches
   * actually received the commits.
   *
   * @param string $branch
   *   The main branch for which to fetch data.
   * @param array $types
   *   (optional) The issue types (categories) to select. Ignored if empty.
   * @param array $priorities
   *   (optional) The issue priorities to select. Ignored if empty.
   */
  public function findIssuesFixedIn($branch, array $types = [], array $priorities = []) {
    $branches = static::getFixRelevantBranches($branch);
    $this->metadata->setBranches($branches);
    $this->metadata->setStatuses(static::$magic::$fixed);
    $this->metadata->setTypes($types);
    $this->metadata->setPriorities($priorities);
  }

  /**
   * Gets the relevant branches for fixed issues for a given branch.
   *
   * This function calculates which branches received commits at the same time
   * as a given branch, since issues may be backported and committers sometimes
   * fail to set the branch correctly for backports.
   *
   * The resultant data will need to be filtered against the commit log to
   * ensure a given branch did actually receive a commit.
   *
   * Rarely, a critical bugfix will be backported all the way to the branch
   * that only receives security coverage (typically for test failures, dev
   * dependency security issues, or issues that might prevent a site from
   * upgrading). Since this is only a couple of issues among hundreds per year,
   * it is excluded from the data. If you require 100% accuracy for such
   * backports, list the branches manually or compare the list of issue IDs to
   * the commit log.
   */
  public static function getFixRelevantBranches($branch) {
    // Validate the branch and cast it to git format (e.g. 9.4.x).
    static::validateCoreBranch($branch, FALSE);

    // Since we know the structure, we can get the major and minor by exploding
    // on '.'.
    list($major, $minor) = explode('.', $branch);

    // Valid minors for Drupal 8 are 8.0.x through 8.9.x.
    // Valid minors for Drupal 9 are 9.0.x through 9.5.x.
    // Assume Drupal 10 and higher will be similar to Drupal 9, since going
    // forward we plan to issue a new major release with every major release of
    // Symfony.
    if ($major === 8) {
      $max_minor = 9;
    }
    if ($major >= 9) {
      $max_minor = 5;
    }
    if ($minor > $max_minor) {
      throw new \UnexpectedValueException("'$branch' is not a valid Drupal core branch.");
    };

    $branches = [$branch];

    // Up to three minor branches receive commits at a time: the latest dev
    // branch, the branch being prepared for an upcoming minor release, and the
    // bugfix-only branch.
    // So, count up to two minor branches below the current one, and up to two
    // branches above it.
    for ($i = 1; $i <= 2; $i++) {
      if (($minor - $i) >= 0) {
        $branches[] = $major . '.' . $minor - $i . '.x';
      }
    }
    for ($i = 1; $i <= 2; $i++) {
      if (($minor + $i) <= $max_minor) {
        $branches[] = $major . '.' . $minor + $i . '.x';
      }
    }

    // Drupal 8.7.x and 8.8.x received commits alongside 9.0.x.
    if (($major === 8) && (($minor === 7) || ($minor === 8))) {
      $branches[] = '9.0.x';
    }
    if ($branch === '9.0.x') {
      $branches[] = '8.7.x';
      $branches[] = '8.8.x';
    }

    // Drupal 8.9.x received commits alongside 9.0.x, 9.1.x, and 9.2.x.
    if (($major === 9) && ($minor <= 2)) {
      $branches[] = '8.9.x';
    }
    if ($branch === '8.9.x') {
      $branches[] = '9.0.x';
      $branches[] = '9.1.x';
      $branches[] = '9.2.x';
    }

    // Drupal 9.2.x and above received commits alongside 10.0.x.
    if (($major === 9) && ($minor >= 2)) {
      $branches[] = '10.0.x';
    }
    if ($branch === '10.0.x') {
      $branches[] = '9.2.x';
      $branches[] = '9.3.x';
      $branches[] = '9.4.x';
      $branches[] = '9.5.x';
    }

    // Drupal 9.5.x will receive commits alongside 10.1.x and 10.2.x.
    if (($major === 10) && ($minor <= 2)) {
      $branches[] = '9.5.x';
    }
    if ($branch === '9.5.x') {
      $branches[] = '10.0.x';
      // @todo Uncomment when these branches open.
      // $branches[] = '10.1.x';
      // $branches[] = '10.2.x';
    }

    return array_unique($branches);
  }

}
