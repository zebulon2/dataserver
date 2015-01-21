# Installation Instructions for Debian Wheezy

## Packages to install

### dataserver
* apache2
* libapache2-mod-php5
* mysql-server
* memcached
* zendframework
* php5-cli
* php5-memcache
* php5-mysql
* php5-curl

### zss
* apache2
* uwsgi
* uwsgi-plugin-psgi
* libplack-perl
* libdigest-hmac-perl
* libjson-xs-perl
* libfile-util-perl
* libapache2-mod-uwsgi

### misc
* git
* gnutls-bin
* runit

## Directories
The following directories are used:
* `/srv/zotero/dataserver`: Zotero Dataserver
* `/srv/zotero/zss`: ZSS
* `/srv/zotero/storage`: Storage directory for all user and group files
* `/srv/zotero/log/{download,upload,error}`: Log files of processor daemons

## Dataserver
### Download source
    git clone git://github.com/sualk/dataserver.git /srv/zotero/dataserver

### Prepare directory rights
    chown www-data:www-data /srv/zotero/dataserver/tmp

### Zend
Use the packaged version

    cd /srv/zotero/dataserver/include
    rm -r Zend
    ln -s /usr/share/php/Zend/

### Apache2
* Generate SSL key and cert
* Enable SSL support: `a2enmod ssl`
* Enable rewrite support: `a2enmod rewrite`
* Create `/etc/apache2/sites-available/zotero`    
* Activate this site: `a2ensite zotero`
* Edit `/srv/zotero/dataserver/htdocs/.htaccess`

#### Selfsigned SSL Cert
    certtool -p --sec-param high --outfile /etc/apache2/zotero.key
    certtool -s --load-privkey /etc/apache2/zotero.key --outfile /etc/apache2/zotero.cert

#### zotero
    <VirtualHost *:443>
      DocumentRoot /srv/zotero/dataserver/htdocs
      SSLEngine on
      SSLCertificateFile /etc/apache2/zotero.cert
      SSLCertificateKeyFile /etc/apache2/zotero.key
    
      <Location /zotero/>
        SetHandler uwsgi-handler
        uWSGISocket /var/run/uwsgi/app/zss/socket
        uWSGImodifier1 5
      </Location>
    
      <Directory "/srv/zotero/dataserver/htdocs/">
        Options FollowSymLinks MultiViews
        AllowOverride All
        Order allow,deny
        Allow from all
        # If you are using a more recent version of apache 
        # and are getting 403 errors, replace the Order and
        # Allow lines with:
        # Require all granted
      </Directory>
    
      ErrorLog /srv/zotero/error.log
      CustomLog /srv/zotero/access.log common
    </VirtualHost>

#### .htaccess
Add the following line

     RewriteCond %{SCRIPT_FILENAME} !-f
     RewriteCond %{SCRIPT_FILENAME} !-d
    +RewriteCond %{REQUEST_URI} !^/zotero
     RewriteRule .* index.php [L]

### MySQL
#### Configuration
Create `/etc/mysql/conf.d/zotero.cnf`:

    [mysqld]
    character-set-server = utf8
    collation-server = utf8_general_ci
    event-scheduler = ON
    sql-mode = STRICT_ALL_TABLES
    default-time-zone = '+0:00'

#### Databases
* Restart mysql after adding the above configuration file
* Change the passwords in `/srv/zotero/dataserver/misc/setup_db`
* Run `setup_db`

### Configuration

In `/srv/zotero/dataserver/include/config` the following two files need to be created.

#### dbconnect.inc.php
Copy the sample file and insert the database, database username and password as used in the `setup_db` script.
The lines you should change are marked with a `+` at the beginning.

    <?
    function Zotero_dbConnectAuth($db) {
            if ($db == 'master') {
    +               $host = 'localhost';
                    $port = 3306;
                    $db = 'zotero_master';
    +               $user = 'zotero';
    +               $pass = 'foobar';
            }
            else if ($db == 'shard') {
                    $host = false;
                    $port = false;
                    $db = false;
    +               $user = 'zotero';
    +               $pass = 'foobar';
            }
            else if ($db == 'id1') {
    +               $host = 'localhost';
                    $port = 3306;
                    $db = 'zotero_ids';
    +               $user = 'zotero';
    +               $pass = 'foobar';
            }
            else if ($db == 'id2') {
    +               $host = 'localhost';
                    $port = 3306;
                    $db = 'zotero_ids';
    +               $user = 'zotero';
    +               $pass = 'foobar';
            }
            else {
                    throw new Exception("Invalid db '$db'");
            }
            return array('host'=>$host, 'port'=>$port, 'db'=>$db, 'user'=>$user, 'pass'=>$pass);
    }
    ?>

