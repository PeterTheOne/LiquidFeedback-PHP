<?php

namespace LiquidFeedback;

// todo: autoload?
require_once 'Repository.php';

class LiquidFeedback {

    /**
     * @var \PDO
     */
    private $pdo;

    /**
     * @var Repository
     */
    private $repository;

    const ACCESS_LEVEL_MEMBER = 0;
    const ACCESS_LEVEL_FULL = 1;
    const ACCESS_LEVEL_PSEUDONYM = 2;
    const ACCESS_LEVEL_ANONYMOUS = 3;
    const ACCESS_LEVEL_NONE = 4;

    /**
     * @var
     */
    private $currentAccessLevel;

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

        $this->currentAccessLevel = self::ACCESS_LEVEL_NONE;
        $this->currentMemberId = null;
    }

    /**
     * @param $accessLevel
     */
    public function setCurrentAccessLevel($accessLevel) {
        if ($accessLevel === self::ACCESS_LEVEL_MEMBER ||
                $accessLevel === self::ACCESS_LEVEL_FULL ||
                $accessLevel === self::ACCESS_LEVEL_PSEUDONYM ||
                $accessLevel === self::ACCESS_LEVEL_ANONYMOUS ||
                $accessLevel === self::ACCESS_LEVEL_NONE) {
            $this->currentAccessLevel = $accessLevel;
        }
    }

    /**
     * @param $currentMemberId
     */
    public function setCurrentMemberId($currentMemberId) {
        $this->currentMemberId = $currentMemberId;
    }

    /**
     * @param $requiredAccessLevel
     * @throws \Exception
     */
    private function requireAccessLevel($requiredAccessLevel) {
        switch($requiredAccessLevel) {
            case self::ACCESS_LEVEL_ANONYMOUS:
                if ($this->currentAccessLevel === self::ACCESS_LEVEL_ANONYMOUS) {
                    return;
                }
            case self::ACCESS_LEVEL_PSEUDONYM:
                if ($this->currentAccessLevel === self::ACCESS_LEVEL_PSEUDONYM) {
                    return;
                }
            case self::ACCESS_LEVEL_FULL:
                if ($this->currentAccessLevel === self::ACCESS_LEVEL_FULL) {
                    return;
                }
            case self::ACCESS_LEVEL_MEMBER:
                if ($this->currentAccessLevel === self::ACCESS_LEVEL_MEMBER) {
                    return;
                }
            default:
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
        $this->requireAccessLevel(self::ACCESS_LEVEL_ANONYMOUS);
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
        $this->requireAccessLevel(self::ACCESS_LEVEL_ANONYMOUS);
        return $this->repository->getMemberCount();
    }

    /**
     * @return array
     */
    public function getContingent() {
        $this->requireAccessLevel(self::ACCESS_LEVEL_ANONYMOUS);
        return $this->repository->getContingent();
    }

    /**
     * @return mixed
     */
    public function getContingentLeft() {
        $this->requireAccessLevel(self::ACCESS_LEVEL_MEMBER);
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
        $this->requireAccessLevel(self::ACCESS_LEVEL_PSEUDONYM);
        if ($this->currentAccessLevel === self::ACCESS_LEVEL_PSEUDONYM) {
            return $this->repository->getMemberPseudonym($id, $orderByName, $orderByCreated);
        }
        return $this->repository->getMember($id, $active, $search, $orderByName, $orderByCreated);
    }


}

