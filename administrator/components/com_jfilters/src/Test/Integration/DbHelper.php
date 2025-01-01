<?php
/**
 * @package     Bluecoder.JFilters
 *
 * @copyright   Copyright © 2024 Blue-Coder.com. All rights reserved.
 * @license     GNU General Public License 2 or later, see COPYING.txt for license details.
 */

namespace Bluecoder\Component\Jfilters\Administrator\Test\Integration;

\defined('_JEXEC') or die();

use Exception;
use Joomla\Database\DatabaseDriver;
use Joomla\Database\DatabaseFactory;

class DbHelper
{
    /**
     * @since 1.0.0
     * @var DatabaseDriver
     */
    protected $db;

    /**
     * @return bool
     * @throws Exception
     * @since 1.0.0
     */
    public function createTables()
    {
        $dbFile = realpath(__DIR__ .'/dataset/schema.sql');
        $extensionInstallFile = __DIR__ .'/../../../sql/install.mysql.utf8.sql';
        if(is_file($dbFile)) {
            $fileContents = @file_get_contents($dbFile);
            if(is_file($extensionInstallFile)) {
                $fileContents.= @file_get_contents($extensionInstallFile);
            }
            $db = $this->getDbo();
            $queries = $db::splitSql($fileContents);
            if (!empty($queries)) {
                $this->createDatabase();
                $db->select(JTEST_DB_NAME);
                foreach ($queries as $query) {
                    try {
                        $db->setQuery($query)->execute();
                    } catch (Exception $e) {
                        echo "Tables Cannot be created";
                        throw $e;
                    }
                }
            } else {
                throw new \UnexpectedValueException('The schema sql file is either absent or invalid.');
            }
        }
        return true;
    }

    /**
     * @return bool
     * @throws Exception
     * @since 1.0.0
     */
    public function dropTables()
    {
        $dbFile = __DIR__ . '/dataset/clearSchema.sql';
        $extensionUninstallFile = __DIR__ .'/../../../sql/uninstall.mysql.utf8.sql';
        if(is_file($dbFile)) {
            $fileContents = @file_get_contents($dbFile);
            if(is_file($extensionUninstallFile)) {
                $fileContents.= @file_get_contents($extensionUninstallFile);
            }
            $db = $this->getDbo();
            $db->select(JTEST_DB_NAME);
            $queries = $db::splitSql($fileContents);
            if (!empty($queries)) {
                foreach ($queries as $query) {
                    // Do not execute the drop indexes queries. We drop the entire tables.
                    if(strpos($query, 'ALTER TABLE') === 0) {
                        continue;
                    }
                    try {
                        $db->setQuery($query)->execute();
                    } catch (Exception $e) {
                        echo "Tables Cannot be dropped";
                        throw $e;
                    }
                }
            } else {
                throw new \UnexpectedValueException('The clearSchema sql file is either absent or invalid.');
            }
        }
        return true;
    }

    /**
     * @param string $filename
     * @return bool
     * @throws Exception
     * @since 1.0.0
     */
    public function executeSqlFile(string $filename)
    {
        if(empty($filename)) {
            throw new \UnexpectedValueException('No filename is passed for sql execution');
        }
        $filePath = __DIR__ .'/dataset/'.$filename;
        if(is_file($filePath)) {
            $fileContents = @file_get_contents($filePath);
            $db = $this->getDbo();
            $db->select(JTEST_DB_NAME);
            $queries = $db::splitSql($fileContents);

            foreach ($queries as $query)
            {
                try
                {
                    $db->setQuery($query)->execute();
                }
                catch (Exception $e)
                {
                    // drop the tables as they will be re-created afresh for every test
                    $this->dropTables();
                    echo "Data Cannot be inserted";
                    throw $e;
                }
            }
        } else {
            throw new \UnexpectedValueException('The data sql file is either absent or invalid.');
        }
        return true;
    }

    /**
     * @return DatabaseDriver
     * @since 1.0.0
     */
    public function getDbo()
    {
        if($this->db === null) {
            $factory = new DatabaseFactory();
            $this->db = $factory->getDriver(JTEST_DB_ENGINE,
                [
                    'host' => JTEST_DB_HOST,
                    'user' => JTEST_DB_USER,
                    'password' => JTEST_DB_PASSWORD,
                    'prefix' => JTEST_DB_TABLE_PREFIX
                ]
            );
        }
        return $this->db;
    }

    /**
     * Create the database
     *
     * @return bool
     * @since 1.0.0
     */
    protected function createDatabase()
    {
        $db = $this->getDbo();
        $db->setQuery('CREATE DATABASE IF NOT EXISTS '. JTEST_DB_NAME)->execute();
        return true;
    }
}
