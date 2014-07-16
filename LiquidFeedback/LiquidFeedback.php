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

        $this->currentAccessLevel = AccessLevel::NONE;
    }

    /**
     * @param $accessLevel
     */
    public function setCurrentAccessLevel($accessLevel, $currentMemberId = null) {
        if (!AccessLevel::validAccessLevel($accessLevel)) {
            throw new \Exception('Invalid AccessLevel');
        }
        $this->currentAccessLevel = $accessLevel;
        $this->currentMemberId = null;
        if ($this->currentAccessLevel === AccessLevel::MEMBER) {
            if ($currentMemberId === null) {
                throw new \Exception('MemberId is required.');
            }
            $this->currentMemberId = $currentMemberId;
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


            $this->setCurrentAccessLevel(AccessLevel::MEMBER, $member->id);
            unset($member->password);
            unset($member->needs_delegation_check_hard);
            return $member;
        }
        return null;
    }

    /**
     * @param $formPassword
     * @param $databasePassword
     * @return bool
     */
    private function checkPassword($formPassword, $databasePassword) {
        return crypt($formPassword, $databasePassword) === $databasePassword;
    }

    public function startSession($key) {
        if (!isset($key)) {
            throw new \Exception('No application key supplied.');
        }
        // todo: fetch key and compare
        $memberApplication = $this->repository->getMemberApplicationByKey($key);
        if (!$memberApplication) {
            throw new \Exception('Supplied application key is not valid.');
        }

        $this->setCurrentAccessLevel(AccessLevel::MEMBER, $memberApplication->id);
        $memberApplication->session_key = $this->randomString(16);
        return $memberApplication;
    }

    private function randomString($length = 32) {
        $characters = '0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ';
        $randomString = '';
        for ($i = 0; $i < $length; $i++) {
            $randomInt = rand(0, strlen($characters) - 1);
            $randomString .= $characters[$randomInt];
        }
        return $randomString;
    }

    /**
     * @return mixed
     */
    public function getInfo() {
        $this->requireAccessLevel(AccessLevel::ANONYMOUS);
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
        $this->requireAccessLevel(AccessLevel::ANONYMOUS);
        return $this->repository->getMemberCount();
    }

    /**
     * @return array
     */
    public function getContingent() {
        $this->requireAccessLevel(AccessLevel::ANONYMOUS);
        return $this->repository->getContingent();
    }

    /**
     * @return mixed
     */
    public function getContingentLeft() {
        $this->requireAccessLevel(AccessLevel::MEMBER);
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
        $this->requireAccessLevel(AccessLevel::PSEUDONYM);
        if ($this->currentAccessLevel === AccessLevel::PSEUDONYM) {
            return $this->repository->getMemberPseudonym($id, $orderByName, $orderByCreated);
        }
        return $this->repository->getMember($id, $active, $search, $orderByName, $orderByCreated);
    }


}

