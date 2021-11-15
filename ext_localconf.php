<?php

defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin (
    'Walther.ImageCopyright',
    'ImageCopyright',
    [\Walther\ImageCopyright\Controller\ImageCopyrightController::class => 'index, indexOnPage, first, firstOnPage'],
    [\Walther\ImageCopyright\Controller\ImageCopyrightController::class => 'index, indexOnPage, first, firstOnPage']
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPageTSConfig("@import 'EXT:image_copyright/Configuration/TSconfig/contentElementWizard.typoscript'");

$templateIcons = ['tx-imagecopyright' => 'image_copyright.svg',];
$templateIconRegistry = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Imaging\IconRegistry::class);
foreach ($templateIcons as $identifier => $path) {
    $templateIconRegistry->registerIcon(
        $identifier,
        \TYPO3\CMS\Core\Imaging\IconProvider\SvgIconProvider::class,
        ['source' => 'EXT:image_copyright/Resources/Public/Images/Backend/' . $path]
    );
}
