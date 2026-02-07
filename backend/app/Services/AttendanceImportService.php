<?php

namespace App\Services;

use App\Models\Attendance;
use App\Models\User;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Facades\DB;

class AttendanceImportService
{
    /**
     * Import attendance from CSV file
     *
     * @param string $filePath
     * @param array $options
     * @return array
     */
    public function importFromCSV(string $filePath, array $options = []): array
    {
        $results = [
            'success' => 0,
            'failed' => 0,
            'errors' => [],
        ];

        if (!file_exists($filePath)) {
            throw new \Exception('File not found');
        }

        $handle = fopen($filePath, 'r');
        if ($handle === false) {
            throw new \Exception('Could not open file');
        }

        $header = fgetcsv($handle);
        $lineNumber = 1;

        DB::beginTransaction();
        try {
            while (($row = fgetcsv($handle)) !== false) {
                $lineNumber++;

                if (count($header) !== count($row)) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'line' => $lineNumber,
                        'errors' => ['Column count mismatch'],
                    ];
                    continue;
                }

                $data = array_combine($header, $row);

                $validator = Validator::make($data, [
                    'member_name' => 'required|string',
                    'service_type' => 'required|in:Sunday,Wednesday,Friday,Special',
                    'service_date' => 'required|date',
                    'present' => 'required|boolean',
                ]);

                if ($validator->fails()) {
                    $results['failed']++;
                    $results['errors'][] = [
                        'line' => $lineNumber,
                        'errors' => $validator->errors()->all(),
                    ];
                    continue;
                }

                // Find member by name or phone (if provided)
                $member = null;
                if (!empty($data['member_phone'])) {
                    $member = User::where('phone', $data['member_phone'])->first();
                } else {
                    $member = User::where('name', 'ILIKE', $data['member_name'])->first();
                }

                Attendance::create([
                    'member_id' => $member?->id,
                    'service_type' => $data['service_type'],
                    'service_date' => $data['service_date'],
                    'count' => filter_var($data['present'], FILTER_VALIDATE_BOOLEAN) ? 1 : 0,
                    'status' => $options['auto_approve'] ?? false ? 'approved' : 'pending',
                    'submitted_by' => auth()->id(),
                    'submitted_at' => now(),
                    'approved_by' => $options['auto_approve'] ?? false ? auth()->id() : null,
                    'approved_at' => $options['auto_approve'] ?? false ? now() : null,
                    'notes' => $data['notes'] ?? null,
                ]);

                $results['success']++;
            }

            DB::commit();
        } catch (\Exception $e) {
            DB::rollBack();
            throw $e;
        } finally {
            fclose($handle);
        }

        return $results;
    }
}
