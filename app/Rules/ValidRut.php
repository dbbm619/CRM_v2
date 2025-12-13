<?php

namespace App\Rules;

use Illuminate\Contracts\Validation\Rule;

class ValidRut implements Rule
{
    public function passes($attribute, $value)
    {
        if (is_null($value) || $value === '') return false;

        // Normalizar: quitar puntos y guión, mayúscula la K
        $rut = preg_replace('/[^0-9kK]/', '', $value);
        $rut = strtoupper($rut);

        if (strlen($rut) < 2) return false;

        $dv = substr($rut, -1);
        $num = substr($rut, 0, -1);

        // Validar que num sea sólo dígitos
        if (!ctype_digit($num)) return false;

        // Calcular dígito verificador con factores 2..7
        $suma = 0;
        $factor = 2;
        for ($i = strlen($num) - 1; $i >= 0; $i--) {
            $suma += intval($num[$i]) * $factor;
            $factor = ($factor == 7) ? 2 : $factor + 1;
        }

        $resto = $suma % 11;
        $calculated = 11 - $resto;

        if ($calculated == 11) $dvCalc = '0';
        elseif ($calculated == 10) $dvCalc = 'K';
        else $dvCalc = (string) $calculated;

        return $dvCalc === $dv;
    }

    public function message()
    {
        return 'El RUT ingresado no es válido.';
    }
}