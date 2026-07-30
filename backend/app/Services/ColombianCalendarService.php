<?php

namespace App\Services;

use DateTime;

class ColombianCalendarService
{
    /**
     * Determina si una fecha dada es un día escolar hábil (Lunes a Viernes no festivo).
     */
    public static function isSchoolDay(string $dateStr): bool
    {
        return !self::isWeekend($dateStr) && !self::isColombianHoliday($dateStr);
    }

    /**
     * Determina si la fecha cae en Sábado (6) o Domingo (7).
     */
    public static function isWeekend(string $dateStr): bool
    {
        $dt = new DateTime($dateStr);
        $dayOfWeek = (int) $dt->format('N'); // 1 = Lunes, 7 = Domingo
        return $dayOfWeek === 6 || $dayOfWeek === 7;
    }

    /**
     * Calcula si una fecha específica es un día festivo oficial en Colombia.
     */
    public static function isColombianHoliday(string $dateStr): bool
    {
        $dt = new DateTime($dateStr);
        $year = (int) $dt->format('Y');
        $month = (int) $dt->format('m');
        $day = (int) $dt->format('d');

        $holidays = self::getColombianHolidays($year);
        $formattedDate = sprintf('%04d-%02d-%02d', $year, $month, $day);

        return in_array($formattedDate, $holidays, true);
    }

    /**
     * Genera la lista completa de fechas de festivos para un año en Colombia.
     */
    public static function getColombianHolidays(int $year): array
    {
        $holidays = [];

        // 1. Festivos de fecha fija
        $fixed = [
            "{$year}-01-01", // Año Nuevo
            "{$year}-05-01", // Día del Trabajo
            "{$year}-07-20", // Independencia de Colombia
            "{$year}-08-07", // Batalla de Boyacá
            "{$year}-12-08", // Inmaculada Concepción
            "{$year}-12-25", // Navidad
        ];
        $holidays = array_merge($holidays, $fixed);

        // 2. Festivos sujetos a Ley Emiliani (Se trasladan al siguiente Lunes si no caen en Lunes)
        $emiliani = [
            "{$year}-01-06", // Reyes Magos
            "{$year}-03-19", // San José
            "{$year}-06-29", // San Pedro y San Pablo
            "{$year}-08-15", // Asunción de la Virgen
            "{$year}-10-12", // Día de la Raza
            "{$year}-11-01", // Todos los Santos
            "{$year}-11-11", // Independencia de Cartagena
        ];
        foreach ($emiliani as $holidayDate) {
            $holidays[] = self::moveToMonday($holidayDate);
        }

        // 3. Festivos basados en la fecha de Pascua / Semana Santa
        $easterDate = self::getEasterDate($year);

        // Jueves Santo (-3 días)
        $juevesSanto = clone $easterDate;
        $juevesSanto->modify('-3 days');
        $holidays[] = $juevesSanto->format('Y-m-d');

        // Viernes Santo (-2 días)
        $viernesSanto = clone $easterDate;
        $viernesSanto->modify('-2 days');
        $holidays[] = $viernesSanto->format('Y-m-d');

        // Ascensión del Señor (Pascua + 43 días -> Lunes)
        $ascension = clone $easterDate;
        $ascension->modify('+43 days');
        $holidays[] = self::moveToMonday($ascension->format('Y-m-d'));

        // Corpus Christi (Pascua + 64 días -> Lunes)
        $corpus = clone $easterDate;
        $corpus->modify('+64 days');
        $holidays[] = self::moveToMonday($corpus->format('Y-m-d'));

        // Sagrado Corazón de Jesús (Pascua + 71 días -> Lunes)
        $sagradoCorazon = clone $easterDate;
        $sagradoCorazon->modify('+71 days');
        $holidays[] = self::moveToMonday($sagradoCorazon->format('Y-m-d'));

        return array_values(array_unique($holidays));
    }

    /**
     * Calcula la fecha del Domingo de Pascua de forma agnóstica sin requerir la extensión PHP 'calendar'.
     */
    private static function getEasterDate(int $year): DateTime
    {
        if (function_exists('easter_date')) {
            $dt = new DateTime();
            $dt->setTimestamp(\easter_date($year));
            return $dt;
        }

        $a = $year % 19;
        $b = (int) ($year / 100);
        $c = $year % 100;
        $d = (int) ($b / 4);
        $e = $b % 4;
        $f = (int) (($b + 8) / 25);
        $g = (int) (($b - $f + 1) / 3);
        $h = (19 * $a + $b - $d - $g + 15) % 30;
        $i = (int) ($c / 4);
        $k = $c % 4;
        $l = (32 + 2 * $e + 2 * $i - $h - $k) % 7;
        $m = (int) (($a + 11 * $h + 22 * $l) / 451);
        $month = (int) (($h + $l - 7 * $m + 114) / 31);
        $day = (($h + $l - 7 * $m + 114) % 31) + 1;

        $dt = new DateTime();
        $dt->setDate($year, $month, $day);
        $dt->setTime(0, 0, 0);
        return $dt;
    }

    /**
     * Aplica la Ley Emiliani: Si la fecha no cae en Lunes, se traslada al Lunes siguiente.
     */
    private static function moveToMonday(string $dateStr): string
    {
        $dt = new DateTime($dateStr);
        $dayOfWeek = (int) $dt->format('N'); // 1 = Lunes, 7 = Domingo

        if ($dayOfWeek !== 1) {
            $daysToAdd = 8 - $dayOfWeek;
            $dt->modify("+{$daysToAdd} days");
        }

        return $dt->format('Y-m-d');
    }
}
