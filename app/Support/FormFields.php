<?php

namespace App\Support;

use App\Rules\TrustedEmailDomain;
use Illuminate\Support\Str;

/**
 * Field definitions for the form builder: which types exist, how a saved field list is
 * cleaned, and how a submitted payload is validated and snapshotted.
 */
class FormFields
{
    public const TYPES = [
        'text' => 'Short answer',
        'textarea' => 'Long answer',
        'email' => 'Email',
        'tel' => 'Phone',
        'number' => 'Number',
        'date' => 'Date',
        'select' => 'Dropdown',
        'radio' => 'Single choice',
        'checkbox' => 'Multiple choice',
        'slot' => 'Date & time slot (booking)',
    ];

    /** Which Brevo attribute a field feeds. Email is automatic. */
    public const MAPS = [
        '' => 'Extra column',
        'FIRSTNAME' => 'First name',
        'LASTNAME' => 'Last name',
        'FULLNAME' => 'Full name',
        'SMS' => 'Phone (SMS)',
    ];

    public const CHOICE_TYPES = ['select', 'radio', 'checkbox'];

    /** Live DNS checks need the network, so they are skipped only while tests run. */
    public static function emailRule(): string
    {
        return app()->runningUnitTests() ? 'email:rfc' : 'email:rfc,dns';
    }

    /** Clean the field list coming from the builder. */
    public static function normalize(array $fields): array
    {
        $seen = [];
        $out = [];

        foreach ($fields as $raw) {
            if (!is_array($raw)) {
                continue;
            }
            $type = array_key_exists($raw['type'] ?? '', self::TYPES) ? $raw['type'] : 'text';
            $label = Str::limit(trim((string) ($raw['label'] ?? '')), 120, '');
            if ($label === '') {
                $label = self::TYPES[$type];
            }

            $key = preg_replace('/[^a-z0-9_]/', '', strtolower((string) ($raw['key'] ?? '')));
            if ($key === '' || isset($seen[$key])) {
                $key = 'f_' . Str::lower(Str::random(6));
            }
            $seen[$key] = true;

            $options = [];
            if (in_array($type, self::CHOICE_TYPES, true)) {
                foreach ((array) ($raw['options'] ?? []) as $option) {
                    $option = Str::limit(trim((string) $option), 120, '');
                    if ($option !== '') {
                        $options[] = $option;
                    }
                }
                $options = array_values(array_unique($options));
            }

            $maps = (string) ($raw['maps_to'] ?? '');
            if (!array_key_exists($maps, self::MAPS) || $type === 'email') {
                $maps = '';
            }

            $out[] = [
                'key' => $key,
                'label' => $label,
                'type' => $type,
                'required' => filter_var($raw['required'] ?? false, FILTER_VALIDATE_BOOLEAN),
                'placeholder' => Str::limit(trim((string) ($raw['placeholder'] ?? '')), 160, ''),
                'help' => Str::limit(trim((string) ($raw['help'] ?? '')), 240, ''),
                'options' => $options,
                'maps_to' => $maps,
            ];
        }

        return $out;
    }

    /** Laravel validation rules for a submitted form. Keys are namespaced under "answers". */
    public static function rules(array $fields): array
    {
        $rules = [];

        foreach ($fields as $field) {
            $key = 'answers.' . $field['key'];
            $base = $field['required'] ? ['required'] : ['nullable'];

            switch ($field['type']) {
                case 'email':
                    $rules[$key] = [...$base, self::emailRule(), 'max:190', new TrustedEmailDomain()];
                    break;
                case 'tel':
                    $rules[$key] = [...$base, 'string', 'max:30', 'regex:/^[0-9+()\-\s.]{5,30}$/'];
                    break;
                case 'number':
                    $rules[$key] = [...$base, 'numeric'];
                    break;
                case 'date':
                    $rules[$key] = [...$base, 'date'];
                    break;
                case 'textarea':
                    $rules[$key] = [...$base, 'string', 'max:5000'];
                    break;
                case 'select':
                case 'radio':
                    $rules[$key] = [...$base, 'string', 'in:' . implode(',', array_map(fn ($o) => str_replace(',', ' ', $o), $field['options']))];
                    break;
                case 'checkbox':
                    $rules[$key] = [...$base, 'array'];
                    $rules[$key . '.*'] = ['string', 'in:' . implode(',', array_map(fn ($o) => str_replace(',', ' ', $o), $field['options']))];
                    break;
                case 'slot':
                    $rules[$key] = ['required', 'string', 'max:30'];
                    break;
                default:
                    $rules[$key] = [...$base, 'string', 'max:500'];
            }
        }

        return $rules;
    }

    public static function attributeNames(array $fields): array
    {
        $names = [];
        foreach ($fields as $field) {
            $names['answers.' . $field['key']] = strtolower($field['label']);
        }

        return $names;
    }

    /**
     * Freeze the answers with their labels, so later edits to the form never rewrite history.
     * Returns [snapshot rows, email, display name].
     */
    public static function snapshot(array $fields, array $answers): array
    {
        $rows = [];
        $email = null;
        $first = null;
        $last = null;
        $full = null;

        foreach ($fields as $field) {
            $value = $answers[$field['key']] ?? null;
            if (is_array($value)) {
                $value = array_values(array_filter(array_map('strval', $value)));
            } elseif ($value !== null) {
                $value = trim((string) $value);
            }
            if ($value === null || $value === '' || $value === []) {
                continue;
            }

            $row = [
                'key' => $field['key'],
                'label' => $field['label'],
                'type' => $field['type'],
                'maps_to' => $field['maps_to'] ?? '',
                'value' => $value,
            ];
            if ($field['type'] === 'slot') {
                $svc = app(\App\Services\BookingService::class);
                $at = $svc->parse((string) $value);
                $row['raw'] = $value;
                $row['value'] = $at ? $svc->label($at) : $value;
            }
            $rows[] = $row;

            if ($field['type'] === 'email' && $email === null) {
                $email = strtolower((string) $value);
            }
            match ($field['maps_to'] ?? '') {
                'FIRSTNAME' => $first = $first ?? (string) $value,
                'LASTNAME' => $last = $last ?? (string) $value,
                'FULLNAME' => $full = $full ?? (string) $value,
                default => null,
            };
        }

        $name = $full ?? trim(($first ?? '') . ' ' . ($last ?? '')) ?: null;

        return [$rows, $email, $name !== null ? Str::limit($name, 190, '') : null];
    }
}
