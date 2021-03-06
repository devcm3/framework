<?php

namespace Bete\Database\Connector;

use PDO;
use Bete\Support\Arr;

class Connector
{
    protected $config;

    protected $dsn;

    protected $username;

    protected $password;

    protected $options = [
        PDO::ATTR_CASE => PDO::CASE_NATURAL,
        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
        PDO::ATTR_ORACLE_NULLS => PDO::NULL_NATURAL,
        PDO::ATTR_STRINGIFY_FETCHES => false,
        PDO::ATTR_EMULATE_PREPARES => false,
    ];

    // abstract public function getDsn();

    // abstract public function connect();

    public function __construct(array $config)
    {
        $this->config = $config;

        $this->parseConfig($config);
    }

    protected function parseConfig(array $config)
    {
        $this->dsn = $this->getDsn();

        if (isset($config['username'])) {
            $this->username = $config['username'];
        }
        
        if (isset($config['password'])) {
            $this->password = $config['password'];
        }
        
        if (isset($config['options'])) {
            $options = Arr::get($config, 'options', []);

            $this->options = array_diff_key($this->options, $options) + $options;
        }
    }

    public function createConnection()
    {
        try {
            $pdo = $this->createPdoConnection();
        } catch (Exception $e) {
            $pdo = $this->tryAgainIfLostConnection($e);
        }

        return $pdo;
    }

    public function tryAgainIfLostConnection(Exception $e)
    {
        if ($this->isLostConnection($e)) {
            return $this->createPdoConnection();
        }

        throw $e;
    }

    protected function isLostConnection(Exception $e)
    {
        $message = $e->getMessage();

        return Str::contains($message, [
            'server has gone away',
            'no connection to the server',
            'Lost connection',
            'is dead or not enabled',
            'Error while sending',
            'decryption failed or bad record mac',
            'server closed the connection unexpectedly',
            'SSL connection has been closed unexpectedly',
            'Error writing data to the connection',
            'Resource deadlock avoided',
        ]);
    }

    protected function createPdoConnection()
    {
        return new PDO($this->dsn, $this->username, $this->password, 
            $this->options);
    }
}
