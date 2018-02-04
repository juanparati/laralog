<?php
return array(
    'host'      =>
    [
        'description'   => 'host',
        'optional'      => false
    ],
    'index'     =>
    [
        'long_arg'      => 'index',
        'description'   => 'Log index',
        'optional'      => true,
        'value'         => 'laravel'
    ],
    'ignore'    =>
    [
        'long_arg'      => 'ignore',
        'descripiton'   => 'Ignore levels',
        'optional'      => true
    ],
    'verbose'   =>
    [
        'short_arg'     => 'v',
        'description'   => 'Verbose mode',
        'optional'      => true
    ],
    'async'     =>
    [
        'long_arg'      => 'async',
        'description'   => 'Asynchronous mode (Use future mode)',
        'optional'      => true
    ],
    'input'     =>
    [
        'long_arg'      => 'input',
        'short_arg'     => 'i',
        'description'   => 'Input file',
        'value'         => 'php://stdin',
        'optional'      => true
    ],
    'batch_size'        =>
    [
        'long_arg'      => 'batch-size',
        'description'   => 'Batch size',
        'optional'      => true,
        'value'         => 100
    ],
    'no_check_cert'     =>
    [
        'long_arg'      => 'no-check-cert',
        'description'   => 'Ignore SSL certificate check',
        'optional'      => true,
        'value'         => false
    ],
    'timezone'          =>
    [
        'long_arg'      => 'timezone',
        'description'   => 'Set time zone',
        'optional'      => true
    ],
    'retries'           =>
    [
        'long_arg'      => 'retries',
        'description'   => 'Retries on fail',
        'optional'      => true,
        'value'         => 2
    ],
    'read_freq'         =>
    [
        'long_arg'      => 'read-freq',
        'description'   => 'Read frequency in milliseconds',
        'optional'      => true,
        'value'         => 500
    ],
    'help'      =>
    [
        'long_arg'      => 'help',
        'short_arg'     => 'h',
        'description'   => 'Show help',
        'optional'      => true
    ]
);