#### config.inc.php
Copy the sample file and adjust a few values. The lines you should change are marked with a `+` at the beginning.
`host.domain.tld` should be the same as in your SSL certificate. If you use a self signed certificate the SSL validation must be deactivated.
`$SYNC_DOMAIN` should just contain `sync`. It does not need to be a valid resolvable domain name.

    <?
    class Z_CONFIG {
      public static $API_ENABLED = true;
      public static $SYNC_ENABLED = true;
      public static $PROCESSORS_ENABLED = true;
      public static $MAINTENANCE_MESSAGE = 'Server updates in progress. Please try again in a few minutes.';
      
    + public static $TESTING_SITE = false;
    + public static $DEV_SITE = false;
      
      public static $DEBUG_LOG = false;
      
      public static $BASE_URI = '';
    + public static $API_BASE_URI = 'https://host.domain.tld[:port]/';
      public static $WWW_BASE_URI = '';
    + public static $SYNC_DOMAIN = 'sync';
      
      public static $AUTH_SALT = '';
    + public static $API_SUPER_USERNAME = 'someusername';
    + public static $API_SUPER_PASSWORD = 'somepassword';
      
      public static $AWS_ACCESS_KEY = '';
    + public static $AWS_SECRET_KEY = 'yoursecretkey';
    + public static $S3_BUCKET = 'zotero';
    + public static $S3_ENDPOINT = 'host.domain.tld[:port]';
    + public static $S3_USE_SSL = true;
    + public static $S3_VALIDATE_SSL = true;
      
    + public static $URI_PREFIX_DOMAIN_MAP = array(
    +   '/sync/' => 'sync'
    + );
      
      public static $MEMCACHED_ENABLED = true;
      public static $MEMCACHED_SERVERS = array(
    +   'localhost:11211'
      );
      
      public static $TRANSLATION_SERVERS = array(
        "translation1.localdomain:1969"
      );
      
      public static $CITATION_SERVERS = array(
        "citeserver1.localdomain:8080", "citeserver2.localdomain:8080"
      );
      
      public static $ATTACHMENT_SERVER_HOSTS = array("files1.localdomain", "files2.localdomain");
      public static $ATTACHMENT_SERVER_DYNAMIC_PORT = 80;
      public static $ATTACHMENT_SERVER_STATIC_PORT = 81;
      public static $ATTACHMENT_SERVER_URL = "https://files.example.net";
      public static $ATTACHMENT_SERVER_DOCROOT = "/var/www/attachments/";
      
      public static $STATSD_ENABLED = false;
      public static $STATSD_PREFIX = "";
      public static $STATSD_HOST = "monitor.localdomain";
      public static $STATSD_PORT = 8125;
      
      public static $LOG_TO_SCRIBE = false;
      public static $LOG_ADDRESS = '';
      public static $LOG_PORT = 1463;
      public static $LOG_TIMEZONE = 'US/Eastern';
      public static $LOG_TARGET_DEFAULT = 'errors';
      
      public static $PROCESSOR_PORT_DOWNLOAD = 3455;
      public static $PROCESSOR_PORT_UPLOAD = 3456;
      public static $PROCESSOR_PORT_ERROR = 3457;
      
      public static $PROCESSOR_LOG_TARGET_DOWNLOAD = 'sync-processor-download';
      public static $PROCESSOR_LOG_TARGET_UPLOAD = 'sync-processor-upload';
      public static $PROCESSOR_LOG_TARGET_ERROR = 'sync-processor-error';
      
      public static $SYNC_DOWNLOAD_SMALLEST_FIRST = false;
      public static $SYNC_UPLOAD_SMALLEST_FIRST = false;
      
      // Set some things manually for running via command line
      public static $CLI_PHP_PATH = '/usr/bin/php';
    + public static $CLI_DOCUMENT_ROOT = "/srv/zotero/dataserver/";
      
      public static $SYNC_ERROR_PATH = '/var/log/httpd/sync-errors/';
      public static $API_ERROR_PATH = '/var/log/httpd/api-errors/';
      
      public static $CACHE_VERSION_ATOM_ENTRY = 1;
      public static $CACHE_VERSION_BIB = 1;
      public static $CACHE_VERSION_ITEM_DATA = 1;
    }
    ?>

