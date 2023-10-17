<?php
/**
 * Licensed to the Apache Software Foundation (ASF) under one
 * or more contributor license agreements.  See the NOTICE file
 * distributed with this work for additional information
 * regarding copyright ownership.  The ASF licenses this file
 * to you under the Apache License, Version 2.0 (the
 * "License"); you may not use this file except in compliance
 * with the License.  You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing,
 * software distributed under the License is distributed on an
 * "AS IS" BASIS, WITHOUT WARRANTIES OR CONDITIONS OF ANY
 * KIND, either express or implied.  See the License for the
 * specific language governing permissions and limitations
 * under the License.
 */

/**
 * a static helper class
 */
class Helper {

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
		if(substr($directory,-1) == '/') {
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
	 * check if a string starts with a given string
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	static function startsWith(string $haystack, string $needle): bool {
		$length = strlen($needle);
		return (substr($haystack, 0, $length) === $needle);
	}

	/**
	 * check if a string ends with a given string
	 *
	 * @param string $haystack
	 * @param string $needle
	 * @return bool
	 */
	static function endsWith(string $haystack, string $needle): bool {
		$length = strlen($needle);
		if ($length == 0) {
			return true;
		}

		return (substr($haystack, -$length) === $needle);
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

		//foreach (glob(rtrim($dir, '/').'/*', GLOB_NOSORT) as $each) {
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
	 * @param string $string The string to be made more safe
	 * @return string
	 */
	static function cleanForLog($input): string {
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
}
