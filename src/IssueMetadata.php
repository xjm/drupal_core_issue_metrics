<?php

namespace Drupal\core_metrics;

/**
 * Static value object of issue metadata.
 *
 * @see https://www.drupal.org/drupalorg/docs/apis/rest-and-other-apis
 */
class IssueMetadata {

  /**
   * Issue statuses.
   */
  public static $status = [
    'active' => 1,
    'nw' => 13,
    'nr'  => 8,
    'rtbc' => 14,
    'postponed' => 4,
    'fixed' => 2,
    'closed_fixed' => 7,
  ];

  /**
   * The fixed statuses.
   */
  public static $fixed = [2, 7];

  /**
   * The relevant open statuses.
   */
  public static $open = [1, 13, 8, 14, 4];

  /**
   * Issue priorities.
   */
  public static $priority = [
   'critical' => 400,
   'major' => 300,
   'normal' => 200,
   'minor' => 100,
  ];

  /**
   * Issue types.
   */
  public static $type = [
    'bug' => 1,
    'task' => 2,
    'plan' => 5,
    'feature' => 3,
  ];

  /**
   * Current issue branches.
   */
  public static $branches = [
   // The current (stable) minor release branch.
   'stable' => '9.4.x',

   // The security-only branch.
   'sec' => '9.3.x',

   // The upcoming (alpha, beta, or RC) minor release branch in preparation.
   'prep' => '9.5.x',

   // The next (developmental) minor release branch.
   'dev' => '9.5.x',

   // The next major release branch.
   'major' => '10.0.x',
  ];

  /**
   * Issue tag term IDs.
   *
   * These term IDs will be found in taxonomy_vocabularly_9.
   */
  public static $tids = [
    'triaged_critical' => 164349,
    'triaged_major' => 174642,
    'critical_triage_deferred' => 169071,
    'major_triage_deferred' => 177412,
    'major_current_state' => 177626,
    'fm_review' => 169963,
    'pm_review' => 170004,
    'rm_review' => 171496,
    'd8up' => 27290,
    'd7backport' => 21556,
    'd6backport' => 182,
    'vdc' => 36416,
    'twig' => 36330,
    'entity' => 38578,
    'blocker' => 38080,
    'api_first' => 177096,
  ];

  public static $uids = [
    'dries' => 1,
    'alexpott' => 157725,
    'catch' => 35733,
    'cilefen' => 1850070,
    'cottser' => 1167326,
    'devin' => 290182,
    'alwaysworking' => 1602706,
    'effulgentsia' => 78040,
    'gabor' => 4166,
    'jessebeach' => 748566,
    'larowlan' => 395439,
    'lauriii' => 1078742,
    'moshe' => 23,
    'plach' => 183211,
    'timplunkett' => 241634,
    'webchick' => 24967,
    'wim' => 99777,
    'xjm' => 65776,
    'yoroy' => 41502,
    'system_message' => 180064,
  ];

  public static $fm = ['alexpott', 'effulgentsia', 'catch', 'larowlan'];
  public static $fefm = ['lauriii', 'bnjmnm', 'ckrina'];
  public static $rm = ['catch', 'xjm', 'quietone'];
  public static $pm = ['dries', 'webchick', 'yoroy', 'gabor'];
  public static $js = ['nod_', 'justafish'];

  public static $dat = [
    'gabor',
    'effulgentsia',
    'wim',
    'xjm',
    'timplunkett',
    'tedbow',
    // ...
  ];

}
