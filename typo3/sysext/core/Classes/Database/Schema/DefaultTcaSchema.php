<?php
declare(strict_types = 1);

namespace TYPO3\CMS\Core\Database\Schema;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use Doctrine\DBAL\Schema\Table;
use TYPO3\CMS\Core\Utility\GeneralUtility;

/**
 * This class is called by the SchemaMigrator after all extension's ext_tables.sql
 * files have been parsed and processed to the doctrine Table/Column/Index objects.
 *
 * Method enrich() goes through all $GLOBALS['TCA'] tables and adds fields like
 * 'uid', 'sorting', 'deleted' and friends if the feature is enabled in TCA and the
 * field has not been defined in ext_tables.sql files.
 *
 * This allows extension developers to leave out the TYPO3 DB management fields
 * and reduce ext_tables.sql of extensions down to the business fields.
 *
 * @internal
 */
class DefaultTcaSchema
{
    /**
     * Add fields to $tables array that has been created from ext_tables.sql files.
     * This goes through all tables defined in TCA, looks for 'ctrl' features like
     * "soft delete" ['ctrl']['delete'] and adds the field if it has not been
     * defined in ext_tables.sql, yet.
     *
     * Note the incoming $tables array must be created from all ext_tables.sql files
     * of all loaded extensions, and TCA must be up-to-date.
     *
     * @param Table[] $tables
     * @return Table[]
     */
    public function enrich(array $tables): array
    {
        foreach ($GLOBALS['TCA'] as $tableName => $tableDefinition) {
            $isTableDefined = $this->isTableDefined($tables, $tableName);
            $tablePosition = null;
            if ($isTableDefined) {
                // If the table is given in existing $tables list, add all fields to the first
                // position of that table - in case it is in there multiple times which happens
                // if extensions add single fields to tables that have been defined in
                // other ext_tables.sql, too.
                $tablePosition = $this->getTableFirstPosition($tables, $tableName);
            } else {
                // Else create this table and add it to table list
                $table = GeneralUtility::makeInstance(Table::class, $tableName);
                $tables[] = $table;
                $tableKeys = array_keys($tables);
                $tablePosition = end($tableKeys);
            }

            // uid column and primary key if uid is not defined
            if (!$this->isColumnDefinedForTable($tables, $tableName, 'uid')) {
                $tables[$tablePosition]->addColumn(
                    'uid',
                    'integer',
                    [
                        'notnull' => true,
                        'unsigned' => true,
                        'autoincrement' => true,
                    ]
                );
                $tables[$tablePosition]->setPrimaryKey(['uid']);
            }

            // pid column and prepare parent key if pid is not defined
            $pidColumnAdded = false;
            if (!$this->isColumnDefinedForTable($tables, $tableName, 'pid')) {
                $options = [
                    'default' => 0,
                    'notnull' => true,
                    'unsigned' => false,
                ];
                if (empty($tableDefinition['ctrl']['versioningWS'])) {
                    // We need negative pid's (-1) if table is workspace aware
                    $options['unsigned'] = true;
                }
                $tables[$tablePosition]->addColumn('pid', 'integer', $options);
                $pidColumnAdded = true;
            }

            // tstamp column
            if (!empty($tableDefinition['ctrl']['tstamp'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['tstamp'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['tstamp'],
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // crdate column
            if (!empty($tableDefinition['ctrl']['crdate'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['crdate'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['crdate'],
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // cruser_id column
            if (!empty($tableDefinition['ctrl']['cruser_id'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['cruser_id'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['cruser_id'],
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // deleted column - soft delete
            if (!empty($tableDefinition['ctrl']['delete'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['delete'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['delete'],
                    'smallint',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // disabled column
            if (!empty($tableDefinition['ctrl']['enablecolumns']['disabled'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['enablecolumns']['disabled'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['enablecolumns']['disabled'],
                    'smallint',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // starttime column
            if (!empty($tableDefinition['ctrl']['enablecolumns']['starttime'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['enablecolumns']['starttime'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['enablecolumns']['starttime'],
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // endtime column
            if (!empty($tableDefinition['ctrl']['enablecolumns']['endtime'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['enablecolumns']['endtime'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['enablecolumns']['endtime'],
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // fe_group column
            if (!empty($tableDefinition['ctrl']['enablecolumns']['fe_group'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['enablecolumns']['fe_group'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['enablecolumns']['fe_group'],
                    'string',
                    [
                        'default' => '0',
                        'notnull' => true,
                        'length' => 255,
                    ]
                );
            }

            // sorting column
            if (!empty($tableDefinition['ctrl']['sortby'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['sortby'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['sortby'],
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => false,
                    ]
                );
            }

            // index on pid column and maybe others - only if pid has not been defined via ext_tables.sql before
            if ($pidColumnAdded && !$this->isIndexDefinedForTable($tables, $tableName, 'parent')) {
                $parentIndexFields = ['pid'];
                if (!empty($tableDefinition['ctrl']['delete'])) {
                    $parentIndexFields[] = (string)$tableDefinition['ctrl']['delete'];
                }
                if (!empty($tableDefinition['ctrl']['enablecolumns']['disabled'])) {
                    $parentIndexFields[] = (string)$tableDefinition['ctrl']['enablecolumns']['disabled'];
                }
                $tables[$tablePosition]->addIndex($parentIndexFields, 'parent');
            }

            // description column
            if (!empty($tableDefinition['ctrl']['descriptionColumn'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['descriptionColumn'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['descriptionColumn'],
                    'text',
                    [
                        'notnull' => false,
                        'length' => 65535,
                    ]
                );
            }

            // editlock column
            if (!empty($tableDefinition['ctrl']['editlock'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['editlock'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['editlock'],
                    'smallint',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // sys_language_uid column
            if (!empty($tableDefinition['ctrl']['languageField'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['languageField'])
            ) {
                $tables[$tablePosition]->addColumn(
                    (string)$tableDefinition['ctrl']['languageField'],
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => false,
                    ]
                );
            }

            // l10n_parent column
            if (!empty($tableDefinition['ctrl']['languageField'])
                && !empty($tableDefinition['ctrl']['transOrigPointerField'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['transOrigPointerField'])
            ) {
                $tables[$tablePosition]->addColumn(
                    (string)$tableDefinition['ctrl']['transOrigPointerField'],
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // l10n_source column
            if (!empty($tableDefinition['ctrl']['languageField'])
                && !empty($tableDefinition['ctrl']['translationSource'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['translationSource'])
            ) {
                $tables[$tablePosition]->addColumn(
                    (string)$tableDefinition['ctrl']['translationSource'],
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // l10n_state column
            if (!empty($tableDefinition['ctrl']['languageField'])
                && !empty($tableDefinition['ctrl']['transOrigPointerField'])
                && !$this->isColumnDefinedForTable($tables, $tableName, 'l10n_state')
            ) {
                $tables[$tablePosition]->addColumn(
                    'l10n_state',
                    'text',
                    [
                        'notnull' => false,
                        'length' => 65535,
                    ]
                );
            }

            // t3_origuid column
            if (!empty($tableDefinition['ctrl']['origUid'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['origUid'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['origUid'],
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // l18n_diffsource column
            if (!empty($tableDefinition['ctrl']['transOrigDiffSourceField'])
                && !$this->isColumnDefinedForTable($tables, $tableName, $tableDefinition['ctrl']['transOrigDiffSourceField'])
            ) {
                $tables[$tablePosition]->addColumn(
                    $tableDefinition['ctrl']['transOrigDiffSourceField'],
                    'blob',
                    [
                        // mediumblob (16MB) on mysql
                        'length' => 16777215,
                        'notnull' => false,
                    ]
                );
            }

            // workspaces t3ver_oid column
            if (!empty($tableDefinition['ctrl']['versioningWS'])
                && (bool)$tableDefinition['ctrl']['versioningWS'] === true
                && !$this->isColumnDefinedForTable($tables, $tableName, 't3ver_oid')
            ) {
                $tables[$tablePosition]->addColumn(
                    't3ver_oid',
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // workspaces t3ver_id column
            if (!empty($tableDefinition['ctrl']['versioningWS'])
                && (bool)$tableDefinition['ctrl']['versioningWS'] === true
                && !$this->isColumnDefinedForTable($tables, $tableName, 't3ver_id')
            ) {
                $tables[$tablePosition]->addColumn(
                    't3ver_id',
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // workspaces t3ver_label column
            if (!empty($tableDefinition['ctrl']['versioningWS'])
                && (bool)$tableDefinition['ctrl']['versioningWS'] === true
                && !$this->isColumnDefinedForTable($tables, $tableName, 't3ver_label')
            ) {
                $tables[$tablePosition]->addColumn(
                    't3ver_label',
                    'string',
                    [
                        'default' => '',
                        'notnull' => true,
                        'length' => 255,
                    ]
                );
            }

            // workspaces t3ver_wsid column
            if (!empty($tableDefinition['ctrl']['versioningWS'])
                && (bool)$tableDefinition['ctrl']['versioningWS'] === true
                && !$this->isColumnDefinedForTable($tables, $tableName, 't3ver_wsid')
            ) {
                $tables[$tablePosition]->addColumn(
                    't3ver_wsid',
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // workspaces t3ver_state column
            if (!empty($tableDefinition['ctrl']['versioningWS'])
                && (bool)$tableDefinition['ctrl']['versioningWS'] === true
                && !$this->isColumnDefinedForTable($tables, $tableName, 't3ver_state')
            ) {
                $tables[$tablePosition]->addColumn(
                    't3ver_state',
                    'smallint',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => false,
                    ]
                );
            }

            // workspaces t3ver_stage column
            if (!empty($tableDefinition['ctrl']['versioningWS'])
                && (bool)$tableDefinition['ctrl']['versioningWS'] === true
                && !$this->isColumnDefinedForTable($tables, $tableName, 't3ver_stage')
            ) {
                $tables[$tablePosition]->addColumn(
                    't3ver_stage',
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // workspaces t3ver_count column
            if (!empty($tableDefinition['ctrl']['versioningWS'])
                && (bool)$tableDefinition['ctrl']['versioningWS'] === true
                && !$this->isColumnDefinedForTable($tables, $tableName, 't3ver_count')
            ) {
                $tables[$tablePosition]->addColumn(
                    't3ver_count',
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // workspaces t3ver_tstamp column
            if (!empty($tableDefinition['ctrl']['versioningWS'])
                && (bool)$tableDefinition['ctrl']['versioningWS'] === true
                && !$this->isColumnDefinedForTable($tables, $tableName, 't3ver_tstamp')
            ) {
                $tables[$tablePosition]->addColumn(
                    't3ver_tstamp',
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // workspaces t3ver_move_id column
            if (!empty($tableDefinition['ctrl']['versioningWS'])
                && (bool)$tableDefinition['ctrl']['versioningWS'] === true
                && !$this->isColumnDefinedForTable($tables, $tableName, 't3ver_move_id')
            ) {
                $tables[$tablePosition]->addColumn(
                    't3ver_move_id',
                    'integer',
                    [
                        'default' => 0,
                        'notnull' => true,
                        'unsigned' => true,
                    ]
                );
            }

            // workspaces index on t3ver_oid and t3ver_wsid fields
            if (!empty($tableDefinition['ctrl']['versioningWS'])
                && (bool)$tableDefinition['ctrl']['versioningWS'] === true
                && !$this->isIndexDefinedForTable($tables, $tableName, 't3ver_oid')
            ) {
                $tables[$tablePosition]->addIndex(['t3ver_oid', 't3ver_wsid'], 't3ver_oid');
            }
        }

        return $tables;
    }

    /**
     * True if table with given table name is defined within incoming $tables array
     *
     * @param Table[] $tables
     * @param string $tableName
     * @return bool
     */
    protected function isTableDefined(array $tables, string $tableName): bool
    {
        foreach ($tables as $table) {
            if ($table->getName() === $tableName) {
                return true;
            }
        }
        return false;
    }

    /**
     * True if a column with a given name is defined within the incoming
     * array of Table's.
     *
     * @param Table[] $tables
     * @param string $tableName
     * @param string $fieldName
     * @return bool
     */
    protected function isColumnDefinedForTable(array $tables, string $tableName, string $fieldName): bool
    {
        foreach ($tables as $table) {
            if ($table->getName() !== $tableName) {
                continue;
            }
            $columns = $table->getColumns();
            foreach ($columns as $column) {
                if ($column->getName() === $fieldName) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * True if an index with a given name is defined within the incoming
     * array of Table's.
     *
     * @param Table[] $tables
     * @param string $tableName
     * @param string $indexName
     * @return bool
     */
    protected function isIndexDefinedForTable(array $tables, string $tableName, string $indexName): bool
    {
        foreach ($tables as $table) {
            if ($table->getName() !== $tableName) {
                continue;
            }
            $indexes = $table->getIndexes();
            foreach ($indexes as $index) {
                if ($index->getName() === $indexName) {
                    return true;
                }
            }
        }
        return false;
    }

    /**
     * The incoming $tables array can contain Table objects for the same table
     * multiple times. This can happen if an extension has the main CREATE TABLE
     * statement in its ext_tables.sql and another extension adds or changes further
     * fields in an own CREATE TABLE statement.
     *
     * @todo It would be better if the incoming $tables structure would be cleaned
     * @todo to contain a table only once before this class is entered.
     *
     * @param Table[] $tables
     * @param string $tableName
     * @return int
     * @throws \RuntimeException
     */
    protected function getTableFirstPosition(array $tables, string $tableName): int
    {
        foreach ($tables as $position => $table) {
            if ($table->getName() === $tableName) {
                return (int)$position;
            }
        }
        throw new \RuntimeException('Table ' . $tableName . ' not found in schema list', 1527854474);
    }
}