<?php
namespace library;

abstract class XmlParserAbstract
{
    const OFFER_TYPE = 'offer';
    /**
     * @var \XMLReader
     */
    protected $xmlReader;
    protected $fileName;

    public function __construct($fileName)
    {
        $this->xmlReader = new \XMLReader();
        $this->fileName = $fileName;
        $this->xmlReader->open($this->fileName);
    }

   abstract protected function parse();

    public function getValues()
    {
        return $this->parse();
    }

    public function __destruct()
    {
        $this->xmlReader->close();
    }
}