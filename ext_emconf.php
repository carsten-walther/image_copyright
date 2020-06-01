<?php

$EM_CONF[$_EXTKEY] = array(
    'title' => 'Image Copyright',
    'description' => 'Image based copyrights',
    'category' => 'plugin',
    'author' => 'Carsten Walther',
    'author_email' => 'walther.carsten@web.de',
    'state' => 'beta',
    'internal' => 0,
    'uploadfolder' => 0,
    'createDirs' => 0,
    'clearCacheOnLoad' => 0,
    'version' => '9.5.0',
    'constraints' => array(
        'depends' => array(
            'typo3' => '9.5',
            'filemetadata' => '9.5'
        ),
        'conflicts' => array(
        ),
        'suggests' => array(
        ),
    ),
    'autoload' => [
        'classmap' => [
            'Classes'
        ],
        'psr-4' => [
            'Fnn\\ImageCopyright\\' => 'Classes'
        ]
    ],
);
