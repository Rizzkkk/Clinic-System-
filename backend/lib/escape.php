<?php
// HTML-escaping helper for safe output in views.

function h($value): string
{
    return htmlspecialchars((string) $value, ENT_QUOTES, 'UTF-8');
}
