<?php
namespace Recoil\Amqp\Protocol\Connection;

use Recoil\Amqp\Protocol\Frame;

final class StartFrame extends Frame
{
    public $versionMajor; // octet
    public $versionMinor; // octet
    public $serverProperties; // table
    public $mechanisms; // longstr
    public $locales; // longstr
}
