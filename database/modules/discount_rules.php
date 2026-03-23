<?php

if (!function_exists('discount_allowed_id_types_catalog')) {
    function discount_allowed_id_types_catalog(): array
    {
        return [
            'national_id' => 'National ID (PhilSys ID / ePhilID)',
            'passport' => 'Passport (Philippine or foreign, if applicable)',
            'drivers_license' => "Driver's License (LTO)",
            'umid' => 'UMID Card (SSS / GSIS)',
            'prc_id' => 'PRC ID (Professional Regulation Commission)',
            'voters_id' => "Voter's ID/Certification (COMELEC)",
            'postal_id' => 'Postal ID',
            'philhealth_id' => 'PhilHealth ID',
            'tin_id' => 'TIN ID (BIR)',
            'school_id' => 'School ID',
            'alumni_id' => 'Alumni ID',
            'personnel_id' => 'Personnel ID',
            'senior_id' => 'Senior Citizen ID',
            'pwd_id' => 'PWD ID'
        ];
    }
}

if (!function_exists('discount_parse_json_array')) {
    function discount_parse_json_array($value): array
    {
        if (!is_string($value) || trim($value) === '') {
            return [];
        }
        $decoded = json_decode($value, true);
        if (!is_array($decoded)) {
            return [];
        }
        return array_values(array_filter(array_map('strval', $decoded), static function ($v) {
            return $v !== '';
        }));
    }
}

if (!function_exists('discount_get_rules')) {
    function discount_get_rules(mysqli $conn, bool $activeOnly = false): array
    {
        $sql = "SELECT id, code, label, description, percentage, accepted_id_types, keywords, is_active
                FROM discount_rules";
        if ($activeOnly) {
            $sql .= " WHERE is_active = 1";
        }
        $sql .= " ORDER BY percentage DESC, label ASC";

        $rows = [];
        $res = $conn->query($sql);
        if (!$res) {
            return $rows;
        }
        while ($r = $res->fetch_assoc()) {
            $r['id'] = (int) $r['id'];
            $r['percentage'] = (float) $r['percentage'];
            $r['is_active'] = (int) $r['is_active'];
            $r['accepted_id_types'] = discount_parse_json_array($r['accepted_id_types'] ?? '');
            $r['keywords'] = discount_parse_json_array($r['keywords'] ?? '');
            $rows[] = $r;
        }
        $res->free();
        return $rows;
    }
}

if (!function_exists('discount_get_rule_map')) {
    function discount_get_rule_map(mysqli $conn, bool $activeOnly = false): array
    {
        $rules = discount_get_rules($conn, $activeOnly);
        $map = [];
        foreach ($rules as $r) {
            $map[$r['code']] = $r;
        }
        return $map;
    }
}

if (!function_exists('discount_rule_accepts_id_type')) {
    function discount_rule_accepts_id_type(array $rule, string $idType): bool
    {
        $accepted = $rule['accepted_id_types'] ?? [];
        if (!is_array($accepted) || count($accepted) === 0) {
            return true;
        }
        if ($idType === '') {
            return false;
        }
        return in_array($idType, $accepted, true);
    }
}

if (!function_exists('discount_get_id_type_options_from_rules')) {
    function discount_get_id_type_options_from_rules(array $rules): array
    {
        $catalog = discount_allowed_id_types_catalog();
        $allowedSet = [];

        foreach ($rules as $rule) {
            $accepted = $rule['accepted_id_types'] ?? [];
            if (!is_array($accepted)) {
                continue;
            }

            foreach ($accepted as $idType) {
                $idType = trim((string) $idType);
                if ($idType !== '') {
                    $allowedSet[$idType] = true;
                }
            }
        }

        // If rules do not define accepted types, fall back to full catalog.
        if (count($allowedSet) === 0) {
            return $catalog;
        }

        $options = [];

        // Keep known types in catalog order.
        foreach ($catalog as $code => $label) {
            if (isset($allowedSet[$code])) {
                $options[$code] = $label;
            }
        }

        // Include unknown custom types from DB with a readable fallback label.
        foreach (array_keys($allowedSet) as $code) {
            if (!isset($options[$code])) {
                $options[$code] = ucwords(str_replace('_', ' ', $code));
            }
        }

        return $options;
    }
}
