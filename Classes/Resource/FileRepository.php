<?php

namespace CarstenWalther\ImageCopyright\Resource;

use Doctrine\DBAL\Exception;
use TYPO3\CMS\Core\Context\Context;
use TYPO3\CMS\Core\Context\Exception\AspectNotFoundException;
use TYPO3\CMS\Core\Core\Environment;
use TYPO3\CMS\Core\Database\Connection;
use TYPO3\CMS\Core\Database\ConnectionPool;
use TYPO3\CMS\Core\Database\Query\QueryHelper;
use TYPO3\CMS\Core\Database\Query\Restriction\DeletedRestriction;
use TYPO3\CMS\Core\Database\Query\Restriction\FrontendRestrictionContainer;
use TYPO3\CMS\Core\Domain\Repository\PageRepository;
use TYPO3\CMS\Core\Http\ApplicationType;
use TYPO3\CMS\Core\Resource\Exception\ResourceDoesNotExistException;
use TYPO3\CMS\Core\Resource\FileReference;
use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\RootlineUtility;
use function file_exists;
use function in_array;

readonly class FileRepository extends \TYPO3\CMS\Core\Resource\FileRepository
{
    /**
     * @throws AspectNotFoundException
     * @throws Exception
     */
    public function findAllByRelation(
        array $tableFieldConfiguration,
        array $tableFieldConfigurationForCollections,
        array $extensions,
        bool $showEmpty,
        ?int $pid = null
    ): array {
        $includeFileCollections = !empty($tableFieldConfigurationForCollections);

        $referenceUids = [];

        // Get the table names for regular sys_file_references from the configuration
        $tableNames = [];
        foreach ($tableFieldConfiguration as $configuration) {
            $tableNames[] = $configuration['tableName'];
        }

        // Get all PIDs in a page tree if no pid is given explicitly
        $pageTreePidArray = $pid !== null ? [$pid] : $this->getPageTreePidArray();

        if (!empty($GLOBALS['TSFE']->sys_page) && ApplicationType::fromRequest($GLOBALS['TYPO3_REQUEST'])->isFrontend()) {

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_file_reference');

            $queryBuilder
                ->getRestrictions()
                ->removeAll();

            $queryBuilder
                ->select('sys_file_reference.uid', 'sys_file_reference.uid_local')
                ->from('sys_file_reference');

            foreach ($tableNames as $tableName) {
                $queryBuilder->leftJoin(
                    'sys_file_reference',
                    $tableName,
                    $tableName,
                    $queryBuilder
                        ->expr()
                        ->eq('sys_file_reference.uid_foreign', $queryBuilder
                            ->quoteIdentifier($tableName . '.uid')) . ' AND ' . $queryBuilder
                        ->expr()
                        ->eq('sys_file_reference.tablenames', $queryBuilder
                            ->createNamedParameter($tableName, Connection::PARAM_STR))
                );

                $queryBuilder
                    ->orWhere(
                        $queryBuilder
                            ->expr()
                            ->and(
                                $queryBuilder
                                    ->expr()
                                    ->eq($tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['delete'], 0),
                                $queryBuilder
                                    ->expr()
                                    ->eq($tableName . '.' . $GLOBALS['TCA'][$tableName]['ctrl']['enablecolumns']['disabled'], 0)
                            )
                    );
            }

            $queryBuilder
                ->andWhere(
                    $queryBuilder
                        ->expr()
                        ->in('sys_file_reference.pid', $pageTreePidArray)
                );

            $queryBuilder
                ->andWhere(
                    $queryBuilder
                        ->expr()
                        ->and(
                            $queryBuilder
                                ->expr()
                                ->eq('sys_file_reference.deleted', 0),
                            $queryBuilder
                                ->expr()
                                ->eq('sys_file_reference.hidden', 0),
                            $queryBuilder
                                ->expr()
                                ->eq('sys_file_reference.sys_language_uid', GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId())
                        )
                );

            $res = $queryBuilder
                ->orderBy('sys_file_reference.sorting_foreign')
                ->groupBy('sys_file_reference.uid_local')
                ->executeQuery();

            while ($row = $res->fetchAssociative()) {
                $referenceUids[] = [
                    'uid' => $row['uid'],
                    'uid_local' => $row['uid_local']
                ];
            }

            $result = $this->prepareList(
                array_merge($referenceUids, $this->getImagesFromFileCollections($tableFieldConfigurationForCollections, $pageTreePidArray, $includeFileCollections)),
                $extensions,
                $showEmpty,
                $tableFieldConfiguration
            );

            usort($result, static function($a, $b) {
                return strcmp($a['file']->getName(), $b['file']->getName());
            });
            
            return $result;
        }

        return [];
    }

    /**
     * @throws Exception
     */
    private function getPageTreePidArray(): array
    {
        $currentPageUid = $GLOBALS['TSFE']->id;

        $rootlineUtility = GeneralUtility::makeInstance(RootlineUtility::class, $currentPageUid, '', null);

        $rootPageUid = 0;
        foreach ($rootlineUtility->get() as $rootlineItem) {
            if ($rootlineItem['is_siteroot'] === 1) {
                $rootPageUid = $rootlineItem['uid'];
                break;
            }
        }

        return explode(',', $this->getTreeList($rootPageUid, 256));
    }

    /**
     * @throws Exception
     */
    public function getTreeList(int $id, int $depth, int $begin = 0, string $permClause = ''): float|int|string
    {
        if ($id < 0) {
            $id = abs($id);
        }

        if ($begin === 0) {
            $theList = $id;
        } else {
            $theList = '';
        }

        if ($id && $depth > 0) {
            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('pages');

            $queryBuilder
                ->getRestrictions()
                ->removeAll()
                ->add(GeneralUtility::makeInstance(DeletedRestriction::class));

            $queryBuilder
                ->select('uid')
                ->from('pages')
                ->where(
                    $queryBuilder
                        ->expr()
                        ->eq('pid', $queryBuilder
                            ->createNamedParameter($id, Connection::PARAM_INT))
                )
                ->orderBy('uid');

            if ($permClause !== '') {
                $queryBuilder
                    ->andWhere(QueryHelper::stripLogicalOperatorPrefix($permClause));
            }

            $statement = $queryBuilder->executeQuery();

            while ($row = $statement->fetchAssociative()) {
                if ($begin <= 0) {
                    $theList .= ',' . $row['uid'];
                }

                if ($depth > 1) {
                    $theSubList = $this->getTreeList($row['uid'], $depth - 1, $begin - 1, $permClause);
                    if (!empty($theList) && !empty($theSubList) && ($theSubList[0] !== ',')) {
                        $theList .= ',';
                    }
                    $theList .= $theSubList;
                }
            }
        }

        return $theList;
    }

    /**
     * @throws AspectNotFoundException
     * @throws Exception
     */
    private function prepareList(array $references, array $extensions, bool $showEmpty, array $tableFieldConfiguration): array
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
                    if ($fileReferenceObject->isMissing() === false && in_array($fileExtension, $extensions, true) && file_exists(Environment::getPublicPath() . $fileReferenceObject->getPublicUrl()) === true) {
                        if ($showEmpty === true || ($showEmpty === false && $fileReferenceObject->hasProperty('copyright'))) {
                            $itemList[] = [
                                'file' => $fileReferenceObject->getOriginalFile(),
                                'pages' => $this->generateFileReferencePages($fileReferenceObject, $tableFieldConfiguration)
                            ];
                        }
                    }
                } catch (ResourceDoesNotExistException $exception) {
                    // nothing to do here
                }
            }
        }

        return $itemList;
    }

    /**
     * @throws AspectNotFoundException
     * @throws Exception
     */
    private function generateFileReferencePages(FileReference $fileReferenceObject, array $tableFieldConfiguration): array
    {
        $pages = [];

        // Get the table names for regular sys_file_references from the configuration
        $tableNames = [];
        foreach ($tableFieldConfiguration as $configuration) {
            $tableNames[] = $configuration['tableName'];
        }

        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file_reference');

        $queryBuilder
            ->getRestrictions()
            ->removeAll();

        $results = $queryBuilder
            ->select('sys_file_reference.*')
            ->from('sys_file_reference')
            ->where(
                $queryBuilder
                    ->expr()
                    ->eq('sys_file_reference.uid_local', $fileReferenceObject->getProperties()['uid_local']),
                $queryBuilder
                    ->expr()
                    ->eq('sys_file_reference.sys_language_uid', GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId())
            )
            ->orderBy('sys_file_reference.sorting_foreign')
            ->executeQuery()
            ->fetchAllAssociative();

        $pageRepository = GeneralUtility::makeInstance(PageRepository::class);

        foreach ($results as $result) {
            if (in_array($result['tablenames'], $tableNames, true)) {
                $pages[] = $pageRepository->getPage($result['pid']);
            }
        }

        return $pages;
    }

    /**
     * @throws AspectNotFoundException
     * @throws Exception
     */
    private function getImagesFromFileCollections(array $tableFieldConfigurationForCollections, array $pid, bool $includeFileCollections): array
    {
        if ($includeFileCollections === true) {
            $referenceUids = [];

            $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
                ->getQueryBuilderForTable('sys_file_reference');

            $queryBuilder
                ->getRestrictions()
                ->removeAll();

            $queryBuilder
                ->select('sys_file_reference.uid', 'sys_file_reference.uid_local')
                ->from('sys_file_reference');

            $queryBuilder->leftJoin(
                'sys_file_reference',
                'sys_file_collection',
                'sys_file_collection',
                $queryBuilder
                    ->expr()
                    ->eq('sys_file_reference.uid_foreign', $queryBuilder
                        ->quoteIdentifier('sys_file_collection.uid')) . ' AND ' . $queryBuilder
                    ->expr()
                    ->eq('sys_file_reference.tablenames', $queryBuilder
                        ->createNamedParameter('sys_file_collection', Connection::PARAM_STR)));

            foreach ($tableFieldConfigurationForCollections as $configuration) {
                $queryBuilder->leftJoin(
                    'sys_file_collection',
                    $configuration['tableName'],
                    $configuration['tableName'],
                    $queryBuilder
                        ->expr()
                        ->inSet($configuration['tableName'] . '.' . $configuration['fieldName'], $queryBuilder
                            ->quoteIdentifier('sys_file_collection.uid'))
                );

                $queryBuilder->orWhere(
                    $queryBuilder
                        ->expr()
                        ->and(
                            $queryBuilder
                                ->expr()
                                ->eq($configuration['tableName'] . '.' . $GLOBALS['TCA'][$configuration['tableName']]['ctrl']['delete'], 0),
                            $queryBuilder
                                ->expr()
                                ->eq($configuration['tableName'] . '.' . $GLOBALS['TCA'][$configuration['tableName']]['ctrl']['enablecolumns']['disabled'], 0),
                            $queryBuilder
                                ->expr()
                                ->in($configuration['tableName'] . '.' . 'pid', $pid)
                        )
                );
            }

            $queryBuilder
                ->andWhere(
                    $queryBuilder
                        ->expr()
                        ->and(
                            $queryBuilder
                                ->expr()
                                ->eq('sys_file_reference.deleted', 0),
                            $queryBuilder
                                ->expr()
                                ->eq('sys_file_reference.hidden', 0),
                            $queryBuilder
                                ->expr()
                                ->eq('sys_file_collection.deleted', 0),
                            $queryBuilder
                                ->expr()
                                ->eq('sys_file_collection.hidden', 0),
                            $queryBuilder
                                ->expr()
                                ->eq('sys_file_reference.sys_language_uid', GeneralUtility::makeInstance(Context::class)->getAspect('language')->getId())
                        )
                );

            $res = $queryBuilder
                ->orderBy('sys_file_reference.sorting_foreign')
                ->groupBy('sys_file_reference.uid_local')
                ->executeQuery();

            while ($row = $res->fetchAssociative()) {
                $referenceUids[] = [
                    'uid' => $row['uid'],
                    'uid_local' => $row['uid_local']
                ];
            }

            return $referenceUids;
        }

        return [];
    }

    /**
     * @throws Exception
     */
    private function getFileCollectionPid(int $uid): int
    {
        $queryBuilder = GeneralUtility::makeInstance(ConnectionPool::class)
            ->getQueryBuilderForTable('sys_file_reference');

        $frontendRestrictionContainer = GeneralUtility::makeInstance(FrontendRestrictionContainer::class);

        $queryBuilder->setRestrictions($frontendRestrictionContainer);

        $result = $queryBuilder
            ->select('pid')
            ->from('sys_file_collection')
            ->where(
                $queryBuilder
                    ->expr()
                    ->eq('uid', $queryBuilder
                        ->createNamedParameter($uid, Connection::PARAM_INT)))
            ->executeQuery()
            ->fetchAssociative();

        if ($result['pid']) {
            $returnPid = $result['pid'];
        } else {
            $returnPid = 0;
        }

        return $returnPid;
    }
}
