<?php
// Shared input-format validators. Keep these deliberately small; validate names
// and email only. Do not use is_valid_name() on fields that legitimately contain
// digits or symbols (address, phone, IDs, notes, medical history, allergies).

// Letters (including accented Latin), spaces, hyphen, apostrophe, and period.
// Must start with a letter. Accepts real names like "O'Brien", "Mary-Jane",
// "Pena", "Cruz Jr.". Rejects digits and other special characters.
function is_valid_name(string $value): bool
{
    return preg_match('/^[A-Za-z\x{00C0}-\x{00FF}][A-Za-z\x{00C0}-\x{00FF} \'.\-]*$/u', trim($value)) === 1;
}

function is_valid_email(string $value): bool
{
    return filter_var(trim($value), FILTER_VALIDATE_EMAIL) !== false;
}

// Validate a set of name fields given as [label => value]. Empty values are
// skipped (callers keep their own required/empty checks). Returns an error
// message for the first invalid field, or null when all fields are valid.
function first_invalid_name(array $fields): ?string
{
    foreach ($fields as $label => $value) {
        if ($value !== '' && $value !== null && !is_valid_name($value)) {
            return $label . ' may only contain letters, spaces, hyphens, apostrophes, and periods.';
        }
    }
    return null;
}
