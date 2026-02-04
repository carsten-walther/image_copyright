<?php

$EM_CONF[$_EXTKEY] = [
    'title' => 'Image Copyright',
    'description' => 'Add copyright information of all images to your site',
    'category' => 'misc',
    'version' => '13.4.8',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author' => 'Carsten Walther',
    'author_email' => 'walther.carsten@web.de',
    'constraints' => [
        'depends' => [
            'typo3' => '13.4.1-13.4.99'
        ]
    ],
    'autoload' => [
        'psr-4' => [
            'CarstenWalther\\ImageCopyright\\' => 'Classes',
        ],
    ],
];
