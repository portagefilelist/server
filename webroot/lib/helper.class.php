<?php
/**
 * This program is free software: you can redistribute it and/or modify
 * it under the terms of the GNU General Public License as published by
 * the Free Software Foundation, either version 3 of the License, or
 * (at your option) any later version.
 *
 * This program is distributed in the hope that it will be useful,
 * but WITHOUT ANY WARRANTY; without even the implied warranty of
 * MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 * GNU General Public License for more details.
 *
 * You should have received a copy of the GNU General Public License
 * along with this program.  If not, see http://www.gnu.org/licenses/gpl-3.0.
 *
 * pre 2023 - https://github.com/tuxmainy
 * 2023 https://www.bananas-playground.net/projekt/portagefilelist/
 */

/**
 * a static helper class
 */
class Helper {

    private const BROWSER_AGENT_STRING = 'Mozilla/5.0 (X11; Linux x86_64; rv:109.0) Gecko/20100101 Firefox/119.0';

	/**
	 * validate the given string with the given type. Optional check the string
	 * length
	 *
	 * @param string $input The string to check
	 * @param string $mode How the string should be checked
	 * @param integer $max If int given the string is checked for length
	 *
	 * @return bool
	 * @see http://de.php.net/manual/en/regexp.reference.unicode.php
	 * http://www.sql-und-xml.de/unicode-database/#pc
	 *
	 * the pattern replaces all that is allowed. the correct result after
	 * the replace should be empty, otherwise are there chars which are not
	 * allowed
	 *
	 */
	static function validate(string $input, string $mode = 'text', int $max = 0): bool {
		// check if we have input
		$input = trim($input);

		if($input == "") return false;

		$ret = false;

		switch ($mode) {
			case 'mail':
				if(filter_var($input,FILTER_VALIDATE_EMAIL) === $input) {
					return true;
				}
				else {
					return false;
				}
			break;

			case 'url':
				if(filter_var($input,FILTER_VALIDATE_URL) === $input) {
					return true;
				}
				else {
					return false;
				}
			break;

			case 'nospace':
				// text without any whitespace and special chars
				$pattern = '/[\p{L}\p{N}]/u';
			break;

			case 'nospaceP':
				// text without any whitespace and special chars
				// but with Punctuation other
				// http://www.sql-und-xml.de/unicode-database/po.html
				$pattern = '/[\p{L}\p{N}\p{Po}\-_]/u';
			break;

			case 'digit':
				// only numbers and digit
				// warning with negative numbers...
				$pattern = '/[\p{N}\-]/u';
			break;

			case 'pageTitle':
				// text with whitespace and without special chars
				// but with Punctuation
				$pattern = '/[\p{L}\p{N}\p{Po}\p{Z}\s\-_]/u';
			break;

			# strange. the \p{M} is needed.. don't know why..
			case 'filename':
				$pattern = '/[\p{L}\p{N}\p{M}\-_\.\p{Zs}]/u';
			break;

			case 'text':
			default:
				$pattern = '/[\p{L}\p{N}\p{P}\p{S}\p{Z}\p{M}\s]/u';
		}

		$value = preg_replace($pattern, '', $input);

		if($value === "") {
			$ret = true;
		}

		if(!empty($max)) {
			# isset starts with 0
			if(isset($input[$max])) {
				# too long
				$ret = false;
			}
		}

		return $ret;
	}

	/**
	 * delete and/or empty a directory
	 *
	 * $empty = true => empty the directory but do not delete it
	 *
	 * @param string $directory
	 * @param bool $empty
	 * @param int $fTime If not false remove files older then this value in sec.
	 * @return bool
	 */
	static function recursive_remove_directory(string $directory, bool $empty = false, int $fTime = 0): bool {
		// if the path has a slash at the end we remove it here
		if(str_ends_with($directory, '/')) {
			$directory = substr($directory,0,-1);
		}

		// if the path is not valid or is not a directory ...
		if(!file_exists($directory) || !is_dir($directory)) {
			// ... we return false and exit the function
			return false;

		// ... if the path is not readable
		}elseif(!is_readable($directory)) {
			// ... we return false and exit the function
			return false;

		// ... else if the path is readable
		}
		else {
			// we open the directory
			$handle = opendir($directory);

			// and scan through the items inside
			while (false !== ($item = readdir($handle))) {
				// if the filepointer is not the current directory
				// or the parent directory
				if($item[0] != '.') {
					// we build the new path to delete
					$path = $directory.'/'.$item;

					// if the new path is a directory
					if(is_dir($path)) {
					   // we call this function with the new path
						self::recursive_remove_directory($path);
					// if the new path is a file
					}
					else {
						// we remove the file
						if($fTime !== false && is_int($fTime)) {
							// check filemtime
							$ft = filemtime($path);
							$offset = time()-$fTime;
							if($ft <= $offset) {
								unlink($path);
							}
						}
						else {
							unlink($path);
						}
					}
				}
			}
			// close the directory
			closedir($handle);

			// if the option to empty is not set to true
			if($empty == false) {
				// try to delete the now empty directory
				if(!rmdir($directory)) {
					// return false if not possible
					return false;
				}
			}
			// return success
			return true;
		}
	}

