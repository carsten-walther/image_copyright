<?php

declare(strict_types=1);

defined('TYPO3') or die();

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addStaticFile(
    'image_copyright',
    'Configuration/TypoScript',
    'Image Copyright'
);
