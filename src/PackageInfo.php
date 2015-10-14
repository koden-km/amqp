<?php

namespace Recoil\Amqp;

class PackageInfo
{
    const NAME = 'Recoil AMQP';
    const VERSION = '0.0.0';

    const AMQP_PLATFORM = 'recoil-amqp/' . self::VERSION . '; php/' . PHP_VERSION;
    const AMQP_COPYRIGHT = '(c) 2015, James Harris, licensed under the MIT license.';
    const AMQP_INFORMATION = self::NAME . ' | http://recoil.io | http://github.com/recoilphp/amqp';
}
