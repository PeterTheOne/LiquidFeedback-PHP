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
        return $this->fpdo->from('liquid_feedback_version')->fetch();
    }

    /**
     * @return mixed
     */
    public function getMemberCount() {
        return $this->fpdo->from('member_count')->fetch();
    }

    /**
     * @param $login
     * @param $password
     * @return array
     */
    public function getMemberByLoginAndPassword($login) {
        // todo: fetch more parameters?
        return $this->fpdo
            ->from('member')
            ->select(null)
            ->select(['id'])
            ->select(['login'])
            ->select(['password'])                                  // todo: get interval from config
            ->select(['now() > COALESCE(last_delegation_check, activated) + \'' . '1 day' . '\'::interval AS needs_delegation_check_hard'])
            ->where('login', $login)
            ->where('NOT "locked"')
            ->fetch();
    }

    /**
     * @param null $id
     * @param null $orderByName
     * @param null $orderByCreated
     * @return mixed
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
        return $statement->orderBy('id')->fetch();
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
                $statement->where('active = TRUE OR active ISNULL');
            } else if (!$active) {
                $statement->where('active = FALSE');
            }
        }
        if (isset($search)) {
            $statement->where('text_search_data @@ text_search_query(?)', $search);
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
     * @return array
     */
    public function getContingent() {
        return $this->fpdo->from('contingent')->fetchAll();
    }

    /**
     * @param $currentMemberId
     * @return mixed
     */
    public function getContingentLeft($currentMemberId) {
        return $this->fpdo
            ->from('member_contingent_left')
            ->select(null)
            ->select(array('text_entries_left', 'initiatives_left'))
            ->where('member_id = ?', $currentMemberId)
            ->fetch();
    }
}

