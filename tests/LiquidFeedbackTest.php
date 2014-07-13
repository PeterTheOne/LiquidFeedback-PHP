<?php

require_once '../vendor/autoload.php';
require_once '../LiquidFeedback/LiquidFeedback.php';
require_once 'config.php';

class LiquidFeedbackTest extends PHPUnit_Framework_TestCase {

    /**
     * @var \LiquidFeedback\LiquidFeedback
     */
    private $lqfb;

    protected function setUp() {
        global $config;

        $this->lqfb = new \LiquidFeedback\LiquidFeedback($config->server->host,
            $config->server->port, $config->server->dbname,
            $config->server->user, $config->server->password
        );
    }

    protected function tearDown() {
        unset($pdo);
    }

    public function testGetLiquidFeedbackVersionAccessLevel() {
        $this->lqfb->setCurrentAccessLevel(\LiquidFeedback\LiquidFeedback::ACCESS_LEVEL_ANONYMOUS);
        $this->lqfb->getLiquidFeedbackVersion();
        $this->lqfb->setCurrentAccessLevel(\LiquidFeedback\LiquidFeedback::ACCESS_LEVEL_PSEUDONYM);
        $this->lqfb->getLiquidFeedbackVersion();
        $this->lqfb->setCurrentAccessLevel(\LiquidFeedback\LiquidFeedback::ACCESS_LEVEL_FULL);
        $this->lqfb->getLiquidFeedbackVersion();
        $this->lqfb->setCurrentAccessLevel(\LiquidFeedback\LiquidFeedback::ACCESS_LEVEL_MEMBER);
        $this->lqfb->getLiquidFeedbackVersion();

        $this->setExpectedException('Exception');
        $this->lqfb->setCurrentAccessLevel(\LiquidFeedback\LiquidFeedback::ACCESS_LEVEL_NONE);
        $this->lqfb->getLiquidFeedbackVersion();
    }
} 