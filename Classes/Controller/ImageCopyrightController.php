<?php

namespace CarstenWalther\ImageCopyright\Controller;

use CarstenWalther\ImageCopyright\Resource\FileRepository;
use Doctrine\DBAL\Driver\Exception;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;

/**
 * Class ImageCopyrightController
 *
 * @package CarstenWalther\ImageCopyright\Controller
 */
class ImageCopyrightController extends ActionController
{
    /**
     * @var array
     */
    protected array $cObjectData = [];

    /**
     * @var array
     */
    protected array $tableFieldConfiguration = [];

    /**
     * @var bool
     */
    protected bool $showEmpty = true;

    /**
     * @var array
     */
    protected array $extensions = [];

    /**
     * @var bool
     */
    protected bool $includeFileCollections = false;

    /**
     * @var array
     */
    protected array $tableFieldConfigurationForCollections = [];

    /**
     * @var FileRepository
     */
    protected FileRepository $fileRepository;

    /**
     * injectFileRepository
     *
     * @param FileRepository $fileRepository
     */
    public function injectFileRepository(FileRepository $fileRepository): void
    {
        $this->fileRepository = $fileRepository;
    }

    /**
     * initializeAction
     *
     * @return void
     */
    public function initializeAction(): void
    {
        $this->cObjectData = $this->configurationManager->getContentObject()->data;

        // get table field configuration
        $tempTableFieldConfiguration = $this->settings['tableFieldConfiguration'];

        // check if extension is loaded
        foreach ($tempTableFieldConfiguration as $config) {
            if (!empty($config['extension']) && !empty($config['tableName']) && ExtensionManagementUtility::isLoaded($config['extension'])) {
                $this->tableFieldConfiguration [] = $config;
            }
        }

        $this->extensions = GeneralUtility::trimExplode(',', $this->settings['extensions'], true);
        $this->showEmpty = (bool)$this->settings['showEmpty'];
        $this->includeFileCollections = (bool)$this->settings['includeFileCollections'];

        if ($this->includeFileCollections === true) {
            // get table field configuration for file collections
            $tempTableFieldConfigurationForCollections = $this->settings['tableFieldConfigurationForCollections'];
            // check if extension is loaded
            foreach ($tempTableFieldConfigurationForCollections as $config) {
                if (!empty($config['extension']) && !empty($config['tableName']) && !empty($config['fieldName']) && ExtensionManagementUtility::isLoaded($config['extension'])) {
                    $this->tableFieldConfigurationForCollections [] = $config;
                }
            }
        }

        parent::initializeAction();
    }

    /**
     * indexAction
     *
     * @return ResponseInterface
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception|AspectNotFoundException
     */
    public function indexAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'images' => $this->fileRepository->findAllByRelation(
                $this->tableFieldConfiguration,
                $this->tableFieldConfigurationForCollections,
                $this->extensions,
                $this->showEmpty
            )
        ]);

        return $this->htmlResponse();
    }

    /**
     * indexOnPageAction
     *
     * @return ResponseInterface
     * @throws Exception
     * @throws \Doctrine\DBAL\Exception|AspectNotFoundException
     */
    public function indexOnPageAction(): ResponseInterface
    {
        $this->view->assignMultiple([
            'images' => $this->fileRepository->findAllByRelation(
                $this->tableFieldConfiguration,
                $this->tableFieldConfigurationForCollections,
                $this->extensions,
                $this->showEmpty,
                $this->cObjectData['pid']
            )
        ]);

        return $this->htmlResponse();
    }

    /**
     * @param bool $showEmpty
     *
     * @return ImageCopyrightController
     */
    public function setShowEmpty(bool $showEmpty): ImageCopyrightController
    {
        $this->showEmpty = $showEmpty;
        return $this;
    }
}
