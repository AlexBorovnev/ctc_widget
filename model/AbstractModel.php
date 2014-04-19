<?php
namespace model;

class AbstractModel
{
    protected $dbh;
    protected $fields = array();

    public function __construct(\PDO $dbh)
    {
        $this->dbh = $dbh;
    }

    protected function getQueryMark($data)
    {
        return str_repeat('?,', count($data) - 1) . '?';
    }

    protected function getRussianABC()
    {
    }
}