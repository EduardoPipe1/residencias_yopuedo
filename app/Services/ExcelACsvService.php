<?php

namespace App\Services;

use DateTimeInterface;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyFactoryInterface;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\CachingStrategyInterface;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\FileBasedStrategy;
use OpenSpout\Reader\XLSX\Manager\SharedStringsCaching\InMemoryStrategy;
use OpenSpout\Reader\XLSX\Reader;
use RuntimeException;

class ExcelACsvService
{
    private const COLUMNA_CLAVE = 'P_FolioBeneficiario';

    // busca la hoja del padron y la convierte a csv
    public static function convertir(string $rutaXlsx): array
    {
        // textos del excel en memoria para que no tarde
        $textosEnMemoria = new class implements CachingStrategyFactoryInterface {
            public function createBestCachingStrategy(?int $sharedStringsUniqueCount, string $tempFolder): CachingStrategyInterface
            {
                return $sharedStringsUniqueCount !== null
                    ? new InMemoryStrategy($sharedStringsUniqueCount)
                    : new FileBasedStrategy($tempFolder, 10000);
            }
        };
        $reader = new Reader(null, $textosEnMemoria);
        $reader->open($rutaXlsx);

        try {
            foreach ($reader->getSheetIterator() as $hoja) {
                $salida = null;
                $rutaCsv = null;
                $indicePorcentaje = null;

                foreach ($hoja->getRowIterator() as $numero => $fila) {
                    $valores = array_map(fn ($c) => $c->getValue(), $fila->getCells());

                    if ($salida === null) {
                        if (!in_array(self::COLUMNA_CLAVE, array_map(fn ($v) => trim((string) $v), $valores), true)) {
                            continue 2;
                        }
                        $indicePorcentaje = array_search('PorcAvance', $valores, true);
                        $rutaCsv = tempnam(sys_get_temp_dir(), 'padron_') . '.csv';
                        $salida = fopen($rutaCsv, 'w');
                        fwrite($salida, "\xEF\xBB\xBF");
                        fputcsv($salida, array_map(fn ($v) => (string) $v, $valores));
                        continue;
                    }

                    if (!array_filter($valores, fn ($v) => $v !== null && $v !== '')) {
                        continue;
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

    private static function comoTexto(mixed $v, bool $esPorcentaje): string
    {
        if ($v instanceof DateTimeInterface) {
            return $v->format('d/m/Y H:i');
        }
        if (is_bool($v)) {
            return $v ? 'VERDADERO' : 'FALSO';
        }
        if (is_float($v) || is_int($v)) {
            if ($esPorcentaje && $v <= 1) {
                $v *= 100;
            }
            $texto = (floor($v) == $v && abs($v) < 1e15) ? (string) (int) $v : (string) $v;
            return $esPorcentaje ? "{$texto} %" : $texto;
        }
        return (string) $v;
    }
}
