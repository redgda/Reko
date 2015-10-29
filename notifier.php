<?php
require __DIR__ . '/vendor/autoload.php';
// psr autoloader doesn't work yet:
require __DIR__ . '/vendor/redgda/fetcher/FetcherInterface.php';
require __DIR__ . '/vendor/redgda/fetcher/SimpleFetcher.php';
require __DIR__ . '/vendor/redgda/fetcher/CurlFetcher.php';
require __DIR__ . '/vendor/redgda/fetcher/CacheCurlFetcher.php';
require 'AttScraper.php';

$url = 'http://www.attrader.pl/ajax/rekomendacje/pl/akcje/23/0/today/0/observed_/0/125';
$fetcher = new lib\Fetcher\CacheCurlFetcher(__DIR__.'/data');
$fetcher->simulate_ff_browser();
$html = $fetcher->load($url);

$scrapper = new AttScraper($html);
$list = $scrapper->get_recommendations();
echo count($list);
