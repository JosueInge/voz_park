<?php
// controllers/ReporteAdminController.php

require_once dirname(__DIR__) . '/config/database.php';
require_once dirname(__DIR__) . '/middleware/auth.php';
require_once dirname(__DIR__) . '/helpers/response.php';
require_once dirname(__DIR__) . '/models/Incidencia.php';

class ReporteAdminController {

    /**
     * GET /admin/reportes/pendientes
     * Retorna los 5 reportes más recientes con estado Sin asignar o Asignado.
     */
    public static function pendientes(): void {
        requireAuth('admin');

        $limite   = isset($_GET['todos']) ? PHP_INT_MAX : 5;
        $reportes = Incidencia::pendientes($limite);

        if (empty($reportes)) {
            success([], 'No hay reportes pendientes de asignación');
            return;
        }

        success($reportes, 'Reportes pendientes obtenidos correctamente');
    }

    /**
     * GET /admin/reportes/exportar-pdf
     * Genera un PDF con los reportes pendientes.
     * Usa la biblioteca FPDF (sin Composer) cargada localmente.
     */
    public static function exportarPdf(): void {
        requireAuth('admin');

        $reportes = Incidencia::pendientes(PHP_INT_MAX);

        if (empty($reportes)) {
            error('No hay reportes pendientes para exportar', 404);
        }

        $fpdfPath = dirname(__DIR__) . '/lib/fpdf/fpdf.php';

        if (!file_exists($fpdfPath)) {
            // Fallback: retornar JSON si FPDF no está instalado
            error('Librería PDF no disponible en el servidor. Instala FPDF en /backend/lib/fpdf/', 500);
        }

        require_once $fpdfPath;

        try {
            $pdf = new FPDF('L', 'mm', 'A4');
            $pdf->AddPage();
            $pdf->SetFont('Arial', 'B', 14);
            $pdf->Cell(0, 10, 'VozPark - Reportes Pendientes de Asignacion', 0, 1, 'C');
            $pdf->SetFont('Arial', '', 10);
            $pdf->Cell(0, 6, 'Generado: ' . date('d/m/Y H:i'), 0, 1, 'C');
            $pdf->Ln(4);

            // Encabezados de tabla
            $pdf->SetFont('Arial', 'B', 10);
            $pdf->SetFillColor(26, 46, 15);
            $pdf->SetTextColor(255, 255, 255);
            $widths = [15, 55, 60, 25, 35];
            $headers = ['#', 'Incidencia', 'Parque', 'Urgencia', 'Estado'];
            foreach ($headers as $i => $h) {
                $pdf->Cell($widths[$i], 8, $h, 1, 0, 'C', true);
            }
            $pdf->Ln();

            // Filas
            $pdf->SetFont('Arial', '', 9);
            $pdf->SetTextColor(0, 0, 0);
            $fill = false;
            foreach ($reportes as $r) {
                $pdf->SetFillColor($fill ? 240 : 255, $fill ? 248 : 255, $fill ? 234 : 255);
                $pdf->Cell($widths[0], 7, '#' . $r['id'],          1, 0, 'C', $fill);
                $pdf->Cell($widths[1], 7, $r['incidencia'],         1, 0, 'L', $fill);
                $pdf->Cell($widths[2], 7, $r['parque'],             1, 0, 'L', $fill);
                $pdf->Cell($widths[3], 7, $r['urgencia'],           1, 0, 'C', $fill);
                $pdf->Cell($widths[4], 7, $r['estado'],             1, 1, 'C', $fill);
                $fill = !$fill;
            }

            // Enviar al navegador
            header('Content-Type: application/pdf');
            header('Content-Disposition: attachment; filename="reportes_pendientes_' . date('Ymd') . '.pdf"');
            header('Cache-Control: no-cache');
            $pdf->Output('D', 'reportes_pendientes_' . date('Ymd') . '.pdf');
            exit;

        } catch (Throwable $e) {
            error('Error al generar el PDF', 500);
        }
    }
}