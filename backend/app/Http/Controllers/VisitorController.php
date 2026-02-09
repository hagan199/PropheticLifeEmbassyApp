<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Http\Requests\Visitor\StoreVisitorRequest;
use App\Http\Requests\Visitor\UpdateVisitorRequest;
use App\Models\Visitor;
use App\Models\User;
use Illuminate\Support\Facades\Auth;

class VisitorController extends Controller
{
    /**
     * Get all visitors
     */
    public function index(Request $request)
    {
        $query = Visitor::query()->with('creator:id,name');

        // Filter by category
        if ($request->has('category')) {
            $query->where('category', $request->category);
        }

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('name', 'LIKE', "%{$search}%")
                  ->orWhere('phone', 'LIKE', "%{$search}%")
                  ->orWhere('occupation', 'LIKE', "%{$search}%");
            });
        }

        $perPage = $request->get('per_page', 10);
        $paginated = $query->orderBy('created_at', 'desc')->paginate($perPage);

        return response()->json([
            'success' => true,
            'data' => $paginated->items(),
            'total' => $paginated->total(),
            'current_page' => $paginated->currentPage(),
            'last_page' => $paginated->lastPage(),
            'per_page' => $paginated->perPage(),
            'visitor_count' => Visitor::where('category', 'Visitor')->count(),
            'partner_count' => Visitor::where('category', 'Partner')->count(),
            'member_count' => Visitor::where('category', 'Wants to be a Member')->count(),
        ]);
    }

    /**
     * Register a new visitor
     */
    public function store(Request $request)
    {
        try {
            $request->validate([
                'name' => 'required|string|max:255',
                'phone' => 'required|string|max:20',
                'category' => 'required|in:Visitor,Partner,Wants to be a Member',
                'service_type' => 'nullable|string|max:255',
                'occupation' => 'nullable|string|max:255',
                'date' => 'required|date',
            ]);

            $visitor = Visitor::create([
                'name' => $request->name,
                'phone' => $request->phone,
                'category' => $request->category,
                'service_type' => $request->service_type,
                'occupation' => $request->occupation,
                'first_visit_date' => $request->date,
                'created_by' => Auth::id() ?? User::where('email', 'admin@church.com')->first()?->id, // Fallback for debugging dev
                'status' => 'not_contacted',
            ]);

            return response()->json([
                'success' => true,
                'message' => 'Visitor registered successfully',
                'data' => $visitor,
            ], 201);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage(),
                'trace' => config('app.debug') ? $e->getTraceAsString() : null
            ], 500);
        }
    }

    /**
     * Get single visitor
     */
    public function show($id)
    {
        $visitor = Visitor::with(['creator:id,name', 'followUps'])->find($id);

        if (!$visitor) {
            return response()->json([
                'success' => false,
                'message' => 'Visitor not found',
            ], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $visitor,
        ]);
    }

    /**
     * Update visitor
     */
    public function update(Request $request, $id)
    {
        try {
            $visitor = Visitor::find($id);

            if (!$visitor) {
                return response()->json(['success' => false, 'message' => 'Visitor not found'], 404);
            }

            $request->validate([
                'name' => 'sometimes|required|string|max:255',
                'phone' => 'sometimes|required|string|max:20',
                'category' => 'sometimes|required|in:Visitor,Partner,Wants to be a Member',
                'service_type' => 'sometimes|nullable|string|max:255',
                'occupation' => 'sometimes|nullable|string|max:255',
                'date' => 'sometimes|required|date',
            ]);

            $data = $request->only(['name', 'phone', 'category', 'service_type', 'occupation']);
            
            if ($request->has('date')) {
                $data['first_visit_date'] = $request->date;
            }

            $visitor->update($data);

            return response()->json([
                'success' => true,
                'message' => 'Visitor updated successfully',
                'data' => $visitor
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ], 500);
        }
    }

    /**
     * Delete visitor
     */
    public function destroy($id)
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return response()->json(['success' => false, 'message' => 'Visitor not found'], 404);
        }

        $visitor->delete();

        return response()->json([
            'success' => true,
            'message' => 'Visitor deleted successfully',
        ]);
    }

    /**
     * Get follow-ups for a visitor
     */
    public function followUps($id)
    {
        $visitor = Visitor::find($id);
        if (!$visitor) {
            return response()->json(['success' => false, 'message' => 'Visitor not found'], 404);
        }

        return response()->json([
            'success' => true,
            'data' => $visitor->followUps,
        ]);
    }
}
