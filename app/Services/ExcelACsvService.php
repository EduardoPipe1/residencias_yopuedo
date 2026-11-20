<?php

namespace App\Services;

use DateTimeInterface;
use OpenSpout\Reader\XLSX\Reader;
use RuntimeException;

/**
 * Convierte el Excel del padrón (.xlsx) al mismo CSV que exporta Excel, para que pase por el
 * importador de siempre. Se lee fila por fila (OpenSpout) para no cargar en memoria los
 * millones de celdas del padrón.
 *
 * El libro trae varias hojas (MATRIZ, tablas dinámicas, informes...): se usa la primera hoja
 * cuyo encabezado contenga P_FolioBeneficiario, sin importar cómo se llame.
 */
class ExcelACsvService
{
    private const COLUMNA_CLAVE = 'P_FolioBeneficiario';

    /** Devuelve [ruta del CSV temporal, nombre de la hoja usada]. */
    public static function convertir(string $rutaXlsx): array
    {
        $reader = new Reader();
        $reader->open($rutaXlsx);

        try {
            foreach ($reader->getSheetIterator() as $hoja) {
                $salida = null;
                $rutaCsv = null;
                $indicePorcentaje = null;

                foreach ($hoja->getRowIterator() as $numero => $fila) {
                    $valores = array_map(fn ($c) => $c->getValue(), $fila->getCells());

                    if ($salida === null) {
                        // Primera fila con datos = encabezados. Si no es el padrón, se pasa a la siguiente hoja.
                        if (!in_array(self::COLUMNA_CLAVE, array_map(fn ($v) => trim((string) $v), $valores), true)) {
                            continue 2;
                        }
                        $indicePorcentaje = array_search('PorcAvance', $valores, true);
                        $rutaCsv = tempnam(sys_get_temp_dir(), 'padron_') . '.csv';
                        $salida = fopen($rutaCsv, 'w');
                        fwrite($salida, "\xEF\xBB\xBF");   // UTF-8 con BOM, como "CSV UTF-8" de Excel
                        fputcsv($salida, array_map(fn ($v) => (string) $v, $valores));
                        continue;
                    }

                    if (!array_filter($valores, fn ($v) => $v !== null && $v !== '')) {
                        continue;   // filas vacías con formato
                    }

                    foreach ($valores as $i => $v) {
                        $valores[$i] = self::comoTexto($v, $i === $indicePorcentaje);
                    }
                    fputcsv($salida, $valores);
                }

                if ($salida) {
                    fclose($salida);
                    return [$rutaCsv, $hoja->getName()];
                }
            }
        } finally {
            $reader->close();
        }

        throw new RuntimeException('No se encontró en el Excel una hoja con la columna ' . self::COLUMNA_CLAVE . '.');
    }

    /** Escribe cada celda como la escribiría Excel al guardar como CSV. */
    private static function comoTexto(mixed $v, bool $esPorcentaje): string
    {
        if ($v instanceof DateTimeInterface) {
            return $v->format('d/m/Y H:i');           // igual que en el CSV: 05/04/2024 00:00
        }
        if (is_bool($v)) {
            return $v ? 'VERDADERO' : 'FALSO';
        }
        if (is_float($v) || is_int($v)) {
            if ($esPorcentaje && $v <= 1) {
                $v *= 100;                             // celda con formato de porcentaje: 1 => 100 %
            }
            $texto = (floor($v) == $v && abs($v) < 1e15) ? (string) (int) $v : (string) $v;
            return $esPorcentaje ? "{$texto} %" : $texto;
        }
        return (string) $v;
    }
}
