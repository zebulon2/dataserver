# Modified zotero dataserver for local installations

This repository contains a modified version of the [zotero dataserver](https://github.com/zotero/dataserver) to make local installations easier and support group file synchronization using a local S3 compatible storage service.

Differences to the official dataserver:

* Replaced the Amazon AWS SDK with an alternative [Amazon S3 client](https://github.com/tpyo/amazon-s3-php-class)
* No fulltext content sync support (This would require an installation of Elasticsearch)
* Removed support for Notifications (uses Amazon SNS) 

## Installation
Instructions are availabe for:

* [Debian Wheezy](Installation-Instructions-for-Debian-Wheezy.md)

## Upgrade
[Upgrade instructions](Upgrade.md) from an older version of this modified dataserver (branch "master") to the current version (branch "2015.02")

## Client
To use zotero with a local dataserver the client must be [patched](Zotero-Client.md).

## Management
To add users and groups some [scripts](Management.md) are available in the `admin` directory of the dataserver source code.
