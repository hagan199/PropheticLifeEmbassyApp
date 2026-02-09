<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Finance\StoreContributionRequest;
use App\Http\Requests\Finance\StoreExpenseRequest;
use App\Http\Requests\Finance\RejectExpenseRequest;
use App\Http\Requests\Finance\StoreExpenseTypeRequest;
use App\Models\ExpenseType;

class FinanceController extends Controller
{
    // ========== Contributions ==========

    /**
     * Get all contributions
     */
    public function indexContributions(Request $request)
    {
        $contributions = $this->getMockContributions();

        return response()->json([
            'success' => true,
            'data' => $contributions,
            'total' => count($contributions),
        ]);
    }

    /**
     * Create contribution record
     */
    public function storeContribution(StoreContributionRequest $request)
    {

        $newContribution = [
            'id' => 'cont-' . rand(1000, 9999),
            'member_id' => $request->member_id,
            'member_name' => 'Member Name',
            'amount' => $request->amount,
            'contribution_date' => $request->contribution_date,
            'payment_method' => $request->payment_method,
            'notes' => $request->notes,
            'recorded_by' => 'Current User',
            'created_at' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Contribution recorded successfully',
            'data' => $newContribution,
        ], 201);
    }

    /**
     * Get single contribution
     */
    public function showContribution($id)
    {
        $contributions = $this->getMockContributions();
        $contribution = collect($contributions)->firstWhere('id', $id);

        if (!$contribution) {
            return response()->json([
                'success' => false,
                'message' => 'Contribution not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $contribution,
        ]);
    }

    /**
     * Update contribution
     */
    public function updateContribution(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Contribution updated successfully',
        ]);
    }

    /**
     * Delete contribution
     */
    public function destroyContribution($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Contribution deleted successfully',
        ]);
    }

    /**
     * Get contributions for a specific partner
     */
    public function partnerContributions($partnerId)
    {
        $contributions = array_filter($this->getMockContributions(), fn($c) => $c['member_id'] === $partnerId);

        return response()->json([
            'success' => true,
            'data' => array_values($contributions),
            'total' => count($contributions),
        ]);
    }

    // ========== Expenses ==========

    /**
     * Get all expenses
     */
    public function indexExpenses(Request $request)
    {
        $expenses = $this->getMockExpenses();

        // Filter by status
        if ($request->has('status')) {
            $expenses = array_filter($expenses, fn($e) => $e['status'] === $request->status);
        }

        return response()->json([
            'success' => true,
            'data' => array_values($expenses),
            'total' => count($expenses),
        ]);
    }

    /**
     * Create expense record
     */
    public function storeExpense(StoreExpenseRequest $request)
    {

        $newExpense = [
            'id' => 'exp-' . rand(1000, 9999),
            'expense_type_id' => $request->expense_type_id,
            'expense_type_name' => 'Expense Type',
            'amount' => $request->amount,
            'expense_date' => $request->expense_date,
            'description' => $request->description,
            'status' => 'pending',
            'receipt_url' => null,
            'submitted_by' => 'Current User',
            'created_at' => now()->toISOString(),
        ];

        return response()->json([
            'success' => true,
            'message' => 'Expense submitted for approval',
            'data' => $newExpense,
        ], 201);
    }

    /**
     * Get single expense
     */
    public function showExpense($id)
    {
        $expenses = $this->getMockExpenses();
        $expense = collect($expenses)->firstWhere('id', $id);

        if (!$expense) {
            return response()->json([
                'success' => false,
                'message' => 'Expense not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $expense,
        ]);
    }

    /**
     * Update expense
     */
    public function updateExpense(Request $request, $id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Expense updated successfully',
        ]);
    }

    /**
     * Delete expense
     */
    public function destroyExpense($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Expense deleted successfully',
        ]);
    }

    /**
     * Approve expense
     */
    public function approveExpense($id)
    {
        return response()->json([
            'success' => true,
            'message' => 'Expense approved',
        ]);
    }

    /**
     * Reject expense
     */
    public function rejectExpense(RejectExpenseRequest $request, $id)
    {

        return response()->json([
            'success' => true,
            'message' => 'Expense rejected',
        ]);
    }

    // ========== Expense Types ==========

    /**
     * Get all expense types with pagination and search
     */
    public function expenseTypes(Request $request)
    {
        $query = ExpenseType::query();

        // Search functionality
        if ($request->has('search') && $request->search) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%");
            });
        }

        // Filter by active/inactive
        if ($request->has('is_active')) {
            $query->where('is_active', $request->is_active);
        } else {
            // Default: only active types
            $query->where('is_active', true);
        }

        // Order by name
        $query->orderBy('name', 'asc');

        // Pagination
        $perPage = $request->get('per_page', 15);
        if ($perPage === 'all') {
            $types = $query->withCount('expenses')->get();
            return response()->json([
                'success' => true,
                'data' => $types,
                'total' => $types->count(),
            ]);
        }

        $types = $query->withCount('expenses')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $types->items(),
            'meta' => [
                'current_page' => $types->currentPage(),
                'last_page' => $types->lastPage(),
                'per_page' => $types->perPage(),
                'total' => $types->total(),
            ],
        ]);
    }

    /**
     * Create expense type
     */
    public function storeExpenseType(StoreExpenseTypeRequest $request)
    {
        // Check for duplicates
        $exists = ExpenseType::where('name', $request->name)
            ->where('is_active', true)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'An expense type with this name already exists',
            ], 422);
        }

        $expenseType = ExpenseType::create([
            'name' => $request->name,
            'description' => $request->description,
            'is_active' => true,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense type created successfully',
            'data' => $expenseType,
        ], 201);
    }

    /**
     * Update expense type
     */
    public function updateExpenseType(Request $request, $id)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string|max:500',
        ]);

        $expenseType = ExpenseType::find($id);

        if (!$expenseType) {
            return response()->json([
                'success' => false,
                'message' => 'Expense type not found',
            ], 404);
        }

        // Check for duplicates (excluding current record)
        $exists = ExpenseType::where('name', $request->name)
            ->where('is_active', true)
            ->where('id', '!=', $id)
            ->exists();

        if ($exists) {
            return response()->json([
                'success' => false,
                'message' => 'An expense type with this name already exists',
            ], 422);
        }

        $expenseType->update([
            'name' => $request->name,
            'description' => $request->description,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Expense type updated successfully',
            'data' => $expenseType,
        ]);
    }

    /**
     * Delete expense type (soft delete - set inactive)
     */
    public function deleteExpenseType($id)
    {
        $expenseType = ExpenseType::find($id);

        if (!$expenseType) {
            return response()->json([
                'success' => false,
                'message' => 'Expense type not found',
            ], 404);
        }

        // Check if type is being used by any expenses
        $expenseCount = $expenseType->expenses()->count();

        if ($expenseCount > 0) {
            // Soft delete - mark as inactive instead of deleting
            $expenseType->update(['is_active' => false]);

            return response()->json([
                'success' => true,
                'message' => "Expense type deactivated (used by {$expenseCount} expense(s))",
                'data' => $expenseType,
            ]);
        }

        // Hard delete if not used
        $expenseType->delete();

        return response()->json([
            'success' => true,
            'message' => 'Expense type deleted successfully',
        ]);
    }

    // ========== Reports ==========

    /**
     * Get monthly financial report
     */
    public function monthlyReport(Request $request)
    {
        $month = $request->get('month', now()->format('Y-m'));

        $report = [
            'month' => $month,
            'contributions' => [
                'total' => 125000,
                'count' => 45,
            ],
            'expenses' => [
                'total' => 42000,
                'count' => 12,
                'pending' => 3,
                'approved' => 9,
            ],
            'net' => 83000,
            'top_contributors' => [
                ['name' => 'Emmanuel Agyei', 'amount' => 5000],
                ['name' => 'Joseph Owusu', 'amount' => 4500],
                ['name' => 'Rebecca Mensah', 'amount' => 4200],
            ],
            'expense_breakdown' => [
                ['type' => 'Utilities', 'amount' => 15000],
                ['type' => 'Equipment', 'amount' => 12000],
                ['type' => 'Ministry', 'amount' => 10000],
                ['type' => 'Office Supplies', 'amount' => 3000],
                ['type' => 'Transportation', 'amount' => 2000],
            ],
        ];

        return response()->json([
            'success' => true,
            'data' => $report,
        ]);
    }

    /**
     * Export financial report
     */
    public function exportReport(Request $request)
    {
        $format = $request->get('format', 'csv');

        return response()->json([
            'success' => true,
            'message' => 'Report export initiated',
            'download_url' => '/exports/finance-report-' . now()->format('Y-m-d') . '.' . $format,
        ]);
    }

    // ========== Helper Methods ==========

    /**
     * Mock contributions data
     */
    private function getMockContributions()
    {
        return [
            [
                'id' => 'cont-001',
                'member_id' => 'mem-001',
                'member_name' => 'Emmanuel Agyei',
                'amount' => 500,
                'contribution_date' => now()->subDays(5)->toDateString(),
                'payment_method' => 'mobile_money',
                'notes' => 'Monthly partnership',
                'recorded_by' => 'Kwame Osei',
                'created_at' => now()->subDays(5)->toISOString(),
            ],
            [
                'id' => 'cont-002',
                'member_id' => 'mem-003',
                'member_name' => 'Joseph Owusu',
                'amount' => 1000,
                'contribution_date' => now()->subDays(3)->toDateString(),
                'payment_method' => 'bank_transfer',
                'notes' => null,
                'recorded_by' => 'Kwame Osei',
                'created_at' => now()->subDays(3)->toISOString(),
            ],
            [
                'id' => 'cont-003',
                'member_id' => 'mem-006',
                'member_name' => 'Rebecca Mensah',
                'amount' => 750,
                'contribution_date' => now()->subDays(7)->toDateString(),
                'payment_method' => 'cash',
                'notes' => 'Thanksgiving offering',
                'recorded_by' => 'Kwame Osei',
                'created_at' => now()->subDays(7)->toISOString(),
            ],
        ];
    }

    /**
     * Mock expenses data
     */
    private function getMockExpenses()
    {
        return [
            [
                'id' => 'exp-001',
                'expense_type_id' => 'et-001',
                'expense_type_name' => 'Utilities',
                'amount' => 450,
                'expense_date' => now()->subDays(5)->toDateString(),
                'description' => 'Electricity bill for December',
                'status' => 'approved',
                'receipt_url' => '/receipts/receipt-001.pdf',
                'submitted_by' => 'Admin User',
                'approved_by' => 'Admin User',
                'approved_at' => now()->subDays(4)->toISOString(),
                'created_at' => now()->subDays(5)->toISOString(),
            ],
            [
                'id' => 'exp-002',
                'expense_type_id' => 'et-002',
                'expense_type_name' => 'Equipment',
                'amount' => 1200,
                'expense_date' => now()->subDays(2)->toDateString(),
                'description' => 'New microphones for sound system',
                'status' => 'pending',
                'receipt_url' => null,
                'submitted_by' => 'Kofi Mensah',
                'approved_by' => null,
                'approved_at' => null,
                'created_at' => now()->subDays(2)->toISOString(),
            ],
            [
                'id' => 'exp-003',
                'expense_type_id' => 'et-003',
                'expense_type_name' => 'Ministry',
                'amount' => 800,
                'expense_date' => now()->subDays(10)->toDateString(),
                'description' => 'Outreach materials and transport',
                'status' => 'approved',
                'receipt_url' => '/receipts/receipt-003.jpg',
                'submitted_by' => 'Ama Boateng',
                'approved_by' => 'Admin User',
                'approved_at' => now()->subDays(9)->toISOString(),
                'created_at' => now()->subDays(10)->toISOString(),
            ],
        ];
    }
}
