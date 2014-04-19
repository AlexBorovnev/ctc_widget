<?php
namespace library;

class OzonParser extends XmlParserAbstract
{

    private $itemTag = array('offer');
    private $parseParam = array('currency', 'category', 'offer');

    protected function parse()
    {
        $item = array();
        $offer = array();

        while ($this->xmlReader->read()) {
            if (in_array($this->xmlReader->localName, $this->parseParam) && $this->xmlReader->nodeType == \XMLReader::ELEMENT) {
                $name = strtolower($this->xmlReader->name);
                while ($this->xmlReader->moveToNextAttribute()) {
                    $item[$name]['attributes'][$this->xmlReader->localName] = $this->xmlReader->value;
                }
                if (in_array($name, $this->itemTag)){
                    $this->xmlReader->read();
                    while ($this->xmlReader->read()){
                        if ($this->xmlReader->nodeType == \XMLReader::END_ELEMENT || $this->xmlReader->nodeType == \XMLReader::ELEMENT){
                            $tagName = $this->xmlReader->localName;
                            if (in_array($tagName, $this->itemTag) && $this->xmlReader->nodeType == \XMLReader::END_ELEMENT){
                                return $item;
                            }
                            if ($tagName == 'orderingTime'){
                                $this->xmlReader->read();
                                while ($this->xmlReader->read()){
                                    $orderingName = $this->xmlReader->localName;
                                    if ('ordering' == $orderingName && $this->xmlReader->nodeType == \XMLReader::ELEMENT){
                                        $this->xmlReader->read();
                                        $item[self::OFFER_TYPE][$orderingName][] = $this->xmlReader->value;
                                        $this->xmlReader->read();
                                        $this->xmlReader->read();
                                    } else {
                                        $this->xmlReader->read();
                                        $this->xmlReader->read();
                                        $tagName = $this->xmlReader->localName;
                                        break;
                                    }
                                }
                            }
                            if ($tagName == 'param'){
                                while ($this->xmlReader->moveToNextAttribute()) {
                                    if ($this->xmlReader->localName == 'name'){
                                        $item[self::OFFER_TYPE][$tagName][$this->xmlReader->localName] = $this->xmlReader->value;
                                        $paramName = $this->xmlReader->value;
                                    }
                                }
                                if (!empty($paramName)){
                                    $this->xmlReader->read();
                                    $item[self::OFFER_TYPE][$tagName][$paramName] = $this->xmlReader->value;
                                    $this->xmlReader->read();
                                    continue;
                                }
                            }
                            $this->xmlReader->read();
                            $item[self::OFFER_TYPE][$tagName][] = $this->xmlReader->value;
                            $this->xmlReader->read();
                        }
                    }
                } else {
                    $this->xmlReader->read();
                    $item[$name]['textValue'] = $this->xmlReader->value;
                }
                return $item;
            }
        }
        return $item;
    }
}