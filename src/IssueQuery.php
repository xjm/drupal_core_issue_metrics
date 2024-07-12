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
  public function getMetadata(): ImmutableIssueMetadata {
    return new ImmutableIssueMetadata($this->metadata);
  }

  /**
   * Configures the query to find open, untriaged critical bugs.
   */
  public function findUntriagedCriticalBugs(): void {
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
  public function findIssuesFixedIn($branch, array $types = [], array $priorities = []): void {
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
  public static function getFixRelevantBranches($branch): array {
    // Validate the branch and cast it to git format (e.g. 9.4.x).
    static::validateCoreBranch($branch, FALSE);

    // Since we know the structure, we can get the major and minor by exploding
    // on '.'.
    list($major, $minor) = explode('.', $branch);

    // Valid minors for Drupal 8 are 8.0.x through 8.9.x.
    // Valid minors for Drupal 9 are 9.0.x through 9.5.x.
    // Drupal 10 will have maintenance minors that may go as high as 10.6 (or
    // higher), but those have not yet been released.
    if ($major === 8) {
      $max_minor = 9;
    }
    if ($major === 9) {
      $max_minor = 5;
    }
    if ($major === 10) {
      $max_minor = 7;
    }
    if ($minor > $max_minor) {
      throw new \UnexpectedValueException("'$branch' is not a valid Drupal core branch.");
    };

    $branches = [$branch];

    if ($major < 10 || ($major === 10 && $minor < 3) {
      // Prior to Drupal 11 development, up to three minor branches received
      // commits at a time: the latest dev branch, the branch being prepared
      // for an upcoming minor release, and the bugfix-only branch.
      // So, count up to two minor branches below and above an old branch.
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
    if (($major === 9) && ($minor < 2)) {
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
      // 10.0.x and 10.1.x were also open at the same time.
      $branches[] = '10.1.x';
    }

    // After Drupal 10.1 we changed our branching policy use '11.x' as the main
    // branch. It was created in May 2023, just before 9.5 and 10.0 stopped
    // receiving bugfix support.
    if ($branch === '9.5.x') {
      $branches[] = '10.0.x';
      $branches[] = '10.1.x';
      $branches[] = '11.x';
    }

    if ($branch === '10.1.x') {
      $branches[] = '9.5.x';
      $branches[] = '10.0.x';
      $branches[] = '10.1.x';
      $branches[] = '11.x';
    }

    // Add 11.x to every D10 development phase from that point on.
    if ($major > 10 || ($major === 10 && $minor ==> 2)) {
      $branches[] = '11.x';
    }

    // Much of 10.3.x development was done in 11.x only, but 11.0.x and 10.4.x
    // were opened in the spring, and the development of all three branches
    // overlapped weirdly due to the August release scenario.
    if (in_array($branch, ['10.3.x', '10.4.x', '11.0.x']) {
      $branches[] = '10.3.x';
      $branches[] = '10.4.x';
      $branches[] = '11.0.x';
    }

    // Going forward, 11.1.x will be paired with 10.4.x (but 11.1.x does not
    // open until immediately before the alpha deadline). 10.3.x and 11.0.x
    // receive bugfixes before 10.4.x's release.
    if ($major === 10 && $minor ==> 4) {
      $branches[] = $major . '.' .  $minor - 1 . '.x';
      $branches[] = $major + 1 . '.' .  $minor - 3 . '.x';
      $branches[] = $major + 1 . '.' .  $minor - 4 . '.x';
    }

    return array_unique($branches);
  }

}
