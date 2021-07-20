# Policy Migration
Migrate policies from DFA Drupal 7 site to CRS Policy Office Drupal 8 site.

## misc drush/config commands

```
lando drush cim --partial --source=../config
lando drush cim --partial --source=modules/custom/cwd_migrate_policy/config/install

drush migr_cim_pol
drush migr_cim_normal

lando drush mim upgrade_d7_taxonomy_term_policy_executive
lando drush mr upgrade_d7_taxonomy_term_policy_executive
lando drush ms upgrade_d7_taxonomy_term_policy_executive
lando drush mmsg upgrade_d7_taxonomy_term_policy_executive
```

## getting started
### A few references...
* https://thinktandem.io/blog/2020/04/28/lando-migration-webinar-part-1-followup/
* https://github.com/thinktandem/migration_boilerplate#setup
* Convo with Duston on Lando Slack: https://devwithlando.slack.com/archives/C2XBSHX8R/p1615916356008600?thread_ts=1604969491.465700&cid=C2XBSHX8R
  * ...for the migration database, you can start by changing the host to `database.whatevertheappnameoftheoldappis.internal`
* FCS migration -- very out-of-date, as emphasized in the README! -- https://github.com/CU-CommunityApps/cwd_migrate_fcs

### Steps
1. Create lando apps for each site (the D7 site and D8 site)
2. Oh by the way, you'll probably have to upgrade drush (on the D8 site) to 9.x, if you haven't already (😭)<br>
  `composer require drush/drush:^9`
3. Look at `lando info` for the D7 site -- copy the "hostnames" string for the "database" service.
4. (not sure if the following step should come after "step 5"??)<br>
Create/edit settings.local.php on the D8 site -- if it's otherwise empty, here's the entirety of the contents (no need to put the default database in), assuming you're using the normal pantheon recipe for both D7 and D8 apps (to find out, check .lando.yml and/or .lando.dist.yml):
    ```
    <?php

    /**
     * @file
     * Add "second database" for the migration source (D7 site).
     */
    $databases['migrate']['default'] = [
      'database' => 'pantheon',
      'username' => 'pantheon',
      'password' => 'pantheon',
      'prefix' => '',
      'host' => 'database.cudfa.internal',
      'port' => '3306',
      'namespace' => 'Drupal\\Core\\Database\\Driver\\mysql',
      'driver' => 'mysql',
    ];
    ```
5. _(not sure if the following step should come before "step 4"??)_<br>
  On Drupal 8 site, add and enable migrate-y modules:
    ```
    composer require 'drupal/migrate_plus'
    composer require 'drupal/migrate_tools'
    composer require 'drupal/migrate_upgrade'
    lando drush en migrate_plus migrate_tools migrate_upgrade migrate_drupal migrate_drupal_ui
    ```
    * _(optional)_
      ```
      composer require drupal/migrate_media_handler
      lando drush en migrate_media_handler
      ```
6. `lando cex` (update your config) and commit those changes...

### More steps!
The rest of your work will depend heavily on the project.  Also, I'm being super rough-drafty in how I write them. YMMV.
* One way to get things rolling -- step 8 of https://github.com/thinktandem/migration_boilerplate#setup:
  * `lando drush migrate-upgrade --legacy-db-key=migrate --configure-only`
  * _(optional)_ add `--migration-prefix` option to add a custom migration prefix
  * ...export the config, but like, somewhere unimportant -- a made-up directory or whatever...
    * `mkdir tmpconfig`
    * `lando drush config-export --destination=../tmpconfig`
* You'll need a new custom module, i.e. `web/modules/custom/cwd_migrate_policy` with a `.info.yml` file and a `config/install` directory.
* Example .info.yml files for your custom module:
  * https://github.com/CU-CommunityApps/cwd_migrate_fcs/blob/master/cwd_migrate_fcs.info.yml
  * https://github.com/thinktandem/migration_boilerplate/blob/master/migration_boilerplate.info.yml
