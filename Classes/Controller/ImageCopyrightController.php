<?php
declare(strict_types=1);

namespace Fnn\ImageCopyright\Controller;

/**
 * Class ImageCopyrightController
 * @package Fnn\ImageCopyright\Controller
 */
class ImageCopyrightController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController
{
    /**
     * @var array
     */
    protected $cObjectData = [];

    /**
     * @var array
     */
    protected $tableFieldConfiguration = [];

    /**
     * @var bool
     */
    protected $showEmpty = TRUE;

    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @var bool
     */
    protected $includeFileCollections = FALSE;

    /**
     * @var array
     */
    protected $tableFieldConfigurationForCollections = [];

    /**
     * @var \Fnn\ImageCopyright\Resource\FileRepository
     */
    protected $fileRepository;

    /**
     * @param \Fnn\ImageCopyright\Resource\FileRepository $fileRepository
     */
    public function injectFileRepository (\Fnn\ImageCopyright\Resource\FileRepository $fileRepository) : void
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * @return void
     */
    public function initializeAction () : void
    {
        $this->cObjectData = $this->configurationManager->getContentObject()->data;

        // get table field configuration
        $tempTableFieldConfiguration = $this->settings['tableFieldConfiguration'];

        // check if extension is loaded
        foreach ($tempTableFieldConfiguration as $config) {
            if (!empty($config['extension']) && !empty($config['tableName'])
                && \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($config['extension'])) {
                $this->tableFieldConfiguration [] = $config;
            }
        }

        $this->extensions = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $this->settings['extensions'], TRUE);
        $this->showEmpty = (bool) $this->settings['showEmpty'];
        $this->includeFileCollections = (bool) $this->settings['includeFileCollections'];

        if ($this->includeFileCollections === true) {
            // get table field configuration for file collections
            $tempTableFieldConfigurationForCollections = $this->settings['tableFieldConfigurationForCollections'];
            // check if extension is loaded
            foreach ($tempTableFieldConfigurationForCollections as $config) {
                if (!empty($config['extension']) && !empty($config['tableName']) && !empty($config['fieldName']) &&
                    \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::isLoaded($config['extension'])) {
                    $this->tableFieldConfigurationForCollections [] = $config;
                }
            }
        }
    }

    /**
     * @throws \TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException
     */
    public function indexAction () : void
    {
        $this->view->assignMultiple([
            'images' => $this->fileRepository->findAllByRelation($this->tableFieldConfiguration, $this->tableFieldConfigurationForCollections, $this->extensions, $this->showEmpty, null)
        ]);
    }

    /**
     * @throws \TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException
     */
    public function indexOnPageAction () : void
    {
        $this->view->assignMultiple([
            'images' => $this->fileRepository->findAllByRelation($this->tableFieldConfiguration, $this->tableFieldConfigurationForCollections, $this->extensions, $this->showEmpty, $this->cObjectData['pid'])
        ]);
    }
}
