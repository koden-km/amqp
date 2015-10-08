<?php
namespace Recoil\Amqp\Transport\Connection;

final class StartMethod
{
    public $versionMajor; // octet
    public $versionMinor; // octet
    public $serverProperties; // table
    public $mechanisms; // longstr
    public $locales; // longstr
}
