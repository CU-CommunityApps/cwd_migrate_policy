<?php

namespace Drupal\cwd_migrate_policy\Plugin\migrate\source;

use Drupal\migrate\Plugin\migrate\source\SqlBase;

/**
 * TO DO: Remove this source plugin, b/c we're actually just gonna use cwd_policy_attachments for the media migration 🙀
 *
 * Original comments...
 * TO DO: explain and whatever.
 * TO DO: apparently "source_module" is supposed to exist on the source site!! -- not sure why this plugin is even working, but it is -- anyway, how about let's look into it, ya?
 * ^^ via Vicky and Benji on Drupal Slack (https://drupal.slack.com/archives/C226VLXBP/p1619146053257500?thread_ts=1619137384.253000&cid=C226VLXBP)
 *
 * @MigrateSource(
 *   id = "cwd_file_entity_document",
 *   source_module = "cwd_migrate_policy",
 * )
 */
class CwdFileEntityDocument extends SqlBase {
  /**
   * {@inheritdoc}
   */
  public function query() {
    $query = $this->select('file_managed', 'f');
    $query->fields('f');
    $query->condition('f.type', 'document');
    $query->orderBy('f.fid');
    return $query;
  }
  /**
   * {@inheritdoc}
   */
  public function fields() {
    return [
      'fid' => $this->t('File ID'),
      'filename' => $this->t('Filename'),
      'uri' => $this->t('URI'),
    ];
  }
  /**
   * {@inheritdoc}
   */
  public function getIds() {
    return [
      'fid' => [
        'type' => 'integer',
        'alias' => 'f',
      ],
    ];
  }
}
