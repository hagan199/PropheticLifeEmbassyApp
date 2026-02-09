<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Services\AttendanceImportService;
use Illuminate\Support\Facades\Gate;
use Carbon\Carbon;

class ImportInsightController extends Controller
{
  /**
   * Get insights and reports for attendance imports
   * Only admin and finance roles can access
   */
  public function attendanceImportInsights(Request $request)
  {
    if (!Gate::any(['admin', 'finance'])) {
      return response()->json(['success' => false, 'message' => 'Unauthorized'], 403);
    }

    // Example: fetch last 10 imports, error rates, top errors, import success/failure trends
    // This is a placeholder; real implementation would require import logs/history
    $insights = [
      'total_imports' => 42,
      'last_import' => [
        'date' => \Carbon\Carbon::now()->subDays(1)->toDateTimeString(),
        'success_count' => 120,
        'fail_count' => 3,
        'top_errors' => [
          'Missing member_name' => 2,
          'Invalid service_date' => 1,
        ],
      ],
      'import_trends' => [
        ['date' => \Carbon\Carbon::now()->subDays(7)->toDateString(), 'success' => 100, 'fail' => 2],
        ['date' => \Carbon\Carbon::now()->subDays(6)->toDateString(), 'success' => 110, 'fail' => 1],
        // ...
      ],
      'top_error_types' => [
        'Missing member_name' => 12,
        'Invalid service_type' => 7,
        'Invalid service_date' => 5,
      ],
    ];

    return response()->json([
      'success' => true,
      'data' => $insights,
    ]);
  }
}
