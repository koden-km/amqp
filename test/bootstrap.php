<?php

ob_start();
Recoil\Amqp\CodeGen\CodeGeneratorEngine::run();
ob_end_clean();
