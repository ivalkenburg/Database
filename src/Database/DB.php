<?php

namespace IgorV\Database;

class DB {

    /**
     * @var Connection
     */
    protected static $instance;

    /**
     * @var array
     */
    protected static $config;

    /**
     * @param $method
     * @param $arguments
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
     * @return mixed
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
