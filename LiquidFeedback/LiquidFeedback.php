<?php

namespace LiquidFeedback;

// todo: autoload?
require_once 'Repository.php';

class LiquidFeedback {

    /**
     * @var \PDO
     */
    public $pdo;

    /**
     * @var Repository
     */
    public $repository;

    /**
     * @param $host
     * @param $port
     * @param $dbname
     * @param $user
     * @param $password
     */
    public function __construct($host, $port, $dbname, $user, $password) {
        $this->pdo = new \PDO(
            'pgsql:host=' . $host .
            ';port=' . $port .
            ';dbname=' . $dbname .
            ';user=' . $user .
            ';password=' . $password
        );
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
        $this->repository = new Repository($this->pdo);
    }

    /**
     * @return mixed
     */
    public function getLiquidFeedbackVersion() {
        $liquidFeedbackVersion = $this->repository->getLiquidFeedbackVersion();
        $liquidFeedbackVersion->core_version = $liquidFeedbackVersion->string;
        unset($liquidFeedbackVersion->string);
        return $liquidFeedbackVersion;
    }

    /**
     * @return mixed
     */
    public function getMemberCount() {
        return $this->repository->getMemberCount();
    }

    /**
     * @param null $id
     * @param null $active
     * @param null $search
     * @param null $orderByName
     * @param null $orderByCreated
     * @param null $renderStatement
     * @return array
     */
    public function getMember($id = null, $active = null, $search = null,
                              $orderByName = null, $orderByCreated = null,
                              $renderStatement = null) {
        // todo: do something with the parameters
        return $this->repository->getMember();
    }


}

