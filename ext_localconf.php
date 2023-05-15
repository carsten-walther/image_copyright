<?php

defined('TYPO3') or die();

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'ImageCopyright',
    'ImageCopyright',
    [
        \CarstenWalther\ImageCopyright\Controller\ImageCopyrightController::class => 'index, indexOnPage, first, firstOnPage'
    ],
    [
        \CarstenWalther\ImageCopyright\Controller\ImageCopyrightController::class => 'index, indexOnPage, first, firstOnPage'
    ]
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig("@import 'EXT:image_copyright/Configuration/TSconfig/contentElementWizard.typoscript'");

$typo3Version = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Information\Typo3Version::class);
if ($typo3Version->getMajorVersion() < '11.4') {
    $templateIconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
    $templateIconRegistry->registerIcon(
        'tx-imagecopyright',
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        [
            'source' => 'EXT:image_copyright/Resources/Public/Images/Backend/image_copyright.svg'
        ]
    );
}
