<?php

namespace App;

use \Exception;

class QueryBuilder 
{

    private $from;

    private $orderBy = [];

    private $limit;

    private $offset = null;

    private $where;

    private $params = [];

    private $pdo;

    private $select = ["*"];

    public function __construct(\PDO $pdo)
    {
        $this->pdo = $pdo;
    }

    public function from(string $table, string $alias = null):self
    {
        $this->from = $alias ? "$table $alias": "$table";
        return $this;
    }

    public function orderBY(string $key, string $order):self
    {
        $order = strtoupper($order);
        if (!in_array($order, ['ASC', 'DESC'])){
            $this->orderBy[] = $key;
        }else{
            $this->orderBy[] = "$key $order";
        }
        return $this;
    }

    public function limit(int $limit):self
    {
        $this->limit = $limit;

        return $this;
    }

    public function offset(int $offset):self
    {
        $this->offset = $offset;

        return $this;
    }

    public function page(int $page):self
    {
        $this->offset($this->limit * $page - $this->limit);

        return $this;
    }

    public function where(string $where):self
    {
        $this->where = $where;
        return $this;
    }

    public function setParam(string $key, $value):self
    {
        $this->params = array_merge($this->params, [$key => $value]);
        return $this;
    }

    public function select(...$keys):self
    {
        if (is_array($keys[0])){
            $keys = $keys[0];
        }
        if ( $this->select === ["*"]){
            $this->select = $keys;
        } else {
            $this->select = array_merge($this->select, $keys);
        }
        return $this;
    }

    public function fetch(string $city)
    {
        $query = $this->execute();
        return $query->fetch()[$city] ?? null;
    }

    public function execute()
    {
        $sql = $this->toSQL();
        $query = $this->pdo->prepare($sql);
        $query->execute($this->params);
        return $query;
    }

    public function fetchAll()
    {
        try {
            $query = $query = $this->execute();
            return $query->fetchAll();
        } catch (Exception $e){
            throw new Exception("Impossible d'exécuter la requête ".$this->toSQL()." : ".$e->getMessage());
        }
    }

    public function count()
    {
        return (int)(clone $this)->select("COUNT(id) count")->fetch('count');
    }

    public function toSQL():string
    {
        $fields = implode(', ', $this->select);
        $sql = "SELECT $fields FROM {$this->from}";
        if ($this->select){
            $keys = implode(", ", $this->select);
            $sql = str_replace("*", "{$keys}", $sql);
        }
        if ( $this->where ){
            $sql .= " WHERE ".$this->where;
        }
        if ( !empty($this->orderBy) ){
            $sql .= " ORDER BY ".implode(", ", $this->orderBy);
        }
        if ( $this->limit > 0 ){
            $sql .= " LIMIT {$this->limit}";
        }
        if ( !is_null($this->offset) ){
            if ($this->limit === null){
                throw new Exception("Impossible de définir un offset sans definir de limit");
            }
            $sql .= " OFFSET {$this->offset}";
        }
        $this->sql = $sql;
        return $sql;
    }
}