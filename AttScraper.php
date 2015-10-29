<?php
/**
 * extract data from atttrader
 * @todo check https://github.com/FriendsOfPHP/Goutte
 */
class AttScraper
{
    public function __construct($html)
    {
        $this->html = $html;
        $this->dom = new DOMDocument();
        // silents markup warnings durign loading html
        libxml_use_internal_errors(true);
        $this->dom->loadHTML($html);
        $this->dx = new DOMXPath($this->dom);
    }

    public function get_recommendations()
    {
        $xpath = '//table[@id="allRecommendations"]/tbody/tr';
        $list = $this->dx->query($xpath);

        foreach ($list as $tr)
        {
            unset($item);
            $tds = $tr->getElementsByTagName('td');
            for ($i = 0; $i < $tds->length; $i++)
            {
                $item[$i] = trim($tds->item($i)->textContent);
            }
            $ret[] = $item;
        }

        return $ret;
    }
}
