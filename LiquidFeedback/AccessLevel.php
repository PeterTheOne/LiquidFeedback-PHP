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
            case self::ANONYMOUS:
                if ($currentAccessLevel === self::ANONYMOUS) {
                    return true;
                }
            case self::PSEUDONYM:
                if ($currentAccessLevel === self::PSEUDONYM) {
                    return true;
                }
            case self::FULL:
                if ($currentAccessLevel === self::FULL) {
                    return true;
                }
            case self::MEMBER:
                if ($currentAccessLevel === self::MEMBER) {
                    return true;
                }
            default:
                return false;
        }
    }
} 