* Move the migration config files from your "whatever directory" (two steps ago) into your new custom module config/install directory.
* Run a config import -- it'll delete all your new migration configs...
* Enable your custom module -- your migration configs will be created again, as part of your custom module.
* Edit config/core.extension.yml to add your custom module.
* Going forward, config import/export commands will be annoying -- you might end up doing lots of single import/exports, or just, be really careful what you commit...
* Going forward, these will be your config import commands:
  * `lando drush cim --partial --source=../config` (aka `lando migr_cim_normal`)
  * `lando drush cim --partial --source=modules/custom/cwd_migrate_policy/config/install` (aka `lando migr_cim_pol`)
  * P.S. I just added these aliases to .lando.dist.yml on the policy site, and, if I remember (🤞), I'll PR them on CD Demo...


## Migration group order
1. `cwd_policy_tax`
1. TO DO

## ...crap copied from my CLI while doing things on pantheon...
Reference: https://pantheon.io/blog/running-drupal-8-data-migrations-pantheon-through-drush

_NOTE: "later", replace cu-dfa.poli-migr with cu-dfa.live_<br>
_NOTE: "later", replace crs-policy-office.pol-mig-stg with crs-policy-office.test_<br>
```
composer create-project -d ~/.terminus/plugins pantheon-systems/terminus-secrets-plugin:~1
  (^^ if you don't have this plugin installed already)
terminus connection:info cu-dfa.poli-migr --field="mysql_url"
  (^^ "just looking")
export D7_MYSQL_URL=$(terminus connection:info cu-dfa.poli-migr --field="mysql_url")
terminus secrets:set crs-policy-office.pol-mig-stg migrate_source_db__url $D7_MYSQL_URL
  (^^ create or update secrets file on multidev server)
terminus secrets:list crs-policy-office.pol-mig-stg --format=json
  (^^ "did it work?")
terminus drush crs-policy-office.pol-mig-stg -- en cwd_migrate_policy migrate_drupal_ui
  (^^ I don't think I need to do this.....)
```

P.S. totally might lose that secrets file -- not sure if I will, but I might, and then just have to regenerate it... (like, if I pull down files from somewhere?? -- or maybe not, I really don't know, just making a note so I don't forget to elaborate later, depending on what happens)

NOTE: later, this all became "nope"... (as implied two lines down)<br>
I manually updated config ignore in the Drupal UI, to ignore core.extension and migrate-y things...<br>
  (^^ but then later I undid this nonsense and just codified/used these config entities the way they should be)<br>
```
core.extension
migrate_media_handler.settings
migrate_drupal.settings
```
_^^ idk why I put these three lines into this file; for now, I'm leaving them_

If my migrate media stuff weren't broken, I would do a manual/single config import of migrate_media_handler.settings on the multidev... (Ok I did it even though it's broken, just because.)

