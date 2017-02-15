<?php

namespace solutionDrive\MySqlDiff;

use solutionDrive\MySqlDiff\Model\Column;
use solutionDrive\MySqlDiff\Model\Database;
use solutionDrive\MySqlDiff\Model\ForeignKey;
use solutionDrive\MySqlDiff\Model\Index;
use solutionDrive\MySqlDiff\Model\IndexColumn;
use solutionDrive\MySqlDiff\Model\Table;

class ParserTest extends AbstractTest
{
    public function testIsParsingTables()
    {
        $parser = new Parser();

        $result = $parser->parseTables($this->getDatabaseFixture('sakila.sql'));

        $this->assertCount(19, $result);
        $this->assertArrayHasKey('actor', $result);
        $this->assertInstanceOf(Table::class, $result['actor']);
        $this->assertEquals('actor', $result['actor']->getName());
        $this->assertEquals('InnoDB', $result['actor']->getEngine());
        $this->assertEquals(201, $result['actor']->getAutoIncrement());
        $this->assertEquals('utf8', $result['actor']->getDefaultCharset());
        $this->assertEquals('CREATE TABLE `actor` (
  `actor_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`actor_id`),
  KEY `idx_actor_last_name` (`last_name`)
) ENGINE=InnoDB AUTO_INCREMENT=201 DEFAULT CHARSET=utf8;', $result['actor']->getCreationScript());
        $this->assertEquals('`actor_id` smallint(5) unsigned NOT NULL AUTO_INCREMENT,
  `first_name` varchar(45) NOT NULL,
  `last_name` varchar(45) NOT NULL,
  `last_update` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`actor_id`),
  KEY `idx_actor_last_name` (`last_name`)', $result['actor']->getDefinition());
    }

    public function testIsParsingColumns()
    {
        $parser = new Parser();

        $tables = $parser->parseTables($this->getDatabaseFixture('sakila.sql'));
        $actorTable = $tables['actor'];

        $parser->parseColumns($actorTable);

        $this->assertCount(4, $actorTable->getColumns());

        $this->assertArrayHasKey('actor_id', $actorTable->getColumns());
        $this->assertInstanceOf(Column::class, $actorTable->getColumnByName('actor_id'));
        $this->assertEquals('actor_id', $actorTable->getColumnByName('actor_id')->getName());
        $this->assertEquals('smallint(5) unsigned', $actorTable->getColumnByName('actor_id')->getColumnType());
        $this->assertEquals('smallint', $actorTable->getColumnByName('actor_id')->getDataType());
        $this->assertTrue($actorTable->getColumnByName('actor_id')->isUnsigned());
        $this->assertEquals(5 , $actorTable->getColumnByName('actor_id')->getLength());
        $this->assertNull($actorTable->getColumnByName('actor_id')->getPrecision());
        $this->assertFalse($actorTable->getColumnByName('actor_id')->isNullable());
        $this->assertTrue($actorTable->getColumnByName('actor_id')->isAutoIncrement());
        $this->assertFalse($actorTable->getColumnByName('actor_id')->isPrimaryKey());
        $this->assertNull($actorTable->getColumnByName('actor_id')->getDefaultValue());
        $this->assertNull($actorTable->getColumnByName('actor_id')->getOnUpdateValue());
        $this->assertNull($actorTable->getColumnByName('actor_id')->getCharacterSet());
        $this->assertNull($actorTable->getColumnByName('actor_id')->getCollate());

        $this->assertArrayHasKey('last_update', $actorTable->getColumns());
        $this->assertInstanceOf(Column::class, $actorTable->getColumnByName('last_update'));
        $this->assertEquals('last_update', $actorTable->getColumnByName('last_update')->getName());
        $this->assertEquals('timestamp', $actorTable->getColumnByName('last_update')->getColumnType());
        $this->assertEquals('timestamp', $actorTable->getColumnByName('last_update')->getDataType());
        $this->assertFalse($actorTable->getColumnByName('last_update')->isUnsigned());
        $this->assertNull($actorTable->getColumnByName('last_update')->getLength());
        $this->assertNull($actorTable->getColumnByName('last_update')->getPrecision());
        $this->assertFalse($actorTable->getColumnByName('last_update')->isNullable());
        $this->assertFalse($actorTable->getColumnByName('last_update')->isAutoIncrement());
        $this->assertFalse($actorTable->getColumnByName('last_update')->isPrimaryKey());
        $this->assertEquals('CURRENT_TIMESTAMP', $actorTable->getColumnByName('last_update')->getDefaultValue());
        $this->assertEquals('CURRENT_TIMESTAMP', $actorTable->getColumnByName('last_update')->getOnUpdateValue());
        $this->assertNull($actorTable->getColumnByName('last_update')->getCharacterSet());
        $this->assertNull($actorTable->getColumnByName('last_update')->getCollate());
    }

    public function testIsParsingColumnWithDefaultCharacterSetAndCollate()
    {
        $parser = new Parser();

        $tables = $parser->parseTables($this->getDatabaseFixture('sakila.sql'));
        $staffTable = $tables['staff'];

        $parser->parseColumns($staffTable);

        $this->assertCount(11, $staffTable->getColumns());

        $this->assertArrayHasKey('password', $staffTable->getColumns());
        $this->assertInstanceOf(Column::class, $staffTable->getColumnByName('password'));
        $this->assertEquals('password', $staffTable->getColumnByName('password')->getName());
        $this->assertEquals('varchar(40)', $staffTable->getColumnByName('password')->getColumnType());
        $this->assertEquals('varchar', $staffTable->getColumnByName('password')->getDataType());
        $this->assertFalse($staffTable->getColumnByName('password')->isUnsigned());
        $this->assertEquals(40 , $staffTable->getColumnByName('password')->getLength());
        $this->assertNull($staffTable->getColumnByName('password')->getPrecision());
        $this->assertTrue($staffTable->getColumnByName('password')->isNullable());
        $this->assertFalse($staffTable->getColumnByName('password')->isAutoIncrement());
        $this->assertFalse($staffTable->getColumnByName('password')->isPrimaryKey());
        $this->assertEquals('NULL', $staffTable->getColumnByName('password')->getDefaultValue());
        $this->assertNull($staffTable->getColumnByName('password')->getOnUpdateValue());
        $this->assertEquals('utf8', $staffTable->getColumnByName('password')->getCharacterSet());
        $this->assertEquals('utf8_bin', $staffTable->getColumnByName('password')->getCollate());
    }

    public function testIsParsingColumnsInTableWithNoPrimaryKey()
    {
        $parser = new Parser();

        $tables = $parser->parseTables($this->getDatabaseFixture('sakila.sql'));
        $testTable = $tables['test'];

        $parser->parseColumns($testTable);

        $this->assertCount(1, $testTable->getColumns());

        $this->assertArrayHasKey('test1', $testTable->getColumns());
        $this->assertInstanceOf(Column::class, $testTable->getColumnByName('test1'));
        $this->assertEquals('test1', $testTable->getColumnByName('test1')->getName());
        $this->assertEquals('int(10)', $testTable->getColumnByName('test1')->getColumnType());
        $this->assertEquals('int', $testTable->getColumnByName('test1')->getDataType());
        $this->assertFalse($testTable->getColumnByName('test1')->isUnsigned());
        $this->assertEquals(10 , $testTable->getColumnByName('test1')->getLength());
        $this->assertNull($testTable->getColumnByName('test1')->getPrecision());
        $this->assertTrue($testTable->getColumnByName('test1')->isNullable());
        $this->assertFalse($testTable->getColumnByName('test1')->isAutoIncrement());
        $this->assertFalse($testTable->getColumnByName('test1')->isPrimaryKey());
        $this->assertEquals('NULL', $testTable->getColumnByName('test1')->getDefaultValue());
        $this->assertNull($testTable->getColumnByName('test1')->getOnUpdateValue());
    }

    public function testIsParsingPrimaryKey()
    {
        $parser = new Parser();

        $tables = $parser->parseTables($this->getDatabaseFixture('sakila.sql'));
        $actorTable = $tables['actor'];

        $parser->parseColumns($actorTable);
        $parser->parsePrimaryKey($actorTable);

        $this->assertCount(1, $actorTable->getPrimaryKeys());
        $this->assertInstanceOf(Column::class, $actorTable->getPrimaryKeys()[0]);
        $this->assertEquals('actor_id', $actorTable->getPrimaryKeys()[0]->getName());
        $this->assertTrue($actorTable->getPrimaryKeys()[0]->isPrimaryKey());
    }

    public function testIsParsingPrimaryKeyInTableWithNoIndexes()
    {
        $parser = new Parser();

        $tables = $parser->parseTables($this->getDatabaseFixture('sakila.sql'));
        $countryTable = $tables['country'];

        $parser->parseColumns($countryTable);
        $parser->parsePrimaryKey($countryTable);

        $this->assertCount(1, $countryTable->getPrimaryKeys());
        $this->assertInstanceOf(Column::class, $countryTable->getPrimaryKeys()[0]);
        $this->assertEquals('country_id', $countryTable->getPrimaryKeys()[0]->getName());
        $this->assertTrue($countryTable->getPrimaryKeys()[0]->isPrimaryKey());
    }

    public function testIsParsingMultiplePrimaryKeys()
    {
        $parser = new Parser();

        $tables = $parser->parseTables($this->getDatabaseFixture('sakila.sql'));
        $filmCategoryTable = $tables['film_category'];

        $parser->parseColumns($filmCategoryTable);
        $parser->parsePrimaryKey($filmCategoryTable);

        $this->assertCount(2, $filmCategoryTable->getPrimaryKeys());
        $this->assertInstanceOf(Column::class, $filmCategoryTable->getPrimaryKeys()[0]);
        $this->assertEquals('film_id', $filmCategoryTable->getPrimaryKeys()[0]->getName());
        $this->assertTrue($filmCategoryTable->getPrimaryKeys()[0]->isPrimaryKey());
        $this->assertInstanceOf(Column::class, $filmCategoryTable->getPrimaryKeys()[1]);
        $this->assertEquals('category_id', $filmCategoryTable->getPrimaryKeys()[1]->getName());
        $this->assertTrue($filmCategoryTable->getPrimaryKeys()[1]->isPrimaryKey());
    }

    public function testIsParsingForeignKeys()
    {
        $parser = new Parser();

        $tables = $parser->parseTables($this->getDatabaseFixture('sakila.sql'));
        $staffTable = $tables['staff'];

        $parser->parseColumns($staffTable);
        $parser->parsePrimaryKey($staffTable);
        $parser->parseForeignKeys($staffTable);

        $this->assertCount(2, $staffTable->getForeignKeys());

        $this->assertInstanceOf(ForeignKey::class, $staffTable->getForeignKeyByName('fk_staff_address'));
        $this->assertEquals('address_id', $staffTable->getForeignKeyByName('fk_staff_address')->getColumnName());
        $this->assertEquals('address', $staffTable->getForeignKeyByName('fk_staff_address')->getReferenceTableName());
        $this->assertEquals('address_id', $staffTable->getForeignKeyByName('fk_staff_address')->getReferenceColumnName());
        $this->assertEquals('ON UPDATE CASCADE', $staffTable->getForeignKeyByName('fk_staff_address')->getOnUpdateClause());
    }

    public function testIsParsingIndexes()
    {
        $parser = new Parser();

        $tables = $parser->parseTables($this->getDatabaseFixture('sakila.sql'));
        $rentalTable = $tables['rental'];

        $parser->parseColumns($rentalTable);
        $parser->parsePrimaryKey($rentalTable);
        $parser->parseForeignKeys($rentalTable);
        $parser->parseIndexes($rentalTable);

        $this->assertCount(4, $rentalTable->getIndexes());

        $this->assertInstanceOf(Index::class, $rentalTable->getIndexByName('rental_date'));
        $this->assertCount(3, $rentalTable->getIndexByName('rental_date')->getIndexColumns());
        $this->assertInstanceOf(IndexColumn::class, $rentalTable->getIndexByName('rental_date')->getIndexColumnByColumnName('rental_date'));
        $this->assertInstanceOf(IndexColumn::class, $rentalTable->getIndexByName('rental_date')->getIndexColumnByColumnName('inventory_id'));
        $this->assertInstanceOf(IndexColumn::class, $rentalTable->getIndexByName('rental_date')->getIndexColumnByColumnName('customer_id'));
        $this->assertTrue($rentalTable->getIndexByName('rental_date')->isUnique());
        $this->assertFalse($rentalTable->getIndexByName('rental_date')->isSpatial());
        $this->assertFalse($rentalTable->getIndexByName('rental_date')->isFulltext());
        $this->assertNull($rentalTable->getIndexByName('rental_date')->getOptions());

        $this->assertInstanceOf(Index::class, $rentalTable->getIndexByName('idx_fk_staff_id'));
        $this->assertCount(1, $rentalTable->getIndexByName('idx_fk_staff_id')->getIndexColumns());
        $this->assertInstanceOf(IndexColumn::class, $rentalTable->getIndexByName('idx_fk_staff_id')->getIndexColumnByColumnName('staff_id'));
        $this->assertFalse($rentalTable->getIndexByName('idx_fk_staff_id')->isUnique());
        $this->assertFalse($rentalTable->getIndexByName('idx_fk_staff_id')->isSpatial());
        $this->assertFalse($rentalTable->getIndexByName('idx_fk_staff_id')->isFulltext());
        $this->assertNull($rentalTable->getIndexByName('idx_fk_staff_id')->getOptions());
    }

    public function testIsParsingDatabase()
    {
        $parser = new Parser();

        $database = $parser->parseDatabase($this->getDatabaseFixture('sakila.sql'));

        $this->assertInstanceOf(Database::class, $database);
        $this->assertCount(19, $database->getTables());
    }

    public function testIsParsingTableComment()
    {
        $parser = new Parser();

        $database = $parser->parseDatabase($this->getDatabaseFixture('sakila.sql'));

        $this->assertInstanceOf(Database::class, $database);
        $this->assertEquals('table\'s comment', $database->getTableByName('test')->getComment());
    }

    public function testIsGeneratingTableCreationScript()
    {
        $parser = new Parser();

        $database = $parser->parseDatabase($this->getDatabaseFixture('sakila.sql'));

        foreach ($database->getTables() as $table) {
            $this->assertEquals($table->getCreationScript(), $table->generateCreationScript(false, false));
        }
    }

    public function testIsParsingTableWithPartitionDefinitions()
    {
        $parser = new Parser();

        $database = $parser->parseDatabase($this->getDatabaseFixture('partition.sql'));

        $this->assertInstanceOf(Database::class, $database);
        $this->assertCount(1, $database->getTables());
        $this->assertCount(9, $database->getTableByName('export')->getColumns());
    }

    public function testIsParsingDoubleUnsignedType()
    {
        $parser = new Parser();

        $database = $parser->parseDatabase($this->getDatabaseFixture('jos_finder_links.sql'));

        $this->assertInstanceOf(Database::class, $database);
        $this->assertCount(1, $database->getTables());
        $this->assertCount(19, $database->getTableByName('jos_finder_links')->getColumns());
        $this->assertEquals('double unsigned', $database->getTableByName('jos_finder_links')->getColumnByName('list_price')->getColumnType());
        $this->assertEquals('double', $database->getTableByName('jos_finder_links')->getColumnByName('list_price')->getDataType());
    }

    public function testIsParsingPrimaryKeyLength()
    {
        $creationScript = $this->getDatabaseFixture('jos_extwebdav_properties.sql');

        $parser = new Parser();

        $database = $parser->parseDatabase($creationScript);

        $this->assertInstanceOf(Database::class, $database);
        $this->assertCount(1, $database->getTables());
        $this->assertCount(4, $database->getTableByName('jos_extwebdav_properties')->getColumns());
        $this->assertCount(3, $database->getTableByName('jos_extwebdav_properties')->getPrimaryKeys());

        $this->assertEquals($creationScript, $database->getTableByName('jos_extwebdav_properties')->generateCreationScript());
    }

    public function testIsParsingCommentsWithSpecialCharacters()
    {
        $creationScript = $this->getDatabaseFixture('jos_ucm_history.sql');

        $parser = new Parser();

        $database = $parser->parseDatabase($creationScript);

        $this->assertInstanceOf(Database::class, $database);
        $this->assertCount(1, $database->getTables());
        $this->assertCount(10, $database->getTableByName('jos_ucm_history')->getColumns());
        $this->assertCount(1, $database->getTableByName('jos_ucm_history')->getPrimaryKeys());
        $this->assertCount(2, $database->getTableByName('jos_ucm_history')->getIndexes());
        $this->assertEquals(1, $database->getTableByName('jos_ucm_history')->getAutoIncrement());
        $this->assertEquals('utf8', $database->getTableByName('jos_ucm_history')->getDefaultCharset());
        $this->assertEquals('InnoDB', $database->getTableByName('jos_ucm_history')->getEngine());
        $this->assertEquals('SHA1 hash of the version\'s data column.', $database->getTableByName('jos_ucm_history')->getColumnByName('sha1_hash')->getComment());
        $this->assertEquals('`sha1_hash` varchar(50) NOT NULL DEFAULT \'\' COMMENT \'SHA1 hash of the version\'\'s data column.\'', $database->getTableByName('jos_ucm_history')->getColumnByName('sha1_hash')->generateCreationScript());
        $this->assertEquals('0=auto delete; 1=keep', $database->getTableByName('jos_ucm_history')->getColumnByName('keep_forever')->getComment());
    }

    public function testIsParsingCaseInsensitiveAndSpaces()
    {
        $creationScript = $this->getDatabaseFixture('jeff.sql');

        $parser = new Parser();

        $database = $parser->parseDatabase($creationScript);

        $this->assertInstanceOf(Database::class, $database);
        $this->assertCount(1, $database->getTables());
        $this->assertCount(1, $database->getTableByName('contact')->getColumns());
        $this->assertCount(0, $database->getTableByName('contact')->getPrimaryKeys());
        $this->assertCount(0, $database->getTableByName('contact')->getIndexes());
        $this->assertNull($database->getTableByName('contact')->getDefaultCharset());
        $this->assertEquals('InnoDB', $database->getTableByName('contact')->getEngine());
        $this->assertEquals('`id` BIGINT UNSIGNED NOT NULL AUTO_INCREMENT', $database->getTableByName('contact')->getColumnByName('id')->generateCreationScript());
    }
}
