<?php

ini_set('display_errors', 1);
ini_set('display_startup_errors', 1);
error_reporting(E_ALL);


/** Initiate process when user submits form */
if ($_SERVER["REQUEST_METHOD"] == "POST" && $_POST['username']) {
	$folderName = $_POST['username'];
	$fileName = 'readme.txt';

	$response = scanUserFile($fileName, $folderName);
	if ($response) {
		echo 'You passed the test';
	} else {
		echo 'You failed the test';
	}
}


/**
 * Get the folder name provided by the user to get their 'readme' file and then
 * scan through the file to ensure all requirements were met
 *
 * @param  string  $fileName
 * @param  string  $folderName
 * @return bool
 */
function scanUserFile($fileName, $folderName) {
	if (isFileInDirectory($fileName, $folderName)) {
		$content = getFileContent($fileName, $folderName);

		if (! hasKeyword($content, 'Full Name:')) return false;

		if (! hasKeyword($content, 'https://res.cloudinary.com')) return false;

		if (! hasKeyword($content, 'Bio:')) return false;

		if (! isFullNameValid($content)) return false;

		if (! isBioValid($content)) return false;

		/* Promote user or fire any event that should happen on success */
		return true;

	} else {
		return false;
	}
}

/**
 * Check if a file exists in a folder name that was provided by the user
 *
 * @param  string  $fileName
 * @param  string  $folderName
 * @return bool
 */
function isFileInDirectory($fileName, $folderName)
{
	$directory = __DIR__.'/../dir';
	$path = $directory . '/' . $folderName;

	if (is_dir($path)) {
		$contents = scandir($path);

		return in_array($fileName, $contents) ? true : false;
	}

	return false;
}

/**
 * Get the content of the file
 *
 * @param  string  $fileName
 * @param  string  $folderName
 * @return string|null
 */
function getFileContent($fileName, $folderName)
{
	$directory = __DIR__.'/../dir';
	$path = $directory . '/' . $folderName . '/' . $fileName;

	if (file_exists($path) && is_readable($path)) {
		return file_get_contents($path);
	}

	return null;
}

/**
 * Check if a certain keyword is provided by the user
 *
 * @param  string|null  $content
 * @param  string|null  $keyword
 * @return bool
 */
function hasKeyword($content = null, $keyword = null)
{
	if (! is_null($content) && ! is_null($keyword)) {
		$split = explode(strtolower($keyword), strtolower( $content));

		if (isset($split[1]) == ' ') return true;
	}

	return false;
}

/**
 * Check if full name was provided. It will return false if only first name
 * or only last name is provided
 *
 * @param  string  $content
 * @return bool
 */
function isFullNameValid($content)
{
	$split = explode('full name:', strtolower($content));
	$fullname = explode('bio:', trim($split[1]));
	$names = explode(' ', trim($fullname[0]));

	return isset($names[0]) && isset($names[1]) ? true : false;
}

/**
 * Checks if the bio content was provided and if it is not more than 150 characters
 *
 * @param  string  $content
 * @return bool
 */
function isBioValid($content)
{
	$split = explode('bio:', strtolower($content));
	$bio = explode('https://res.cloudinary.com', trim($split[1]));

	if (strlen(trim($bio[0])) < 1) return false;

	return (isset($bio[0]) && strlen(trim($bio[0])) <= 150) ? true : false;
}
