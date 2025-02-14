<?php
/**
 * Created by Roquie.
 * E-mail: roquie0@gmail.com
 * GitHub: Roquie
 */

require __DIR__ . '/credentials.php';

//$log = new \Monolog\Logger('name');
//$formatter = new \Monolog\Formatter\LineFormatter(
//    null, // Format of message in log, default [%datetime%] %channel%.%level_name%: %message% %context% %extra%\n
//    null, // Datetime format
//    true, // allowInlineLineBreaks option, default false
//    true  // ignoreEmptyContextAndExtra option, default false
//);
//$debugHandler = new \Monolog\Handler\StreamHandler('out.log', \Monolog\Logger::DEBUG);
//$debugHandler->setFormatter($formatter);
//$log->pushHandler($debugHandler);


/** @var \Tmconsulting\Uniteller\Client $uniteller */

if (! $uniteller->getSignature()->verify('signature_from_post_params', ['all_parameters_from_post'])) {
    return 'invalid_signature';
}

// ok!
