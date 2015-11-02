<?php
date_default_timezone_set('Europe/Warsaw');
require __DIR__ . '/vendor/autoload.php';

// psr autoloader doesn't work yet:
require __DIR__ . '/vendor/redgda/fetcher/FetcherInterface.php';
require __DIR__ . '/vendor/redgda/fetcher/SimpleFetcher.php';
require __DIR__ . '/vendor/redgda/fetcher/CurlFetcher.php';
require __DIR__ . '/vendor/redgda/fetcher/CacheCurlFetcher.php';

require_once 'AttScraper.php';
require_once 'config.php';

$subscribbers = ['mail1@domain.com', 'mail2@domain.com'];

$url = 'http://www.attrader.pl/ajax/rekomendacje/pl/akcje/23/0/today/0/observed_/0/125';
$fetcher = new lib\Fetcher\CacheCurlFetcher(__DIR__.'/data');
$fetcher->simulate_ff_browser();
$html = $fetcher->load($url);

$scrapper = new AttScraper($html);
$list = $scrapper->get_recommendations();


foreach ($list as $i)
{
    $potential = floatval((str_replace(',', '.', $i['potential'])));

    if ($i['publication_date'] == date("Y-m-d")
        && $potential > POTENTIAL_THRESHOLD
    )
    {
        $to_send[] = $i;
    }
}

if ($to_send)
{
    $content = '';
    $subject_names = '';

    foreach ($to_send as $i)
    {
        $content .= implode($i, ' ') . "\n";
        $subject_names .= "{$i['name']} ";
    }

    echo "found:" . count($to_send) . "\n";
    echo $subject_names . "\n";

    $mailer = new PHPMailer;
    $mailer->IsSMTP();
    $mailer->SMTPAuth   = true;
    $mailer->SMTPSecure = 'ssl';
    $mailer->Host       = MAIL_HOST;
    $mailer->Port       = MAIL_PORT;
    $mailer->Username   = MAIL_USER;
    $mailer->Password   = MAIL_PASS;
    $mailer->SetFrom(MAIL_USER, 'redmailer');
    foreach ($subscribbers as $email) 
    {
        $mailer->AddAddress($email);
    }
    $mailer->Subject = "Nowe rekomendacje z ATTrader.pl: $subject_names";
    $mailer->Body = $content;
    $sent = $mailer->Send();

    if ($sent)
    {
        echo 'sent to: '. implode($subscribbers, ';');
        echo "\n";
    }
}
