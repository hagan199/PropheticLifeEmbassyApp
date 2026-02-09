<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceType;
use Illuminate\Support\Facades\Auth;

class ServiceTypeController extends Controller
{
    public function index()
    {
        $types = ServiceType::orderBy('name')->get();
        return response()->json(['success' => true, 'data' => $types]);
    }

    public function store(Request $request)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:service_types,name',
        ]);

        $type = ServiceType::create([
            'name' => $request->name,
        ]);

        return response()->json(['success' => true, 'data' => $type], 201);
    }

    public function update(Request $request, $id)
    {
        $user = Auth::user();
        if (!$user || !$user->hasAnyRole(['admin'])) {
            return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
        }

        $type = ServiceType::find($id);
        if (!$type) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $request->validate([
            'name' => 'required|string|max:255|unique:service_types,name,' . $id,
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

        $type = ServiceType::find($id);
        if (!$type) {
            return response()->json(['success' => false, 'message' => 'Not found'], 404);
        }

        $type->delete();

        return response()->json(['success' => true]);
    }
}
<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\ServiceType;

class ServiceTypeController extends Controller
{
  public function index(Request $request)
  {
    $types = ServiceType::orderBy('name')->get();
    return response()->json(['success' => true, 'data' => $types]);
  }

  public function store(Request $request)
  {
    $user = $request->user();
    if (!$user || !$user->hasAnyRole(['admin'])) {
      return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }

    $data = $request->validate([
      'name' => 'required|string|max:191|unique:service_types,name',
    ]);

    $type = ServiceType::create($data);

    return response()->json(['success' => true, 'data' => $type], 201);
  }

  public function update(Request $request, $id)
  {
    $user = $request->user();
    if (!$user || !$user->hasAnyRole(['admin'])) {
      return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }

    $type = ServiceType::findOrFail($id);

    $data = $request->validate([
      'name' => 'required|string|max:191|unique:service_types,name,' . $type->id,
    ]);

    $type->update($data);

    return response()->json(['success' => true, 'data' => $type]);
  }

  public function destroy(Request $request, $id)
  {
    $user = $request->user();
    if (!$user || !$user->hasAnyRole(['admin'])) {
      return response()->json(['success' => false, 'message' => 'Forbidden'], 403);
    }

    $type = ServiceType::findOrFail($id);
    $type->delete();

    return response()->json(['success' => true, 'message' => 'Deleted']);
  }
}
