<?php declare(strict_types=1);


namespace NoOrm\Tests;

use PHPUnit\Framework\TestCase;

class EntityGeneratorTest extends TestCase
{
    protected static string $dir = '/Book/';

    protected string $host = '0.0.0.0';
    protected string $dbName = 'no_orm';
    protected string $user  = 'root';
    protected string $password = 'secretadmin';
    protected string $namespace = 'Db\\Book';

    public static function setUpBeforeClass(): void
    {
        EntityGenerator::installDB();

        self::$dir = dirname(__DIR__) . self::$dir;
    }

    public function testCreatePath(): void
    {
        $result = EntityGenerator::createPath(self::$dir);

        $this->assertTrue($result, 'Error on createPath');
    }

    /**
     * @depends testCreatePath
     */
    public function testDeleteAllFiles(): void
    {
        $fileName = self::$dir . 'test.txt';

        $result = file_put_contents($fileName, 'Delete me!');
        $this->assertIsNumeric($result, 'Error on file_put_contents');

        $result = EntityGenerator::deleteAllFiles(self::$dir);

        $this->assertTrue($result, 'Error on deleteAllFiles');
    }

    /**
     * @depends testDeleteAllFiles
     */
    public function testSetTargetFolder(): void
    {
        $result = EntityGenerator::setTargetFolder(self::$dir);

        $this->assertNull($result, 'Error on setTargetFolder');
    }

    /**
     * @depends testSetTargetFolder
     */
    public function testSetTemplatesFolder(): void
    {
        $result = EntityGenerator::setTemplatesFolder();

        $this->assertTrue($result, 'Error on setTemplatesFolder');
    }

    /**
     * @depends testSetTemplatesFolder
     */
    public function testCopyReplace(): void
    {
        $result = EntityGenerator::copyReplace('EntityBody', ['{{namespace}}'], [$this->namespace]);
        $this->assertTrue($result, 'Error on copyReplace');

        $file = self::$dir . 'EntityBody.php';

        $result = file_exists($file);
        $this->assertTrue($result, 'Error on copyReplace, file not created');

        $content = file_get_contents($file);
        $this->assertIsString($content, 'Error on copyReplace, file_get_contents fails');
        $this->assertStringContainsString($this->namespace, $content, 'Error on copyReplace, replace fails');
    }

    /**
     * @depends testCopyReplace
     */
    public function testConnect(): void
    {
        $result = EntityGenerator::connect($this->host, $this->dbName, $this->user, $this->password);

        $this->assertTrue($result, 'Error on connect');
    }

    /**
     * @depends testConnect
     */
    public function testGenerateFiles(): void
    {
        EntityGenerator::setTargetFolder(self::$dir);

        $result = EntityGenerator::generateFiles($this->host, $this->dbName, $this->user, $this->password, $this->namespace);

        $this->assertNull($result, 'Error on generateFiles');
    }

    /**
     * @depends testSetTemplatesFolder
     */
    public function testGetTplContent(): void
    {
        $tpl = EntityGenerator::getTplContent($this->namespace);

        $this->assertIsString($tpl, 'Error on getTplContent, result is not an string');
        $this->assertStringContainsString($this->namespace, $tpl, 'Error on getTplContent, replace fails');
    }

    /**
     * @depends testConnect
     */
    public function testGetTablesInfo(): void
    {
        $tables = ['book', 'book_category', 'category'];
        list($descs, $creates) = EntityGenerator::getTablesInfo();

        $this->assertIsArray($descs, 'Table descriptions is not an array');
        $this->assertIsArray($creates, 'Table creates is not an array');

        $descTables = array_keys($descs);

        $this->assertSame($tables, $descTables);

        $this->assertCount(7, $descs['book']);
        $this->assertCount(2, $descs['book_category']);
        $this->assertCount(5, $descs['category']);
    }

    public function testToPascalCase(): void
    {
        $words = 'date_time';
        $result = EntityGenerator::toPascalCase($words);
        $this->assertSame($result, 'DateTime');
    }

    public function testGetTypeFromMysql(): void
    {
        $result = EntityGenerator::getTypeFromMysql('int');
        $this->assertSame($result, 'integer');
        $result = EntityGenerator::getTypeFromMysql('float');
        $this->assertSame($result, 'float');
        $result = EntityGenerator::getTypeFromMysql('double');
        $this->assertSame($result, 'double');
        $result = EntityGenerator::getTypeFromMysql('real');
        $this->assertSame($result, 'real');
        $result = EntityGenerator::getTypeFromMysql('bit');
        $this->assertSame($result, 'boolean');
        $result = EntityGenerator::getTypeFromMysql('boolean');
        $this->assertSame($result, 'boolean');
        $result = EntityGenerator::getTypeFromMysql('date');
        $this->assertSame($result, 'datetime');
        $result = EntityGenerator::getTypeFromMysql('time');
        $this->assertSame($result, 'datetime');
        $result = EntityGenerator::getTypeFromMysql('samosa');
        $this->assertSame($result, 'string');
    }

    public function testExtractRelationships(): void
    {
        $create = <<<SQL
CREATE TABLE `book_category` (
  `id_book` int(10) unsigned NOT NULL,
  `id_category` int(10) unsigned NOT NULL,
  KEY `book_index` (`id_book`),
  KEY `category_index` (`id_category`),
  CONSTRAINT `fk_book_category_book` FOREIGN KEY (`id_book`) REFERENCES `book` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION,
  CONSTRAINT `fk_book_category_category` FOREIGN KEY (`id_category`) REFERENCES `category` (`id`) ON DELETE NO ACTION ON UPDATE NO ACTION
) ENGINE=InnoDB DEFAULT CHARSET=utf8 COLLATE=utf8_unicode_ci;
SQL;
        $result = EntityGenerator::extractRelationships($create, 'book_category', $this->namespace);
        eval("\$result = $result;");

        $this->assertIsArray($result, 'extractRelationships returns wrong string');
        $this->assertCount(2, $result);
    }

    /**
     * @depends testGetTplContent
     * @depends testGetTablesInfo
     * @depends testToPascalCase
     * @depends testGetTypeFromMysql
     * @depends testExtractRelationships
     */
    public function testGenerateEntities(): void
    {
        $tpl = EntityGenerator::getTplContent($this->namespace);
        list($descs, $creates) = EntityGenerator::getTablesInfo();

        $result = EntityGenerator::generateEntities($descs, $creates, $this->namespace, $tpl);

        $this->assertTrue($result, 'Problem on generateEntities');
    }

    /**
     * @depends testGetTplContent
     * @depends testGetTablesInfo
     * @depends testGenerateEntities
     */
    public function testGenerateClasses(): void
    {
        $result = EntityGenerator::generateClasses($this->namespace);

        $this->assertNull($result, 'Problem on testGenerateClasses');
    }
}
