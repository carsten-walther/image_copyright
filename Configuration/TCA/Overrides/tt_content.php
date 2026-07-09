<?php

declare(strict_types=1);

defined('TYPO3') or die();

$ctypeKey = \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'ImageCopyright',
    'ImageCopyright',
    'LLL:EXT:image_copyright/Resources/Private/Language/locallang_be.xlf:general.title',
    'EXT:image_copyright/Resources/Public/Images/Backend/image_copyright.svg',
    'plugins',
    'LLL:EXT:image_copyright/Resources/Private/Language/locallang_be.xlf:general.description',
    'FILE:EXT:image_copyright/Configuration/FlexForms/ImageCopyright.xml',
);
