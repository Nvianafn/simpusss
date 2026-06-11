<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api; use App\Http\Controllers\Controller; use App\Models\HasilKesehatan; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request; use Illuminate\Validation\Rule;
class KesehatanController extends Controller {
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    { return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code); }
    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    { return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code); }

 public function latest(string $nim): JsonResponse { $h=HasilKesehatan::where('nim',$nim)->latest('tanggal_cek')->latest('id')->first(); return $h?$this->ok('Status kesehatan terkini',['nim'=>$nim,'is_eligible'=>$h->status_kesehatan==='layak','pemeriksaan'=>$h]):$this->fail('Belum ada pemeriksaan kesehatan',['nim'=>$nim,'is_eligible'=>false],404); }
 public function history(string $nim): JsonResponse { return $this->ok('Riwayat kesehatan',HasilKesehatan::where('nim',$nim)->latest('tanggal_cek')->paginate(15)); }
 public function store(Request $request): JsonResponse { $data=$request->validate(['nim'=>'required|string','tanggal_cek'=>'required|date','tinggi_badan'=>'required|integer|min:1','berat_badan'=>'required|integer|min:1','tekanan_darah'=>'required|string|max:32','hasil_pemeriksaan'=>'required|string','status_kesehatan'=>['required',Rule::in(['layak','tidak_layak'])]]); return $this->ok('Hasil pemeriksaan dibuat',HasilKesehatan::create($data),201); }
 public function update(Request $request,int $id): JsonResponse { $h=HasilKesehatan::findOrFail($id); $data=$request->validate(['tanggal_cek'=>'sometimes|date','tinggi_badan'=>'sometimes|integer|min:1','berat_badan'=>'sometimes|integer|min:1','tekanan_darah'=>'sometimes|string|max:32','hasil_pemeriksaan'=>'sometimes|string','status_kesehatan'=>['sometimes',Rule::in(['layak','tidak_layak'])]]); $h->update($data); return $this->ok('Hasil pemeriksaan diperbarui',$h->fresh()); }
}
