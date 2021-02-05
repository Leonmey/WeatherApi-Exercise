<?php
require_once('./weatherController.php');

$wc = new WeatherController();

//Here are some basic use cases. 
//These are not unit tests but just examples

echo "Should I wear a jacket today in Toronto, Canada? \n";
$wc->doINeedAJacket('toronto', '', 'CA');

echo "Should I wear a jacket today in Chicago, USA? \n";
$wc->doINeedAJacket('chicago', 'IL', 'US');

echo "Should I wear a jacket today in Mexico City, Mexico? \n";
$wc->doINeedAJacket('mexico city', '', 'MX');

echo "Should I wear a jacket today in Cairo, Egypt? \n";
$wc->doINeedAJacket('cairo', '', 'EG');

//should throw a 404 error when api cannot find the city
echo "Should I wear a jacket today in FakeCity? \n";
$wc->doINeedAJacket('not a city', '', '');
