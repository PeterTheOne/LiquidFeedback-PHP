<?php

require_once '../vendor/autoload.php';
require_once '../LiquidFeedback/Repository.php';
require_once 'config.php';

class RepositoryTest extends PHPUnit_Extensions_Database_TestCase {

    /**
     * @var \PDO
     */
    private $pdo;

    // fix foreign key constraint, see: https://github.com/sebastianbergmann/dbunit/issues/56#issuecomment-4368206
    protected function getSetUpOperation() {
        return PHPUnit_Extensions_Database_Operation_Factory::CLEAN_INSERT(TRUE);
    }

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

    // todo: fix
    public function testGetInfo() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $this->assertNotNull($repository->getLiquidFeedbackVersion());
    }

    // todo: fix
    public function testGetMemberCount() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $this->assertEquals(0, $repository->getMemberCount());
    }

    public function testGetMemberPseudonymReturnsMemberById() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $excepted = array(array('id' => 1, 'name' => 'PeterTheOne'));
        $this->assertEquals($excepted, $repository->getMemberPseudonym(array(1), null, null));
    }

    public function testGetMemberPseudonymReturnsMulitpleMembersById() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $excepted = array(
            array('id' => 1, 'name' => 'PeterTheOne'),
            array('id' => 2, 'name' => 'Alf')
        );
        $this->assertEquals($excepted, $repository->getMemberPseudonym(array(1, 2), null, null));
    }

    public function testGetMemberPseudonymReturnsMembersOrderedByName() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $excepted = array(
            array('id' => 2, 'name' => 'Alf'),
            array('id' => 1, 'name' => 'PeterTheOne')
        );
        $this->assertEquals($excepted, $repository->getMemberPseudonym(null, true, null));
    }

    public function testGetMemberPseudonymReturnsMembersOrderedByCreated() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $excepted = array(
            array('id' => 2, 'name' => 'Alf'),
            array('id' => 1, 'name' => 'PeterTheOne')
        );
        $this->assertEquals($excepted, $repository->getMemberPseudonym(null, null, true));
    }

    public function testGetMemberReturnsMemberById() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $excepted = array(
            array('id' => 1, 'name' => 'PeterTheOne', 'active' => true,
                'locked' => false, 'last_activity' => '2010-04-14')
        );
        $actual = $repository->getMember(array(1), null, null, null, null);
        $this->assertEquals($excepted[0]['id'], $actual[0]['id']);
    }

    public function testGetMemberReturnsMulitpleMembersById() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $excepted = array(
            array('id' => 1, 'name' => 'PeterTheOne'),
            array('id' => 2, 'name' => 'Alf')
        );
        $actual = $repository->getMember(array(1, 2), null, null, null, null);
        $this->assertEquals($excepted[0]['id'], $actual[0]['id']);
        $this->assertEquals($excepted[1]['id'], $actual[1]['id']);
    }

    public function testGetMemberReturnsMembersByActive() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $excepted = array(
            array('id' => 1, 'name' => 'PeterTheOne', 'active' => true)
        );
        $actual = $repository->getMember(null, true, null, true, null);
        $this->assertEquals($excepted[0]['id'], $actual[0]['id']);
        $this->assertEquals($excepted[0]['active'], $actual[0]['active']);
    }

    // todo: test search
    /*public function testGetMemberReturnsMembersBySearch() {

    }*/

    public function testGetMemberReturnsMembersOrderedByName() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $excepted = array(
            array('id' => 2, 'name' => 'Alf'),
            array('id' => 1, 'name' => 'PeterTheOne')
        );
        $actual = $repository->getMember(null, null, null, true, null);
        $this->assertEquals($excepted[0]['id'], $actual[0]['id']);
        $this->assertEquals($excepted[1]['id'], $actual[1]['id']);
    }

    public function testGetMemberReturnsMembersOrderedByCreated() {
        $repository = new \LiquidFeedback\Repository($this->pdo);
        $excepted = array(
            array('id' => 2, 'name' => 'Alf'),
            array('id' => 1, 'name' => 'PeterTheOne')
        );
        $actual = $repository->getMember(null, null, null, null, true);
        $this->assertEquals($excepted[0]['id'], $actual[0]['id']);
        $this->assertEquals($excepted[1]['id'], $actual[1]['id']);
    }
} 