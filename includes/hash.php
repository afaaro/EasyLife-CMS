<?php
//Deny direct access
defined("_LOAD") or die("Access denied");

/**
 * hash class
 * Creates and verifies hashes
 */
class Hash {

    /**
     * Creates a new hash
     *
     * @param string $value  string to hash
     * @param int    $rounds (optional) hashing rounds to apply
     *
     * @return bool|string
     */
    public static function make($value, $rounds = 12) {
        return password_hash($value, PASSWORD_BCRYPT, ['cost' => $rounds]);
    }

    /**
     * Verifies a hash
     *
     * @param string $value value to verify the hash against
     * @param string $hash  hash to check
     *
     * @return bool whether the hash is valud
     */
    public static function check($value, $hash) {
        return password_verify($value, $hash);
    }
}

