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

}
