<?php

/**
 * ext_emconf.php
 */

$EM_CONF[$_EXTKEY] = [
    'title' => 'Image Copyright',
    'description' => 'Image based copyrights',
    'category' => 'misc',
    'version' => '11.5.0',
    'state' => 'stable',
    'uploadfolder' => 0,
    'createDirs' => '',
    'clearCacheOnLoad' => 0,
    'author' => 'Carsten Walther',
    'author_email' => 'walther.carsten@web.de',
    'constraints' => [
        'depends' => [
            'typo3' => '10.4.99-11.5.99',
        ],
    ]
];
