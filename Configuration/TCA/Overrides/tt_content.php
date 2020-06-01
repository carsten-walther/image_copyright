<?php
defined('TYPO3_MODE') || die('Access denied.');

\TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerPlugin(
    'Walther.ImageCopyright',
    'ImageCopyright',
    'LLL:EXT:image_copyright/Resources/Private/Language/locallang_be.xlf:general.title',
    'EXT:image_copyright/Resources/Public/Images/Backend/image_copyright.svg'
);

$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_excludelist']['imagecopyright_imagecopyright'] = 'select_key,pages,recursive';
$GLOBALS['TCA']['tt_content']['types']['list']['subtypes_addlist']['imagecopyright_imagecopyright'] = 'pi_flexform';

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addPiFlexFormValue('imagecopyright_imagecopyright', 'FILE:EXT:image_copyright/Configuration/FlexForms/flexform_imagecopyright.xml');
