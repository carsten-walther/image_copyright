<?php

namespace CarstenWalther\ImageCopyright\Controller;

use CarstenWalther\ImageCopyright\Resource\FileRepository;
use Doctrine\DBAL\Exception;
use Psr\Http\Message\ResponseInterface;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Pagination\ArrayPaginator;
use TYPO3\CMS\Core\Pagination\SlidingWindowPagination;
use TYPO3\CMS\Core\Utility\ExtensionManagementUtility;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Extbase\Mvc\Controller\ActionController;
use TYPO3\CMS\Frontend\ContentObject\ContentObjectRenderer;

class ImageCopyrightController extends ActionController
{
    protected ?ContentObjectRenderer $cObjectData = null;
    protected array $tableFieldConfiguration = [];
    protected bool $showEmpty = true;
    protected array $extensions = [];
    protected bool $includeFileCollections = false;
    protected array $tableFieldConfigurationForCollections = [];

    public function __construct(
        protected readonly FileRepository $fileRepository
    ) {}

    public function initializeAction(): void
    {
        $this->cObjectData = $this->request->getAttribute('currentContentObject');

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
     * @throws AspectNotFoundException
     * @throws Exception
     */
    public function indexAction(): ResponseInterface
    {
        switch ($this->settings['action']) {
            default:
            case 'onAllPages':
                $pid = null;
                break;
            case 'onThisPage':
                $pid = $this->cObjectData->data['pid'];
                break;
        }

        $itemsPerPage = $this->settings['itemsPerPage'] ?: 10;
        $maximumLinks = $this->settings['maximumLinks'] ?:  15;
        $currentPage = $this->request->hasArgument('currentPageNumber') ? (int)$this->request->getArgument('currentPageNumber') : 1;

        $images = $this->fileRepository->findAllByRelation(
            $this->tableFieldConfiguration,
            $this->tableFieldConfigurationForCollections,
            $this->extensions,
            $this->showEmpty,
            $this->settings,
            $pid
        );

        $paginator = new ArrayPaginator($images, $currentPage, $itemsPerPage);
        $pagination = new SlidingWindowPagination($paginator, $maximumLinks);

        $this->view->assignMultiple([
            'pagination' => $pagination,
            'paginator' => $paginator,
            'images' => $images
        ]);

        return $this->htmlResponse();
    }
}
