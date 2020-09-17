<?php

require_once(__DIR__ . '/shared/utils.php');

function getBookData()
{
  $url = 'https://randomuser.me/api';
  $bookData = fetchFakeData($url);

  while (is_null($bookData)) {
    $bookData = fetchFakeData($url);
  }

  $description = file_get_contents(FAKE_DESCRIPTION_URL);
  $imageUrl = FAKE_IMAGE_URL;
  $year = random_int(MIN_YEAR, MAX_YEAR);

  $uuid = $bookData['results'][0]['login']['uuid'];
  $firstname = $bookData['results'][0]['name']['first'];
  $lastname = $bookData['results'][0]['name']['last'];

  return [
    $uuid,
    sprintf('%s %s', $firstname, $lastname),
    $description,
    $year,
    $imageUrl,
  ];
}

function generateBooks($count)
{
  $books = [];
  for ($i = 0; $i < $count; $i++) {
    $books[$i] = getBookData();
  }
  return $books;
}
