# Zotero Client
To use a local dataserver the zotero client needs to be patched to use the local server instead of the offical server.

In the file `chrome/content/zotero/xpcom/zotero.js` the following changes are required:

    const ZOTERO_CONFIG = {
    	GUID: 'zotero@chnm.gmu.edu',
    	DB_REBUILD: false, // erase DB and recreate from schema
    	REPOSITORY_URL: 'https://repo.zotero.org/repo',
    	REPOSITORY_CHECK_INTERVAL: 86400, // 24 hours
    	REPOSITORY_RETRY_INTERVAL: 3600, // 1 hour
    	BASE_URI: 'http://zotero.org/',
    	WWW_BASE_URL: 'http://www.zotero.org/',
    +	SYNC_URL: 'https://host.domain.tld/sync/',
    +	API_URL: 'https://host.domain.tld/',
    	API_VERSION: 2,
    	PREF_BRANCH: 'extensions.zotero.',
    	BOOKMARKLET_URL: 'https://www.zotero.org/bookmarklet/',
    	VERSION: "4.0.x"
    };
