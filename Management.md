# Management
To create users or groups or add users to groups there a simple scripts available in the `admin` directory of the dataserver source code.

## Create user
You have to manually assign the user ids.

    add_user [user id] [username]

The hashed password needs to be set directly in the database `zotero_master:users`. For hashing SHA1 is used and the password is prepended with the salt set in `config.inc.php`.

    SHA1($salt.$password)

## Create group

    add_group --owner [username] [groupname]

## Add user to group

    add_groupuser [groupname] [username] [role]

Role can be `member` or `admin`.