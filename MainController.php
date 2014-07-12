<?php

namespace LqfbPhpApi;

// todo: autoload?
require_once 'MainRepository.php';

class MainController {

    /**
     * @var MainRepository
     */
    public $repository;

    /**
     *
     */
    public function __construct() {
        $this->repository = new MainRepository();
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

