<?php

namespace App\Controllers;

use App\Models\ItemModel;
use App\Models\PurchaseModel;
use App\Models\SubconModel;
use App\Models\PartnerModel;
use App\Models\ProductionModel;

class AdminController extends BaseController
{
    // ==================== MASTER PARTNER (VENDOR, SUPPLIER, CUSTOMER) ====================

    public function partners()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $partnerModel = new PartnerModel();
        $typeFilter = $this->request->getGet('type');

        if (!empty($typeFilter) && in_array($typeFilter, ['Supplier', 'Vendor', 'Customer'])) {
            $data['partners'] = $partnerModel->where('type', $typeFilter)->orderBy('name', 'ASC')->findAll();
            $data['active_filter'] = $typeFilter;
        } else {
            $data['partners'] = $partnerModel->orderBy('type', 'ASC')->orderBy('name', 'ASC')->findAll();
            $data['active_filter'] = 'Semua';
        }

        $data['title'] = 'Master Mitra (Vendor, Supplier & PT Customer) - PT Sigma';

        return view('admin/partners', $data);
    }

    public function storePartner()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $partnerModel = new PartnerModel();
        $name = trim((string)$this->request->getVar('name'));
        $type = $this->request->getVar('type');

        if (empty($name) || empty($type)) {
            return redirect()->back()->withInput()->with('error', 'Nama dan tipe mitra wajib diisi.');
        }

        $partnerModel->save([
            'name'    => strtoupper($name),
            'type'    => $type,
            'address' => $this->request->getVar('address'),
            'phone'   => $this->request->getVar('phone'),
        ]);

        return redirect()->to('/admin/partners?type=' . $type)->with('success', "Data {$type} berhasil ditambahkan!");
    }

    public function updatePartner($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $partnerModel = new PartnerModel();
        $type = $this->request->getVar('type');

        $partnerModel->update($id, [
            'name'    => strtoupper(trim((string)$this->request->getVar('name'))),
            'type'    => $type,
            'address' => $this->request->getVar('address'),
            'phone'   => $this->request->getVar('phone'),
        ]);

        return redirect()->to('/admin/partners?type=' . $type)->with('success', 'Data mitra berhasil diperbarui!');
    }

    public function deletePartner($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $partnerModel = new PartnerModel();
        $partner = $partnerModel->find($id);
        $type = $partner['type'] ?? '';

        $partnerModel->delete($id);

        return redirect()->to('/admin/partners' . (!empty($type) ? '?type=' . $type : ''))->with('success', 'Data mitra berhasil dihapus!');
    }

    // ==================== DASHBOARD ADMIN ====================

    public function dashboard()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $selectedYear = (int)($this->request->getGet('year') ?? date('Y'));
        $data['selected_year'] = $selectedYear;

        $data['total_items'] = $db->table('items')->countAllResults();

        $rowQc = $db->table('productions')->selectSum('qty_ok')->get()->getRow();
        $totalQcOk = $rowQc ? (float)$rowQc->qty_ok : 0;

        $deliveries = $db->table('deliveries')->get()->getResultArray();
        $totalSent = 0;
        foreach ($deliveries as $d) {
            $totalSent += (float) filter_var($d['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
        }
        $data['total_fg_ready'] = max(0, $totalQcOk - $totalSent);

        $subconOuts = $db->table('subcons')->where('type', 'OUT')->get()->getResultArray();
        $subconIns  = $db->table('subcons')->where('type', 'IN')->get()->getResultArray();
        $totOutPcs = 0; $totOutKg = 0; $totInPcs = 0; $totInKg = 0;

        foreach ($subconOuts as $o) {
            $totOutPcs += (float) filter_var($o['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $totOutKg  += (float) ($o['weight_kg'] ?? 0);
        }
        foreach ($subconIns as $i) {
            $totInPcs += (float) filter_var($i['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $totInKg  += (float) ($i['weight_kg'] ?? 0);
        }

        $data['vendor_outstanding_pcs'] = max(0, $totOutPcs - $totInPcs);
        $data['vendor_outstanding_kg']  = max(0, $totOutKg - $totInKg);
        $data['total_delivered'] = $totalSent;

        $data['count_material'] = $db->table('items')->where('category', 'Material')->countAllResults();
        $data['count_cutting']  = $db->table('items')->where('category', 'Cutting')->countAllResults();
        $data['count_op1']      = $db->table('items')->groupStart()->where('category', 'WIP OP1')->orWhere('category', 'OP 1')->orWhere('category', 'OP1')->groupEnd()->countAllResults();
        $data['count_wip']      = $db->table('items')->groupStart()->where('category', 'WIP Final')->orWhere('category', 'WIPFinal')->groupEnd()->countAllResults();
        $data['count_qc']       = $db->table('productions')->where('category', 'QC Pengecekan')->countAllResults();
        $data['count_fg']       = $db->table('items')->where('category', 'FG')->countAllResults();

        $ngCategories = [
            'Burry Grooving'     => 'ng_repair_burry',
            'Belum Proses'       => 'ng_repair_belum',
            'Grooving (Dimensi)' => 'ng_repair_dimensi',
            'Hole Sempit'        => 'ng_reject_hole',
            'Replating'          => 'ng_reject_replating',
            'Dacon'              => 'ng_reject_dacon',
            'Gompal'             => 'ng_reject_gompal',
            'Baret'              => 'ng_reject_baret',
            'Panjang'            => 'ng_reject_panjang',
            'Pendek'             => 'ng_reject_pendek'
        ];

        $data['ng_labels'] = array_keys($ngCategories);

        $monthlyNgDatasets = [];
        $colors = ['#dc3545', '#fd7e14', '#ffc107', '#20c997', '#0d6efd', '#6610f2', '#d63384', '#6c757d', '#198754', '#0dcaf0'];
        $colorIdx = 0;

        foreach ($ngCategories as $label => $column) {
            $monthlyValues = array_fill(1, 12, 0);
            $qcRows = $db->table('productions')
                         ->where('category', 'QC Pengecekan')
                         ->where("YEAR(date)", $selectedYear)
                         ->select("date, $column as val")
                         ->get()
                         ->getResultArray();

            foreach ($qcRows as $row) {
                if (!empty($row['date'])) {
                    $m = (int)date('m', strtotime($row['date']));
                    if ($m >= 1 && $m <= 12) {
                        $monthlyValues[$m] += (float)($row['val'] ?? 0);
                    }
                }
            }

            $monthlyNgDatasets[] = [
                'label' => $label,
                'data' => array_values($monthlyValues),
                'backgroundColor' => $colors[$colorIdx % count($colors)],
                'borderRadius' => 2
            ];
            $colorIdx++;
        }

        $data['chart_months'] = ['Januari', 'Februari', 'Maret', 'April', 'Mei', 'Juni', 'Juli', 'Agustus', 'September', 'Oktober', 'November', 'Desember'];
        $data['monthly_ng_datasets'] = $monthlyNgDatasets;

        $yearlyNgDatasets = [];
        $yearsList = [];
        $currentYearNum = (int)date('Y');
        for ($y = $currentYearNum - 4; $y <= $currentYearNum; $y++) {
            $yearsList[] = (string)$y;
        }

        $colorIdx2 = 0;
        foreach ($ngCategories as $label => $column) {
            $yearlyValues = [];
            foreach ($yearsList as $yStr) {
                $rowY = $db->table('productions')
                         ->where('category', 'QC Pengecekan')
                         ->where("YEAR(date)", (int)$yStr)
                         ->selectSum($column, 'total')
                         ->get()
                         ->getRow();
                $sumY = $rowY ? $rowY->total : 0;
                $yearlyValues[] = (float)$sumY;
            }

            $yearlyNgDatasets[] = [
                'label' => $label,
                'data' => $yearlyValues,
                'backgroundColor' => $colors[$colorIdx2 % count($colors)],
                'borderRadius' => 2
            ];
            $colorIdx2++;
        }

        $data['chart_years'] = $yearsList;
        $data['yearly_ng_datasets'] = $yearlyNgDatasets;

        $data['recent_productions'] = $db->table('productions')->orderBy('id', 'DESC')->limit(5)->get()->getResultArray();
        $data['recent_deliveries']  = $db->table('deliveries')->orderBy('id', 'DESC')->limit(5)->get()->getResultArray();

        $data['title'] = 'Dashboard Admin - PT Sigma Manufacturing';

        return view('admin/dashboard', $data);
    }

    // ==================== MONITORING PRODUKSI KHUSUS ADMIN ====================

    public function produksi()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $prodModel = new \App\Models\ProductionModel();
        $rawKategori = $this->request->getGet('kategori');
        $kategori = $rawKategori ? urldecode(str_replace('+', ' ', $rawKategori)) : 'Semua';

        $startDate = $this->request->getGet('start_date');
        $endDate = $this->request->getGet('end_date');

        $builder = $prodModel;

        if ($kategori && $kategori !== 'Semua') {
            if ($kategori === 'QC Pengecekan' || $kategori === 'QC') {
                $builder = $builder->groupStart()
                                   ->where('category', 'QC Pengecekan')
                                   ->orLike('category', 'QC')
                                   ->orLike('status', 'QC')
                                   ->groupEnd();
                $data['active_tab'] = 'QC Pengecekan';
            } elseif ($kategori === 'WIP OP1' || $kategori === 'WIPOP1' || $kategori === 'OP 1' || $kategori === 'OP1' || $kategori === 'Cutting') {
                $builder = $builder->groupStart()
                                   ->where('category', 'Cutting')
                                   ->orWhere('category', 'WIP OP1')
                                   ->orWhere('category', 'WIPOP1')
                                   ->orWhere('category', 'OP 1')
                                   ->orWhere('category', 'OP1')
                                   ->orLike('status', 'OP1')
                                   ->orLike('status', 'OP 1')
                                   ->groupEnd();
                $data['active_tab'] = 'Cutting';
            } elseif ($kategori === 'WIP OP2' || $kategori === 'WIP OP1' || $kategori === 'OP 2') {
                $builder = $builder->groupStart()
                                   ->where('category', 'WIP OP1')
                                   ->orWhere('category', 'WIPOP1')
                                   ->orWhere('category', 'OP 2')
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
            $data['active_tab']  = 'Semua';
        }

        if (!empty($startDate)) {
            $builder = $builder->where('date >=', $startDate);
        }
        if (!empty($endDate)) {
            $builder = $builder->where('date <=', $endDate);
        }

        $data['productions'] = $builder->orderBy('id', 'DESC')->findAll();
        $data['start_date']  = $startDate;
        $data['end_date']    = $endDate;
        $data['title']       = 'Monitoring Produksi & QC - Panel Admin';

        return view('admin/monitoring', $data);
    }

    public function updateProduksi($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $prodModel = new ProductionModel();
        $production = $prodModel->find($id);

        if (!$production) {
            return redirect()->back()->with('error', 'Data produksi tidak ditemukan!');
        }

        $materialAmountInput = $this->request->getPost('material_amount');
        $quantityInput       = (float)$this->request->getPost('quantity');
        $unitInput           = $this->request->getPost('unit') ?? 'Pcs';

        $updateData = [
            'material_amount' => $materialAmountInput,
            'quantity'        => $quantityInput,
            'unit'            => $unitInput
        ];

        // Catat jejak pengeditan jika kolom is_edited tersedia
        $db = \Config\Database::connect();
        if ($db->fieldExists('is_edited', 'productions')) {
            $updateData['is_edited'] = 1;
            $updateData['edit_note'] = 'Diperbarui oleh ' . (session()->get('full_name') ?? 'Admin') . ' pada ' . date('Y-m-d H:i:s');
        }

        $prodModel->update($id, $updateData);

        return redirect()->to('/admin/produksi?kategori=' . urlencode($production['category']))->with('success', 'Data monitoring produksi berhasil diperbarui!');
    }

    public function deleteProduksi($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $prodModel = new ProductionModel();
        $itemModel = new ItemModel();

        $production = $prodModel->find($id);
        if ($production) {
            $matAmountStr = $production['material_amount'] ?? '';
            $qtyKeluar = (float)filter_var($matAmountStr, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            // 1. Kembalikan stok material asal
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

            // 2. Kurangi / sesuaikan stok hasil produksi
            $prodItemName = $production['item_name'] ?? '';
            $prodQtyResult = (float)filter_var($production['quantity'] ?? 0, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

            if (!empty($prodItemName) && $prodQtyResult > 0) {
                $itemResultMaster = $itemModel->where('item_name', $prodItemName)->first();
                if ($itemResultMaster) {
                    $currResultStock = (float)filter_var($itemResultMaster['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $updatedResultStock = max(0, $currResultStock - $prodQtyResult);
                    $itemModel->update($itemResultMaster['id'], [
                        'stock' => $updatedResultStock . ' ' . ($itemResultMaster['unit'] ?? 'Pcs')
                    ]);
                }
            }

            // SOFT DELETE / TANDAI STATUS HAPUS AGAR DAPAT TERTRAK DI RIWAYAT
            $db = \Config\Database::connect();
            if ($db->fieldExists('is_deleted', 'productions')) {
                $prodModel->update($id, [
                    'is_deleted' => 1,
                    'status'     => 'Dihapus (Soft Delete)'
                ]);
            } else {
                $prodModel->delete($id);
            }
        }

        $kategoriRedirect = $production['category'] ?? 'Semua';
        return redirect()->to('/admin/produksi?kategori=' . urlencode($kategoriRedirect))->with('success', 'Data produksi berhasil dihapus dan stok berhasil disesuaikan!');
    }

    public function barang()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $model = new ItemModel();
        $subconModel = new SubconModel();
        $db = \Config\Database::connect();
        
        $rawCategory = $this->request->getGet('cat') ?? 'Material';
        $category = urldecode(str_replace('+', ' ', $rawCategory));
        
        if ($category === 'OP 2' || $category === 'OP2') {
            $items = $model->groupStart()
                           ->where('category', 'WIP OP1')
                           ->orWhere('category', 'WIPOP1')
                           ->orWhere('category', 'OP 1')
                           ->orWhere('category', 'OP1')
                           ->groupEnd()
                           ->findAll();
        } elseif ($category === 'Cutting' || $category === 'OP 1') {
            $items = $model->where('category', 'Cutting')->findAll();
            foreach ($items as &$item) {
                $cleanName = strtoupper(trim($item['item_name']));
                $rowProd = $db->table('productions')
                              ->where('UPPER(TRIM(item_name))', $cleanName)
                              ->where('category', 'Cutting')
                              ->selectSum('quantity')
                              ->get()->getRow();
                $totalProd = $rowProd ? (float)$rowProd->quantity : 0;

                $rowUsed = $db->table('productions')
                              ->where('UPPER(TRIM(material_name))', $cleanName)
                              ->selectSum('quantity', 'used')
                              ->get()->getRow();
                $totalUsed = $rowUsed ? (float)$rowUsed->used : 0;

                $realStock = max(0, $totalProd - $totalUsed);
                $item['stock'] = $realStock . ' ' . ($item['unit'] ?? 'Pcs');
                $model->update($item['id'], ['stock' => $item['stock']]);
            }
        } else {
            $items = $model->where('category', $category)->findAll();
        }

        if (empty($items) && ($category === 'WIP Final' || $category === 'WIPFinal')) {
            $items = $model->where('category', 'WIPFinal')->findAll();
        }

        if ($category === 'Plating') {
            foreach ($items as &$item) {
                $cleanItemName = strtoupper(trim($item['item_name']));

                $outs = $subconModel->where('UPPER(TRIM(item_name))', $cleanItemName)
                                    ->where('type', 'OUT')
                                    ->findAll();
                
                $totalKirimPcs = 0; $totalKirimKg = 0;
                foreach ($outs as $out) {
                    $totalKirimPcs += (float) filter_var($out['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $totalKirimKg  += (float) ($out['weight_kg'] ?? 0);
                }

                $ins = $subconModel->where('UPPER(TRIM(item_name))', $cleanItemName)
                                   ->where('type', 'IN')
                                   ->findAll();
                
                $totalKembaliPcs = 0; $totalKembaliKg = 0;
                foreach ($ins as $in) {
                    $totalKembaliPcs += (float) filter_var($in['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $totalKembaliKg  += (float) ($in['weight_kg'] ?? 0);
                }

                $sisaOutstandingPcs = max(0, $totalKirimPcs - $totalKembaliPcs);
                $sisaOutstandingKg  = max(0, $totalKirimKg - $totalKembaliKg);

                $item['stock']             = $sisaOutstandingPcs . ' Pcs';
                $item['total_out_pcs']     = $totalKirimPcs;
                $item['total_out_kg']      = $totalKirimKg;
                $item['total_in_pcs']      = $totalKembaliPcs;
                $item['total_in_kg']       = $totalKembaliKg;
                $item['sisa_di_vendor_pcs'] = $sisaOutstandingPcs;
                $item['sisa_di_vendor_kg']  = $sisaOutstandingKg;
            }
        }

        if ($category === 'FC') {
            foreach ($items as &$item) {
                $cleanItemName = strtoupper(trim($item['item_name']));

                $wipItem = $model->where('UPPER(TRIM(item_name))', $cleanItemName)
                                ->groupStart()
                                    ->where('category', 'WIP Final')
                                    ->orWhere('category', 'WIPFinal')
                                ->groupEnd()
                                ->first();
                $sisaInhouse = 0;
                if ($wipItem) {
                    $sisaInhouse = (float) filter_var($wipItem['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                }

                $subconIns = $subconModel->where('UPPER(TRIM(item_name))', $cleanItemName)
                                        ->where('type', 'IN')
                                        ->findAll();
                $totalSubconIn = 0;
                foreach ($subconIns as $sin) {
                    $totalSubconIn += (float) filter_var($sin['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                }

                $rowQc = $db->table('productions')
                            ->where('UPPER(TRIM(item_name))', $cleanItemName)
                            ->where('category', 'QC Pengecekan')
                            ->like('material_name', 'Subcon')
                            ->selectSum('quantity')
                            ->get()
                            ->getRow();

                $subconQcChecked = $rowQc ? $rowQc->quantity : 0;

                $sisaSubcon = max(0, $totalSubconIn - (float)$subconQcChecked);
                $totalAntreanFC = $sisaInhouse + $sisaSubcon;

                $item['stock']         = $totalAntreanFC . ' Pcs';
                $item['antrean_inhouse'] = $sisaInhouse;
                $item['antrean_subcon']  = $sisaSubcon;
            }
        }

        if ($category === 'FG') {
            foreach ($items as &$item) {
                $cleanItemName = strtoupper(trim($item['item_name']));

                $rowFg = $db->table('productions')
                            ->where('UPPER(TRIM(item_name))', $cleanItemName)
                            ->selectSum('qty_ok')
                            ->get()
                            ->getRow();
                $totalFgFromQC = $rowFg ? (float)$rowFg->qty_ok : 0;

                $deliveries = $db->table('deliveries')
                               ->where('UPPER(TRIM(item_name))', $cleanItemName)
                               ->get()
                               ->getResultArray();
                $totalDelivered = 0;
                foreach ($deliveries as $del) {
                    $totalDelivered += (float) filter_var($del['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                }

                $sisaStockFG = max(0, (float)$totalFgFromQC - $totalDelivered);
                
                $manualStock = (float) filter_var($item['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                
                if ($manualStock <= 0 && $sisaStockFG > 0) {
                    $model->update($item['id'], ['stock' => $sisaStockFG . ' ' . ($item['unit'] ?? 'Pcs')]);
                    $item['stock'] = $sisaStockFG . ' Pcs';
                } elseif ($manualStock > 0) {
                    $item['stock'] = $manualStock . ' Pcs';
                } else {
                    $item['stock'] = $sisaStockFG . ' Pcs';
                }
            }
        }

        if ($category === 'NG') {
            $allCandidateNames = [];
            $wipList = $model->whereIn('category', ['WIP Final', 'WIPFinal'])->findAll();
            foreach ($wipList as $w) {
                $allCandidateNames[] = trim($w['item_name']);
            }
            $qcList = $db->table('productions')->where('category', 'QC Pengecekan')->select('item_name')->distinct()->get()->getResultArray();
            foreach ($qcList as $q) {
                $name = trim($q['item_name']);
                if (!in_array($name, $allCandidateNames)) {
                    $allCandidateNames[] = $name;
                }
            }
            $existingNgMaster = $model->where('category', 'NG')->findAll();
            $existingNgMap = [];
            foreach ($existingNgMaster as $exNg) {
                $exName = trim($exNg['item_name']);
                $existingNgMap[strtoupper($exName)] = $exNg;
                if (!in_array($exName, $allCandidateNames)) {
                    $allCandidateNames[] = $exName;
                }
            }

            $syncedItems = [];
            foreach ($allCandidateNames as $candName) {
                $cleanItemName = strtoupper($candName);

                $qcResults = $db->table('productions')
                                ->where('UPPER(TRIM(item_name))', $cleanItemName)
                                ->where('category', 'QC Pengecekan')
                                ->get()
                                ->getResultArray();

                $totalNg = 0;
                foreach ($qcResults as $res) {
                    $qtyNg = (float)($res['qty_ng'] ?? 0);
                    if ($qtyNg == 0) {
                        $repB    = (float)($res['ng_repair_burry'] ?? 0);
                        $repBl   = (float)($res['ng_repair_belum'] ?? 0);
                        $repD    = (float)($res['ng_repair_dimensi'] ?? 0);
                        $rejG    = (float)($res['ng_reject_gompal'] ?? 0);
                        $rejB    = (float)($res['ng_reject_baret'] ?? 0);
                        $rejHole   = (float)($res['ng_reject_hole'] ?? 0);
                        $rejRep    = (float)($res['ng_reject_replating'] ?? 0);
                        $rejDacon = (float)($res['ng_reject_dacon'] ?? 0);
                        $rejPanj   = (float)($res['ng_reject_panjang'] ?? 0);
                        $rejPend   = (float)($res['ng_reject_pendek'] ?? 0);
                        $qtyNg = $repB + $repBl + $repD + $rejG + $rejB + $rejHole + $rejRep + $rejDacon + $rejPanj + $rejPend;
                    }
                    $totalNg += $qtyNg;
                }

                $rowRep = $db->table('productions')
                            ->where('category !=', 'QC Pengecekan')
                            ->groupStart()
                                ->where('UPPER(TRIM(item_name))', $cleanItemName)
                                ->orLike('UPPER(material_name)', $cleanItemName)
                            ->groupEnd()
                            ->groupStart()
                                ->like('material_name', 'Part NG')
                                ->orLike('status', 'Repair')
                                ->orLike('status', 'Retur')
                            ->groupEnd()
                            ->selectSum('quantity')
                            ->get()
                            ->getRow();

                $alreadyRepaired = $rowRep ? $rowRep->quantity : 0;
                $calculatedSisaNG = max(0, $totalNg - (float)$alreadyRepaired);

                if (isset($existingNgMap[$cleanItemName])) {
                    $existNg = $existingNgMap[$cleanItemName];
                    $manualStock = (float) filter_var($existNg['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    
                    $finalNgStock = max($manualStock, $calculatedSisaNG);

                    $existNg['stock'] = $finalNgStock . ' Pcs';
                    $syncedItems[] = $existNg;
                } else {
                    $randCode = 'NG-' . rand(100, 999);
                    $newId = $model->insert([
                        'item_code' => $randCode,
                        'item_name' => $candName,
                        'category'  => 'NG',
                        'stock'     => $calculatedSisaNG . ' Pcs',
                        'unit'      => 'Pcs'
                    ]);
                    $syncedItems[] = [
                        'id'        => $newId,
                        'item_code' => $randCode,
                        'item_name' => $candName,
                        'category'  => 'NG',
                        'stock'     => $calculatedSisaNG . ' Pcs',
                        'unit'      => 'Pcs'
                    ];
                }
            }

            $items = $syncedItems;
        }

        $data['items'] = $items;
        $data['selected_cat'] = $category;
        $data['all_master_items'] = array_column($model->select('item_name')->distinct()->findAll(), 'item_name');

        return view('admin/barang', $data);
    }

    public function storeBarang()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $model = new ItemModel();
        $subconModel = new SubconModel();
        $category = $this->request->getVar('category');
        $itemName = trim((string)($this->request->getVar('item_name') ?: $this->request->getVar('item_name_select')));
        $unit = $this->request->getVar('unit') ?? 'Pcs';
        $stockInput = (float) $this->request->getVar('stock');

        if (empty($itemName)) {
            return redirect()->back()->withInput()->with('error', 'Nama barang wajib dipilih atau diisi!');
        }

        if ($category === 'Plating') {
            $platingType = $this->request->getVar('plating_type') ?? 'OUT';
            $weightKg = (float) ($this->request->getVar('weight_kg') ?? 0);

            $subconModel->save([
                'date'        => date('Y-m-d'),
                'type'        => $platingType,
                'item_name'   => strtoupper($itemName),
                'vendor_name' => 'VENDOR PLATING',
                'quantity'    => $stockInput . ' Pcs',
                'weight_kg'   => $weightKg,
                'monitoring'  => ($platingType === 'OUT') ? 'Manual Input OUT' : 'Manual Input IN',
                'status'      => ($platingType === 'OUT') ? 'Proses di Vendor' : 'Selesai'
            ]);
        } 
        elseif ($category === 'FC') {
            $fcType = $this->request->getVar('fc_type') ?? 'inhouse';
            if ($fcType === 'inhouse') {
                $wipItem = $model->where('UPPER(TRIM(item_name))', strtoupper($itemName))
                               ->groupStart()
                                    ->where('category', 'WIP Final')
                                    ->orWhere('category', 'WIPFinal')
                               ->groupEnd()
                               ->first();
                if ($wipItem) {
                    $currStock = (float) filter_var($wipItem['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $model->update($wipItem['id'], [
                        'stock' => ($currStock + $stockInput) . ' Pcs'
                    ]);
                } else {
                    $model->save([
                        'item_code'   => 'WIP-' . rand(100, 999),
                        'item_name'   => strtoupper($itemName),
                        'category'    => 'WIP Final',
                        'stock'       => $stockInput . ' Pcs',
                        'unit'        => 'Pcs'
                    ]);
                }
            } else {
                $subconModel->save([
                    'date'        => date('Y-m-d'),
                    'type'        => 'IN',
                    'item_name'   => strtoupper($itemName),
                    'vendor_name' => 'VENDOR PLATING',
                    'quantity'    => $stockInput . ' Pcs',
                    'weight_kg'   => 0,
                    'monitoring'  => 'Manual Input Subcon IN',
                    'status'      => '-'
                ]);
            }
        }

        $existingItem = $model->where('item_name', $itemName)->where('category', $category)->first();

        if ($existingItem) {
            $currentStock = (float) filter_var($existingItem['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $newStock = $currentStock + $stockInput;
            $model->update($existingItem['id'], [
                'stock' => $newStock . ' ' . $unit,
                'unit'  => $unit
            ]);
        } else {
            $model->save([
                'item_code' => strtoupper(substr($category, 0, 3)) . '-' . rand(100, 999),
                'item_name' => strtoupper($itemName),
                'category'  => $category,
                'stock'     => $stockInput . ' ' . $unit,
                'unit'      => $unit
            ]);
        }

        return redirect()->to('/admin/barang?cat=' . urlencode((string)$category))->with('success', 'Data barang berhasil ditambahkan!');
    }

    public function updateBarang($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $model = new ItemModel();
        $subconModel = new SubconModel();
        
        $category = $this->request->getVar('category');
        $itemName = strtoupper(trim((string)$this->request->getVar('item_name')));
        $unit = $this->request->getVar('unit') ?? 'Pcs';
        $stockInput = (float) $this->request->getVar('stock');

        $currentItem = $model->find($id);

        if ($category === 'Plating') {
            $platingType = $this->request->getVar('plating_type');
            $weightKg = (float) ($this->request->getVar('weight_kg') ?? 0);

            if ($platingType === 'OUT' || $platingType === 'IN') {
                $subconModel->save([
                    'date'        => date('Y-m-d'),
                    'type'        => $platingType,
                    'item_name'   => $itemName,
                    'vendor_name' => 'VENDOR PLATING',
                    'quantity'    => $stockInput . ' Pcs',
                    'weight_kg'   => $weightKg,
                    'monitoring'  => ($platingType === 'OUT') ? 'Manual Input OUT via Edit' : 'Manual Input IN via Edit',
                    'status'      => ($platingType === 'OUT') ? 'Proses di Vendor' : 'Selesai'
                ]);
            }
        } 
        elseif ($category === 'FC') {
            $fcType = $this->request->getVar('fc_type');
            if ($fcType === 'inhouse') {
                $wipItem = $model->where('UPPER(TRIM(item_name))', $itemName)
                               ->groupStart()
                                    ->where('category', 'WIP Final')
                                    ->orWhere('category', 'WIPFinal')
                               ->groupEnd()
                               ->first();
                if ($wipItem) {
                    $model->update($wipItem['id'], [
                        'stock' => $stockInput . ' Pcs'
                    ]);
                }
            } elseif ($fcType === 'subcon') {
                $subconModel->save([
                    'date'        => date('Y-m-d'),
                    'type'        => 'IN',
                    'item_name'   => $itemName,
                    'vendor_name' => 'VENDOR PLATING',
                    'quantity'    => $stockInput . ' Pcs',
                    'weight_kg'   => 0,
                    'monitoring'  => 'Manual Input Subcon IN via Edit FC',
                    'status'      => '-'
                ]);
            }
        }

        if ($currentItem) {
            $model->update($id, [
                'item_name' => $itemName,
                'category'  => $category,
                'stock'     => $stockInput . ' ' . $unit,
                'unit'      => $unit
            ]);
        }

        return redirect()->to('/admin/barang?cat=' . urlencode((string)$category))->with('success', 'Data barang berhasil diperbarui!');
    }

    public function deleteBarang($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $model = new ItemModel();
        $item = $model->find($id);
        $category = $item['category'] ?? 'Material';

        $model->delete($id);
        return redirect()->to('/admin/barang?cat=' . urlencode((string)$category))->with('success', 'Data barang berhasil dihapus!');
    }

    // ==================== PEMBELIAN / MATERIAL MASUK ====================

    public function pembelian()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $purchaseModel = new PurchaseModel();
        $itemModel = new ItemModel();
        $partnerModel = new PartnerModel();

        $data['purchases'] = $purchaseModel->orderBy('id', 'DESC')->findAll();
        $data['items'] = $itemModel->where('category', 'Material')->findAll();
        $data['suppliers'] = $partnerModel->where('type', 'Supplier')->orderBy('name', 'ASC')->findAll();

        return view('admin/pembelian', $data);
    }

    public function storePembelian()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $purchaseModel = new PurchaseModel();
        $itemModel = new ItemModel();

        $itemName = $this->request->getVar('item_name');
        $qtyInput = (float) $this->request->getVar('quantity'); 

        $item = $itemModel->where('item_name', $itemName)->where('category', 'Material')->first();
        $unit = $item['unit'] ?? 'Pcs'; 

        $fullQuantity = $qtyInput . ' ' . $unit;

        $purchaseModel->save([
            'date'      => $this->request->getVar('date'),
            'item_name' => $itemName,
            'supplier'  => $this->request->getVar('supplier'),
            'quantity'  => $fullQuantity,
        ]);

        if ($item) {
            $currentStock = (float) filter_var($item['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $newStock = $currentStock + $qtyInput;
            $itemModel->update($item['id'], ['stock' => $newStock . ' ' . $unit]);
        }

        return redirect()->to('/admin/pembelian')->with('success', 'Transaksi pembelian berhasil disimpan dan stok material bertambah!');
    }

    public function updatePembelian($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $model = new PurchaseModel();
        $model->update($id, [
            'date'      => $this->request->getVar('date'),
            'item_name' => $this->request->getVar('item_name'),
            'supplier'  => $this->request->getVar('supplier'),
            'quantity'  => $this->request->getVar('quantity'),
        ]);

        return redirect()->to('/admin/pembelian')->with('success', 'Data pembelian berhasil diperbarui!');
    }

    public function deletePembelian($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $model = new PurchaseModel();
        $model->delete($id);

        return redirect()->to('/admin/pembelian')->with('success', 'Data pembelian berhasil dihapus!');
    }

    // ==================== VENDOR / SUBCON ====================

    public function subcon()
    {
        if (session()->get('role') != 'admin' && session()->get('role') != 'produksi') {
            return redirect()->to('/login');
        }

        $subconModel = new SubconModel();
        $itemModel = new ItemModel();
        $partnerModel = new PartnerModel();

        $outs = $subconModel->where('type', 'OUT')->orderBy('id', 'ASC')->findAll();
        
        $sortedSubcons = [];
        foreach ($outs as $out) {
            $sortedSubcons[] = $out;
            
            $ins = $subconModel->where('type', 'IN')
                               ->where('reference_id', $out['id'])
                               ->orderBy('id', 'ASC')
                               ->findAll();
            foreach ($ins as $in) {
                $sortedSubcons[] = $in;
            }
        }

        $remaining = $subconModel->where('type', 'IN')
                               ->where('(reference_id IS NULL OR reference_id = 0)')
                               ->findAll();
        foreach ($remaining as $rem) {
            $sortedSubcons[] = $rem;
        }

        $data['subcons'] = $sortedSubcons;
        $data['items_wip'] = $itemModel->whereIn('category', ['Plating', 'WIP Final', 'WIPFinal'])->findAll();
        
        $data['items_retur_material'] = $itemModel->where('category', 'Material')
                                                ->whereIn('item_name', [
                                                    'COLLAR Ø 11.5 X Ø 6.5 FORGING',
                                                    'COLLAR Ø 12.5 X Ø8.0 FORGING'
                                                ])->findAll();

        $data['vendors'] = $partnerModel->where('type', 'Vendor')->orderBy('name', 'ASC')->findAll();
        
        $data['out_transactions'] = $subconModel->where('type', 'OUT')
                                                ->where('status !=', 'Selesai')
                                                ->where('status !=', 'Selesai Repair')
                                                ->where('status !=', 'Selesai Retur')
                                                ->findAll();

        return view('admin/subcon', $data);
    }

    public function storeSubcon()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $model     = new SubconModel();
        $itemModel = new ItemModel();
        
        $qtyPcs       = (float) ($this->request->getVar('quantity_pcs') ?? $this->request->getVar('quantity'));
        $weightKg     = (float) ($this->request->getVar('weight_kg') ?? 0);
        $type         = $this->request->getVar('type');
        $referenceId  = $this->request->getPost('reference_id') ?? $this->request->getVar('reference_id');
        $itemName     = $this->request->getVar('item_name');
        $subconType   = $this->request->getVar('subcon_type') ?? 'reguler';
        $fullQuantity = $qtyPcs . ' Pcs';

        if ($type == 'OUT') {
            if ($subconType === 'return_material') {
                $statusOut = 'Proses Retur Material di Vendor';
                $monitoring = 'Masuk: 0 Pcs | Sisa: ' . $qtyPcs . ' Pcs (Retur Material)';
            } elseif ($subconType === 'return_repair') {
                $statusOut = 'Proses Repair di Vendor';
                $monitoring = 'Masuk: 0 Pcs | Sisa: ' . $qtyPcs . ' Pcs (Repair Vendor)';
            } else {
                $statusOut = 'Proses di Vendor';
                $monitoring = 'Masuk: 0 Pcs | Sisa: ' . $qtyPcs . ' Pcs';
            }

            if ($weightKg > 0) {
                $monitoring .= ' (' . $weightKg . ' Kg)';
            }

            $model->save([
                'date'         => $this->request->getVar('date'),
                'type'         => 'OUT',
                'item_name'    => $itemName,
                'vendor_name'  => $this->request->getVar('vendor_name'),
                'quantity'     => $fullQuantity,
                'weight_kg'    => $weightKg,
                'reference_id' => null,
                'monitoring'   => $monitoring,
                'status'       => $statusOut
            ]);

            if ($subconType === 'reguler') {
                $itemWip = $itemModel->where('item_name', $itemName)
                                    ->groupStart()
                                        ->where('category', 'WIP Final')
                                        ->orWhere('category', 'WIPFinal')
                                        ->orWhere('category', 'Plating')
                                    ->groupEnd()
                                    ->first();

                if ($itemWip && $itemWip['category'] != 'Plating') {
                    $currentWipStock = (float) filter_var($itemWip['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $itemModel->update($itemWip['id'], ['stock' => max(0, $currentWipStock - $qtyPcs) . ' Pcs']);
                }
            }

        } else {
            if (empty($referenceId)) {
                return redirect()->back()->withInput()->with('error', 'Gagal! Harap pilih Kiriman OUT Asal dari dropdown.');
            }

            $parentOut = $model->find($referenceId);
            if ($parentOut) {
                $qtyOutPcs = (float) filter_var($parentOut['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $qtyOutKg  = (float) ($parentOut['weight_kg'] ?? 0);
                $isRepair  = str_contains((string)$parentOut['status'], 'Repair');
                $isRetur   = str_contains((string)$parentOut['status'], 'Retur');
                
                $model->save([
                    'date'         => $this->request->getVar('date'),
                    'type'         => 'IN',
                    'item_name'    => $parentOut['item_name'],
                    'vendor_name'  => $parentOut['vendor_name'],
                    'quantity'     => $fullQuantity,
                    'weight_kg'    => $weightKg,
                    'reference_id' => $referenceId, 
                    'monitoring'   => 'Penerimaan In',
                    'status'       => '-' 
                ]);

                if ($isRetur) {
                    $itemMaterial = $itemModel->where('item_name', $parentOut['item_name'])
                                              ->where('category', 'Material')
                                              ->first();
                    if ($itemMaterial) {
                        $currStock = (float) filter_var($itemMaterial['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        $itemModel->update($itemMaterial['id'], [
                            'stock' => ($currStock + $qtyPcs) . ' Pcs'
                        ]);
                    }
                }

                $allIns = $model->where('reference_id', $referenceId)->findAll();
                $totalMasukPcs = 0;
                $totalMasukKg  = 0;
                foreach ($allIns as $ins) {
                    $totalMasukPcs += (float) filter_var($ins['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $totalMasukKg  += (float) ($ins['weight_kg'] ?? 0);
                }

                $sisaPcsBaru = max(0, $qtyOutPcs - $totalMasukPcs);
                $sisaKgBaru  = max(0, $qtyOutKg - $totalMasukKg);

                $labelSelesai = $isRetur ? 'Selesai Retur' : ($isRepair ? 'Selesai Repair' : 'Selesai');
                $labelProses  = $isRetur ? 'Proses Retur (Sebagian)' : ($isRepair ? 'Proses Repair (Sebagian)' : 'Proses di Vendor (Sebagian)');
                $statusInduk  = ($sisaPcsBaru == 0) ? $labelSelesai : $labelProses;

                $monitoringInduk = 'Masuk: ' . $totalMasukPcs . ' Pcs | Sisa: ' . $sisaPcsBaru . ' Pcs';
                if ($qtyOutKg > 0) {
                    $monitoringInduk .= ' (Sisa: ' . number_format($sisaKgBaru, 2) . ' Kg)';
                }

                $model->update($referenceId, [
                    'monitoring' => $monitoringInduk,
                    'status'     => $statusInduk
                ]);
            }
        }

        return redirect()->to('/admin/subcon')->with('success', 'Transaksi subcon berhasil disimpan!');
    }

    public function updateSubcon($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $model = new SubconModel();
        $qtyPcs   = (float) ($this->request->getVar('quantity_pcs') ?? $this->request->getVar('quantity'));
        $weightKg = (float) ($this->request->getVar('weight_kg') ?? 0);

        $model->update($id, [
            'date'        => $this->request->getVar('date'),
            'item_name'   => $this->request->getVar('item_name'),
            'vendor_name' => $this->request->getVar('vendor_name'),
            'quantity'    => $qtyPcs . ' Pcs',
            'weight_kg'   => $weightKg,
        ]);

        return redirect()->to('/admin/subcon')->with('success', 'Data transaksi subcon berhasil diperbarui!');
    }

    public function deleteSubcon($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $model = new SubconModel();
        $itemModel = new ItemModel();
        
        $subcon = $model->find($id);
        if ($subcon && $subcon['type'] == 'IN' && !empty($subcon['reference_id'])) {
            $parentOut = $model->find($subcon['reference_id']);
            if ($parentOut) {
                $qtyOutPcs = (float) filter_var($parentOut['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                $qtyOutKg  = (float) ($parentOut['weight_kg'] ?? 0);
                $isRepair  = str_contains((string)$parentOut['status'], 'Repair');
                $isRetur   = str_contains((string)$parentOut['status'], 'Retur');

                if ($isRetur) {
                    $itemMaterial = $itemModel->where('item_name', $parentOut['item_name'])
                                              ->where('category', 'Material')
                                              ->first();
                    if ($itemMaterial) {
                        $currStock = (float) filter_var($itemMaterial['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        $qtyDelete = (float) filter_var($subcon['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                        $itemModel->update($itemMaterial['id'], [
                            'stock' => max(0, $currStock - $qtyDelete) . ' Pcs'
                        ]);
                    }
                }
                
                $existingIns = $model->where('reference_id', $subcon['reference_id'])->where('id !=', $id)->findAll();
                $totalMasukPcs = 0;
                $totalMasukKg  = 0;
                foreach ($existingIns as $ins) {
                    $totalMasukPcs += (float) filter_var($ins['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
                    $totalMasukKg  += (float) ($ins['weight_kg'] ?? 0);
                }

                $sisaPcsBaru = max(0, $qtyOutPcs - $totalMasukPcs);
                $sisaKgBaru  = max(0, $qtyOutKg - $totalMasukKg);

                $labelSelesai = $isRetur ? 'Selesai Retur' : ($isRepair ? 'Selesai Repair' : 'Selesai');
                $labelProses  = $isRetur ? 'Proses Retur di Vendor' : ($isRepair ? 'Proses Repair di Vendor' : 'Proses di Vendor');
                $statusInduk  = ($sisaPcsBaru == 0) ? $labelSelesai : (($totalMasukPcs > 0) ? ($isRetur ? 'Proses Retur (Sebagian)' : ($isRepair ? 'Proses Repair (Sebagian)' : 'Proses di Vendor (Sebagian)')) : $labelProses);

                $monitoringInduk = 'Masuk: ' . $totalMasukPcs . ' Pcs | Sisa: ' . $sisaPcsBaru . ' Pcs';
                if ($qtyOutKg > 0) {
                    $monitoringInduk .= ' (Sisa: ' . number_format($sisaKgBaru, 2) . ' Kg)';
                }

                $model->update($subcon['reference_id'], [
                    'monitoring' => $monitoringInduk,
                    'status'     => $statusInduk
                ]);
            }
        }

        $model->delete($id);

        return redirect()->to('/admin/subcon')->with('success', 'Data subcon berhasil dihapus!');
    }

    // ==================== PENGIRIMAN FINISHED GOODS (FG) ====================

    public function pengiriman()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $itemModel = new ItemModel();
        $partnerModel = new PartnerModel();

        $data['deliveries'] = $db->table('deliveries')->orderBy('id', 'DESC')->get()->getResultArray();
        $data['customers']  = $partnerModel->where('type', 'Customer')->orderBy('name', 'ASC')->findAll();

        $fgMasterList = $itemModel->where('category', 'FG')->findAll();
        $fgItems = [];

        foreach ($fgMasterList as $fg) {
            $cleanName = strtoupper(trim($fg['item_name']));

            $rowQc = $db->table('productions')
                        ->where('UPPER(TRIM(item_name))', $cleanName)
                        ->selectSum('qty_ok')
                        ->get()
                        ->getRow();
            $totalQcOk = $rowQc ? (float)$rowQc->qty_ok : 0;

            $delRows = $db->table('deliveries')
                          ->where('UPPER(TRIM(item_name))', $cleanName)
                          ->get()
                          ->getResultArray();
            $totalDelivered = 0;
            foreach ($delRows as $del) {
                $totalDelivered += (float) filter_var($del['quantity'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            }

            $manualStock = (float) filter_var($fg['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $sisaQcFg    = max(0, $totalQcOk - $totalDelivered);
            
            $stockReady  = max($manualStock, $sisaQcFg);

            $fgItems[] = [
                'id'          => $fg['id'],
                'item_name'   => $fg['item_name'],
                'unit'        => $fg['unit'] ?? 'Pcs',
                'stock_ready' => $stockReady
            ];
        }

        $data['fg_items'] = $fgItems;
        $data['title']    = 'Kelola Pengiriman (Finished Goods) - PT Sigma';

        return view('admin/pengiriman', $data);
    }

    public function storePengiriman()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $itemModel = new ItemModel();

        $itemName    = $this->request->getVar('item_name');
        $destination = $this->request->getVar('destination');
        $qtyInput    = (float) $this->request->getVar('quantity');

        $item = $itemModel->where('item_name', $itemName)->where('category', 'FG')->first();
        $unit = $item['unit'] ?? 'Pcs';
        $fullQuantity = $qtyInput . ' ' . $unit;

        $db->table('deliveries')->insert([
            'date'        => $this->request->getVar('date'),
            'item_name'   => $itemName,
            'destination' => $destination,
            'quantity'    => $fullQuantity,
        ]);

        if ($item) {
            $currentStock = (float) filter_var($item['stock'], FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            $newStock = max(0, $currentStock - $qtyInput);
            $itemModel->update($item['id'], ['stock' => $newStock . ' ' . $unit]);
        }

        return redirect()->to('/admin/pengiriman')->with('success', 'Transaksi pengiriman berhasil disimpan dan stok FG berkurang!');
    }

    public function updatePengiriman($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $qtyInput = (float) $this->request->getVar('quantity');
        $unit      = $this->request->getVar('unit') ?? 'Pcs';

        $db->table('deliveries')->where('id', $id)->update([
            'date'        => $this->request->getVar('date'),
            'item_name'   => $this->request->getVar('item_name'),
            'destination' => $this->request->getVar('destination'),
            'quantity'    => $qtyInput . ' ' . $unit,
        ]);

        return redirect()->to('/admin/pengiriman')->with('success', 'Data pengiriman berhasil diperbarui!');
    }

    public function deletePengiriman($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $db->table('deliveries')->where('id', $id)->delete();

        return redirect()->to('/admin/pengiriman')->with('success', 'Data pengiriman berhasil dihapus!');
    }

    // ==================== LAPORAN & REKAPITULASI ====================

    public function laporan()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        $tipeLaporan        = $this->request->getGet('tipe') ?? '';
        $startDate          = $this->request->getGet('start_date');
        $endDate            = $this->request->getGet('end_date');
        $selectedYear       = (int)($this->request->getGet('year') ?? date('Y'));
        $selectedMonth      = $this->request->getGet('month') ?? date('m');
        $selectedProses     = $this->request->getGet('proses') ?? 'all';
        $stokMode           = $this->request->getGet('stok_mode') ?? 'hari';
        $stokMonth          = $this->request->getGet('stok_month') ?? date('m');
        $stokYear           = $this->request->getGet('stok_year') ?? date('Y');
        
        $filterItem         = $this->request->getGet('filter_item') ?? 'all';
        $isPrint            = $this->request->getGet('print') === '1';

        $data['item_list'] = $db->table('items')
                            ->select('item_name')
                            ->distinct()
                            ->orderBy('item_name', 'ASC')
                            ->get()
                            ->getResultArray();

        $data['proses_list'] = $db->table('productions')
                            ->select('category')
                            ->where('category !=', '')
                            ->distinct()
                            ->orderBy('category', 'ASC')
                            ->get()
                            ->getResultArray();

        $reportData = $this->getReportDataForExport(
            $tipeLaporan, $startDate, $endDate, $selectedYear, $selectedMonth, $selectedProses, 
            $stokMode, $stokMonth, $stokYear, $filterItem
        );

        $data['tipe_laporan']        = $tipeLaporan;
        $data['start_date']          = $startDate;
        $data['end_date']            = $endDate;
        $data['selected_year']       = $selectedYear;
        $data['selected_month']      = $selectedMonth;
        $data['selected_proses']     = $selectedProses;
        $data['stok_mode']           = $stokMode;
        $data['stok_month']          = $stokMonth;
        $data['stok_year']           = $stokYear;
        $data['filter_item']         = $filterItem;
        $data['report_data']         = $reportData;
        $data['is_print']            = $isPrint;
        $data['title']               = 'Pusat Laporan Pabrik - Panel Admin';

        return view('admin/laporan', $data);
    }

    public function laporanHistoryItem()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $rawItemName = $this->request->getGet('item_name');
        
        if (empty($rawItemName)) {
            return redirect()->to('/admin/laporan')->with('error', 'Silakan pilih nama barang terlebih dahulu.');
        }

        $itemName = trim((string)$rawItemName);
        $cleanName = strtoupper($itemName);
        
        // Buat keyword pencarian yang luwes tanpa menghapus karakter simbolik utama seperti Ø
        $searchPattern = preg_replace('/\s+(CUTTING|OP\s*1|OP1|OP\s*2|OP2|WIP\s*FINAL|WIPFINAL|FINAL)$/i', '', $cleanName);
        $searchPattern = trim($searchPattern);

        // 1. Pembelian
        $data['purchases'] = $db->table('purchases')
                                ->groupStart()
                                    ->where('UPPER(TRIM(item_name))', $cleanName)
                                    ->orLike('UPPER(item_name)', $searchPattern)
                                ->groupEnd()
                                ->orderBy('date', 'ASC')
                                ->get()
                                ->getResultArray();

        // 2. Produksi Internal (termasuk yang diedit/dihapus untuk audit trail)
        $data['productions'] = $db->table('productions')
                                   ->groupStart()
                                       ->where('UPPER(TRIM(item_name))', $cleanName)
                                       ->orWhere('UPPER(TRIM(material_name))', $cleanName)
                                       ->orLike('UPPER(item_name)', $searchPattern)
                                       ->orLike('UPPER(material_name)', $searchPattern)
                                   ->groupEnd()
                                   ->orderBy('date', 'ASC')
                                   ->orderBy('id', 'ASC')
                                   ->get()
                                   ->getResultArray();

        // 3. Subcon / Plating Vendor
        $data['subcons'] = $db->table('subcons')
                            ->groupStart()
                                ->where('UPPER(TRIM(item_name))', $cleanName)
                                ->orLike('UPPER(item_name)', $searchPattern)
                            ->groupEnd()
                            ->orderBy('date', 'ASC')
                            ->orderBy('id', 'ASC')
                            ->get()
                            ->getResultArray();

        // 4. Pengiriman FG
        $data['deliveries'] = $db->table('deliveries')
                               ->groupStart()
                                   ->where('UPPER(TRIM(item_name))', $cleanName)
                                   ->orLike('UPPER(item_name)', $searchPattern)
                               ->groupEnd()
                               ->orderBy('date', 'ASC')
                               ->get()
                               ->getResultArray();

        $data['selected_item'] = $itemName;
        $data['title']          = 'History Alur Produksi: ' . $itemName;

        return view('admin/laporan_history_item', $data);
    }

    public function exportExcelLaporan()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $tipeLaporan        = $this->request->getGet('tipe') ?? 'stok';
        $startDate          = $this->request->getGet('start_date');
        $endDate            = $this->request->getGet('end_date');
        $selectedYear       = (int)($this->request->getGet('year') ?? date('Y'));
        $selectedMonth      = $this->request->getGet('month') ?? date('m');
        $selectedProses     = $this->request->getGet('proses') ?? 'all';
        $stokMode           = $this->request->getGet('stok_mode') ?? 'hari';
        $stokMonth          = $this->request->getGet('stok_month') ?? date('m');
        $stokYear           = $this->request->getGet('stok_year') ?? date('Y');
        
        $filterItem         = $this->request->getGet('filter_item') ?? 'all';

        $reportData = $this->getReportDataForExport(
            $tipeLaporan, $startDate, $endDate, $selectedYear, $selectedMonth, $selectedProses, 
            $stokMode, $stokMonth, $stokYear, $filterItem
        );
        
        $filename = "Laporan_" . ucfirst($tipeLaporan) . "_" . date('Ymd_His') . ".csv";

        header("Content-Type: text/csv; charset=utf-8");
        header("Content-Disposition: attachment; filename=\"$filename\"");
        
        $output = fopen("php://output", "w");
        fprintf($output, chr(0xEF).chr(0xBB).chr(0xBF));

        $delimiter = ";";

        fputcsv($output, ["PT SIGMA MANUFACTURING"], $delimiter);
        fputcsv($output, ["LAPORAN " . strtoupper($tipeLaporan)], $delimiter);
        
        if ($tipeLaporan === 'stok') {
            $periodeStr = ($stokMode === 'bulan') ? "PERIODE BULAN: {$stokMonth}/{$stokYear}" : "PER TANGGAL: " . date('d/m/Y', strtotime($endDate ?? date('Y-m-d')));
            fputcsv($output, [$periodeStr], $delimiter);
        }
        fputcsv($output, [], $delimiter);

        if ($tipeLaporan === 'operator') {
            fputcsv($output, ["NO", "NAMA OPERATOR", "TAHAPAN / PROSES", "JUMLAH LOT", "TOTAL OUTPUT", "TOTAL OK", "TOTAL NG"], $delimiter);
            $no = 1;
            foreach ($reportData as $row) {
                fputcsv($output, [$no++, $row['operator_name'], $row['category'], $row['total_lot'], $row['total_output'], $row['total_ok'] ?? 0, $row['total_ng'] ?? 0], $delimiter);
            }
        } elseif ($tipeLaporan === 'stok') {
            fputcsv($output, ["NO", "NAMA PART / ITEM", "MATERIAL", "CUTTING", "WIP OP 1", "WIP FINAL", "PLATING", "FC", "NG", "FG", "DELIVERY"], $delimiter);
            $no = 1;
            $totMat = 0; $totCut = 0; $totOp1 = 0; $totWip = 0;
            $totPlat = 0; $totFc = 0; $totNg = 0; $totFg = 0; $totDel = 0;

            $parseNum = function($val) {
                if (empty($val) || $val === '-') return 0;
                return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            };

            foreach ($reportData as $row) {
                $totMat  += $parseNum($row['material']);
                $totCut  += $parseNum($row['cutting']);
                $totOp1  += $parseNum($row['op1']);
                $totWip  += $parseNum($row['wip_final']);
                $totPlat += $parseNum($row['plating']);
                $totFc   += $parseNum($row['fc']);
                $totNg   += $parseNum($row['ng']);
                $totFg   += $parseNum($row['fg']);
                $totDel  += $parseNum($row['delivery']);

                fputcsv($output, [
                    $no++, 
                    $row['item_name'], 
                    $row['material'], 
                    $row['cutting'], 
                    $row['op1'], 
                    $row['wip_final'], 
                    $row['plating'], 
                    $row['fc'], 
                    $row['ng'], 
                    $row['fg'], 
                    $row['delivery']
                ], $delimiter);
            }
            fputcsv($output, ["TOTAL SEMUA STOK", "", number_format($totMat), number_format($totCut), number_format($totOp1), number_format($totWip), number_format($totPlat), number_format($totFc), number_format($totNg), number_format($totFg), number_format($totDel)], $delimiter);

        } elseif ($tipeLaporan === 'pembelian') {
            fputcsv($output, ["NO", "TANGGAL", "NAMA MATERIAL", "SUPPLIER", "JUMLAH MASUK"], $delimiter);
            $no = 1;
            foreach ($reportData as $row) {
                fputcsv($output, [$no++, date('d/m/Y', strtotime($row['date'])), $row['item_name'], $row['supplier'], $row['quantity']], $delimiter);
            }
        } elseif ($tipeLaporan === 'subcon') {
            fputcsv($output, ["NO", "TANGGAL", "TIPE", "NAMA BARANG", "VENDOR", "JUMLAH", "MONITORING SISA", "STATUS"], $delimiter);
            $no = 1;
            foreach ($reportData as $row) {
                fputcsv($output, [$no++, date('d/m/Y', strtotime($row['date'])), $row['type'], $row['item_name'], $row['vendor_name'], $row['quantity'], $row['monitoring'], $row['status'] ?? '-'], $delimiter);
            }
        } elseif ($tipeLaporan === 'produksi') {
            fputcsv($output, ["NO", "TANGGAL", "SHIFT", "KATEGORI", "NAMA BARANG", "MATERIAL ASAL", "OUTPUT", "QTY OK", "QTY NG", "PIC"], $delimiter);
            $no = 1;
            foreach ($reportData as $row) {
                fputcsv($output, [$no++, date('d/m/Y', strtotime($row['date'])), $row['shift'] ?? 'Shift 1', $row['category'], $row['item_name'], $row['material_name'] ?? '-', $row['quantity'] . ' ' . ($row['unit'] ?? 'Pcs'), $row['qty_ok'] ?? '-', $row['qty_ng'] ?? '-', $row['operator_name'] ?? '-'], $delimiter);
            }
        } elseif ($tipeLaporan === 'qc') {
            fputcsv($output, ["NO", "TANGGAL", "NAMA BARANG", "MATERIAL ASAL", "QTY CHECK", "QTY OK", "QTY NG", "INSPECTOR"], $delimiter);
            $no = 1;
            foreach ($reportData as $row) {
                fputcsv($output, [$no++, date('d/m/Y', strtotime($row['date'] ?? $row['created_at'])), $row['item_name'], $row['material_name'] ?? '-', $row['quantity'] ?? 0, $row['qty_ok'] ?? 0, $row['qty_ng'] ?? 0, $row['operator_name'] ?? '-'], $delimiter);
            }
        } elseif ($tipeLaporan === 'ng') {
            fputcsv($output, ["DATE", "PART / ITEM", "QTY CHECK", "QTY OK", "GRAND TOTAL NG", "BURRY GROOVING", "BELUM PROSES", "DIMENSI GROOVING", "GOMPAL", "BARET", "HOLE SEMPIT", "REPLATING", "VISUAL DACON", "PANJANG", "PENDEK", "PIC"], $delimiter);
            foreach ($reportData as $row) {
                $dOnly = isset($row['date']) ? date('d/m/Y', strtotime($row['date'])) : '-';
                $b = (float)($row['ng_repair_burry'] ?? 0); $bl = (float)($row['ng_repair_belum'] ?? 0); $dm = (float)($row['ng_repair_dimensi'] ?? 0);
                $gm = (float)($row['ng_reject_gompal'] ?? 0); $br = (float)($row['ng_reject_baret'] ?? 0); $hl = (float)($row['ng_reject_hole'] ?? 0);
                $rp = (float)($row['ng_reject_replating'] ?? 0); $dc = (float)($row['ng_reject_dacon'] ?? 0); $pj = (float)($row['ng_reject_panjang'] ?? 0); $pd = (float)($row['ng_reject_pendek'] ?? 0);
                $tot = $b + $bl + $dm + $gm + $br + $hl + $rp + $dc + $pj + $pd;
                fputcsv($output, [$dOnly, $row['item_name'], $row['quantity'] ?? 0, $row['qty_ok'] ?? 0, $tot, $b, $bl, $dm, $gm, $br, $hl, $rp, $dc, $pj, $pd, $row['operator_name'] ?? '-'], $delimiter);
            }
        } elseif ($tipeLaporan === 'pengiriman') {
            fputcsv($output, ["NO", "TANGGAL", "NAMA BARANG (FG)", "TUJUAN PT", "JUMLAH", "STATUS"], $delimiter);
            $no = 1;
            foreach ($reportData as $row) {
                fputcsv($output, [$no++, date('d/m/Y', strtotime($row['date'])), $row['item_name'], $row['destination'], $row['quantity'], 'Terkirim'], $delimiter);
            }
        }

        fclose($output);
        exit;
    }

    private function getReportDataForExport(
        $tipeLaporan, $startDate, $endDate, $year = null, $month = null, $proses = 'all', 
        $stokMode = 'hari', $stokMonth = null, $stokYear = null, $filterItem = 'all'
    )
    {
        $db = \Config\Database::connect();

        if ($tipeLaporan === 'operator') {
            $y = $year ?? date('Y');
            $m = $month ?? date('m');

            $builder = $db->table('productions');
            $builder->select('date, operator_name, category, COUNT(id) as total_lot, SUM(quantity) as total_output, SUM(qty_ok) as total_ok, SUM(qty_ng) as total_ng');
            $builder->where('YEAR(date)', (int)$y);
            if ($m !== 'all') {
                $builder->where('MONTH(date)', (int)$m);
            }
            if (!empty($proses) && $proses !== 'all') {
                $builder->where('category', $proses);
            }
            if ($filterItem !== 'all') {
                $builder->where('item_name', $filterItem);
            }
            $builder->where('operator_name !=', '');
            $builder->groupBy('date, operator_name, category');
            $builder->orderBy('date', 'ASC');
            $builder->orderBy('operator_name', 'ASC');
            return $builder->get()->getResultArray();
        }

        if ($tipeLaporan === 'pembelian') {
            $builder = $db->table('purchases');
            if (!empty($startDate) && !empty($endDate)) {
                $builder->where('date >=', $startDate)->where('date <=', $endDate);
            }
            if ($filterItem !== 'all') {
                $builder->where('item_name', $filterItem);
            }
            return $builder->orderBy('date', 'DESC')->get()->getResultArray();

        } elseif ($tipeLaporan === 'subcon') {
            $builder = $db->table('subcons');
            if (!empty($startDate) && !empty($endDate)) {
                $builder->where('date >=', $startDate)->where('date <=', $endDate);
            }
            if ($filterItem !== 'all') {
                $builder->where('item_name', $filterItem);
            }
            return $builder->orderBy('date', 'DESC')->orderBy('id', 'ASC')->get()->getResultArray();

        } elseif ($tipeLaporan === 'produksi') {
            $builder = $db->table('productions');
            if (!empty($startDate) && !empty($endDate)) {
                $builder->where('date >=', $startDate)->where('date <=', $endDate);
            }
            if ($filterItem !== 'all') {
                $builder->where('item_name', $filterItem);
            }
            return $builder->orderBy('date', 'DESC')->get()->getResultArray();

        } elseif ($tipeLaporan === 'qc') {
            $builder = $db->table('productions')->where('category', 'QC Pengecekan');
            if (!empty($startDate) && !empty($endDate)) {
                $builder->where('date >=', $startDate)->where('date <=', $endDate);
            }
            if ($filterItem !== 'all') {
                $builder->where('item_name', $filterItem);
            }
            return $builder->orderBy('date', 'DESC')->get()->getResultArray();

        } elseif ($tipeLaporan === 'ng') {
            $builder = $db->table('productions')->where('category', 'QC Pengecekan')->where('qty_ng >', 0);
            if (!empty($startDate) && !empty($endDate)) {
                $builder->where('date >=', $startDate)->where('date <=', $endDate);
            }
            if ($filterItem !== 'all') {
                $builder->where('item_name', $filterItem);
            }
            return $builder->orderBy('date', 'DESC')->get()->getResultArray();

        } elseif ($tipeLaporan === 'pengiriman') {
            $builder = $db->table('deliveries');
            if (!empty($startDate) && !empty($endDate)) {
                $builder->where('date >=', $startDate)->where('date <=', $endDate);
            }
            if ($filterItem !== 'all') {
                $builder->where('item_name', $filterItem);
            }
            return $builder->orderBy('date', 'DESC')->get()->getResultArray();

        } elseif ($tipeLaporan === 'stok') {
            if ($stokMode === 'bulan') {
                $y = $stokYear ?? date('Y');
                $m = $stokMonth ?? date('m');
                $targetDate = date('Y-m-t', strtotime("$y-$m-01"));
            } else {
                $targetDate = !empty($endDate) ? $endDate : date('Y-m-d');
            }

            $parseNum = function($val) {
                if (empty($val)) return 0;
                return (float) filter_var($val, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);
            };

            $normalizeItemName = function($rawName) {
                $name = strtoupper(trim($rawName));
                
                if (str_contains($name, '62670')) return 'COLLAR 62670 (T5)';
                if (str_contains($name, '87180')) return 'COLLAR 87180 (T6)';
                if (str_contains($name, '87181')) return 'COLLAR 87181 (T7)';
                
                $clean = preg_replace('/\s+(CUTTING|OP\s*1|OP1|OP\s*2|OP2|WIP\s*FINAL|WIPFINAL|FINAL)$/i', '', $name);
                return trim((string)preg_replace('/\s+/', ' ', $clean));
            };

            $itemQuery = $db->table('items');
            if ($filterItem !== 'all') {
                $itemQuery->where('item_name', $filterItem);
            }
            $allItems = $itemQuery->orderBy('id', 'ASC')->get()->getResultArray();
            $partDirectory = [];

            foreach ($allItems as $item) {
                $orig = trim($item['item_name']);
                if (empty($orig)) continue;

                $normalizedName = $normalizeItemName($orig);

                if (!isset($partDirectory[$normalizedName])) {
                    $partDirectory[$normalizedName] = [
                        'display_name' => $normalizedName,
                        'exact_names'  => [],
                        'unit'         => $item['unit'] ?? 'Pcs'
                    ];
                }
                if (!in_array($orig, $partDirectory[$normalizedName]['exact_names'])) {
                    $partDirectory[$normalizedName]['exact_names'][] = $orig;
                }
            }

            $purchasesRows   = $db->table('purchases')->get()->getResultArray();
            $productionsRows = $db->table('productions')->get()->getResultArray();
            $subconsRows     = $db->table('subcons')->get()->getResultArray();
            $deliveriesRows  = $db->table('deliveries')->get()->getResultArray();

            $rekapStok = [];

            foreach ($partDirectory as $normKey => $meta) {
                $exactNames = $meta['exact_names'];
                $itemUnit   = $meta['unit'];

                $isMatch = function($sourceStr) use ($exactNames, $normKey, $normalizeItemName) {
                    if (empty($sourceStr)) return false;
                    $src = trim($sourceStr);
                    if (in_array($src, $exactNames)) return true;
                    if ($normalizeItemName($src) === $normKey) return true;
                    return false;
                };

                $currentMasterMaterial = 0; $currentMasterCutting = 0; $currentMasterOp1 = 0; $currentMasterWip = 0; $currentMasterFg = 0; $currentMasterNg = 0;

                foreach ($allItems as $item) {
                    if ($isMatch($item['item_name'])) {
                        $cat = strtoupper(trim($item['category']));
                        $val = $parseNum($item['stock']);

                        if ($cat === 'MATERIAL') $currentMasterMaterial += $val;
                        elseif ($cat === 'CUTTING') $currentMasterCutting += $val;
                        elseif ($cat === 'WIP OP1' || $cat === 'WIPOP1' || $cat === 'OP 1' || $cat === 'OP1') $currentMasterOp1 += $val;
                        elseif ($cat === 'WIP FINAL' || $cat === 'WIPFINAL') $currentMasterWip += $val;
                        elseif ($cat === 'FG') $currentMasterFg += $val;
                        elseif ($cat === 'NG') $currentMasterNg += $val;
                    }
                }

                $stokMaterial = $currentMasterMaterial;
                $stokCutting  = $currentMasterCutting;
                $stokOp1      = $currentMasterOp1;
                $stokWipFinal = $currentMasterWip;

                $totOutPcs = 0; $totInPcs = 0;
                foreach ($subconsRows as $sc) {
                    if ($sc['date'] <= $targetDate && $isMatch($sc['item_name'])) {
                        $statusSc = strtoupper($sc['status'] ?? '');
                        $monitoringSc = strtoupper($sc['monitoring'] ?? '');
                        if (strpos($statusSc, 'RETUR') !== false || strpos($monitoringSc, 'RETUR MATERIAL') !== false || strpos($statusSc, 'REPAIR') !== false || strpos($monitoringSc, 'REPAIR VENDOR') !== false) {
                            continue;
                        }

                        if ($sc['type'] === 'OUT') $totOutPcs += $parseNum($sc['quantity']);
                        elseif ($sc['type'] === 'IN') $totInPcs += $parseNum($sc['quantity']);
                    }
                }
                $stokPlating = max(0, $totOutPcs - $totInPcs);

                $subconQcChecked = 0;
                foreach ($productionsRows as $pro) {
                    if ($pro['date'] <= $targetDate && $pro['category'] === 'QC Pengecekan' && $isMatch($pro['item_name'])) {
                        $materialNameSrc = strtoupper($pro['material_name'] ?? '');
                        if (strpos($materialNameSrc, 'SUBCON') !== false || strpos($materialNameSrc, 'PLATING') !== false) {
                            $subconQcChecked += $parseNum($pro['quantity']);
                        }
                    }
                }
                $sisaSubcon = max(0, $totInPcs - $subconQcChecked);
                $stokFc = $stokWipFinal + $sisaSubcon;

                $totalNg = 0;
                foreach ($productionsRows as $pro) {
                    if ($pro['date'] <= $targetDate && $isMatch($pro['item_name'])) {
                        if ($pro['category'] === 'QC Pengecekan') {
                            $qtyNg = (float)($pro['qty_ng'] ?? 0);
                            if ($qtyNg == 0) {
                                $qtyNg = (float)($pro['ng_repair_burry'] ?? 0) + (float)($pro['ng_repair_belum'] ?? 0) + (float)($pro['ng_repair_dimensi'] ?? 0)
                                       + (float)($pro['ng_reject_gompal'] ?? 0) + (float)($pro['ng_reject_baret'] ?? 0) + (float)($pro['ng_reject_hole'] ?? 0)
                                       + (float)($pro['ng_reject_replating'] ?? 0) + (float)($pro['ng_reject_dacon'] ?? 0) + (float)($pro['ng_reject_panjang'] ?? 0) + (float)($pro['ng_reject_pendek'] ?? 0);
                            }
                            $totalNg += $qtyNg;
                        }
                    }
                }
                $stokNg = max($currentMasterNg, $totalNg);

                $totalQcOk = 0;
                foreach ($productionsRows as $pro) {
                    if ($pro['date'] <= $targetDate && $pro['category'] === 'QC Pengecekan' && $isMatch($pro['item_name'])) {
                        $totalQcOk += (float)($pro['qty_ok'] ?? 0);
                    }
                }

                $totalDelivered = 0;
                foreach ($deliveriesRows as $del) {
                    if ($del['date'] <= $targetDate && $isMatch($del['item_name'])) $totalDelivered += $parseNum($del['quantity']);
                }

                $calculatedFg = max(0, $totalQcOk - $totalDelivered);
                $stokFg = max($currentMasterFg, $calculatedFg);
                $stokDelivery = $totalDelivered;

                $formatWithUnit = function($val) use ($itemUnit) {
                    if ($val <= 0) return '-';
                    return number_format($val) . ' ' . $itemUnit;
                };

                $rekapStok[] = [
                    'item_name' => $meta['display_name'],
                    'material'  => $formatWithUnit($stokMaterial),
                    'cutting'   => $formatWithUnit($stokCutting),
                    'op1'       => $formatWithUnit($stokOp1),
                    'wip_final' => $formatWithUnit($stokWipFinal),
                    'plating'   => $formatWithUnit($stokPlating),
                    'fc'        => $formatWithUnit($stokFc),
                    'ng'        => $formatWithUnit($stokNg),
                    'fg'        => $formatWithUnit($stokFg),
                    'delivery'  => $formatWithUnit($stokDelivery)
                ];
            }

            return $rekapStok;
        }

        return [];
    }

    // ==================== PENGATURAN USER ====================

    public function pengaturan()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $users = $db->table('users')->orderBy('id', 'ASC')->get()->getResultArray();

        $data = [
            'tipe_laporan' => '',
            'current_menu' => 'pengaturan',
            'users' => $users
        ];

        return view('admin/pengaturan', $data);
    }

    public function storeUser()
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        
        $username = $this->request->getPost('username');
        $fullName = $this->request->getPost('full_name');
        $password = $this->request->getPost('password');
        $role     = $this->request->getPost('role');

        $existing = $db->table('users')->where('username', $username)->get()->getRow();
        if ($existing) {
            return redirect()->to(base_url('/admin/pengaturan'))->with('error', 'Username/Email tersebut sudah terdaftar!');
        }

        $data = [
            'username'   => $username,
            'full_name'  => $fullName,
            'password'   => password_hash((string)$password, PASSWORD_DEFAULT),
            'role'       => $role,
            'created_at' => date('Y-m-01 H:i:s')
        ];

        $db->table('users')->insert($data);
        return redirect()->to(base_url('/admin/pengaturan'))->with('success', 'Akun pengguna berhasil ditambahkan!');
    }

    public function updateUser($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();

        $data = [
            'username'  => $this->request->getPost('username'),
            'full_name' => $this->request->getPost('full_name'),
            'role'      => $this->request->getPost('role')
        ];

        $db->table('users')->where('id', $id)->update($data);
        return redirect()->to(base_url('/admin/pengaturan'))->with('success', 'Data akun berhasil diperbarui!');
    }

    public function updatePassword($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $newPassword = $this->request->getPost('new_password');

        $data = [
            'password' => password_hash((string)$newPassword, PASSWORD_DEFAULT)
        ];

        $db->table('users')->where('id', $id)->update($data);
        return redirect()->to(base_url('/admin/pengaturan'))->with('success', 'Password akun berhasil diganti!');
    }

    public function deleteUser($id)
    {
        if (session()->get('role') != 'admin') {
            return redirect()->to('/login');
        }

        $db = \Config\Database::connect();
        $db->table('users')->where('id', $id)->delete();
        return redirect()->to(base_url('/admin/pengaturan'))->with('success', 'Akun berhasil dihapus dari sistem!');
    }
}