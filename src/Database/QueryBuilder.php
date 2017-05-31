<?php

namespace IgorV\Database;

class QueryBuilder {

    /**
     * @var Connection
     */
    protected $connection;

    /**
     * @var string
     */
    protected $table;

    /**
     * @var array
     */
    protected $where = [];

    /**
     * @var array
     */
    protected $order = [];

    /**
     * @var string
     */
    protected $as;

    /**
     * @var int
     */
    protected $limit;

    /**
     * @var array
     */
    protected $select = [];

    /**
     * @var int
     */
    protected $offset;

    /**
     * @param Connection $connection
     * @param            $table
     */
    public function __construct(Connection $connection, $table)
    {
        $this->connection = $connection;
        $this->table      = $table;
    }

    /**
     * @param array ...$args
     * @return $this
     */
    public function where(...$args)
    {
        if (count($args) === 3) {
            $this->where[] = [
                'field'    => $args[0],
                'operator' => $args[1],
                'value'    => $args[2]
            ];
        } elseif (is_array($args[0])) {
            foreach ($args[0] as $condition) {
                $this->where(...$condition);
            }
        } else {
            $this->where[] = [
                'field'    => $args[0],
                'operator' => '=',
                'value'    => $args[1]
            ];
        }

        return $this;
    }

    /**
     * @param array $data
     * @return int
     */
    public function insert($data = [])
    {
        return $this->connection->insert($this->buildInsert($data), array_values($data));
    }

    /**
     * @param $data
     * @return string
     */
    protected function buildInsert($data)
    {
        $fields = implode(',', array_keys($data));
        $values = implode(',', array_fill(0, count($data), '?'));

        return "INSERT INTO {$this->table} ({$fields}) VALUES ({$values})";
    }

    /**
     * @param $data
     * @return int
     */
    public function update($data)
    {
        $query = $this->buildUpdate($data) . $this->buildWhere();

        return $this->connection->update(
            $query, array_merge(array_values($data), array_column($this->where, 'value'))
        );

    }

    /**
     * @param $data
     * @return string
     */
    protected function buildUpdate($data)
    {
        $updates = [];

        foreach (array_keys($data) as $field) {
            $updates[] = "{$field} = ?";
        }

        return "UPDATE {$this->table} SET " . implode(',', $updates);
    }

    /**
     * @return string
     */
    protected function buildWhere()
    {
        if (empty($this->where)) return '';

        $constraints = [];

        foreach ($this->where as $where) {
            $constraints[] = "{$where['field']} {$where['operator']} ?";
        }

        return ' WHERE ' . implode(' AND ', $constraints);
    }

    /**
     * @param        $column
     * @param string $order
     * @return $this
     */
    public function sortBy($column, $order = 'ASC')
    {
        $this->order[$column] = strtoupper($order);

        return $this;
    }

    /**
     * @return null
     */
    public function first()
    {
        $this->limit = 1;

        $query = $this->buildSelect() . $this->buildWhere() . $this->buildLimit();

        return $this->connection->selectOne(
            $query, array_column($this->where, 'value'), $this->as ?? ResultSet::class
        );
    }

    /**
     * @return string
     */
    protected function buildSelect()
    {
        $columns = ! empty($this->select) ? implode(',', $this->select) : '*';

        return "SELECT {$columns} FROM {$this->table}";
    }

    /**
     * @return string
     */
    protected function buildLimit()
    {
        if ( ! isset($this->limit)) return '';

        return " LIMIT {$this->limit}";
    }

    /**
     * @param array ...$columns
     * @return Collection
     */
    public function get(...$columns)
    {
        if ( ! empty($columns)) $this->select = $columns;

        $query = $this->buildSelect() . $this->buildWhere() . $this->buildOrder() . $this->buildLimit() . $this->buildOffset();

        return $this->connection->select(
            $query, array_column($this->where, 'value'), $this->as ?? ResultSet::class
        );
    }

    /**
     * @return string
     */
    protected function buildOrder()
    {
        if (empty($this->order)) return '';

        $orders = [];

        foreach ($this->order as $column => $order) {
            $orders[] = "{$column} {$order}";
        }

        return ' ORDER BY ' . implode(',', $orders);
    }

    /**
     * @return string
     */
    protected function buildOffset()
    {
        if ( ! isset($this->offset)) return '';

        return " OFFSET {$this->offset}";
    }

    /**
     * @return int
     */
    public function delete()
    {
        $query = $this->buildDelete() . $this->buildWhere();

        return $this->connection->delete(
            $query, array_column($this->where, 'value')
        );
    }

    /**
     * @return string
     */
    protected function buildDelete()
    {
        return "DELETE FROM {$this->table}";
    }

    /**
     * @param array ...$select
     * @return $this
     */
    public function select(...$select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * @param $amount
     * @return QueryBuilder
     */
    public function take($amount)
    {
        return $this->limit($amount);
    }

    /**
     * @param $amount
     * @return $this
     */
    public function limit($amount)
    {
        $this->limit = $amount;

        return $this;
    }

    /**
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = $offset;

        return $this;
    }

    /**
     * @param $class
     * @return $this
     */
    public function as ($class)
    {
        $this->as = $class;

        return $this;
    }
}