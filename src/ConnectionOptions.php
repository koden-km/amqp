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
        return PackageInfo::NAME; // TODO make property
    }

    /**
     * Get the product version to report to the server.
     *
     * @return string The product version.
     */
    public function productVersion()
    {
        return PackageInfo::VERSION; // TODO make property
    }

    /**
     * The maximum time to allow for the connection to be established.
     *
     * @return integer|float The timeout, in seconds.
     */
    public function timeout()
    {
        return 3; // TODO make property
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
    }

    private $host;
    private $port;
    private $username;
    private $password;
    private $vhost;
}
