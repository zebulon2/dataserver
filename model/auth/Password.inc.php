<?
/*
    ***** BEGIN LICENSE BLOCK *****
    
    This file is part of the Zotero Data Server.
    
    Copyright © 2010 Center for History and New Media
                     George Mason University, Fairfax, Virginia, USA
                     http://zotero.org
    
    This program is free software: you can redistribute it and/or modify
    it under the terms of the GNU Affero General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.
    
    This program is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU Affero General Public License for more details.
    
    You should have received a copy of the GNU Affero General Public License
    along with this program.  If not, see <http://www.gnu.org/licenses/>.
    
    ***** END LICENSE BLOCK *****
*/

class Zotero_AuthenticationPlugin_Password implements Zotero_AuthenticationPlugin {
	public static function authenticate($data) {
		$salt = Z_CONFIG::$AUTH_SALT;
		
		$username = $data['username'];
		$password = $data['password'];
		
		$cacheKey = 'userAuthHash_' . hash('sha256', $username . $password);
		$userID = Z_Core::$MC->get($cacheKey);
		if ($userID) {
			return $userID;
		}
		
		// Username
		$sql = "SELECT userID, username, password AS hash FROM users WHERE username=?";
		$params = [$username];
		
		$rows = Zotero_DB::query($sql, $params);
		
		if (!$rows) {
			return false;
		}
		
		$found = false;
		foreach ($rows as $row) {
			// Try salted SHA1
			if (!$found) {
				$found = sha1($salt . $password) == $row['hash'];
			}
			
			if ($found) {
				$foundRow = $row;
				break;
			}
		}
		
		if (!$found) {
			return false;
		}
		
		Z_Core::$MC->set($cacheKey, $foundRow['userID'], 60);
		return $foundRow['userID'];
	}
}
?>
