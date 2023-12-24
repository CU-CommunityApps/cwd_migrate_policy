<?php

// PUT THIS FILE IN web/sites/default/
// ADD "include" TO web/sites/default/settings.php (refer to pantheon docs link below, and cwd_policy_fcs/README.md)

/**
/**
 * @file
 * Configuration file with source Drupal 7 site database credentials.
 * DB creds can be updated with pantheon-systems/terminus-secrets-plugin, on
 * whichever env you're migrating **INTO.**
 * Reference: https://pantheon.io/blog/running-drupal-8-data-migrations-pantheon-through-drush
 * Also: https://github.com/CU-CommunityApps/cwd_migrate_fcs
 */
$secretsFile = $_SERVER['HOME'] . '/files/private/secrets.json';
if (file_exists($secretsFile)) {
  $secrets = json_decode(file_get_contents($secretsFile), 1);
}

if (!empty($secrets['migrate_source_db__url'])) {
  $parsed_url = parse_url($secrets['migrate_source_db__url']);
  if (!empty($parsed_url['port']) && !empty($parsed_url['host']) && !empty($parsed_url['pass'])) {
    $databases['migrate']['default'] = array (
      'database' => 'pantheon',
      'username' => 'pantheon',
      'password' => $parsed_url['pass'],
      'host' => $parsed_url['host'],
      'port' => $parsed_url['port'],
      'driver' => 'mysql',
      'prefix' => '',
      'collation' => 'utf8mb4_general_ci',
    );
  }
}
