<?php

namespace IgorV\Database;

class QueryBuilder {

    /**
     * Instance of Connection object.
     *
     * @var Connection
     */
    protected $connection;

    /**
     * Table its building the query for.
     *
     * @var string
     */
    protected $table;

    /**
     * WHERE constraints.
     *
     * @var array
     */
    protected $where = [];

    /**
     * ORDER BY order.
     *
     * @var array
     */
    protected $order = [];

    /**
     * Class it injects rows into.
     *
     * @var string
     */
    protected $as = null;

    /**
     * Number of maximum returned rows.
     *
     * @var int
     */
    protected $limit;

    /**
     * Selected columns.
     *
     * @var array
     */
    protected $select = [];

    /**
     * OFFSET setting.
     *
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
     * Sets WHERE constraints.
     *
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
     * Execute as an INSERT query.
     *
     * @param array $data
     * @return int
     */
    public function insert($data = [])
    {
        return $this->connection->insert($this->buildInsert($data), array_values($data));
    }

    /**
     * Build INSERT query string.
     *
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
     * Execute query as an UPDATE query.
     *
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
     * Build UPDATE query string.
     *
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
     * Build WHERE query string.
     *
     * @return string
     */
    protected function buildWhere()
    {
        if (empty($this->where)) return '';

        $constraints = [];

        foreach ($this->where as $constraint) {
            $constraints[] = "{$constraint['field']} {$constraint['operator']} ?";
        }

        return ' WHERE ' . implode(' AND ', $constraints);
    }

    /**
     * Duplicate of sortBy()
     *
     * @param        $column
     * @param string $order
     * @return QueryBuilder
     */
    public function orderBy($column, $order = 'ASC')
    {
        return $this->sortBy($column, $order);
    }

    /**
     * Set the order for a query.
     *
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
     * Execute query as an SELECT query and return the first row.
     *
     * @param array ...$columns
     * @return mixed
     */
    public function first(...$columns)
    {
        $this->limit  = 1;
        $this->select = $columns;

        $query = $this->buildSelect() . $this->buildWhere() . $this->buildOrder() . $this->buildLimit();

        return $this->connection->selectOne(
            $query, array_column($this->where, 'value'), $this->as
        );
    }

    /**
     * Build SELECT query string.
     *
     * @return string
     */
    protected function buildSelect()
    {
        $columns = ! empty($this->select) ? implode(',', $this->select) : '*';

        return "SELECT {$columns} FROM {$this->table}";
    }

    /**
     * Build ORDER query string.
     *
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
     * Build LIMIT query string.
     *
     * @return string
     */
    protected function buildLimit()
    {
        if ( ! isset($this->limit)) return '';

        return " LIMIT {$this->limit}";
    }

    /**
     * Execute query as an SELECT query and return the results as a Collection object.
     *
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
     * Build OFFSET query string.
     *
     * @return string
     */
    protected function buildOffset()
    {
        if ( ! isset($this->offset)) return '';

        return " OFFSET {$this->offset}";
    }

    /**
     * Execute SELECT query and return the number of rows in the result.
     *
     * @param string $column
     * @return int
     */
    public function count($column = '*')
    {
        $this->select = ["COUNT($column)"];

        $query = $this->buildSelect() . $this->buildWhere() . $this->buildLimit() . $this->buildOffset();

        return (int) current(
            $this->connection->selectOne($query, array_column($this->where, 'value'))
        );
    }

    /**
     * Execute query as a DELETE query and return the number of rows affected.
     *
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
     * Build DELETE query string.
     *
     * @return string
     */
    protected function buildDelete()
    {
        return "DELETE FROM {$this->table}";
    }

    /**
     * Set the SELECT columns.
     *
     * @param array ...$select
     * @return $this
     */
    public function select(...$select)
    {
        $this->select = $select;

        return $this;
    }

    /**
     * Duplicate of limit().
     *
     * @param $amount
     * @return QueryBuilder
     */
    public function take($amount)
    {
        return $this->limit($amount);
    }

    /**
     * Set the maximum number of returned rows.
     *
     * @param $amount
     * @return $this
     */
    public function limit($amount)
    {
        $this->limit = (int) $amount;

        return $this;
    }

    /**
     * Set the row OFFSET clause.
     *
     * @param $offset
     * @return $this
     */
    public function offset($offset)
    {
        $this->offset = (int) $offset;

        return $this;
    }

    /**
     * Set the class to inject rows into. Must be instantiable.
     *
     * @param $class
     * @return $this
     */
    public function as ($class)
    {
        $this->as = $class;

        return $this;
    }
}