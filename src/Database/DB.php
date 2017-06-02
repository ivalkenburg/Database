<?php

namespace IgorV\Database;

class DB {

    /**
     * Instance of Connection object.
     *
     * @var Connection
     */
    protected static $instance;

    /**
     * Database configuration.
     *
     * @var array
     */
    protected static $config;

    /**
     * Tunnel any non-existent static calls as object call on Connection.
     *
     * @param $method
     * @param $arguments
     * @throws \PDOException
     * @return mixed
     */
    public static function __callStatic($method, $arguments)
    {
        try {
            return static::getInstance()->$method(...$arguments);
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
        if ( ! is_array($config)) {
            throw new \InvalidArgumentException('Config must an array');
        }

        static::$config = $config;
    }

    /**
     * Return singleton instance of Connection.
     *
     * @return Connection
     * @throws \RuntimeException
     */
    public static function getInstance()
    {
        if ( ! isset(static::$instance)) {

            if ( ! isset(static::$config)) {
                throw new \RuntimeException('No configuration details are set');
            }

            static::$instance = new Connection(static::$config);
        }

        return static::$instance;
    }
}
