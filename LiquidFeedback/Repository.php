<?php

namespace LiquidFeedback;

class Repository {

    /**
     * @var \PDO
     */
    public $pdo;

    /**
     * @param $pdo
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
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