	/**
	 * http_build_query with modify array
	 * modify will add: key AND value not empty
	 * modify will remove: only key with no value
	 *
	 * @param array $array
	 * @param array $modify
	 * @return string
	 */
	static function createFromParameterLinkQuery(array $array, array $modify = array()): string {
		$ret = '';

		if(!empty($modify)) {
			foreach($modify as $k=>$v) {
				if(empty($v)) {
					unset($array[$k]);
				}
				else {
					$array[$k] = $v;
				}
			}
		}

		if(!empty($array)) {
			$ret = http_build_query($array);
		}

		return $ret;
	}

	/**
	 * Return given string with given $endChar with the max $length
	 *
	 * @param string $string
	 * @param int $length
	 * @param string $endChar
	 * @return string
	 */
	static function limitWithDots(string $string, int $length, string $endChar): string {
		$ret = $string;

		if(strlen($string.$endChar) > $length) {
			$ret = substr($string,0, $length).$endChar;
		}

		return $ret;
	}

	/**
	 * Size of the folder and the data within in bytes
	 *
	 * @param string $dir
	 * @return int
	 */
	static function folderSize(string $dir): int {
		$size = 0;

        foreach (glob(rtrim($dir, '/').'/{,.}*[!.]*', GLOB_MARK | GLOB_BRACE) as $each) {
			$size += is_file($each) ? filesize($each) : self::folderSize($each);
		}

		return $size;
	}

	/**
	 * Given bytes to human format with unit
	 *
	 * @param int $bytes
	 * @return string
	 */
	static function bytesToHuman(int $bytes): string {
		$units = ['B', 'KB', 'MB', 'GB', 'TB', 'PB'];
		for ($i = 0; $bytes > 1024; $i++) {
			$bytes /= 1024;
		}
		return round($bytes, 2) . ' ' . $units[$i];
	}

	/**
	 * Make the input more safe for logging
	 *
	 * @param mixed $input The string to be made more safe
	 * @return string
	 */
	static function cleanForLog(mixed $input): string {
		$input = var_export($input, true);
		$input = preg_replace( "/[\t\n\r]/", " ", $input);
		return addcslashes($input, "\000..\037\177..\377\\");
	}

	/**
	 * error_log with a dedicated destination
	 * Uses LOGFILE const
	 *
	 * @param string $msg The string to be written to the log
	 */
	static function sysLog(string $msg): void {
		error_log(date("c")." ".$msg."\n", 3, LOGFILE);
	}

    /**
     * execute a curl GET call to the given $url
     *
     * @param string $url The request url
     * @param int $port
     * @return array
     */
    static function curlCall(string $url, int $port=0): array {
        $ret = array('status' => false, 'message' => 'Unknown');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_USERAGENT,self::BROWSER_AGENT_STRING);

        // curl_setopt($ch, CURLOPT_VERBOSE, true);
        // curl_setopt($ch, CURLOPT_HEADER, true);

        if(!empty($port)) {
            curl_setopt($ch, CURLOPT_PORT, $port);
        }

        $do = curl_exec($ch);

        if(is_string($do) === true) {
            $ret['status'] = true;
            $ret['message'] = $do;
        }
        else {
            $ret['message'] = curl_error($ch);
        }

        curl_close($ch);

        return $ret;
    }

    /**
     * Download from given URL to given path
     *
     * @param string $url
     * @param string $whereToStore
     * @return bool
     */
    static function downloadFile(string $url, string $whereToStore): bool {
        $fh = fopen($whereToStore, 'w+');

        $ret = false;

        if($fh !== false) {
            $ch = curl_init($url);
            curl_setopt($ch, CURLOPT_FILE, $fh);

            curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
            curl_setopt($ch, CURLOPT_TIMEOUT, 30);
            curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
            curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
            curl_setopt($ch, CURLOPT_USERAGENT, self::BROWSER_AGENT_STRING);

            curl_exec($ch);
            curl_close($ch);

            $ret = true;
        }

        fclose($fh);

        return $ret;
    }

    /**
     * Execute a POST to given URL with data and optional headers
     *
     * return array('status' => boolean, 'message' => 'curl return')
     *
     * @param string $url
     * @param mixed $data
     * @param array $header
     * @return array
     */
    static function curlPOST(string $url, mixed $data, array $header = array('Content-Type:  multipart/form-data')): array {
        $ret = array('status' => false, 'message' => '');

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_POST, 1);
        curl_setopt($ch, CURLOPT_POSTFIELDS, $data);
        curl_setopt($ch, CURLOPT_HTTPHEADER, $header);

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_CONNECTTIMEOUT, 15);
        curl_setopt($ch, CURLOPT_TIMEOUT, 30);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, true);
        curl_setopt($ch, CURLOPT_MAXREDIRS, 2);
        curl_setopt($ch, CURLOPT_USERAGENT,self::BROWSER_AGENT_STRING);

        $do = curl_exec($ch);

        if(is_string($do) === true) {
            $ret['status'] = true;
            $ret['message'] = $do;
        }
        else {
            $ret['message'] = curl_error($ch);
        }

        curl_close($ch);

        return $ret;
    }

    /**
     * Send message to bot service
     *
     * @param string $message
     * @return void
     */
    static function notify(string $message): void {
        Helper::curlPOST(IMPORTER_BOT_ENDPOINT,
            json_encode(array("chat_id" => IMPORTER_BOT_CHATID, "text" => $message, "disable_notification" => false)),
            array('Content-Type:application/json', 'Accept: application/json')
        );
    }
}
