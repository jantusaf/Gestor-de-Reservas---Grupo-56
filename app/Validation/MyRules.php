<?php
namespace App\Validation;

class MyRules {
    public function check_past_date(string $str, string &$error = null): bool {
        if (strtotime($str) > time()) {
            $error = 'La fecha no puede ser futura.';
            return false;
        }
        return true;
    }
}
