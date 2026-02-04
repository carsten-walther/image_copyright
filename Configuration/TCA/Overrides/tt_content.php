<?php

declare(strict_types=1);

$ctypeKey = \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'ImageCopyright',
    'ImageCopyright',
    'LLL:EXT:image_copyright/Resources/Private/Language/locallang_be.xlf:general.title',
    'EXT:image_copyright/Resources/Public/Images/Backend/image_copyright.svg',
    'plugins',
    'LLL:EXT:image_copyright/Resources/Private/Language/locallang_be.xlf:general.description',
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes(
    'tt_content',
    '--div--;Configuration,pi_flexform,',
    $ctypeKey,
    'after:subheader',
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue(
    '',
    'FILE:EXT:image_copyright/Configuration/FlexForms/imagecopyright.xml',
    $ctypeKey,
);
