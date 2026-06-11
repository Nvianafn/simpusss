<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api;
use App\Http\Controllers\Controller; use App\Models\Mahasiswa; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class MahasiswaController extends Controller {
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    { return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code); }
    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    { return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code); }

 public function index(Request $request): JsonResponse { $query=Mahasiswa::query(); foreach(['prodi','fakultas','angkatan','semester','status'] as $field) { if($request->filled($field)) $query->where($field,$request->input($field)); } if($request->filled('q')) { $term='%'.$request->input('q').'%'; $query->where(fn($q)=>$q->where('nim','like',$term)->orWhere('nama','like',$term)); } return $this->ok('Daftar mahasiswa',$query->latest()->paginate($request->integer('per_page',15))); }
 public function store(Request $request): JsonResponse { $data=$request->validate(['nim'=>'required|string|max:32|unique:mahasiswa,nim','nama'=>'required|string|max:255','email'=>'required|email|unique:mahasiswa,email','prodi'=>'required|string|max:255','fakultas'=>'required|string|max:255','semester'=>'required|integer|min:1|max:14','angkatan'=>'required|integer|min:2000|max:2100','status'=>['required',Rule::in(['aktif','non_aktif','cuti','lulus'])]]); return $this->ok('Mahasiswa dibuat',Mahasiswa::create($data),201); }
 public function show(string $nim): JsonResponse { $m=Mahasiswa::where('nim',$nim)->first(); return $m?$this->ok('Detail mahasiswa',$m):$this->fail('Mahasiswa tidak ditemukan',null,404); }
 public function update(Request $request,string $nim): JsonResponse { $m=Mahasiswa::where('nim',$nim)->firstOrFail(); $data=$request->validate(['nama'=>'sometimes|string|max:255','email'=>['sometimes','email',Rule::unique('mahasiswa','email')->ignore($m->id)],'prodi'=>'sometimes|string|max:255','fakultas'=>'sometimes|string|max:255','semester'=>'sometimes|integer|min:1|max:14','angkatan'=>'sometimes|integer|min:2000|max:2100','status'=>['sometimes',Rule::in(['aktif','non_aktif','cuti','lulus'])]]); $m->update($data); return $this->ok('Mahasiswa diperbarui',$m->fresh()); }
 public function destroy(string $nim): JsonResponse { Mahasiswa::where('nim',$nim)->firstOrFail()->delete(); return $this->ok('Mahasiswa dihapus'); }
 public function status(string $nim): JsonResponse { $m=Mahasiswa::where('nim',$nim)->first(); return $m?$this->ok('Status mahasiswa',['nim'=>$nim,'status'=>$m->status,'is_active'=>$m->status==='aktif']):$this->fail('Mahasiswa tidak ditemukan',['nim'=>$nim,'is_active'=>false],404); }
}
