<?php
class shopWidget
{
    const BASE_PATH = '../base.xml';
    private $cache = null;

    public function __construct()
    {
        $this->cache = new Memcache();
        $this->cache->addServer('localhost', 11211);
    }

    private function getOffers()
    {
        if (file_exists(self::BASE_PATH)) {
            $config = simplexml_load_file(self::BASE_PATH);
            return $config->shop->offers;
        }
    }

    public function getWidget($widgetsId)
    {
        sort($widgetsId);
        $key = implode($widgetsId);
        if ($this->cache->get($key) === false) {
            $widgetContent = $this->getWidgetContent($widgetsId);
            if ($widgetContent){
                $this->cache->add($key, serialize($widgetContent));
            }
            return $widgetContent;
        } else {
            return unserialize($this->cache->get($key));
        }
    }

    public function deleteWidget($widgetId)
    {
        $this->cache->delete($widgetId);
    }

    private function getWidgetContent($widgetsId)
    {
        $widgetsContent = array();
        foreach ($this->getOffers()->offer as $offer) {
            if (in_array((string)$offer->attributes()->id, $widgetsId)) {
                $widgetsContent[] = array(
                    'picture' => (string)$offer->picture,
                    'price' => array('totalPrice' => (string)$offer->price,
                                      'viewPrice' => $this->getPrice((string)$offer->price)
                    ),
                    'url' => (string)$offer->url,
                    'id' => (string)$offer->attributes()->id
                );
            }
        }
        return $widgetsContent;
    }

    private function getPrice($value){
        list($intValue, $floatValue) = explode('.', $value);

        return array('intValue' => $intValue?:'0', 'floatValue' => $floatValue?:'00');
    }


}
$widget = new shopWidget();
if (!empty($_GET['widget_id'])) {
    $widgetsId = explode(',', strip_tags(trim($_GET['widget_id'])));
    require_once('widget.php');


} elseif (!empty($_GET['clear_widget_id'])) {
    $widget->deleteWidget(strip_tags(trim($_GET['clear_widget_id'])));
}

