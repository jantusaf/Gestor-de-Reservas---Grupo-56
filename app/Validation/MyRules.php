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

        public function check_future_or_today(string $str, string &$error = null): bool {
        if (strtotime($str) < strtotime(date('Y-m-d'))) {
            $error = 'La fecha no puede ser anterior a hoy.';
            return false;
        }
        return true;
    }
}
