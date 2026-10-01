@props(['disabled' => false])

@php
    $inputType = strtolower((string) $attributes->get('type', 'text'));
    $fieldName = (string) $attributes->get('name', $attributes->get('id', ''));
    $sensitiveType = in_array($inputType, ['email', 'password', 'url', 'hidden', 'number', 'tel'], true);
    $sensitiveName = (bool) preg_match('/account[_-]?id|email|password|username|token|otp|pin|code|slug/i', $fieldName);
    $personName = (bool) preg_match('/^(name|first_name|last_name|display_name|leader)$/i', $fieldName);
    $defaultAutocapitalize = ($sensitiveType || $sensitiveName || ! $personName) ? 'none' : 'words';
@endphp

<input
    @disabled($disabled)
    {{ $attributes->merge([
        'class' => 'border-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-md shadow-sm',
        'autocapitalize' => $defaultAutocapitalize,
    ]) }}
>
