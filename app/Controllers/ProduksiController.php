<?php

namespace App\Controllers;

use App\Controllers\BaseController;
use App\Models\ProductionModel;
use App\Models\ItemModel;
use App\Models\SubconModel;
use App\Models\PartnerModel;

class ProduksiController extends BaseController
{
    // ==================== DASHBOARD SHIFT PRODUKSI ====================

    public function dashboard()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $today = date('Y-m-d');

        $data['antrean_material'] = $db->table('items')->where('category', 'Material')->countAllResults();
        $data['antrean_cutting']  = $db->table('items')->where('category', 'Cutting')->countAllResults();
        $data['antrean_op1']      = $db->table('items')->groupStart()->where('category', 'WIP OP1')->orWhere('category', 'OP 1')->orWhere('category', 'OP1')->groupEnd()->countAllResults();

        $subconIns = $db->table('subcons')->where('type', 'IN')->get()->getResultArray();
        $totSubconPending = 0;
        foreach ($subconIns as $sin) {
            $qtySin = (float) filter_var($sin['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $sudahCek = $db->table('productions')->where('subcon_id', $sin['id'])->selectSum('quantity')->get()->getRow()->quantity ?? 0;
            $totSubconPending += max(0, $qtySin - (float)$sudahCek);
        }
        $data['antrean_qc_subcon'] = $totSubconPending;

        $qcRecords = $db->table('productions')->where('category', 'QC Pengecekan')->get()->getResultArray();
        $totalNgAkumulasi = 0;
        foreach ($qcRecords as $qc) {
            $qtyNg = (float)($qc['qty_ng'] ?? 0);
            if ($qtyNg == 0) {
                $qtyNg = (float)($qc['ng_repair_burry'] ?? 0) + (float)($qc['ng_repair_belum'] ?? 0) + (float)($qc['ng_repair_dimensi'] ?? 0)
                       + (float)($qc['ng_reject_gompal'] ?? 0) + (float)($qc['ng_reject_baret'] ?? 0) + (float)($qc['ng_reject_hole'] ?? 0)
                       + (float)($qc['ng_reject_replating'] ?? 0) + (float)($qc['ng_reject_dacon'] ?? 0) + (float)($qc['ng_reject_panjang'] ?? 0) + (float)($qc['ng_reject_pendek'] ?? 0);
            }
            $totalNgAkumulasi += $qtyNg;
        }

        $alreadyRepaired = $db->table('productions')
                            ->where('category !=', 'QC Pengecekan')
                            ->groupStart()
                                ->like('material_name', 'Part NG')
                                ->orLike('status', 'Repair')
                                ->orLike('status', 'Retur')
                            ->groupEnd()
                            ->selectSum('quantity')
                            ->get()->getRow()->quantity ?? 0;

        $data['antrean_rework'] = max(0, $totalNgAkumulasi - (float)$alreadyRepaired);

        $prodToday = $db->table('productions')
                        ->where('date', $today)
                        ->where('category !=', 'QC Pengecekan')
                        ->selectSum('quantity')
                        ->get()
                        ->getRow()->quantity ?? 0;
        $data['output_today'] = (float)$prodToday;

        $qcOkToday = $db->table('productions')
                        ->where('date', $today)
                        ->where('category', 'QC Pengecekan')
                        ->selectSum('qty_ok')
                        ->get()
                        ->getRow()->qty_ok ?? 0;
        $data['qc_ok_today'] = (float)$qcOkToday;

        $data['recent_activities'] = $db->table('productions')
                                        ->orderBy('id', 'DESC')
                                        ->limit(6)
                                        ->get()
                                        ->getResultArray();

        $data['title'] = 'Dashboard Shift Produksi - PT Sigma';

        return view('produksi/dashboard', $data);
    }

    // ==================== RIWAYAT PRODUKSI & QC ====================

    public function riwayat()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $prodModel = new ProductionModel();
        $rawKategori = $this->request->getGet('kategori');
        $kategori = $rawKategori ? urldecode(str_replace('+', ' ', $rawKategori)) : 'Semua';

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $builder = $prodModel;

        if ($kategori && $kategori !== 'Semua') {
            if ($kategori === 'QC') {
                $builder = $builder->groupStart()
                                   ->where('category', 'QC Pengecekan')
                                   ->orLike('category', 'QC')
                                   ->orLike('status', 'QC')
                                   ->groupEnd();
                $data['active_tab'] = 'QC';
            } elseif ($kategori === 'Repair') {
                $builder = $builder->groupStart()
                                   ->like('material_name', 'Part NG')
                                   ->orLike('status', 'Repair')
                                   ->orLike('status', 'Retur')
                                   ->orLike('status', 'Kirim Repair')
                                   ->orLike('status', 'Proses Retur')
                                   ->groupEnd();
                $data['active_tab'] = 'Repair';
            } elseif ($kategori === 'WIP OP1' || $kategori === 'WIPOP1' || $kategori === 'OP 1' || $kategori === 'OP1' || $kategori === 'OP 2' || $kategori === 'OP2') {
                $builder = $builder->groupStart()
                                   ->where('category', 'WIP OP1')
                                   ->orWhere('category', 'WIPOP1')
                                   ->orWhere('category', 'OP 1')
                                   ->orWhere('category', 'OP1')
                                   ->orLike('status', 'OP1')
                                   ->orLike('status', 'OP 1')
                                   ->orLike('status', 'OP2')
                                   ->groupEnd();
                $data['active_tab'] = 'WIP OP1';
            } elseif ($kategori === 'WIP Final' || $kategori === 'WIPFinal') {
                $builder = $builder->groupStart()
                                   ->where('category', 'WIP Final')
                                   ->orWhere('category', 'WIPFinal')
                                   ->orLike('status', 'WIP Final')
                                   ->orLike('status', 'Repair Selesai')
                                   ->groupEnd();
                $data['active_tab'] = 'WIP Final';
            } else {
                $builder = $builder->where('category', $kategori);
                $data['active_tab'] = $kategori;
            }
        } else {
            $data['active_tab'] = 'Semua';
        }

        // Filter Tanggal
        if (!empty($startDate)) {
            $builder = $builder->where('date >=', $startDate);
        }
        if (!empty($endDate)) {
            $builder = $builder->where('date <=', $endDate);
        }

        $data['productions'] = $builder->orderBy('id', 'DESC')->findAll();
        $data['start_date']  = $startDate;
        $data['end_date']    = $endDate;

        return view('produksi/riwayat', $data);
    }

