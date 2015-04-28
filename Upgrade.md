# Upgrading from previous versions
Before starting to upgrade the following steps should be done:

* Stop the running processors (download, upload and error)
* Make sure no client connects to the dataserver (e.g. stopping apache)
* Backup the databases in case of error

## Changed dependencies and configuration
The new version uses the php memcached extension instead of the memcache extenstion:

* Remove php5-memcache
* Install php5-memcached

In the configuration file `include/config/config.inc.php` the varibale `$BASE_URL` must be set to "http://zotero.org/"

      public static $BASE_URI = 'http://zotero.org/';

## Database schema
The schema of the master and the shards database has changed since the last version.

The sql instructions for the upgrade are included in the dataserver sources in the directory `misc`. These must be applied using a mysql user, who has full access to the zotero databases.

    mysql -u root -p zotero_master < misc/upgrade_master.sql
    mysql -u root -p zotero_shards < misc/upgrade_shards.sql

If the `$BASE_URL` configuration wasn't set to "http://zotero.org/" then the relations table in the shards database requires additional updates.
if `$BASE_URL` was empty there are entries like:

    | relationID | libraryID | key                              | subject                | predicate   | object                 | serverDateModified  |
    |          1 |         1 | a209e4a3315f33727db7c9c32e9ac56e | users/1/items/ART4FCEC | dc:relation | users/1/items/VEDETMWA | 2015-02-26 17:33:18 |

All these entries must be changed to read:

    | relationID | libraryID | key                              | subject                                  | predicate   | object                                   | serverDateModified  |
    |          1 |         1 | a209e4a3315f33727db7c9c32e9ac56e | http://zotero.org/users/1/items/ART4FCEC | dc:relation | http://zotero.org/users/1/items/VEDETMWA | 2015-02-26 17:33:18 |

For the empty case this can be done with the following sql instructions:

    UPDATE `relations` SET `subject`=CONCAT("http://zotero.org/",`subject`) WHERE `subject` like "users%";
    UPDATE `relations` SET `subject`=CONCAT("http://zotero.org/",`subject`) WHERE `subject` like "groups%";
    UPDATE `relations` SET `object`=CONCAT("http://zotero.org/",`object`) WHERE `object` like "users%";
    UPDATE `relations` SET `object`=CONCAT("http://zotero.org/",`object`) WHERE `object` like "groups%";

After changing the values for `key` must be updated as well:

    UPDATE `relations` SET `key`=MD5(CONCAT(`subject`, ' ', `predicate`, ' ', `object`));

## ZSS
The latest version of [zss](http://git.27o.de/zss) is required.

## Memcache
Before starting the processor daemons and apache again, the memcache should be cleared by restarting the memcached daemon.