##### Using a different port (Optional)
An alternative port has to be specified as part of the `$API_BASE_URI`, the `$S3_ENDPOINT` and also in the `SYNC_URL` and `API_URL` of the [client patch](https://github.com/sualk/dataserver/wiki/Zotero-Client).

### Processor daemons
The upload, download and error processor daemons need to run for syncing with the zotero clients to work.

#### Using runit
Create the directories `/etc/sv/zotero-download`, `/etc/sv/zotero-upload` and `/etc/sv/zotero-error` with a subdirectory `log` in each one.
Create the following file named `run` in each of the `zotero-*` dirs:

    #!/bin/sh
    
    cd /srv/zotero/dataserver/processor/download
    exec 2>&1
    exec chpst -u www-data:www-data php5 daemon.php

And the following file named `run` in each of the `log` subdirs:

    #!/bin/sh
    
    exec svlogd /srv/zotero/log/download

Replace "download" with "upload" and "error" as apropriate.

To automatically start the daemons create symlinks in `/etc/service`

    ln -s ../sv/zotero-download /etc/service/
    ln -s ../sv/zotero-upload /etc/service/
    ln -s ../sv/zotero-error /etc/service/

## ZSS

### Download source
    git clone git://github.com/sualk/zss.git /srv/zotero/zss

### Prepare directory rights
    mkdir /srv/zotero/storage
    chown www-data:www-data /srv/zotero/storage

### Configure ZSS

#### zss.psgi
You need to adjust the path to ZSS.pm in zss.psgi. Change the line

    use lib ('/path/to/ZSS.pm');

to fit your installation. With zss installed to /srv/zotero/zss/ the line should look like this:

    use lib ('/srv/zotero/zss/');

#### ZSS.pm
Adjust the path to your storage directory and set the secretkey to something random.

    $self->{buckets}->{zotero}->{secretkey} = "yoursecretkey";
    $self->{buckets}->{zotero}->{store} = ZSS::Store->new("/srv/zotero/storage/");

### Configure uwsgi
1. In `/etc/uwsgi/apps-available` create the file `zss.yaml`
2. Create a link in `apps-enabled` to this file
3. Restart uwsgi

#### zss.yaml
    uwsgi:
      plugin: psgi
      psgi: /srv/zotero/zss/zss.psgi

## Test the installation

### Sync access
Access to `https://host.domain.tld/sync/login?version=9` should result in the following output

    <response version="9" timestamp="1398715040">
      <error code="NO_USER_NAME">Username not provided</error>
    </response>

### Storage access
Access to `https://host.domain.tld/zotero/` should result in the following output

    <Error><Code>SignatureDoesNotMatch</Code></Error>

### API access
Access to `https://host.domain.tld/users/1/items` should result in the following output

    Not found

Access to `https://host.domain.tld/itemTypes` should result in the following output

    [{"itemType":"artwork","localized":"Artwork"},{"itemType":"audioRecording","localized":"Audio Recording"},{"itemType":"bill","localized":"Bill"},
    [... a lot more of these itemType records ...]
    {"itemType":"videoRecording","localized":"Video Recording"},{"itemType":"webpage","localized":"Web Page"}]

## Differences when installing on Ubuntu Server 14.04 LTS
The above instructions also work to a large degree when installing the dataserver on an Ubuntu Server 14.04 LTS system.  Here are some issues that need to be taken care of though:

### Zend
The Zend package is called "zend-framework" (with a dash). The Zend hierarchy starts at /usr/share/php/libzend-framework-php/Zend, so the symlink needs to be adapted:

    cd /srv/zotero/dataserver/include
    rm -r Zend
    ln -s /usr/share/php/libzend-framework-php/Zend

### Apache
Ubuntu 14.04 LTS comes with apache 2.4, so the access control config needs to be adapted ("Order" and "Allow From" no longer work). See "Access control" in the [upgrading docs](http://httpd.apache.org/docs/2.4/upgrading.html). Adapt the VirtualHost configuration and the .htaccess file to suit your needs.  You may also try the backwards compatibility module mod_access_compat but the upgrade guide suggests updating the configuration instead.

short_open_tags is disabled by default but Zotero currently relies on it. Add

    php_value short_open_tag 1

to your VirtualHost configuration to enable it.

### ZSS
Switch.pm which is required by the zss cannot be found. It is available as a package though:

    apt-get install libswitch-perl

### runit
The runit services require manual creation of the log directories:

    mkdir -p /srv/zotero/log/{upload,download,error}

### Zend application log
Logging directories for sync and API need manual creation according to the values set in config.inc.php ($SYNC_ERROR_PATH and $API_ERROR_PATH), e.g. 

    mkdir -p /var/log/httpd/{sync-errors,api-errors}/
    chown www-data: /var/log/httpd/{sync-errors,api-errors}/


