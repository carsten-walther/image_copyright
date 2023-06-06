<?php

/**
 * ext_emconf.php
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Image Copyright',
    'description' => 'Image based copyrights',
    'category' => 'misc',
    'version' => '12.4.4',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author' => 'Carsten Walther',
    'author_email' => 'walther.carsten@web.de',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.99-12.4.99'
        ]
    ],
    'autoload' => [
        'psr-4' => [
            'CarstenWalther\\ImageCopyright\\' => 'Classes',
        ],
    ],
];
