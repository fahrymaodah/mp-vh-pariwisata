<?php

declare(strict_types=1);

namespace App\Filament\Traits;

use App\Services\ReportExportService;
use Filament\Actions\Action;
use Filament\Notifications\Notification;
use Filament\Support\Icons\Heroicon;

trait HasReportExport
{
    /**
     * Get header actions for export buttons.
     */
    protected function getHeaderActions(): array
    {
        return [
            Action::make('exportCsv')
                ->label('Export CSV')
                ->icon(Heroicon::ArrowDownTray)
                ->color('success')
                ->action(fn () => $this->exportToCsv()),
            Action::make('exportPdf')
                ->label('Export PDF')
                ->icon(Heroicon::DocumentArrowDown)
                ->color('danger')
                ->action(fn () => $this->exportToPdf()),
        ];
    }

    /**
     * Override this method to provide CSV export data.
     * Should return ['headers' => [...], 'rows' => [[...], ...]].
     */
    abstract protected function getExportData(): array;

    /**
     * Override this method to provide the report title.
     */
    abstract protected function getReportTitle(): string;

    /**
     * Export to CSV.
     */
    public function exportToCsv()
    {
        try {
            $data = $this->getExportData();

            if (empty($data['rows'] ?? [])) {
                Notification::make()
                    ->title('No data to export')
                    ->body('There are no records matching the current filters.')
                    ->warning()
                    ->send();

                return null;
            }

            return ReportExportService::exportCsv(
                $data['headers'] ?? [],
                $data['rows'] ?? [],
                $this->getExportFilename(),
            );
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to generate CSV. Please try again.')
                ->danger()
                ->send();

            return null;
        }
    }

    /**
     * Export to PDF.
     */
    public function exportToPdf()
    {
        try {
            $data = $this->getExportData();

            if (empty($data['rows'] ?? [])) {
                Notification::make()
                    ->title('No data to export')
                    ->body('There are no records matching the current filters.')
                    ->warning()
                    ->send();

                return null;
            }

            $pdfHeaders = array_map(fn ($h) => ['label' => $h, 'align' => ''], $data['headers'] ?? []);
            $pdfRows = array_map(function ($row) {
                return array_map(fn ($cell) => ['value' => $cell, 'align' => ''], $row);
            }, $data['rows'] ?? []);

            return ReportExportService::exportPdf(
                'reports.pdf-template',
                [
                    'title' => $this->getReportTitle(),
                    'subtitle' => $data['subtitle'] ?? '',
                    'summary' => $data['summary'] ?? [],
                    'headers' => $pdfHeaders,
                    'rows' => $pdfRows,
                ],
                $this->getExportFilename(),
                $data['orientation'] ?? 'portrait',
            );
        } catch (\Throwable $e) {
            report($e);
            Notification::make()
                ->title('Export Failed')
                ->body('Unable to generate PDF. Please try again.')
                ->danger()
                ->send();

            return null;
        }
    }

    protected function getExportFilename(): string
    {
        $title = str_replace(' ', '-', strtolower($this->getReportTitle()));
        return $title . '-' . now()->format('Y-m-d-His');
    }
}
