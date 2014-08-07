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

    public function getMemberApplicationByKey($key) {
        return $this->fpdo
            ->from('member_application')
            ->select(null)
            ->select('member.id')
            ->where('member.activated NOTNULL')
            ->where('member_application.key', $key)
            ->fetch();
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

    /**
     * @param \SelectQuery $statement
     */
    private function addUnitOptions(\SelectQuery $statement, $id, $parentId,
                                    $withoutParent, $disabled, $orderByPath) {
        if (isset($id)) {
            $statement->where('unit.id', $id);
        }
        if (isset($parentId)) {
            $statement->where('unit.parent_id = ?', $parentId);
        }
        if (isset($withoutParent) && $withoutParent) {
            $statement->where('unit.partent_id ISNULL');
        }
        if (isset($disabled)) {
            if ($disabled === 'only') {
                $statement->where('unit.active = FALSE');
            } else if ($disabled === 'include') {
                $statement->where('unit.active = TRUE');
            }
        }
        if (isset($orderByPath)) {
            $statement->orderBy('unit.name');
        }
    }

    /**
     * @param null $id
     * @param null $parentId
     * @param null $withoutParent
     * @param null $disabled
     * @param null $orderByPath
     * @return array
     */
    public function getUnit($id = null, $parentId = null, $withoutParent = null,
                            $disabled = null, $orderByPath = null) {
        $statement = $this->fpdo->from('unit')->select(null)->select([
            'unit.id', 'unit.parent_id', 'unit.active', 'unit.name',
            'unit.description', 'unit.member_count'
        ]);

        $this->addUnitOptions($statement, $id, $parentId, $withoutParent,
            $disabled, $orderByPath);

        return $statement->orderBy('id')->fetchAll();
    }

    private function addAreaOptions(\SelectQuery $statement, $id, $disabled, $orderByName,
                                    $unitId, $unitParentId, $unitWithoutParent,
                                    $unitDisabled, $unitOrderByPath) {
        $this->addUnitOptions($statement, $unitId, $unitParentId, $unitWithoutParent,
            $unitDisabled, $unitOrderByPath);

        if (isset($id)) {
            $statement->where('area.id', $id);
        }
        if (isset($disabled)) {
            if ($disabled === 'only') {
                $statement->where('area.active = FALSE');
            } else if ($disabled === 'include') {
                $statement->where('area.active = TRUE');
            }
        }

        // todo: area_my with access level check

        if (isset($orderByName)) {
            $statement->orderBy('area.name');
        }
    }

    /**
     * @param null $id
     * @param null $disabled
     * @param null $unitId
     * @param null $unitParentId
     * @param null $unitWithoutParent
     * @param null $unitDisabled
     * @param null $unitOrderByPath
     * @return array
     */
    public function getArea($id = null, $disabled = null, $orderByName = null,
                            $unitId = null, $unitParentId = null,
                            $unitWithoutParent = null, $unitDisabled = null,
                            $unitOrderByPath = null) {
        $statement = $this->fpdo
            ->from('area')
            ->leftJoin('unit ON area.unit_id = unit.id')
            ->select(null)
            ->select([
                'area.id', 'area.unit_id', 'area.active', 'area.name', 'area.description',
                'area.direct_member_count', 'area.member_weight'
            ]);

        $this->addAreaOptions($statement, $id, $disabled, $orderByName, $unitId,
            $unitParentId, $unitWithoutParent,
            $unitDisabled, $unitOrderByPath);

        // todo: add relatedData unit

        return $statement->orderBy('area.id')->fetchAll();
    }

    /**
     * @param \SelectQuery $statement
     */
    private function addIssueOptions(\SelectQuery $statement, $id = null,
                                     $state = null, $createdAfter = null,
                                     $createdBefore = null, $accepted = null,
                                     $acceptedAfter = null, $acceptedBefore = null,
                                     $halfFrozenAfter = null, $halfFrozenBefore = null,
                                     $closed = null, $closedAfter = null,
                                     $closedBefore = null, $cleaned = null,
                                     $cleanedAfter = null, $cleanedBefore = null,
                                     $stateTimeLeftBelow = null, $areaId = null,
                                     $areaDisabled = null, $areaOrderByName = null,
                                     $unitId = null, $unitParentId = null,
                                     $unitWithoutParent = null, $unitDisabled = null,
                                     $unitOrderByPath = null) { // todo: policy options
        $this->addAreaOptions($statement, $areaId, $areaDisabled, $areaOrderByName, $unitId,
            $unitParentId, $unitWithoutParent,
            $unitDisabled, $unitOrderByPath);

        // todo: add policy options

        if (isset($id)) {
            $statement->where('issue.id', $id);
        }

        // todo: issue.state

        // todo: rest
    }

    /**
     * @param null $id
     * @param null $state
     * @param null $createdAfter
     * @param null $createdBefore
     * @param null $accepted
     * @param null $acceptedAfter
     * @param null $acceptedBefore
     * @param null $halfFrozenAfter
     * @param null $halfFrozenBefore
     * @param null $closed
     * @param null $closedAfter
     * @param null $closedBefore
     * @param null $cleaned
     * @param null $cleanedAfter
     * @param null $cleanedBefore
     * @param null $stateTimeLeftBelow
     * @param null $areaId
     * @param null $areaDisabled
     * @param null $areaOrderByName
     * @param null $unitId
     * @param null $unitParentId
     * @param null $unitWithoutParent
     * @param null $unitDisabled
     * @param null $unitOrderByPath
     * @return array
     */
    public function getIssue($id = null, $state = null, $createdAfter = null,
                             $createdBefore = null, $accepted = null,
                             $acceptedAfter = null, $acceptedBefore = null,
                             $halfFrozenAfter = null, $halfFrozenBefore = null,
                             $closed = null, $closedAfter = null, $closedBefore = null,
                             $cleaned = null, $cleanedAfter = null, $cleanedBefore = null,
                             $stateTimeLeftBelow = null, $areaId = null,
                             $areaDisabled = null, $areaOrderByName = null,
                             $unitId = null, $unitParentId = null,
                             $unitWithoutParent = null, $unitDisabled = null,
                             $unitOrderByPath = null) { // todo: policy options
        $statement = $this->fpdo
            ->from('issue')
            ->leftJoin('policy ON policy.id = issue.policy_id')
            ->leftJoin('area ON area.id = issue.area_id')
            ->leftJoin('unit ON area.unit_id = unit.id')
            ->select(null)
            ->select([
                'issue.id', 'issue.area_id', 'issue.policy_id', 'issue.state', 'issue.created', 'issue.accepted',
                'issue.half_frozen', 'issue.fully_frozen', 'issue.closed', 'issue.cleaned',
                'issue.admission_time', 'issue.discussion_time', 'issue.verification_time',
                'issue.voting_time', 'issue.snapshot', 'issue.latest_snapshot_event', 'issue.population',
                'issue.voter_count', 'issue.status_quo_schulze_rank'
            ]);

        $this->addIssueOptions($statement);

        // todo: add relatedData area, unit, policy

        return $statement->orderBy('issue.id')->fetchAll();
    }
}

