<?php

require_once(__DIR__ . '/shared/utils.php');

function getUserData()
{
	$url = 'https://randomuser.me/api';
	$userData = fetchFakeData($url);

	while (is_null($userData)) {
		$userData = fetchFakeData($url);
	}

	$uuid = $userData['results'][0]['login']['uuid'];
	$firstname = $userData['results'][0]['name']['first'];
	$lastname = $userData['results'][0]['name']['last'];
	$username = $userData['results'][0]['login']['username'];
	$password = $userData['results'][0]['login']['password'];

	return [
		$uuid,
		$username,
		$password,
		$firstname,
		$lastname
	];
}

function generateUsers($count)
{
	$users = [];
	for ($i = 0; $i < $count; $i++) {
		$users[$i] = getUserData();
	}
	return $users;
}
