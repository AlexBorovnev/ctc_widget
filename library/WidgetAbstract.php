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
            $commonData = unserialize($offer['common_data']);
            $widgetsContent[] = array(
                'picture' => $offer['picture'],
                'picture_our_src' => $pictureSrc,
                'price' => array(
                    'totalPrice' => $offer['price'],
                    'viewPrice' => $this->getPrice($offer['price'])
                ),
                'title' => $offer['title'],
                'url' => $offer['url'],
                'id' => $offer['offer_id'],
                'common_data' => $commonData
            );
        }
        return $widgetsContent;
    }

    protected function getPrice($value)
    {
        $viewPrice = explode('.', $value);
        return array(
            'intValue' => !empty($viewPrice[0]) ? $viewPrice[0] : '0',
            'floatValue' => !empty($viewPrice[1]) ? $viewPrice[1] : '00'
        );
    }

    protected function getValueFromParam($param)
    {
        if (is_array($param)){
            return array_pop($param);
        } else {
            return $param;
        }
    }
}