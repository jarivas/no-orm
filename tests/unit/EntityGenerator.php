<?php


namespace NoOrm\Tests;

use NoOrm\EntityGenerator as EntityGeneratorNo;
use PDO;

class EntityGenerator extends EntityGeneratorNo
{
    public static function installDB(): ?string
    {
        $installFolder = dirname(__DIR__) . '/install/';
        $output = [];
        $resultCode = 0;

        chdir($installFolder);

        exec('docker-compose up -d', $output, $resultCode);
        if ($resultCode) {
            return print_r($output, true);
        }

        exec('docker-compose exec mariadb /db.sh', $output, $resultCode);

        if ($resultCode) {
            return print_r($output, true);
        }

        return null;
    }

    public static function setTargetFolder(string $folderPath): ?string
    {
        return parent::setTargetFolder($folderPath);
    }

    public static function setTemplatesFolder(): bool
    {
        return parent::setTemplatesFolder();
    }

    public static function createPath(string $path): bool
    {
        return parent::createPath($path);
    }

    public static function deleteAllFiles(string $path): bool
    {
        return parent::deleteAllFiles($path);
    }

    public static function connect(string $host, string $dbname, string $username, string $password): bool
    {
        return parent::connect($host, $dbname, $username, $password);
    }

    public static function generateFiles(string $host, string $dbname, string $username, string $password, string $namespace): ?string
    {
        return parent::generateFiles($host, $dbname, $username, $password, $namespace);
    }

    public static function copyReplace(string $fileName, array $search, array $replace): bool
    {
        return parent::copyReplace($fileName, $search, $replace);
    }

    public static function generateClasses(string $namespace): ?string
    {
        return parent::generateClasses($namespace);
    }

    public static function getTplContent(string $namespace): ?string
    {
        return parent::getTplContent($namespace);
    }

    public static function getTablesInfo(): array
    {
        return parent::getTablesInfo();
    }

    public static function generateEntities(array $descs, array $creates, string $namespace, string $tpl): bool
    {
        return parent::generateEntities($descs, $creates, $namespace, $tpl);
    }

    public static function toPascalCase($string): string
    {
        return parent::toPascalCase($string);
    }

    public static function getTypeFromMysql($type): string
    {
        return parent::getTypeFromMysql($type);
    }

    public static function extractRelationships(string $create, string $tableName, string $namespace): string
    {
        return parent::extractRelationships($create, $tableName, $namespace);
    }


}