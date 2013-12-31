<?php
namespace library;
use library\Config;


abstract class WidgetAbstract
{
    protected $config;
    protected $prodEnv = false;

    protected $convertFilter = array('categoryId' => 'category_id', 'color' => 'color');

    public function __construct(array $config)
    {
        $this->config = $config;
        $this->prodEnv = $this->config['env']['prod'];
    }

    abstract protected function getOffers($widgetId);

    public function getWidget($widgetsId)
    {
        return $this->getWidgetContent($widgetsId);
    }

    protected  function getWidgetContent($widgetsId)
    {
        $widgetsContent = array();
        foreach ($this->getOffers($widgetsId) as $offer) {
            $pictureSrc = $offer['offer_id'] . time();
            $widgetsContent[] = array(
                'picture' => $offer['picture'],
                'picture_our_src' => $pictureSrc,
                'price' => array(
                    'totalPrice' => $offer['price'],
                    'viewPrice' => $this->getPrice($offer['price'])
                ),
                'url' => $offer['url'],
                'id' => $offer['offer_id'],
                'common_data' => unserialize($offer['common_data'])
            );
        }
        return $widgetsContent;
    }

    protected function getPrice($value)
    {
        list($intValue, $floatValue) = explode('.', $value);
        return array('intValue' => $intValue ? : '0', 'floatValue' => $floatValue ? : '00');
    }
}