<?php
namespace library;
use library\Config;


abstract class WidgetAbstract
{
    const RULE_TYPE_SINGLE = 2;
    const RULE_TYPE_RULE = 1;

    const WIDGET_SKIN_STS = 1;
    const WIDGET_SKIN_HOME = 2;
    const WIDGET_SKIN_VIDEOMORE =3;

    const WIDGET_TYPE_SMALL = 1;
    const WIDGET_TYPE_BIG = 2;
    const WIDGET_TYPE_FREE = 3;

    const WIDGET_TYPE_SMALL_POSITIONS = 1;
    const WIDGET_TYPE_BIG_POSITIONS = 2;
    const WIDGET_MAX_POSITIONS = 7;

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
                'id' => $offer['offer_id']
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