    // ==================== EDIT & DELETE BERDASARKAN TANGGAL INPUT (created_at) ====================

    public function edit($id)
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $prodModel = new ProductionModel();
        $production = $prodModel->find($id);

        if (!$production) {
            return redirect()->to('/produksi/riwayat')->with('error', 'Data produksi tidak ditemukan.');
        }

        $tanggalInput = date('Y-m-d', strtotime($production['created_at']));
        if ($tanggalInput !== date('Y-m-d')) {
            return redirect()->to('/produksi/riwayat')->with('error', 'Akses ditolak! Data yang di-input pada hari sebelumnya tidak dapat diedit.');
        }

        $data['production'] = $production;
        $data['title'] = 'Edit Data Produksi - PT Sigma';

        return view('produksi/form_edit', $data);
    }

    public function update($id)
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $prodModel = new ProductionModel();
        $production = $prodModel->find($id);

        if (!$production) {
            return redirect()->to('/produksi/riwayat')->with('error', 'Data tidak ditemukan.');
        }

        $tanggalInput = date('Y-m-d', strtotime($production['created_at']));
        if ($tanggalInput !== date('Y-m-d')) {
            return redirect()->to('/produksi/riwayat')->with('error', 'Akses ditolak! Batas waktu edit data ini sudah lewat.');
        }

        $tglProd          = $this->request->getVar('date');
        $shift            = $this->request->getVar('shift');
        $operator         = $this->request->getVar('operator_name');
        
        $qtyPakaiBaru     = (float) $this->request->getVar('material_amount_qty');
        $satuanPakaiBaru  = $this->request->getVar('material_amount_unit');
        $materialAmountGabung = $qtyPakaiBaru . ' ' . $satuanPakaiBaru;

        $qtyOutputBaru    = (float) $this->request->getVar('quantity');
        $satuanOutputBaru = $this->request->getVar('unit');

        $prodModel->update($id, [
            'date'            => $tglProd,
            'shift'           => $shift,
            'operator_name'   => $operator,
            'material_amount' => $materialAmountGabung,
            'quantity'        => $qtyOutputBaru,
            'unit'            => $satuanOutputBaru
        ]);

        return redirect()->to('/produksi/riwayat')->with('success', 'Data produksi berhasil diperbarui.');
    }

    public function delete($id)
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $prodModel = new ProductionModel();
        $itemModel = new ItemModel();

        $production = $prodModel->find($id);

        if (!$production) {
            return redirect()->to('/produksi/riwayat')->with('error', 'Data tidak ditemukan.');
        }

        $tanggalInput = date('Y-m-d', strtotime($production['created_at']));
        if ($tanggalInput !== date('Y-m-d')) {
            return redirect()->to('/produksi/riwayat')->with('error', 'Akses ditolak! Data yang di-input pada hari sebelumnya tidak dapat dihapus.');
        }

        // Kembalikan stok material asal jika ada pemakaian material
        $matAmountStr = $production['material_amount'] ?? '';
        $qtyKeluar = (float)filter_var($matAmountStr, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if ($qtyKeluar > 0 && !empty($production['material_name'])) {
            $itemMaster = $itemModel->where('item_name', $production['material_name'])->first();
            if (!$itemMaster) {
                $itemMaster = $itemModel->where('item_name', $production['item_name'])->first();
            }

            if ($itemMaster) {
                $currentStock = (float)filter_var($itemMaster['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $newStock = $currentStock + $qtyKeluar;
                $itemModel->update($itemMaster['id'], [
                    'stock' => $newStock . ' ' . ($itemMaster['unit'] ?? 'Pcs')
                ]);
            }
        }

        $prodModel->delete($id);

        return redirect()->to('/produksi/riwayat')->with('success', 'Data produksi berhasil dihapus dan stok material berhasil dikembalikan!');
    }

    // ==================== TAHAP 1: PROSES OP 1 (Cutting) ====================

    public function formCutting()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }
        $itemModel = new ItemModel();
        $data['items'] = $itemModel->where('category', 'Material')->findAll();
        return view('produksi/form_cutting', $data);
    }

    public function storeCutting()
    {
        $prodModel = new ProductionModel();
        $itemModel = new ItemModel();

        $date             = $this->request->getVar('date');
        $shift            = $this->request->getVar('shift');
        $operatorName     = $this->request->getVar('operator_name'); 
        $materialAsal     = trim($this->request->getVar('material_asal')); 
        $qtyMaterialPakai = (float) $this->request->getVar('qty_material_pakai'); 
        $unitPakai        = $this->request->getVar('unit_pakai'); 
        $hasilCutting     = $this->request->getVar('item_hasil_cutting'); 
        $quantity         = (float) $this->request->getVar('quantity'); 
        $unit             = 'Pcs'; 

        $barangAsal = $itemModel->where('item_name', $materialAsal)->where('category', 'Material')->first();

        if (!$barangAsal) {
            return redirect()->back()->with('error', 'Gagal! Material asal "' . $materialAsal . '" tidak ditemukan di kategori Material.');
        }

        $stokTersedia = (float) filter_var($barangAsal['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

        if ($stokTersedia < $qtyMaterialPakai) {
            return redirect()->back()->with('error', 'Gagal! Stok material mentah tidak mencukupi (Tersedia: ' . $stokTersedia . ').');
        }

        $satuanAsal = preg_replace('/[0-9,\.\s]/', '', $barangAsal['stock']); 
        if (empty($satuanAsal)) {
            $satuanAsal = 'Batang';
        }
        $sisaStokBaru = ($stokTersedia - $qtyMaterialPakai) . ' ' . $satuanAsal;
        
        $itemModel->update($barangAsal['id'], ['stock' => $sisaStokBaru]);

        $barangTujuan = $itemModel->where('item_name', $hasilCutting)->where('category', 'Cutting')->first();
        if ($barangTujuan) {
            $stokTujuanTersedia = (float) filter_var($barangTujuan['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $itemModel->update($barangTujuan['id'], ['stock' => ($stokTujuanTersedia + $quantity) . ' Pcs']);
        } else {
            $itemModel->save([
                'item_code' => 'CUT-' . rand(100, 999),
                'item_name' => $hasilCutting,
                'category'  => 'Cutting',
                'stock'     => $quantity . ' Pcs',
                'unit'      => $unit
            ]);
        }

        $prodModel->save([
            'date'              => $date,
            'shift'             => $shift,
            'operator_name'     => $operatorName,
            'material_name'     => $materialAsal,                                     
            'material_amount'   => $qtyMaterialPakai . ' ' . $unitPakai, 
            'item_name'         => $hasilCutting,
            'category'          => 'Cutting',
            'quantity'          => $quantity,
            'unit'              => $unit,
            'status'            => 'Cutting',
            'created_by'        => session()->get('id')
        ]);

        return redirect()->to('/produksi/riwayat?kategori=Cutting')->with('success', 'Berhasil! Proses OP 1 oleh ' . $operatorName . ' tersimpan.');
    }

    // ==================== TAHAP 2: PROSES OP 2 ====================

    public function formOp1()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }
        $itemModel = new ItemModel();
        $data['items'] = $itemModel->where('category', 'Cutting')->findAll();

        return view('produksi/form_op1', $data);
    }

    public function storeOp1()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $prodModel = new ProductionModel();
        $itemModel = new ItemModel();

        $date      = $this->request->getVar('date');
        $shift     = $this->request->getVar('shift');
        $operator  = $this->request->getVar('operator_name');
        $material  = $this->request->getVar('material_asal'); 
        $qtyPakai  = (float) $this->request->getVar('qty_material_pakai');
        $quantity  = (float) $this->request->getVar('quantity');
        $unit      = 'Pcs';

        $cleanMaterial = strtoupper(trim($material));
        if (str_contains($cleanMaterial, 'COLLAR Ø 11.5 X Ø 6.5 FORGING')) {
            $namaHasil = 'COLLAR RADIATOR 6.5MM';
        } elseif (str_contains($cleanMaterial, 'COLLAR Ø 12.5 X Ø8.0 FORGING')) {
            $namaHasil = 'COLLAR RADIATOR 8.0MM';
        } else {
            $namaHasil = $this->request->getVar('item_hasil_op1') ?? ($material . ' OP1');
        }

        $barangAsal = $itemModel->where('item_name', $material)->where('category', 'Cutting')->first();
        if (!$barangAsal) {
            return redirect()->back()->with('error', 'Gagal! Barang asal "' . $material . '" tidak ditemukan di kategori Cutting.');
        }

        $stokTersedia = (float) filter_var($barangAsal['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if ($stokTersedia < $qtyPakai) {
            return redirect()->back()->with('error', 'Gagal! Stok barang asal tidak mencukupi (Tersedia: ' . $stokTersedia . ').');
        }

        $satuanAsal = preg_replace('/[0-9,\.\s]/', '', $barangAsal['stock']);
        if (empty($satuanAsal)) $satuanAsal = 'Pcs';
        $itemModel->update($barangAsal['id'], ['stock' => ($stokTersedia - $qtyPakai) . ' ' . $satuanAsal]);

        $barangTujuan = $itemModel->where('item_name', $namaHasil)->groupStart()->where('category', 'WIP OP1')->orWhere('category', 'OP 1')->orWhere('category', 'OP1')->groupEnd()->first();
        if ($barangTujuan) {
            $stokTujuanTersedia = (float) filter_var($barangTujuan['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $itemModel->update($barangTujuan['id'], ['stock' => ($stokTujuanTersedia + $quantity) . ' Pcs']);
        } else {
            $itemModel->save([
                'item_code' => 'OP1-' . rand(100, 999),
                'item_name' => $namaHasil,
                'category'  => 'WIP OP1',
                'stock'     => $quantity . ' Pcs',
                'unit'      => $unit
            ]);
        }

        $prodModel->save([
            'date'            => $date,
            'shift'           => $shift,
            'operator_name'   => $operator,
            'material_name'   => $material,
            'material_amount' => $qtyPakai . ' Pcs',
            'item_name'       => $namaHasil,
            'category'        => 'WIP OP1',
            'quantity'        => $quantity,
            'unit'            => $unit,
            'status'          => 'Proses Mesin OP2',
            'created_by'      => session()->get('id')
        ]);

        return redirect()->to('/produksi/riwayat?kategori=' . urlencode('WIP OP1'))->with('success', 'Berhasil! Proses OP 2 tersimpan dan stok diperbarui.');
    }

    // ==================== TAHAP 3: PROSES WIP ====================

    public function formFinal()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }
        $itemModel = new ItemModel();
        $data['items'] = $itemModel->whereIn('category', ['Material', 'Cutting', 'OP 1', 'WIP OP1', 'WIPOP1', 'OP1', 'WIP Final', 'WIPFinal'])
                                    ->orderBy('category', 'ASC')
                                    ->findAll();
        return view('produksi/form_final', $data);
    }

    public function storeFinal()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }
        
        return $this->prosesProduksiFleksibel(
            ['Material', 'Cutting', 'OP 1', 'WIP OP1', 'WIPOP1', 'OP1', 'WIP Final', 'WIPFinal'], 
            'WIP Final', 
            'Proses WIP (Direct/Flexible)', 
            'item_hasil_final'
        );
    }

    // ==================== TAHAP 4: PENGECEKAN QC ====================

    public function formQc()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }
        
        $itemModel   = new ItemModel();
        $prodModel   = new ProductionModel();
        $subconModel = new SubconModel();

        $data['wip_final'] = $itemModel->whereIn('category', ['WIP Final', 'WIPFinal'])->orderBy('id', 'DESC')->findAll();
        
        $subconInList = $subconModel->where('type', 'IN')->orderBy('id', 'ASC')->findAll();
        $availableSubconIn = [];

        foreach ($subconInList as $sub) {
            $qtyKirim = (float) filter_var($sub['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $unitTrans = str_contains(strtolower($sub['quantity']), 'kg') ? 'Kg' : 'Pcs';
            
            $sudahCek = $prodModel->where('subcon_id', $sub['id'])
                                    ->selectSum('quantity')
                                    ->get()
                                    ->getRow()->quantity ?? 0;

            $sisaBelumCek = max(0, $qtyKirim - (float)$sudahCek);

            if ($sisaBelumCek > 0) {
                $sub['qty_total']      = $qtyKirim;
                $sub['checked_qty']    = (float)$sudahCek;
                $sub['sisa_qty']       = $sisaBelumCek;
                $sub['unit_transaksi'] = $unitTrans;
                $availableSubconIn[]   = $sub;
            }
        }

        $data['subcon_in'] = $availableSubconIn;

        return view('produksi/form_qc', $data);
    }

    public function storeQc()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $prodModel = new ProductionModel();
        $itemModel = new ItemModel();

        $date         = $this->request->getVar('date');
        $shift        = $this->request->getVar('shift') ?? 'Shift 1';
        $operatorName = $this->request->getVar('operator_name');
        $sumberBarang = $this->request->getVar('sumber_barang'); 
        $rawItemName  = $this->request->getVar('item_name'); 
        $namaVendor   = $this->request->getVar('nama_vendor');
        $subconId     = $this->request->getVar('subcon_id'); 
        $quantity     = (float) $this->request->getVar('quantity');
        $unit         = $this->request->getVar('unit') ?? 'Pcs';
        $qtyOk        = (float) $this->request->getVar('qty_ok');
        
        $ngRepBurry   = (float) $this->request->getVar('ng_repair_burry');
        $ngRepBelum   = (float) $this->request->getVar('ng_repair_belum');
        $ngRepDim     = (float) $this->request->getVar('ng_repair_dimensi');
        $totalRepair  = $ngRepBurry + $ngRepBelum + $ngRepDim;

        $ngRejGompal    = (float) $this->request->getVar('ng_reject_gompal');
        $ngRejBaret     = (float) $this->request->getVar('ng_reject_baret');
        $ngRejHole      = (float) $this->request->getVar('ng_reject_hole');
        $ngRejReplating = (float) $this->request->getVar('ng_reject_replating');
        $ngRejDacon     = (float) $this->request->getVar('ng_reject_dacon');
        $ngRejPanjang   = (float) $this->request->getVar('ng_reject_panjang');
        $ngRejPendek    = (float) $this->request->getVar('ng_reject_pendek');
        
        $totalReject    = $ngRejGompal + $ngRejBaret + $ngRejHole + $ngRejReplating + $ngRejDacon + $ngRejPanjang + $ngRejPendek;
        $totalNg        = $totalRepair + $totalReject;

        $catatan      = $this->request->getVar('catatan');
        $itemName     = trim(preg_replace('/\(Stok:.*\)/i', '', $rawItemName));

        if ($sumberBarang === 'In-house') {
            $barangWip = $itemModel->where('item_name', $itemName)
                                    ->groupStart()
                                        ->where('category', 'WIP Final')
                                        ->orWhere('category', 'WIPFinal')
                                    ->groupEnd()
                                    ->first();

            if ($barangWip) {
                $stokTersedia = (float) filter_var($barangWip['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                if ($stokTersedia < $quantity) {
                    return redirect()->back()->withInput()->with('error', 'Gagal! Jumlah QC melebihi sisa stok WIP di gudang.');
                }
                $itemModel->update($barangWip['id'], ['stock' => ($stokTersedia - $quantity) . ' ' . $unit]);
            }
            $materialAsalLabel = 'WIP (Gudang)';
            $savedSubconId     = null;
        } else {
            $materialAsalLabel = 'Subcon IN (' . ($namaVendor ? $namaVendor : 'Vendor') . ')';
            $savedSubconId     = !empty($subconId) ? (int)$subconId : null;
        }

        $statusDetail = "QC Check | OK: $qtyOk $unit | Repair: $totalRepair $unit | Reject: $totalReject $unit";
        if (!empty($catatan)) {
            $statusDetail .= " - Catatan: $catatan";
        }

        $prodModel->save([
            'date'                => $date,
            'shift'               => $shift,
            'operator_name'       => $operatorName,
            'material_name'       => $materialAsalLabel, 
            'material_amount'     => $quantity . ' ' . $unit, 
            'item_name'           => $itemName,
            'category'            => 'QC Pengecekan', 
            'quantity'            => $quantity, 
            'unit'                => $unit, 
            'weight_kg'           => ($unit === 'Kg') ? $quantity : 0,
            'qty_ok'              => $qtyOk,
            'qty_ng'              => $totalNg,
            'ng_repair_burry'     => $ngRepBurry,
            'ng_repair_belum'     => $ngRepBelum,
            'ng_repair_dimensi'   => $ngRepDim,
            'ng_reject_gompal'    => $ngRejGompal,
            'ng_reject_baret'     => $ngRejBaret,
            'ng_reject_hole'      => $ngRejHole,
            'ng_reject_replating' => $ngRejReplating,
            'ng_reject_dacon'     => $ngRejDacon,
            'ng_reject_panjang'   => $ngRejPanjang,
            'ng_reject_pendek'    => $ngRejPendek,
            'material_info'       => $catatan,
            'status'              => $statusDetail,
            'subcon_id'           => $savedSubconId,
            'created_by'          => session()->get('id')
        ]);

        return redirect()->to('/produksi/riwayat?kategori=QC')->with('success', 'Berhasil! Laporan QC untuk ' . $itemName . ' telah disimpan.');
    }

    // ==================== TAHAP 5: REWORK / RETUR PART NG ====================

    public function formRepair()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $itemModel = new ItemModel();
        $partnerModel = new PartnerModel();

        $qcItemRows = $db->table('productions')
                         ->where('category', 'QC Pengecekan')
                         ->select('item_name')
                         ->distinct()
                         ->get()
                         ->getResultArray();

        $wipItems = $itemModel->whereIn('category', ['WIP Final', 'WIPFinal', 'NG'])->findAll();

        $candidateNames = [];
        foreach ($qcItemRows as $q) {
            $name = trim($q['item_name']);
            if (!empty($name) && !in_array($name, $candidateNames)) {
                $candidateNames[] = $name;
            }
        }
        foreach ($wipItems as $w) {
            $name = trim($w['item_name']);
            if (!empty($name) && !in_array($name, $candidateNames)) {
                $candidateNames[] = $name;
            }
        }

        $availableRepair = [];

        foreach ($candidateNames as $candName) {
            $cleanName = strtoupper($candName);

            $qcRecords = $db->table('productions')
                            ->where('UPPER(TRIM(item_name))', $cleanName)
                            ->where('category', 'QC Pengecekan')
                            ->get()
                            ->getResultArray();

            $totalNgKalkulasi = 0;
            foreach ($qcRecords as $qc) {
                $qtyNg = (float)($qc['qty_ng'] ?? 0);
                if ($qtyNg == 0) {
                    $repB     = (float)($qc['ng_repair_burry'] ?? 0);
                    $repBl    = (float)($qc['ng_repair_belum'] ?? 0);
                    $repD     = (float)($qc['ng_repair_dimensi'] ?? 0);
                    $rejG     = (float)($qc['ng_reject_gompal'] ?? 0);
                    $rejB     = (float)($qc['ng_reject_baret'] ?? 0);
                    $rejHole  = (float)($qc['ng_reject_hole'] ?? 0);
                    $rejRep   = (float)($qc['ng_reject_replating'] ?? 0);
                    $rejDacon = (float)($qc['ng_reject_dacon'] ?? 0);
                    $rejPanj  = (float)($qc['ng_reject_panjang'] ?? 0);
                    $rejPend  = (float)($qc['ng_reject_pendek'] ?? 0);
                    $qtyNg = $repB + $repBl + $repD + $rejG + $rejB + $rejHole + $rejRep + $rejDacon + $rejPanj + $rejPend;
                }
                $totalNgKalkulasi += $qtyNg;
            }

            $alreadyHandled = $db->table('productions')
                               ->where('category !=', 'QC Pengecekan')
                               ->groupStart()
                                   ->where('UPPER(TRIM(item_name))', $cleanName)
                                   ->orLike('UPPER(material_name)', $cleanName)
                               ->groupEnd()
                               ->groupStart()
                                   ->like('material_name', 'Part NG')
                                   ->orLike('status', 'Repair')
                                   ->orLike('status', 'Retur')
                               ->groupEnd()
                               ->selectSum('quantity')
                               ->get()
                               ->getRow()->quantity ?? 0;

            $sisaAkumulasiNg = max(0, $totalNgKalkulasi - (float)$alreadyHandled);

            $ngMasterItem = $itemModel->where('item_name', $candName)->where('category', 'NG')->first();
            if ($ngMasterItem) {
                $itemModel->update($ngMasterItem['id'], ['stock' => $sisaAkumulasiNg . ' Pcs']);
            } elseif ($totalNgKalkulasi > 0) {
                $itemModel->save([
                    'item_code' => 'NG-' . rand(100, 999),
                    'item_name' => $candName,
                    'category'  => 'NG',
                    'stock'     => $sisaAkumulasiNg . ' Pcs',
                    'unit'      => 'Pcs'
                ]);
            }

            $availableRepair[] = [
                'item_name'   => $candName,
                'sisa_repair' => $sisaAkumulasiNg,
            ];
        }

        usort($availableRepair, function($a, $b) {
            return $b['sisa_repair'] <=> $a['sisa_repair'];
        });

        $data['ng_items'] = $availableRepair;
        $data['vendors']  = $partnerModel->where('type', 'Vendor')->orderBy('name', 'ASC')->findAll();

        return view('produksi/form_repair', $data);
    }

    public function storeRepair()
    {
        if (session()->get('role') != 'produksi' && session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $prodModel   = new ProductionModel();
        $itemModel   = new ItemModel();
        $subconModel = new SubconModel();

        $date         = $this->request->getVar('date');
        $shift        = $this->request->getVar('shift');
        $operatorName = $this->request->getVar('operator_name');
        $itemName     = trim($this->request->getVar('item_name'));
        $qtyRepair    = (float) $this->request->getVar('quantity');
        $tipeRework   = $this->request->getVar('tipe_rework') ?? 'inhouse';
        $tindakan     = $this->request->getVar('tindakan_repair');
        $namaVendor   = $this->request->getVar('vendor_name');
        $catatan      = $this->request->getVar('catatan');

        $itemNg = $itemModel->where('item_name', $itemName)->where('category', 'NG')->first();
        if ($itemNg) {
            $currNg = (float) filter_var($itemNg['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $newNgStock = max(0, $currNg - $qtyRepair) . ' Pcs';
            $itemModel->update($itemNg['id'], ['stock' => $newNgStock]);
        }

        $mapReturMaterial = [
            'COLLAR RADIATOR 6.5MM' => 'COLLAR Ø 11.5 X Ø 6.5 FORGING',
            'COLLAR RADIATOR 8.0MM' => 'COLLAR Ø 12.5 X Ø8.0 FORGING',
        ];

        if ($tipeRework === 'retur') {
            $namaMaterialKonversi = $mapReturMaterial[$itemName] ?? $itemName;
            $monitoring = 'Masuk: 0 Pcs | Sisa: ' . $qtyRepair . ' Pcs (Retur Material)';

            $subconModel->save([
                'date'         => $date,
                'type'         => 'OUT',
                'item_name'    => $namaMaterialKonversi,
                'vendor_name'  => $namaVendor,
                'quantity'     => $qtyRepair . ' Pcs',
                'weight_kg'    => 0,
                'reference_id' => null,
                'monitoring'   => $monitoring,
                'status'       => 'Proses Retur Material di Vendor'
            ]);

            $prodModel->save([
                'date'            => $date,
                'shift'           => $shift,
                'operator_name'   => $operatorName,
                'material_name'   => "Part NG ($itemName) Retur Material",
                'material_amount' => $qtyRepair . ' Pcs',
                'item_name'       => $namaMaterialKonversi,
                'category'        => 'Material',
                'quantity'        => $qtyRepair,
                'unit'            => 'Pcs',
                'status'          => "Retur Material ke Vendor: {$namaVendor} (Asal: {$itemName})",
                'material_info'   => $catatan . ($tindakan ? " - Tindakan: $tindakan" : ''),
                'created_by'      => session()->get('id')
            ]);

            return redirect()->to('/produksi/riwayat?kategori=Repair')->with('success', "Berhasil! {$qtyRepair} Pcs {$itemName} dikonversi menjadi material mentah {$namaMaterialKonversi}.");

        } elseif ($tipeRework === 'vendor') {
            $monitoring = 'Masuk: 0 Pcs | Sisa: ' . $qtyRepair . ' Pcs (Repair Vendor)';

            $subconModel->save([
                'date'         => $date,
                'type'         => 'OUT',
                'item_name'    => $itemName,
                'vendor_name'  => $namaVendor,
                'quantity'     => $qtyRepair . ' Pcs',
                'weight_kg'    => 0,
                'reference_id' => null,
                'monitoring'   => $monitoring,
                'status'       => 'Proses Repair di Vendor'
            ]);

            $prodModel->save([
                'date'            => $date,
                'shift'           => $shift,
                'operator_name'   => $operatorName,
                'material_name'   => "Part NG ($itemName) Kirim Repair Vendor",
                'material_amount' => $qtyRepair . ' Pcs',
                'item_name'       => $itemName,
                'category'        => 'Plating',
                'quantity'        => $qtyRepair,
                'unit'            => 'Pcs',
                'status'          => "Kirim Repair ke Vendor: {$namaVendor} (Tindakan: {$tindakan})",
                'material_info'   => $catatan,
                'created_by'      => session()->get('id')
            ]);

            return redirect()->to('/produksi/riwayat?kategori=Repair')->with('success', "Berhasil! {$qtyRepair} Pcs {$itemName} dikirim repair ke {$namaVendor}.");

        } else {
            $itemWip = $itemModel->where('item_name', $itemName)
                                    ->groupStart()
                                        ->where('category', 'WIP Final')
                                        ->orWhere('category', 'WIPFinal')
                                    ->groupEnd()
                                    ->first();

            if ($itemWip) {
                $currWip = (float) filter_var($itemWip['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $itemModel->update($itemWip['id'], ['stock' => ($currWip + $qtyRepair) . ' Pcs']);
            } else {
                $itemModel->save([
                    'item_code' => 'WIP-' . rand(100, 999),
                    'item_name' => $itemName,
                    'category'  => 'WIP Final',
                    'stock'     => $qtyRepair . ' Pcs',
                    'unit'      => 'Pcs'
                ]);
            }

            $keteranganStatus = "Repair Selesai ➔ Kembali ke WIP (Tindakan: $tindakan)";
            if (!empty($catatan)) {
                $keteranganStatus .= " - Catatan: $catatan";
            }

            $prodModel->save([
                'date'            => $date,
                'shift'           => $shift,
                'operator_name'   => $operatorName,
                'material_name'   => 'Part NG (Repair/Rework)',
                'material_amount' => $qtyRepair . ' Pcs',
                'item_name'       => $itemName,
                'category'        => 'WIP Final',
                'quantity'        => $qtyRepair,
                'unit'            => 'Pcs',
                'status'          => 'Repair Selesai ➔ Kembali ke WIP',
                'material_info'   => $keteranganStatus,
                'created_by'      => session()->get('id')
            ]);

            return redirect()->to('/produksi/riwayat?kategori=Repair')->with('success', "Berhasil! {$qtyRepair} Pcs {$itemName} selesai diperbaiki in-house.");
        }
    }

    // ==================== HELPER FLOW PRODUKSI ====================

    private function prosesProduksiFleksibel($arrayAsalKat, $tujuanKat, $keterangan, $inputNameHasil)
    {
        $prodModel = new ProductionModel();
        $itemModel = new ItemModel();

        $date             = $this->request->getVar('date');
        $shift            = $this->request->getVar('shift');
        $operatorName     = $this->request->getVar('operator_name'); 
        $materialAsal     = trim($this->request->getVar('material_asal')); 
        $qtyMaterialPakai = (float) $this->request->getVar('qty_material_pakai'); 
        $unitPakai        = 'Pcs'; 
        
        $hasilTujuan      = $this->request->getVar($inputNameHasil) ?? $this->request->getVar('item_name'); 
        $quantity         = (float) $this->request->getVar('quantity'); 
        $unit             = 'Pcs'; 

        $barangAsal = null;
        foreach ($arrayAsalKat as $kat) {
            $cek = $itemModel->where('item_name', $materialAsal)->where('category', $kat)->first();
            if ($cek) {
                $barangAsal = $cek;
                break;
            }
        }

        if (!$barangAsal) {
            $barangAsal = $itemModel->where('item_name', $materialAsal)->first();
        }

        if (!$barangAsal) {
            return redirect()->back()->with('error', 'Gagal! Barang asal "' . $materialAsal . '" tidak ditemukan.');
        }

        $stokTersedia = (float) filter_var($barangAsal['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        if ($stokTersedia < $qtyMaterialPakai) {
            return redirect()->back()->with('error', 'Gagal! Stok barang asal tidak mencukupi.');
        }

        $satuanAsal = preg_replace('/[0-9,\.\s]/', '', $barangAsal['stock']);
        if (empty($satuanAsal)) $satuanAsal = 'Pcs';
        $itemModel->update($barangAsal['id'], ['stock' => ($stokTersedia - $qtyMaterialPakai) . ' ' . $satuanAsal]);

        $barangTujuan = $itemModel->where('item_name', $hasilTujuan)->where('category', $tujuanKat)->first();
        if ($barangTujuan) {
            $stokTujuanTersedia = (float) filter_var($barangTujuan['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $itemModel->update($barangTujuan['id'], ['stock' => ($stokTujuanTersedia + $quantity) . ' Pcs']);
        } else {
            $itemModel->save([
                'item_code' => strtoupper(str_replace(' ', '', $tujuanKat)) . '-' . rand(100, 999),
                'item_name' => $hasilTujuan,
                'category'  => $tujuanKat, 
                'stock'     => $quantity . ' Pcs', 
                'unit'      => $unit
            ]);
        }

        $prodModel->save([
            'date'            => $date,
            'shift'           => $shift,
            'operator_name'   => $operatorName,
            'material_name'   => $materialAsal,                                                             
            'material_amount' => $qtyMaterialPakai . ' ' . $unitPakai, 
            'item_name'       => $hasilTujuan,
            'category'        => $tujuanKat, 
            'quantity'        => $quantity, 
            'unit'            => $unit, 
            'status'          => $keterangan, 
            'created_by'      => session()->get('id')
        ]);

        return redirect()->to('/produksi/riwayat?kategori=' . urlencode($tujuanKat))->with('success', 'Berhasil! Proses produksi tersimpan.');
    }
}