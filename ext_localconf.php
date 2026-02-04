<?php

declare(strict_types=1);

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::configurePlugin(
    'ImageCopyright',
    'ImageCopyright',
    [
        \CarstenWalther\ImageCopyright\Controller\ImageCopyrightController::class => 'index, indexOnPage, first, firstOnPage'
    ],
    [
        \CarstenWalther\ImageCopyright\Controller\ImageCopyrightController::class => 'index, indexOnPage, first, firstOnPage'
    ],
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::PLUGIN_TYPE_CONTENT_ELEMENT
);
