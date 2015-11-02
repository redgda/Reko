<?php
/**
 * extract data from atttrader
 * @todo check https://github.com/FriendsOfPHP/Goutte
 */
class AttScraper
{
    private $map = [
        0 => 'publication_date',
        1 => 'category',
        3 => 'name',
        4 => 'author',
        5 => 'recommendation_date',
        6 => 'type',
        7 => 'target_price',
        8 => 'recommnedation_date_price',
        9 => 'potential',
        10 => 'previous_recommendation_date',
        11 => 'previous_type',
        12 => 'previous_recommendation_target_price',
        13 => 'previous_recommendation_date_price',
    ];

    public function __construct($html)
    {
        $this->html = $html;
        $this->dom = new DOMDocument();
        // silents markup warnings durign loading html
        libxml_use_internal_errors(true);
        $this->dom->loadHTML($html);
        $this->dx = new DOMXPath($this->dom);
    }

    public function get_data()
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

    public function get_recommendations()
    {
        $data = $this->get_data();
        foreach ($data as $row)
        {
            foreach ($row as $key=>$value)
            {
                if (isset($this->map[$key]) && $this->map[$key])
                {
                    $new[$this->map[$key]] = $value;
                }
            }

            $ret[] = $new;
        }

        return $ret;
    }
}
