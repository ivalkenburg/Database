<?php

namespace IgorV\Database;

use \PDO;

class Connection {

    /**
     * @var PDO
     */
    protected $pdo;

    /**
     * @param $config
     */
    public function __construct($config)
    {
        $this->pdo = new PDO(...array_values($config));
    }

    /**
     * @param $table
     * @return QueryBuilder
     */
    public function table($table)
    {
        return new QueryBuilder($this, $table);
    }

    /**
     * @param        $query
     * @param array  $params
     * @param string $class
     * @return Collection
     */
    public function select($query, $params = [], $class = ResultSet::class)
    {
        return new Collection(
            $this->execute($query, $params)->fetchAll(PDO::FETCH_CLASS, $class)
        );
    }

    /**
     * @param       $query
     * @param array $params
     * @return \PDOStatement
     */
    public function execute($query, $params = [])
    {
        try {
            $stmt = $this->pdo->prepare($query);
            $stmt->execute($params);

            return $stmt;
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * @param        $query
     * @param array  $params
     * @param string $class
     * @return null
     */
    public function selectOne($query, $params = [], $class = ResultSet::class)
    {
        $results = $this->execute($query, $params)->fetchAll(PDO::FETCH_CLASS, $class);

        return (count($results)) ? $results[0] : null;
    }

    /**
     * @param $query
     * @param $params
     * @return int
     */
    public function insert($query, $params)
    {
        return $this->execute($query, $params)->rowCount();
    }

    /**
     * @param $query
     * @param $params
     * @return int
     */
    public function update($query, $params)
    {
        return $this->execute($query, $params)->rowCount();
    }

    /**
     * @param       $query
     * @param array $params
     * @return int
     */
    public function delete($query, $params = [])
    {
        return $this->execute($query, $params)->rowCount();
    }

    /**
     * @param callable $callback
     * @throws \Exception
     */
    public function transaction(callable $callback)
    {
        try {
            $this->beginTransaction();
            $callback();
            $this->commit();
        } catch (\Exception $e) {
            if ($this->pdo->inTransaction()) {
                $this->rollBack();
            }

            throw $e;
        }
    }

    /**
     * @return bool
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }

    /**
     * @return bool
     */
    public function commit()
    {
        return $this->pdo->commit();
    }

    /**
     * @return bool
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }

    /**
     * @return PDO
     */
    public function getPDO()
    {
        return $this->pdo;
    }

    public function __destruct()
    {
        $this->pdo = null;
    }

    /**
     * @param $method
     * @param $arguments
     * @return mixed
     */
    public function __call($method, $arguments)
    {
        return $this->pdo->$method(...$arguments);
    }
}
