<?php

namespace Drupal\cwd_migrate_policy\Plugin\migrate\source;

use Drupal\Core\Database\Query\Condition;
use Drupal\file\Plugin\migrate\source\d7\File;

/**
 * Drupal 7 file source from database.
 *
 * @MigrateSource(
 *   id = "cwd_policy_attachments",
 *   source_module = "file"
 * )
 */
class CwdPolicyAttachments extends File {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = parent::query();

    // This attempt didn't work: Duplicate results b/c some files are on more than one node, and, it seems you can't add distinct() to an existing/"parent" query 😭
    // $query->join('field_data_field_attachment', 'fdfa', 'f.fid = fdfa.field_attachment_fid');
    // $query->fields('fdfa');
    // $query->condition('fdfa.bundle', 'policies');
    // $query->distinct();

    $subquery = $this->select('field_data_field_attachment', 'fdfa');
    $subquery->fields('fdfa', ['field_attachment_fid']);
    $subquery->distinct();
    $subquery->condition('fdfa.bundle', 'policies');

    $query->condition('f.fid', $subquery, 'IN');

    return $query;
  }
}
