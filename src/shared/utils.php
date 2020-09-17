<?php

function fetchFakeData($url)
{
  $curl = curl_init();
  curl_setopt($curl, CURLOPT_RETURNTRANSFER, true);
  curl_setopt($curl, CURLOPT_URL, $url);
  $json = json_decode(curl_exec($curl), true);
  curl_close($curl);
  return $json;
}
