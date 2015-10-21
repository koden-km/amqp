<?php

namespace Recoil\Amqp;

/**
 * AMQP connection options.
 */
final class ConnectionOptions
{
    /**
     * Create a connection options object.
     *
     * @param string  $host     The hostname or IP address of the AMQP server.
     * @param integer $port     The TCP port of the AMQP server.
     * @param string  $username The username to use to authentication.
     * @param string  $password The password to use for authentication.
     * @param string  $vhost    The virtual-host to use.
     *
     * @return ConnectionOptions
     */
    public static function create(
        $host = 'localhost',
        $port = 5672,
        $username = 'guest',
        $password = 'guest',
        $vhost = '/'
    ) {
        return new self(
            $host,
            $port,
            $username,
            $password,
            $vhost
        );
    }

    /**
     * Get the hostname or IP address of the AMQP server.
     *
     * @return string The hostname or IP address of the AMQP server.
     */
    public function host()
    {
        return $this->host;
    }

    /**
     * Get the TCP port of the AMQP server.
     *
     * @return integer The TCP port of the AMQP server.
     */
    public function port()
    {
        return $this->port;
    }

    /**
     * Get the username to use for authentication.
     *
     * @return string The username to use for authentication.
     */
    public function username()
    {
        return $this->username;
    }

    /**
     * Get the password to use for authentication.
     *
     * @return string The password to use for authentication.
     */
    public function password()
    {
        return $this->password;
    }

    /**
     * Get the virtual-host to use.
     *
     * @return string The virtual-host to use.
     */
    public function vhost()
    {
        return $this->vhost;
    }

    /**
     * Get the product name to report to the server.
     *
     * @return string The product name.
     */
    public function productName()
    {
        return $this->productName;
    }

    /**
     * Set the product name to report to the server.
     *
     * @param string $name The product name.
     *
     * @return ConnectionOptions
     */
    public function setProductName($name)
    {
        if ($name === $this->productName) {
            return $this;
        }

        $connectionOptions = clone $this;
        $connectionOptions->productName = $name;

        return $connectionOptions;
    }

    /**
     * Get the product version to report to the server.
     *
     * @return string The product version.
     */
    public function productVersion()
    {
        return $this->productVersion;
    }

    /**
     * Set the product version to report to the server.
     *
     * @param string $version The product version.
     *
     * @return ConnectionOptions
     */
    public function setProductVersion($version)
    {
        if ($version === $this->productVersion) {
            return $this;
        }

        $connectionOptions = clone $this;
        $connectionOptions->productVersion = $version;

        return $connectionOptions;
    }

    /**
     * Get the maximum time to allow for the connection to be established.
     *
     * @return integer|float|null The timeout, in seconds.
     */
    public function connectionTimeout()
    {
        return $this->connectionTimeout;
    }

    /**
     * Set the maximum time to allow for the connection to be established.
     *
     * @param integer|float|null $timeout The timeout, in seconds.
     *
     * @return ConnectionOptions
     */
    public function setConnectionTimeout($timeout)
    {
        if ($timeout === $this->connectionTimeout) {
            return $this;
        }

        $connectionOptions = clone $this;
        $connectionOptions->connectionTimeout = $timeout;

        return $connectionOptions;
    }

    /**
     * Get how often the server and client must send heartbeat packets to keep the connection alive.
     *
     * @return integer|float|null The timeout, in seconds.
     */
    public function heartbeatTimeout()
    {
        return $this->heartbeatTimeout;
    }

    /**
     * Set how often the server and client must send heartbeat packets to keep the connection alive.
     *
     * @param integer|float|null $timeout The timeout, in seconds.
     *
     * @return ConnectionOptions
     */
    public function setHeartbeatTimeout($timeout)
    {
        if ($timeout === $this->heartbeatTimeout) {
            return $this;
        }

        $connectionOptions = clone $this;
        $connectionOptions->heartbeatTimeout = $timeout;

        return $connectionOptions;
    }

    /**
     * @param string  $host     The hostname or IP address of the AMQP server.
     * @param integer $port     The TCP port of the AMQP server.
     * @param string  $username The username to use to authentication.
     * @param string  $password The password to use for authentication.
     * @param string  $vhost    The virtual-host to use.
     */
    private function __construct($host, $port, $username, $password, $vhost)
    {
        $this->host = $host;
        $this->port = $port;
        $this->username = $username;
        $this->password = $password;
        $this->vhost = $vhost;
        $this->productName = PackageInfo::NAME;
        $this->productVersion = PackageInfo::VERSION;
        $this->connectionTimeout = null;
        $this->heartbeatTimeout = null;
    }

    private $host;
    private $port;
    private $username;
    private $password;
    private $vhost;
    private $productName;
    private $productVersion;
    private $connectionTimeout;
    private $heartbeatTimeout;
}
