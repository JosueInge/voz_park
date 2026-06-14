<?php
// controllers/ReporteEstrategicoController.php

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/middleware/auth.php';
require_once dirname(__DIR__) . '/helpers/response.php';
require_once dirname(__DIR__) . '/models/ReporteEstrategico.php';

class ReporteEstrategicoController {

    /**
     * GET /admin/reportes-estrategicos
     * Retorna todos los datos estratégicos del mes actual.
     */
    public static function index(): void {
        requireAuth('admin');

        if (!ReporteEstrategico::hayDatosMes()) {
            success([
                'mes'                   => date('F Y'),
                'pct_uso_parques'       => 0,
                'pct_tiempo_respuesta'  => 0,
                'total_reportes_mes'    => 0,
                'tasa_resolucion'       => 0,
                'incidencias_por_tipo'  => [],
                'top_mayor_incidencias' => [],
                'top_menor_incidencias' => [],
            ], 'No hay datos disponibles para este mes');
            return;
        }

        try {
            $datos = ReporteEstrategico::datosMesActual();
            success($datos, 'Reportes estratégicos obtenidos correctamente');
        } catch (Throwable) {
            error('Error al procesar la solicitud', 500);
        }
    }

    /**
     * GET /admin/reportes-estrategicos/exportar-pdf
     * Genera y descarga un PDF con los datos estratégicos del mes.
     * Requiere FPDF en /backend/lib/fpdf/fpdf.php
     */
    public static function exportarPdf(): void {
        requireAuth('admin');

        $fpdfPath = dirname(__DIR__) . '/lib/fpdf/fpdf.php';
        if (!file_exists($fpdfPath)) {
            error('Librería PDF no disponible. Instala FPDF en /backend/lib/fpdf/', 500);
        }

        if (!ReporteEstrategico::hayDatosMes()) {
            error('No hay datos disponibles para este mes', 404);
        }

        try {
            $datos = ReporteEstrategico::datosMesActual();
            require_once $fpdfPath;

            $pdf = new FPDF('P', 'mm', 'A4');
            $pdf->SetMargins(15, 15, 15);
            $pdf->AddPage();

            // ── Encabezado ──────────────────────────────────────────────────
            $pdf->SetFillColor(26, 46, 15);
            $pdf->SetTextColor(223, 255, 170);
            $pdf->SetFont('Arial', 'B', 16);
            $pdf->Cell(0, 12, 'VozPark - Reportes Estrategicos', 0, 1, 'C', true);

            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->Cell(0, 7, 'Mes: ' . $datos['mes'] . '   Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');
            $pdf->Ln(4);

            // ── Métricas clave ───────────────────────────────────────────────
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetFillColor(45, 79, 26);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 8, 'Metricas del Mes', 0, 1, 'L', true);
            $pdf->Ln(2);

            $pdf->SetFont('Arial', '', 10);
            $pdf->SetTextColor(0, 0, 0);
            $metricas = [
                ['Total de reportes',        $datos['total_reportes_mes']],
                ['Tasa de resolucion',        $datos['tasa_resolucion'] . '%'],
                ['Uso de parques',            $datos['pct_uso_parques'] . '%'],
                ['Tiempo promedio respuesta', $datos['pct_tiempo_respuesta'] . 'h'],
            ];
            foreach ($metricas as [$label, $val]) {
                $pdf->SetFont('Arial', 'B', 10);
                $pdf->Cell(90, 7, $label . ':', 0);
                $pdf->SetFont('Arial', '', 10);
                $pdf->Cell(0, 7, (string)$val, 0, 1);
            }
            $pdf->Ln(4);

            // ── Por tipo de incidencia ───────────────────────────────────────
            $pdf->SetFont('Arial', 'B', 12);
            $pdf->SetFillColor(45, 79, 26);
            $pdf->SetTextColor(255, 255, 255);
            $pdf->Cell(0, 8, 'Incidencias por Tipo', 0, 1, 'L', true);
            $pdf->Ln(2);

            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetTextColor(0, 0, 0);
            $pdf->SetFillColor(240, 248, 234);
            $pdf->Cell(100, 7, 'Tipo',       1, 0, 'C', true);
            $pdf->Cell(40,  7, 'Total',      1, 0, 'C', true);
            $pdf->Cell(40,  7, 'Porcentaje', 1, 1, 'C', true);

            $pdf->SetFont('Arial', '', 9);
            $fill = false;
            foreach ($datos['incidencias_por_tipo'] as $item) {
                $pdf->SetFillColor($fill ? 245 : 255, $fill ? 252 : 255, $fill ? 238 : 255);
                $pdf->Cell(100, 6, $item['tipo'],                    1, 0, 'L', $fill);
                $pdf->Cell(40,  6, (string)$item['total'],           1, 0, 'C', $fill);
                $pdf->Cell(40,  6, $item['porcentaje'] . '%',        1, 1, 'C', $fill);
                $fill = !$fill;
            }
            $pdf->Ln(4);

            // ── Top parques mayor/menor ──────────────────────────────────────
            foreach ([
                ['Top 5 Parques con Mayor Incidencias', $datos['top_mayor_incidencias']],
                ['Top 5 Parques con Menor Incidencias', $datos['top_menor_incidencias']],
            ] as [$titulo, $lista]) {
                $pdf->SetFont('Arial', 'B', 12);
                $pdf->SetFillColor(45, 79, 26);
                $pdf->SetTextColor(255, 255, 255);
                $pdf->Cell(0, 8, $titulo, 0, 1, 'L', true);
                $pdf->Ln(2);

                $pdf->SetFont('Arial', 'B', 10);
                $pdf->SetTextColor(0, 0, 0);
                $pdf->SetFillColor(240, 248, 234);
                $pdf->Cell(140, 7, 'Parque', 1, 0, 'C', true);
                $pdf->Cell(40,  7, 'Total',  1, 1, 'C', true);

                $pdf->SetFont('Arial', '', 9);
                $fill = false;
                foreach ($lista as $p) {
                    $pdf->SetFillColor($fill ? 245 : 255, $fill ? 252 : 255, $fill ? 238 : 255);
                    $pdf->Cell(140, 6, $p['parque'],        1, 0, 'L', $fill);
                    $pdf->Cell(40,  6, (string)$p['total'], 1, 1, 'C', $fill);
                    $fill = !$fill;
                }
                $pdf->Ln(4);
            }

            // ── Enviar al navegador ──────────────────────────────────────────
            $filename = 'reportes_estrategicos_' . date('Ym') . '.pdf';
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="' . $filename . '"');
            header('Cache-Control: no-cache');
            $pdf->Output('D', $filename);
            exit;

        } catch (Throwable) {
            error('Error al procesar la solicitud', 500);
        }
    }

    /**
     * GET /admin/reportes-estrategicos/exportar-excel
     * Genera y descarga un Excel (.xlsx) con los datos estratégicos.
     * Usa PhpSpreadsheet (composer) o fallback a CSV si no está disponible.
     */
    public static function exportarExcel(): void {
        requireAuth('admin');

        if (!ReporteEstrategico::hayDatosMes()) {
            error('No hay datos disponibles para este mes', 404);
        }

        try {
            $datos    = ReporteEstrategico::datosMesActual();
            $filename = 'reportes_estrategicos_' . date('Ym');

            // ── Intentar PhpSpreadsheet ──────────────────────────────────────
            $spreadsheetPath = dirname(__DIR__) . '/vendor/autoload.php';

            if (file_exists($spreadsheetPath)) {
                require_once $spreadsheetPath;
                self::generarXlsx($datos, $filename);
            } else {
                // ── Fallback: CSV (siempre disponible) ───────────────────────
                self::generarCsv($datos, $filename);
            }

        } catch (Throwable) {
            error('Error al procesar la solicitud', 500);
        }
    }

    // ── Generador XLSX con PhpSpreadsheet ────────────────────────────────────
    private static function generarXlsx(array $datos, string $filename): void {
        $spreadsheet = new \PhpOffice\PhpSpreadsheet\Spreadsheet();

        // ── Hoja 1: Métricas ─────────────────────────────────────────────────
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setTitle('Metricas');

        $sheet->setCellValue('A1', 'VozPark - Reportes Estrategicos');
        $sheet->setCellValue('A2', 'Mes: ' . $datos['mes']);
        $sheet->setCellValue('A4', 'Metrica');
        $sheet->setCellValue('B4', 'Valor');

        $filaMetricas = [
            ['Total de reportes',         $datos['total_reportes_mes']],
            ['Tasa de resolución (%)',     $datos['tasa_resolucion']],
            ['Uso de parques (%)',         $datos['pct_uso_parques']],
            ['Tiempo promedio resp. (h)',  $datos['pct_tiempo_respuesta']],
        ];
        $row = 5;
        foreach ($filaMetricas as [$label, $val]) {
            $sheet->setCellValue("A{$row}", $label);
            $sheet->setCellValue("B{$row}", $val);
            $row++;
        }

        // ── Hoja 2: Por tipo ─────────────────────────────────────────────────
        $sheet2 = $spreadsheet->createSheet();
        $sheet2->setTitle('Por Tipo');
        $sheet2->setCellValue('A1', 'Tipo');
        $sheet2->setCellValue('B1', 'Total');
        $sheet2->setCellValue('C1', 'Porcentaje (%)');
        $row = 2;
        foreach ($datos['incidencias_por_tipo'] as $item) {
            $sheet2->setCellValue("A{$row}", $item['tipo']);
            $sheet2->setCellValue("B{$row}", $item['total']);
            $sheet2->setCellValue("C{$row}", $item['porcentaje']);
            $row++;
        }

        // ── Hoja 3: Top parques ──────────────────────────────────────────────
        $sheet3 = $spreadsheet->createSheet();
        $sheet3->setTitle('Top Parques');
        $sheet3->setCellValue('A1', 'Mayor incidencias - Parque');
        $sheet3->setCellValue('B1', 'Total');
        $sheet3->setCellValue('D1', 'Menor incidencias - Parque');
        $sheet3->setCellValue('E1', 'Total');
        $row = 2;
        foreach ($datos['top_mayor_incidencias'] as $i => $p) {
            $sheet3->setCellValue("A{$row}", $p['parque']);
            $sheet3->setCellValue("B{$row}", $p['total']);
            $menor = $datos['top_menor_incidencias'][$i] ?? null;
            if ($menor) {
                $sheet3->setCellValue("D{$row}", $menor['parque']);
                $sheet3->setCellValue("E{$row}", $menor['total']);
            }
            $row++;
        }

        // ── Enviar ───────────────────────────────────────────────────────────
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment; filename="' . $filename . '.xlsx"');
        header('Cache-Control: no-cache');

        $writer = new \PhpOffice\PhpSpreadsheet\Writer\Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }

    // ── Fallback CSV ─────────────────────────────────────────────────────────
    private static function generarCsv(array $datos, string $filename): void {
        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="' . $filename . '.csv"');
        header('Cache-Control: no-cache');

        $out = fopen('php://output', 'w');
        fprintf($out, chr(0xEF) . chr(0xBB) . chr(0xBF)); // BOM UTF-8

        // Métricas
        fputcsv($out, ['VozPark - Reportes Estrategicos - ' . $datos['mes']]);
        fputcsv($out, []);
        fputcsv($out, ['Metrica', 'Valor']);
        fputcsv($out, ['Total de reportes',         $datos['total_reportes_mes']]);
        fputcsv($out, ['Tasa de resolucion (%)',     $datos['tasa_resolucion']]);
        fputcsv($out, ['Uso de parques (%)',         $datos['pct_uso_parques']]);
        fputcsv($out, ['Tiempo promedio resp. (h)',  $datos['pct_tiempo_respuesta']]);
        fputcsv($out, []);

        // Por tipo
        fputcsv($out, ['Tipo de incidencia', 'Total', 'Porcentaje (%)']);
        foreach ($datos['incidencias_por_tipo'] as $item) {
            fputcsv($out, [$item['tipo'], $item['total'], $item['porcentaje']]);
        }
        fputcsv($out, []);

        // Top parques
        fputcsv($out, ['Mayor incidencias - Parque', 'Total', '', 'Menor incidencias - Parque', 'Total']);
        foreach ($datos['top_mayor_incidencias'] as $i => $p) {
            $menor = $datos['top_menor_incidencias'][$i] ?? ['parque' => '', 'total' => ''];
            fputcsv($out, [$p['parque'], $p['total'], '', $menor['parque'], $menor['total']]);
        }

        fclose($out);
        exit;
    }
}