THEN I tried to do a partial config import on the multidev (via drush, b/c in the UI it won't do a partial import, so my migration configs get removed), but it didn't ignore config-ignored configs, so it was going to enable/disable things, other annoying stuff -- idk if config_ignore and drush 9 hate each other, super annoying, but whatever, gotta move on, so I codified core.extension, THEN ran the partial config import...<br>
`terminus drush crs-policy-office.test -- cim --partial --source=../config`<br>
  ^^remember, in the case of this migration, the config import includes creating the policy content type and its "peripherals"!

Then I imported the migration configs:<br>
`terminus drush crs-policy-office.test -- cim --partial --source=modules/custom/cwd_migrate_policy/config/install`

(I also did cache rebuilds here and there, just for kicks...)

On the multidev, I checked the taxonomy migration group, b/c it's the simplest (and the first that needs to be run):<br>
https://pol-mig-stg-crs-policy-office.pantheonsite.io/admin/structure/migrate/manage/cwd_policy_tax/migrations
* It said it couldn't find any entities to migrate, and was I sure I put my DB creds in? -- in fact, no, I'm not sure I put my DB creds in...
* To be fair, after the partial import of "normal configs", I realized I created my multidev out of "dev" instead of "test", and the content was WAY out of date, so I copied the DB/files from "test", then had to do some stuff again.
* Ok that was a lie, I copied the db/files from poli-migr (to pol-mig-stg), b/c I saw that the content was fairly up-to-date on that multidev, and I knew my life would be easier if I copied from that other multidev...
* I checked "secrets" on pol-mig-stg -- indeed, the file was empty:
  ```
  $ terminus secrets:list crs-policy-office.pol-mig-stg --format=json
  []
  ```
* So I re-did the secrets stuff... (see above -- same command(s))<br>
  ...and re-checked the taxonomy migration group (in Drupal UI)...
* BUT STILL NOTHING FOUND! - but no "did you do the DB thing we told you about?" error at the top...
* OH RIGHT! The Pantheon docs use a DB key that's different from the source key I used in my migrations...
  * Pantheon docs DB info (in `settings.migrate-on-pantheon.php`)
    ```
    $databases['drupal_7']['default'] = array (
    ```
  * My migration source key (in `migrate_plus.migration_group.cwd_policy.yml`)
    ```
    shared_configuration:
      source:
        key: migrate
    ```
* Ok so I fixed settings.migrate-on-pantheon.php ("migrate" as the source DB key really does seem to be the "norm", from what I see on the interwebs and in Drupal slack convos...)...
  ```
  $databases['migrate']['default'] = array (
  ```
* ...committed/pushed that change...
* STILL NOTHING - tried "ms" via terminus/drush - got "connection refused" - drupal logs showed a similar error...
* After fighting for a while, it hit me: The source site was ASLEEP.<br>
`terminus env:wake cu-dfa.poli-migr`
* TA DA!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!!<br>
🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉🎉
* Taxonomy migration group shows entities and so on!!<br>
https://pol-mig-stg-crs-policy-office.pantheonsite.io/admin/structure/migrate/manage/cwd_policy_tax/migrations
* Now, running migration things on my multidev...<br>
* `terminus drush crs-policy-office.pol-mig-stg -- mim --group=cwd_policy_tax`
  * Checked Drupal - the terms exist, the migration statuses look good 🎉
* `terminus drush crs-policy-office.pol-mig-stg -- mim upgrade_d7_node_complete_policies`
  * Checked Drupal - things look good (except for my non-functional files/media stuff) 🎉

----
### Reminder - when something fails + hangs
1. Stop the stuck migration:
  ```
  lando drush mst upgrade_d7_file
  ```
1. Reset the status of the stopped-stuck migration:
  ```
  lando drush mrs upgrade_d7_file
  ```

### Order of operations
1. `--group=cwd_policy_tax` -- looks like:
  ```
  terminus drush crs-policy-office.pol-mig-stg -- mim --group=cwd_policy_tax
  ```
1. `upgrade_d7_policy_files` -- looks like:
  ```
  terminus drush crs-policy-office.pol-mig-stg -- mim upgrade_d7_policy_files
  ```
1. `upgrade_d7_policy_document_media` -- looks like:
  ```
  terminus drush crs-policy-office.pol-mig-stg -- mim upgrade_d7_policy_document_media
  ```
1. `upgrade_d7_node_complete_policies` -- looks like:
  ```
  terminus drush crs-policy-office.pol-mig-stg -- mim upgrade_d7_node_complete_policies
  ```

----
### troubleshooting notes
**_TO DO: clean up or something_**

mysql query I wrote while figuring out how to do the custom source plugin stuff (only grab files that are referenced in field_attachment on nodes of type "policies")
```sql
SELECT file_managed.fid, file_managed.filename, field_data_field_attachment.bundle, field_data_field_attachment.entity_id, field_data_field_attachment.field_attachment_fid, field_data_field_attachment.delta
  FROM file_managed
  INNER JOIN field_data_field_attachment ON file_managed.fid=field_data_field_attachment.field_attachment_fid
  WHERE field_data_field_attachment.bundle = 'policies'
  AND file_managed.type = 'document'
  LIMIT 10;
```

...troubleshooting unprocessed items...

SELECT file_managed.fid, file_managed.filename
  FROM file_managed
  INNER JOIN field_data_field_attachment ON file_managed.fid=field_data_field_attachment.field_attachment_fid
  WHERE field_data_field_attachment.bundle = 'policies'
  AND file_managed.type = 'document';

SELECT media__field_media_file.entity_id, media__field_media_file.field_media_file_target_id
  FROM media__field_media_file
  INNER JOIN node__field_policy_file ON media__field_media_file.entity_id=node__field_policy_file.field_policy_file_target_id
  INNER JOIN file_managed ON file_managed.fid=field_data_field_attachment.field_attachment_fid
  WHERE node__field_policy_file.bundle = 'policy';

ah ha!
SELECT DISTINCT file_managed.fid, file_managed.filename
  FROM file_managed
  INNER JOIN field_data_field_attachment ON file_managed.fid=field_data_field_attachment.field_attachment_fid
  WHERE field_data_field_attachment.bundle = 'policies'
  AND file_managed.type = 'document';


Policy node migration error (it didn't work til I used --force, btw.)
```bash
$ terminus drush crs-policy-office.poli-migr -- mim upgrade_d7_node_complete_policies --force
 [warning] This environment is in read-only Git mode. If you want to make changes to the codebase of this site (e.g. updating modules or plugins), you will need to toggle into read/write SFTP mode first.
 [error]  Invalid translation language (und) specified. (/code/web/core/lib/Drupal/Core/Entity/ContentEntityBase.php:955)
  11/112 [==>-------------------------]   9%
# etc etc etc
 110/112 [===========================>]  98% [notice] Processed 112 items (111 created, 0 updated, 1 failed, 0 ignored) - done with 'upgrade_d7_node_complete_policies'
In MigrateToolsCommands.php line 866:
  upgrade_d7_node_complete_policies Migration - 1 failed.

# and...
$ terminus drush crs-policy-office.poli-migr -- mmsg upgrade_d7_node_complete_policies
 [warning] This environment is in read-only Git mode. If you want to make changes to the codebase of this site (e.g. updating modules or plugins), you will need to toggle into read/write SFTP mode first.
 --------------- ------------------- ------- ----------------------------------
  Source ID(s)    Destination ID(s)   Level   Message
 --------------- ------------------- ------- ----------------------------------
  556, 571, und   , ,                 1       Invalid translation language
                                              (und) specified.
                                              (/code/web/core/lib/Drupal/Core/
                                              Entity/ContentEntityBase.php:955
                                              )
 --------------- ------------------- ------- ----------------------------------
```

(Previously, when trying to run without --force)
```bash
$ terminus drush crs-policy-office.poli-migr -- mim upgrade_d7_node_complete_policies
 [warning] This environment is in read-only Git mode. If you want to make changes to the codebase of this site (e.g. updating modules or plugins), you will need to toggle into read/write SFTP mode first.
 [error]  Migration upgrade_d7_node_complete_policies did not meet the requirements. Missing migrations upgrade_d7_policy_document_media. requirements: upgrade_d7_policy_document_media.

In MigrateToolsCommands.php line 866:

  upgrade_d7_node_complete_policies migration failed.
```

----
## actual deployment
Assumed: Run `cr` a whole lot, because, obviously.
1. create backup of test env
1. export DB creds from DFA live env, create "secrets" thing with these creds on CRS Policy Office test env
```
TO DO
```
1. deploy code from poli-migr (to dev then) to test
1. (if applicable) manually update config ignore settings, b/c sometimes it's annoying if you have config_ignore config updates and you run cim
1. do "partial import" of "normal" config:
```
TO DO
```
1. do "partial import" of migration module config:
```
TO DO
```
1. (def run `cr` here)
1. (optional) check migrate status, see if the counts look like what I expect:
```
terminus drush crs-policy-office.test -- ms
```
1. run migrations! -- see "order of operations" section above
1. update static "policy library" page and add menu item for real/new policy library view (and remove the redirect after creating the new alias for the static page)
1. (manually add the one policy that's being a real d-bag at me, node 571)
1. add two "applicability" taxonomy terms, just to seed/help demonstrate the purpose
1. look at the library (and "minors" policy page/file), just, basic gut-check review

----
## Later
1. uninstall migrate* modules:
  ```
  drush @pantheon.crs-policy-office.test pmu migrate
  ```
  * (it'll prompt me to uninstall all other migrate modules, including this custom module)
2. codify the uninstallation
3. composer remove migration contrib modules (and push):
  ```
  composer remove drupal/migrate_*
  ```
4. remove migration-specific settings.php file (and push):
  ```
  git rm web/sites/default/settings.migrate-on-pantheon.php
  ```
5. remove secrets from server -- I'm not going to put the command here, because IRL I would check before deleting, but that's up to you -- here's the delete command info:
  ```
  terminus help secrets:delete crs-policy-office.test
  ```
