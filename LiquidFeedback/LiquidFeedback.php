<?php

namespace LiquidFeedback;

// todo: autoload?
require_once 'Repository.php';
require_once 'AccessLevel.php';

class LiquidFeedback {

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var Repository
     */
    private $repository;

    /**
     * @var
     */
    private $currentAccessLevel;

    /**
     * @var null
     */
    private $currentMemberId;

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

        $this->currentAccessLevel = \LiquidFeedback\AccessLevel::NONE;
    }

    /**
     * @param $accessLevel
     */
    public function setCurrentAccessLevel($accessLevel, $currentMemberId = null) {
        if (!AccessLevel::validAccessLevel($accessLevel)) {
            throw new \Exception('Invalid AccessLevel');
        }
        $this->currentAccessLevel = $accessLevel;
        $this->currentMemberId = $currentMemberId;
        if ($this->currentAccessLevel !== AccessLevel::MEMBER) {
            $this->currentMemberId = null;
        }
    }

    /**
     * @param $requiredAccessLevel
     */
    private function requireAccessLevel($requiredAccessLevel) {
        if (!AccessLevel::requireAccessLevel($this->currentAccessLevel, $requiredAccessLevel)) {
            throw new \Exception('you don\'t have the required accessLevel');
        }
    }

    /**
     * @param $login
     * @param $password
     */
    public function login($login, $password) {
        $member = $this->repository->getMemberByLoginAndPassword($login);
        if ($member && $this->checkPassword($password, $member->password)) {

            // todo: set last login = now
            // todo: delegations to check for member id
            // todo: set last delegation check = now
            // todo: set active = true
            // todo: rehash password if hash needs update



            unset($member->password);
            unset($member->needs_delegation_check_hard);
            return $member;
        }
        return null;
    }

    private function checkPassword($formPassword, $databasePassword) {
        return crypt($formPassword, $databasePassword) === $databasePassword;
    }

    /**
     * @return mixed
     */
    public function getInfo() {
        $this->requireAccessLevel(\LiquidFeedback\AccessLevel::ANONYMOUS);
        $result = $this->repository->getLiquidFeedbackVersion();
        $result->core_version = $result->string;
        unset($result->string);
        $result->current_access_level = $this->currentAccessLevel;
        $result->current_member_id = $this->currentMemberId;
        return $result;
    }

    /**
     * @return mixed
     */
    public function getMemberCount() {
        $this->requireAccessLevel(\LiquidFeedback\AccessLevel::ANONYMOUS);
        return $this->repository->getMemberCount();
    }

    /**
     * @return array
     */
    public function getContingent() {
        $this->requireAccessLevel(\LiquidFeedback\AccessLevel::ANONYMOUS);
        return $this->repository->getContingent();
    }

    /**
     * @return mixed
     */
    public function getContingentLeft() {
        $this->requireAccessLevel(\LiquidFeedback\AccessLevel::MEMBER);
        return $this->repository->getContingentLeft($this->currentMemberId);
    }

    /**
     * @param null $id
     * @param null $active
     * @param null $search
     * @param null $orderByName
     * @param null $orderByCreated
     * @return array
     */
    public function getMember($id = null, $active = null, $search = null,
                              $orderByName = null, $orderByCreated = null) {
        $this->requireAccessLevel(\LiquidFeedback\AccessLevel::PSEUDONYM);
        if ($this->currentAccessLevel === \LiquidFeedback\AccessLevel::PSEUDONYM) {
            return $this->repository->getMemberPseudonym($id, $orderByName, $orderByCreated);
        }
        return $this->repository->getMember($id, $active, $search, $orderByName, $orderByCreated);
    }


}

