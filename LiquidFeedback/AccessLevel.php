<?php

namespace LiquidFeedback;


class AccessLevel {

    const MEMBER = 0;
    const FULL = 1;
    const PSEUDONYM = 2;
    const ANONYMOUS = 3;
    const NONE = 4;

    /**
     * @param $accessLevel
     * @return bool
     */
    public static function validAccessLevel($accessLevel) {
        return $accessLevel === self::MEMBER ||
            $accessLevel === self::FULL ||
            $accessLevel === self::PSEUDONYM ||
            $accessLevel === self::ANONYMOUS ||
            $accessLevel === self::NONE;
    }

    /**
     * @param $requiredAccessLevel
     * @throws \Exception
     */
    public static function requireAccessLevel($currentAccessLevel, $requiredAccessLevel) {
        switch($requiredAccessLevel) {
            case self::ACCESS_LEVEL_ANONYMOUS:
                if ($currentAccessLevel === self::ACCESS_LEVEL_ANONYMOUS) {
                    return;
                }
            case self::ACCESS_LEVEL_PSEUDONYM:
                if ($currentAccessLevel === self::ACCESS_LEVEL_PSEUDONYM) {
                    return;
                }
            case self::ACCESS_LEVEL_FULL:
                if ($currentAccessLevel === self::ACCESS_LEVEL_FULL) {
                    return;
                }
            case self::ACCESS_LEVEL_MEMBER:
                if ($currentAccessLevel === self::ACCESS_LEVEL_MEMBER) {
                    return;
                }
            default:
                throw new \Exception('you don\'t have the required accessLevel');
        }
    }
} 