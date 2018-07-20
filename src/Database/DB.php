<?php

namespace IgorV\Database;

class DB
{
    /**
     * Instance of Connection object.
     *
     * @var Connection
     */
    protected static $connection;

    /**
     * Database configuration.
     *
     * @var array
     */
    protected static $config;

    /**
     * Tunnel any non-existent static calls as object calls on the Connection object.
     *
     * @param $method
     * @param $arguments
     *
     * @throws \PDOException
     *
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        try {
            return static::getConnection()->$method(...$arguments);
        } catch (\PDOException $e) {
            die($e->getMessage());
        }
    }

    /**
     * Set database configuration.
     *
     * @param $config
     */
    public static function config($config)
    {
        if (!is_array($config)) {
            throw new \InvalidArgumentException('Argument must be an array');
        }

        static::$config = $config;
    }

    /**
     * Return singleton instance of Connection.
     *
     * @throws \RuntimeException
     *
     * @return Connection
     */
    public static function getConnection()
    {
        if (!isset(static::$connection)) {
            if (!isset(static::$config)) {
                throw new \RuntimeException('No database configurations are set');
            }

            static::$connection = new Connection(static::$config);
        }

        return static::$connection;
    }
}
