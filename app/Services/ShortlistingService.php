<?php

namespace App\Services;

use App\Models\Application;
use App\Models\VerificationCode;
use App\Services\Notification\NotificationService;
use Barryvdh\DomPDF\Facade\Pdf;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;

class ShortlistingService
{
    public function __construct(
        protected NotificationService $notificationService,
    ) {}

    public function generateVerificationCode($applicationId): string
    {
        $code = strtoupper(Str::random(12));

        VerificationCode::create([
            'application_id' => $applicationId,
            'code_value'     => $code,
            'issue_date'     => Carbon::now(),
            'expiry_date'    => Carbon::now()->addMonths(6),
            'used_status'    => false,
        ]);

        return $code;
    }

    public function generateQrCode($code): string
    {
        $path = "qr-codes/{$code}.svg";

        $svg = $this->generateTextQrSvg($code);

        Storage::disk('public')->put($path, $svg);

        return Storage::url($path);
    }

    public function bulkShortlist(array $applicationIds, $adminId): array
    {
        $processed = [];

        DB::transaction(function () use ($applicationIds, $adminId, &$processed) {
            $applications = Application::whereIn('id', $applicationIds)
                ->where('status', 'eligibility_passed')
                ->get();

            foreach ($applications as $app) {
                $app->update([
                    'status'     => 'shortlisted',
                    'updated_at' => Carbon::now(),
                ]);

                $this->notificationService->shortlisted($app);

                $processed[] = [
                    'application_id' => $app->id,
                    'gaf_id'         => $app->gaf_id,
                ];
            }
        });

        return $processed;
    }

    public function exportShortlist($cycleId, $format = 'pdf'): string
    {
        $applications = Application::with('applicant')
            ->where('cycle_id', $cycleId)
            ->where('status', 'shortlisted')
            ->orderBy('gaf_id')
            ->get();

        if ($format === 'pdf') {
            return $this->exportPdf($applications, $cycleId);
        }

        if ($format === 'excel') {
            return $this->exportExcel($applications, $cycleId);
        }

        throw new \InvalidArgumentException("Unsupported export format: {$format}");
    }

    protected function exportPdf($applications, $cycleId): string
    {
        $data = [
            'title'        => 'Shortlisted Candidates',
            'date'         => Carbon::now()->format('Y-m-d'),
            'applications' => $applications,
        ];

        $pdf = Pdf::loadView('exports.shortlist-pdf', $data);

        $fileName = "shortlist-cycle-{$cycleId}-" . Carbon::now()->format('YmdHis') . '.pdf';
        $path = "exports/{$fileName}";

        Storage::disk('public')->put($path, $pdf->output());

        return Storage::url($path);
    }

    protected function exportExcel($applications, $cycleId): string
    {
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->setCellValue('A1', 'GAF ID')
            ->setCellValue('B1', 'Full Name')
            ->setCellValue('C1', 'Email')
            ->setCellValue('D1', 'Contact')
            ->setCellValue('E1', 'Region')
            ->setCellValue('F1', 'Education Level')
            ->setCellValue('G1', 'Status');

        $row = 2;
        foreach ($applications as $app) {
            $sheet->setCellValue("A{$row}", $app->gaf_id)
                ->setCellValue("B{$row}", ($app->applicant->first_name ?? '') . ' ' . ($app->applicant->last_name ?? ''))
                ->setCellValue("C{$row}", $app->applicant->email ?? '')
                ->setCellValue("D{$row}", $app->applicant->contact_number ?? '')
                ->setCellValue("E{$row}", $app->applicant->region ?? '')
                ->setCellValue("F{$row}", $app->education_level)
                ->setCellValue("G{$row}", $app->status);
            $row++;
        }

        $writer = new Xlsx($spreadsheet);
        $fileName = "shortlist-cycle-{$cycleId}-" . Carbon::now()->format('YmdHis') . '.xlsx';
        $path = "exports/{$fileName}";

        $tempPath = Storage::disk('local')->path($path);
        $dir = dirname($tempPath);

        if (!is_dir($dir)) {
            mkdir($dir, 0755, true);
        }

        $writer->save($tempPath);

        Storage::disk('public')->put($path, file_get_contents($tempPath));
        unlink($tempPath);

        return Storage::url($path);
    }

    protected function generateTextQrSvg($code): string
    {
        $size = 200;
        $moduleCount = 25;
        $moduleSize = $size / $moduleCount;

        $hash = crc32($code);
        srand($hash);

        $svg = "<svg xmlns=\"http://www.w3.org/2000/svg\" width=\"{$size}\" height=\"{$size}\" viewBox=\"0 0 {$size} {$size}\">\n";
        $svg .= "  <rect width=\"{$size}\" height=\"{$size}\" fill=\"white\"/>\n";

        for ($row = 0; $row < $moduleCount; $row++) {
            for ($col = 0; $col < $moduleCount; $col++) {
                if (rand(0, 1)) {
                    $x = $col * $moduleSize;
                    $y = $row * $moduleSize;
                    $svg .= "  <rect x=\"{$x}\" y=\"{$y}\" width=\"{$moduleSize}\" height=\"{$moduleSize}\" fill=\"black\"/>\n";
                }
            }
        }

        $svg .= "  <text x=\"{$size}\" y=\"" . ($size + 20) . "\" font-size=\"12\" text-anchor=\"end\">{$code}</text>\n";
        $svg .= "</svg>";

        return $svg;
    }
}
