<?php

require_once '../vendor/autoload.php';
require_once '../LiquidFeedback/Repository.php';
require_once 'config.php';

class RepositoryTest extends PHPUnit_Extensions_Database_TestCase {

    /**
     * @var \PDO
     */
    private $pdo;

    public function getConnection() {
        global $config;

        $this->pdo = new \PDO(
            'pgsql:host=' . $config->server->host .
            ';port=' . $config->server->port .
            ';dbname=' . $config->server->dbname .
            ';user=' . $config->server->user .
            ';password=' . $config->server->password
        );
        return $this->createDefaultDBConnection($this->pdo);
    }

    public function getDataSet() {
        // todo: expand DataSet!
        return $this->createFlatXMLDataSet(dirname(__FILE__) . '/files/liquid_feedback.xml');
    }

    public function testMemberCount() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $this->assertEquals(0, $repository->getMemberCount());
    }
} 