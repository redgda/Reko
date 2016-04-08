<?php
require_once 'config.php';
require __DIR__ . '/vendor/autoload.php';

// psr autoloader doesn't work yet:
require __DIR__ . '/vendor/redgda/fetcher/FetcherInterface.php';
require __DIR__ . '/vendor/redgda/fetcher/SimpleFetcher.php';
require __DIR__ . '/vendor/redgda/fetcher/CurlFetcher.php';
require __DIR__ . '/vendor/redgda/fetcher/CacheCurlFetcher.php';

require_once 'AttScraper.php';
require_once 'Reko.php';

$url = 'http://www.attrader.pl/ajax/rekomendacje/pl/akcje/23/0/today/0/observed_/0/125';
//@todo often change in producition/dev - to config?
$fetcher = new lib\Fetcher\CacheCurlFetcher(__DIR__ . '/data');
$fetcher->simulate_ff_browser();
$html = $fetcher->load($url);

$scrapper = new AttScraper($html);
$list = $scrapper->get_recommendations();

$mailer = new PHPMailer;
$mailer->IsSMTP();
$mailer->SMTPAuth   = true;
$mailer->SMTPSecure = 'ssl';
$mailer->Host       = MAIL_HOST;
$mailer->Port       = MAIL_PORT;
$mailer->Username   = MAIL_USER;
$mailer->Password   = MAIL_PASS;
$mailer->SetFrom(MAIL_USER, 'redmailer');

$reko = new Reko($mailer);
$reko->load($list);
$reko->set_min_potential(POTENTIAL_THRESHOLD);
$sent_recomendations = $reko->notify(EMAIL_ADDRESSES);
echo "sent recomendations: $sent_recomendations\n";
