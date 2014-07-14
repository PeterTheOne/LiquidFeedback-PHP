<?php

namespace LiquidFeedback;

class Repository {

    /**
     * @var \PDO
     */
    public $pdo;

    /**
     * @var \FluentPDO
     */
    public $fpdo;

    /**
     * @param $pdo
     */
    public function __construct($pdo) {
        $this->pdo = $pdo;
        $this->fpdo = new \FluentPDO($this->pdo);
    }

    /**
     * @return mixed
     */
    public function getLiquidFeedbackVersion() {
        return $this->fpdo->from('liquid_feedback_version')->fetchAll();
    }

    /**
     * @return mixed
     */
    public function getMemberCount() {
        return $this->fpdo->from('member_count')->fetch();
    }

    /**
     * @param null $id
     * @param null $orderByName
     * @param null $orderByCreated
     * @return array
     */
    public function getMemberPseudonym($id = null, $orderByName = null,
                                       $orderByCreated = null) {
        $statement = $this->fpdo->from('member')->select(null)->select(['id', 'name']);
        if (isset($id)) {
            $statement->where('id', $id);
        }
        if (isset($orderByName)) {
            $statement->orderBy('name');
        }
        if (isset($orderByCreated)) {
            $statement->orderBy('created DESC');
        }
        return $statement->orderBy('id')->fetchAll();
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
        $statement = $this->fpdo->from('member')->select(null)->select([
            'id', 'name', 'organizational_unit', 'internal_posts', 'realname',
            'birthday', 'address', 'email', 'xmpp_address', 'website',
            'phone', 'mobile_phone', 'profession', 'external_memberships',
            'external_posts', 'statement', 'active', 'locked', 'created',
            'last_activity'
        ]);
        if (isset($id)) {
            $statement->where('id', $id);
        }
        if (isset($active)) {
            if ($active) {
                $statement->where('active = :active OR active ISNULL', array(':active' => true));
            } else if (!$active) {
                $statement->where('active = :active', array(':active' => false));
            }
        }
        if (isset($search)) {
            $statement->where('text_search_data @@ text_search_query(:search)', array(':search' => $search));
        }
        if (isset($orderByName)) {
            $statement->orderBy('name');
        }
        if (isset($orderByCreated)) {
            $statement->orderBy('created DESC');
        }
        return $statement->orderBy('id')->fetchAll();
    }
}

