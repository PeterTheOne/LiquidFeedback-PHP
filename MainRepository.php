<?php

namespace LqfbPhpApi;

// todo: autoload?
require_once 'config.php';

class MainRepository {

    /**
     * @var \PDO
     */
    public $pdo;

    /**
     *
     */
    public function __construct() {
        // todo: inject config in a better way
        global $config;

        $this->pdo = new \PDO(
            'pgsql:host=' . $config->server->host .
            ';port=' . $config->server->port .
            ';dbname=' . $config->server->dbname .
            ';user=' . $config->server->user .
            ';password=' . $config->server->password
        );
        $this->pdo->setAttribute(\PDO::ATTR_ERRMODE, \PDO::ERRMODE_EXCEPTION);
        $this->pdo->setAttribute(\PDO::ATTR_DEFAULT_FETCH_MODE, \PDO::FETCH_OBJ);
    }

    /**
     * @return mixed
     */
    public function getLiquidFeedbackVersion() {
        $statement = $this->pdo->prepare('
            SELECT * FROM liquid_feedback_version;
        ');
        $statement->execute();
        return $statement->fetch();
    }

    /**
     * @return mixed
     */
    public function getMemberCount() {
        $statement = $this->pdo->prepare('
            SELECT * FROM member_count;
        ');
        $statement->execute();
        return $statement->fetch();
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
        // todo: access level
        $statement = $this->pdo->prepare('
            SELECT id, name FROM member ORDER BY id;
        ');
        $statement->execute();
        return $statement->fetchAll();
    }
}

