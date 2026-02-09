<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\VisitorType;
use Illuminate\Support\Facades\Auth;

class VisitorTypeController extends Controller
{
  public function index()
  {
    $types = VisitorType::orderBy('name')->get();
    return response()->json(['success' => true, 'data' => $types]);
  }

  public function store(Request $request)
  {
    $user = Auth::user();
    if (!$user || !$user->hasAnyRole(['admin'])) {
      return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }

    $request->validate([
      'name' => 'required|string|max:255|unique:visitor_types,name',
    ]);

    $type = VisitorType::create(['name' => $request->name]);

    return response()->json(['success' => true, 'data' => $type], 201);
  }

  public function update(Request $request, $id)
  {
    $user = Auth::user();
    if (!$user || !$user->hasAnyRole(['admin'])) {
      return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }

    $type = VisitorType::find($id);
    if (!$type) return response()->json(['success' => false, 'message' => 'Not found'], 404);

    $request->validate([
      'name' => 'required|string|max:255|unique:visitor_types,name,' . $id,
    ]);

    $type->update(['name' => $request->name]);

    return response()->json(['success' => true, 'data' => $type]);
  }

  public function destroy($id)
  {
    $user = Auth::user();
    if (!$user || !$user->hasAnyRole(['admin'])) {
      return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }

    $type = VisitorType::find($id);
    if (!$type) return response()->json(['success' => false, 'message' => 'Not found'], 404);

    $type->delete();
    return response()->json(['success' => true]);
  }
}
