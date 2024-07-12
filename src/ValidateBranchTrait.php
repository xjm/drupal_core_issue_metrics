<?php

namespace Drupal\core_metrics;

/**
 * Validates and formats  branch data.
 */
trait ValidateBranchTrait {

  /**
   * Validates a branch name.
   *
   * @param string $branch
   *   The name of the branch.
   * @param bool $issueFormat
   *   Casts the branch to issue format (e.g. 9.4.x-dev) if TRUE, or git format
   *   (e.g. 9.4.x). Defaults to FALSE.
   *
   * @return string
   *   The branch name formatted according to the setting in $issueFormat.
   *
   * @throws \UnexpectedValueException
   *   Thrown when the branch name is not formatted correctly.
   */
  public static function validateCoreBranch(string $branch, bool $issueFormat = FALSE, $core = TRUE): string {
    // Special-case 11.x for core.
    if ($core && ($branch === '11.x' || $branch === '11.x-dev')) {
      return $issueFormat ? '11.x-dev' : '11.x';
    }
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
   * Sanitizes a branch name.
   *
   * Git branch names are very permissive, but rather than write a monster
   * regex, we use a list of allowed and commonly used branch name characters.
   *
   * @param string $branch
   *   The branch name to sanitize.
   *
   * @return string
   *   The sanitized branch name.
   *
   * @throws \UnexpectedValueException
   *   Thrown if there are characters disallowed by our regex.
   */
  public static function sanitizeBranch(string $branch): string {
    $regex = '/^[A-Za-z0-9_.\-\/]+$/';
    if (!preg_match($regex, $branch)) {
      throw new \UnexpectedValueException("$branch is not an allowed git branch name.");
    }
    return $branch;
  }

}
