<?php
declare(strict_types=1);

namespace Fnn\ImageCopyright\Resource;

/**
 * Class FileRepository
 * @package Fnn\ImageCopyright\Resource
 */
class FileRepository extends \TYPO3\CMS\Core\Resource\FileRepository
{
    /**
     * @var array
     */
    protected $extensions = [];

    /**
     * @var bool
     */
    protected $showEmpty = FALSE;

    /**
     * @var bool
     */
    protected $includeFileCollections = FALSE;

    /**
     * @param array $tableFieldConfiguration
     * @param array $tableFieldConfigurationForCollections
     * @param array $extensions
     * @param bool  $showEmpty
     * @param int|null  $pid
     *
     * @return array
     * @throws \TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException
     */
    public function findAllByRelation (array $tableFieldConfiguration, array $tableFieldConfigurationForCollections, array $extensions, bool $showEmpty, ?int $pid = null) : array
    {

        $this->extensions = $extensions;
        $this->showEmpty = $showEmpty;
        $this->includeFileCollections = !empty($tableFieldConfigurationForCollections);
        $referenceUids = [];

        /** Get the table names for regular sys_file_references from the configuration */
        $tableNames = [];
        foreach ($tableFieldConfiguration as $configuration) {
            $tableNames[] = $configuration['tableName'];
        }

        /** Get all PIDs in a page tree if no pid is given explicitly */
        $pageTreePidArray = $pid !== null ? [$pid] : $this->getPageTreePidArray();

        if (!empty($GLOBALS['TSFE']->sys_page) && $this->getEnvironmentMode() === 'FE') {

            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');

            $queryBuilder
                ->getRestrictions()
                ->removeAll();

            $queryBuilder
                ->select('sys_file_reference.uid', 'sys_file_reference.uid_local')
                ->from('sys_file_reference');

            foreach ($tableNames as $tableName) {
                $queryBuilder->leftJoin (
                    'sys_file_reference',
                    $tableName,
                    $tableName,
                    $queryBuilder->expr()->eq('sys_file_reference.uid_foreign', $queryBuilder->quoteIdentifier($tableName . '.uid')) . ' AND ' . $queryBuilder->expr()->eq('sys_file_reference.tablenames', $queryBuilder->createNamedParameter($tableName, \PDO::PARAM_STR))
                );

                $queryBuilder->orWhere(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq($tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['delete'], 0),
                        $queryBuilder->expr()->eq($tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'], 0)
                    )
                );
            }

            $queryBuilder->andWhere(
                $queryBuilder->expr()->in('sys_file_reference.pid', $pageTreePidArray)
            );

            $queryBuilder->andWhere(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('sys_file_reference.deleted', 0),
                    $queryBuilder->expr()->eq('sys_file_reference.hidden', 0)
                )
            );

            $res = $queryBuilder->orderBy('sys_file_reference.sorting_foreign')->groupBy('sys_file_reference.uid_local')->execute();

            while ($row = $res->fetch()) {
                $referenceUids[] = ['uid' => $row['uid'], 'uid_local' => $row['uid_local']];
            }

            return $this->prepareList(array_merge($referenceUids, $this->getImagesFromFileCollections($tableFieldConfigurationForCollections, $pageTreePidArray)));

        } else {
            return [];
        }
    }

    /**
     * @param array $tableFieldConfigurationForCollections
     * @param array $pid
     * @return array
     */
    private function getImagesFromFileCollections (array $tableFieldConfigurationForCollections, array $pid) : array
    {
        if ($this->includeFileCollections === TRUE) {
            $referenceUids = [];

            /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
            $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');

            $queryBuilder
                ->getRestrictions()
                ->removeAll();

            $queryBuilder
                ->select('sys_file_reference.uid', 'sys_file_reference.uid_local')
                ->from('sys_file_reference');

            $queryBuilder->leftJoin (
                'sys_file_reference',
                'sys_file_collection',
                'sys_file_collection',
                $queryBuilder->expr()->eq('sys_file_reference.uid_foreign', $queryBuilder->quoteIdentifier('sys_file_collection.uid')) . ' AND ' . $queryBuilder->expr()->eq('sys_file_reference.tablenames', $queryBuilder->createNamedParameter('sys_file_collection', \PDO::PARAM_STR))
            );

            foreach ($tableFieldConfigurationForCollections as $configuration) {
                $queryBuilder->leftJoin (
                    'sys_file_collection',
                    $configuration['tableName'],
                    $configuration['tableName'],
                    $queryBuilder->expr()->inSet($configuration['tableName'].'.'.$configuration['fieldName'],$queryBuilder->quoteIdentifier('sys_file_collection.uid'))
                );

                $queryBuilder->orWhere(
                    $queryBuilder->expr()->andX(
                        $queryBuilder->expr()->eq($configuration['tableName'] . '.' . $GLOBALS['TCA'][$configuration['tableName']]['ctrl']['delete'], 0),
                        $queryBuilder->expr()->eq($configuration['tableName'] . '.' . $GLOBALS['TCA'][$configuration['tableName']]['ctrl']['enablecolumns']['disabled'], 0),
                        $queryBuilder->expr()->in($configuration['tableName'] . '.' . 'pid', $pid)
                    )
                );
            }

            $queryBuilder->andWhere(
                $queryBuilder->expr()->andX(
                    $queryBuilder->expr()->eq('sys_file_reference.deleted', 0),
                    $queryBuilder->expr()->eq('sys_file_reference.hidden', 0),
                    $queryBuilder->expr()->eq('sys_file_collection.deleted', 0),
                    $queryBuilder->expr()->eq('sys_file_collection.hidden', 0)
                )
            );

            $res = $queryBuilder->orderBy('sys_file_reference.sorting_foreign')->groupBy('sys_file_reference.uid_local')->execute();

            while ($row = $res->fetch()) {
                $referenceUids[] = ['uid' => $row['uid'], 'uid_local' => $row['uid_local']];
            }

            return $referenceUids;
        } else {
            return [];
        }
    }

    /**
     * @param $references
     *
     * @return array
     * @throws \TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException
     */
    private function prepareList ($references) : array
    {
        $itemList = [];

        if (!empty($references)) {
            $referencesUnique = [];

            foreach ($references as $reference) {
                $referencesUnique[$reference['uid_local']] = $reference['uid'];
            }

            $references = $referencesUnique;
            $references = array_flip($references);
            $referenceUids = array_keys($references);
        }

        if (!empty($referenceUids)) {
            foreach ($referenceUids as $referenceUid) {

                try {
                    $fileReferenceObject = $this->factory->getFileReferenceObject($referenceUid);
                    $fileExtension = $fileReferenceObject->getExtension();

                    if ($fileReferenceObject->isMissing() === FALSE && \in_array($fileExtension, $this->extensions, TRUE) && \file_exists($fileReferenceObject->getPublicUrl()) === TRUE) {
                        if ($this->showEmpty === TRUE || ($this->showEmpty === FALSE && !empty($fileReferenceObject->getProperty('copyright')))) {
                            $itemList[] = $fileReferenceObject->getOriginalFile();
                        }
                    }
                } catch(\TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException $exception) {}
            }
        }

        return $itemList;
    }

    /**
     * @param int $uid
     * @return int
     */
    private function getFileCollectionPid (int $uid) : int
    {
        /** @var \TYPO3\CMS\Core\Database\Query\QueryBuilder $queryBuilder */
        $queryBuilder = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class)->getQueryBuilderForTable('sys_file_reference');
        /** @var \TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer $frontendRestrictionContainer */
        $frontendRestrictionContainer = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer::class);

        $queryBuilder->setRestrictions($frontendRestrictionContainer);

        $result = $queryBuilder
            ->select('pid')
            ->from('sys_file_collection')
            ->where(
                $queryBuilder->expr()->eq('uid', $queryBuilder->createNamedParameter($uid, \PDO::PARAM_INT))
            )
            ->execute()->fetch();

        if ($result['pid']) {
            $returnPid = $result['pid'];
        } else {
            $returnPid = 0;
        }

        return $returnPid;
    }

    /**
     * Get all pids in current page tree
     *
     * @return array
     */
    private function getPageTreePidArray() : array
    {
        /** @var int $currentPageUid */
        $currentPageUid = $GLOBALS['TSFE']->id;

        /** @var \TYPO3\CMS\Core\Utility\RootlineUtility $rootlineUtility */
        $rootlineUtility = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Utility\RootlineUtility::class, $currentPageUid, '', false);

        $rootPageUid = 0;
        foreach ($rootlineUtility->get() as $rootlineItem) {
            if ($rootlineItem['is_siteroot'] === 1) {
                $rootPageUid = $rootlineItem['uid'];
                break;
            }
        }
        /** @var \TYPO3\CMS\Core\Database\QueryGenerator $queryGenerator */
        $queryGenerator = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\QueryGenerator::class);

        return explode(',', $queryGenerator->getTreeList($rootPageUid,256));
    }
}
