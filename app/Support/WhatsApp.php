<?php

namespace App\Support;

use App\Models\WhatsappTemplate;

class WhatsApp
{
    /**
     * Normaliza un teléfono chileno a formato internacional sin "+".
     * Acepta: +56912345678, 56912345678, 912345678, 9 1234 5678, etc.
     */
    public static function normalizePhone(?string $phone): ?string
    {
        if (!$phone) return null;

        // Quitar todo lo que no sea dígito
        $clean = preg_replace('/\D/', '', $phone);
        if ($clean === '') return null;

        // Si empieza con 56 y tiene 11 dígitos -> ya está OK
        if (str_starts_with($clean, '56') && strlen($clean) === 11) {
            return $clean;
        }

        // Si tiene 9 dígitos y empieza con 9 (celular CL) -> agregar 56
        if (strlen($clean) === 9 && str_starts_with($clean, '9')) {
            return '56' . $clean;
        }

        // Si tiene 8 dígitos -> asumir celular y agregar 569
        if (strlen($clean) === 8) {
            return '569' . $clean;
        }

        // Devolver como viene si ya parece internacional con 11+ dígitos
        if (strlen($clean) >= 11) return $clean;

        return null;
    }

    /**
     * Reemplaza placeholders {nombre}, {fecha}, etc. en una plantilla.
     */
    public static function render(string $body, array $vars): string
    {
        foreach ($vars as $key => $value) {
            $body = str_replace('{' . $key . '}', (string) $value, $body);
        }
        return $body;
    }

    /**
     * Construye el link wa.me con mensaje codificado.
     */
    public static function link(?string $phone, string $message): ?string
    {
        $phone = self::normalizePhone($phone);
        if (!$phone) return null;
        return 'https://wa.me/' . $phone . '?text=' . rawurlencode($message);
    }

    /**
     * Atajo: arma link a partir de una plantilla por key + variables.
     */
    public static function linkFromTemplate(?string $phone, string $templateKey, array $vars): ?string
    {
        $template = WhatsappTemplate::get($templateKey);
        if (!$template) return null;
        $message = self::render($template->body, $vars);
        return self::link($phone, $message);
    }
}
