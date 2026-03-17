<?php

if (!function_exists('admin_badge_normalize')) {
    function admin_badge_normalize($value)
    {
        return strtolower(trim((string) $value));
    }
}

if (!function_exists('admin_badge_inline_style')) {
    /**
     * Returns a consistent inline style string for admin badges.
     * No animations, no blinking — just clean solid pills.
     */
    function admin_badge_inline_style($fontSize = '0.65rem')
    {
        return "font-size: {$fontSize}; padding: 0.35rem 0.6rem; border-radius: 0.75rem; font-weight: 600; letter-spacing: 0.02em; white-space: nowrap;";
    }
}

if (!function_exists('admin_badge_html')) {
    /**
     * Returns a full badge <span> with proper class and inline style.
     */
    function admin_badge_html($label, $bgClass, $fontSize = '0.65rem')
    {
        $style = admin_badge_inline_style($fontSize);
        $escaped = htmlspecialchars($label);
        return "<span class=\"badge bg-{$bgClass}\" style=\"{$style}\">{$escaped}</span>";
    }
}

if (!function_exists('admin_badge_booking_status_class')) {
    function admin_badge_booking_status_class($status)
    {
        $map = [
            'pending' => 'warning',
            'approved' => 'success',
            'confirmed' => 'info',
            'checked_in' => 'primary',
            'checked_out' => 'secondary',
            'cancelled' => 'warning',
            'rejected' => 'danger',
            'expired' => 'secondary',
        ];

        $key = admin_badge_normalize($status);
        return $map[$key] ?? 'secondary';
    }
}

if (!function_exists('admin_badge_discount_status_class')) {
    function admin_badge_discount_status_class($status)
    {
        $map = [
            'pending' => 'warning',
            'approved' => 'success',
            'rejected' => 'danger',
            'none' => 'secondary',
        ];

        $key = admin_badge_normalize($status);
        return $map[$key] ?? 'secondary';
    }
}

if (!function_exists('admin_badge_booking_type')) {
    function admin_badge_booking_type($type)
    {
        $key = admin_badge_normalize($type);
        if ($key === 'reservation' || $key === '') {
            return ['class' => 'primary', 'label' => 'Reserve'];
        }

        return ['class' => 'warning', 'label' => 'Pencil'];
    }
}

if (!function_exists('admin_badge_item_type')) {
    function admin_badge_item_type($itemType)
    {
        $key = admin_badge_normalize($itemType);
        if ($key !== 'room' && $key !== 'facility') {
            $key = 'room';
        }

        return $key;
    }
}
