<?php
declare(strict_types=1);
namespace App\Http\Controllers\Api; use App\Http\Controllers\Controller; use App\Models\Pembayaran; use Illuminate\Http\JsonResponse; use Illuminate\Http\Request;
class PembayaranController extends Controller {
    private function ok(string $message, mixed $data = null, int $code = 200): JsonResponse
    { return response()->json(['status' => 'success', 'message' => $message, 'data' => $data], $code); }
    private function fail(string $message, mixed $data = null, int $code = 422): JsonResponse
    { return response()->json(['status' => 'error', 'message' => $message, 'data' => $data], $code); }

 public function index(Request $request): JsonResponse { $query=Pembayaran::query(); foreach(['nim','jenis_pembayaran','status_pembayaran'] as $field) { if($request->filled($field)) $query->where($field,$request->input($field)); } return $this->ok('Daftar pembayaran',$query->latest()->paginate($request->integer('per_page',15))); }
 public function byNim(string $nim): JsonResponse { return $this->ok('Tagihan mahasiswa',Pembayaran::where('nim',$nim)->latest()->get()); }
 public function store(Request $request): JsonResponse { $data=$request->validate(['nim'=>'required|string','kode_tagihan'=>'required|string|unique:pembayaran,kode_tagihan','jenis_pembayaran'=>'required|string','nominal'=>'required|numeric|min:0','jatuh_tempo'=>'nullable|date']); $data['status_pembayaran']='belum_bayar'; return $this->ok('Tagihan dibuat',Pembayaran::create($data),201); }
 public function confirm(Request $request,int $id): JsonResponse { $p=Pembayaran::findOrFail($id); $data=$request->validate(['metode_pembayaran'=>'required|string|max:255','tanggal_bayar'=>'required|date']); $p->update($data + ['status_pembayaran'=>'lunas']); return $this->ok('Pembayaran dikonfirmasi',$p->fresh()); }
 public function status(Request $request,string $nim): JsonResponse { $jenis=(string)$request->query('jenis_pembayaran','biaya_ppl'); $paid=Pembayaran::where('nim',$nim)->where('jenis_pembayaran',$jenis)->where('status_pembayaran','lunas')->exists(); return $this->ok('Status pembayaran',['nim'=>$nim,'jenis_pembayaran'=>$jenis,'is_paid'=>$paid]); }
}
