<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\Barang;
use App\Models\BarangKeluar;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanController extends Controller
{
    public function index()
    {
        return view('laporan.index');
    }

    // Fungsi bantu ambil data laporan
    private function getLaporanData($kategori, $tanggal_dari, $tanggal_sampai)
    {
        $query = Barang::with(['satuan']);
        if ($kategori != 'semua') {
            $query->where('kategori', $kategori);
        }
        $barangs = $query->get();

        return $barangs->map(function($barang) use ($tanggal_dari, $tanggal_sampai) {
            $keluarData = BarangKeluar::where('id_barang', $barang->id_barang)
                ->whereBetween('tanggal_keluar', [$tanggal_dari, $tanggal_sampai])
                ->get();

            $totalKeluar = $keluarData->sum('jumlah_keluar');
            $keterangan  = $keluarData->pluck('keterangan')
                            ->filter()->unique()->implode(', ');

            return [
                'nama_barang'    => $barang->nama_barang,
                'satuan'         => $barang->satuan->nama_satuan ?? '-',
                'stok_sekarang'  => $barang->stok,
                'stok_minimum'   => $barang->stok_minimum,
                'pemakaian'      => $totalKeluar,
                'sisa_pemakaian' => $barang->stok - $totalKeluar,
                'keterangan'     => $keterangan ?: '-',
            ];
        });
    }

    // Fungsi bantu buat judul laporan (ditampilkan di halaman & isi dokumen)
    private function getJudul($kategori, $tanggal_dari, $tanggal_sampai)
    {
        $bulanAwal  = \Carbon\Carbon::parse($tanggal_dari)->locale('id')->isoFormat('MMMM YYYY');
        $bulanAkhir = \Carbon\Carbon::parse($tanggal_sampai)->locale('id')->isoFormat('MMMM YYYY');
        $periode    = $bulanAwal == $bulanAkhir ? strtoupper($bulanAwal) : strtoupper($bulanAwal . ' - ' . $bulanAkhir);

        if ($kategori == 'semua') {
            return "DAFTAR STOCK BARANG DI GUDANG DECK $periode";
        } else {
            return "DAFTAR STOCK BARANG " . strtoupper($kategori) . " DI GUDANG DECK $periode";
        }
    }

    // Fungsi bantu buat nama file download
    private function getFilename($kategori, $tanggal_dari, $tanggal_sampai, $ext)
    {
        $bulanAwal  = \Carbon\Carbon::parse($tanggal_dari)->locale('id')->isoFormat('MMMM_YYYY');
        $bulanAkhir = \Carbon\Carbon::parse($tanggal_sampai)->locale('id')->isoFormat('MMMM_YYYY');
        $periode    = $bulanAwal == $bulanAkhir ? $bulanAwal : $bulanAwal . '-' . $bulanAkhir;

        $kategoriLabel = $kategori == 'semua' ? 'Semua' : str_replace(' ', '_', $kategori);

        return "Laporan_Stok_Barang_{$kategoriLabel}_{$periode}.{$ext}";
    }

    public function generate(Request $request)
    {
        $request->validate([
            'kategori'       => 'required',
            'tanggal_dari'   => 'required|date|before_or_equal:today',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_dari|before_or_equal:today',
        ], [
            'kategori.required'              => 'Kategori wajib dipilih.',
            'tanggal_dari.required'          => 'Tanggal awal wajib diisi.',
            'tanggal_dari.before_or_equal'   => 'Tanggal awal tidak boleh melebihi tanggal hari ini.',
            'tanggal_sampai.required'        => 'Tanggal akhir wajib diisi.',
            'tanggal_sampai.after_or_equal'  => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            'tanggal_sampai.before_or_equal' => 'Tanggal akhir tidak boleh melebihi tanggal hari ini.',
        ]);

        $laporanData = $this->getLaporanData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $judul       = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);

        return view('laporan.index', compact('laporanData', 'request', 'judul'));
    }

    public function downloadWord(Request $request)
    {
        $laporanData = $this->getLaporanData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $judul       = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $filename    = $this->getFilename($request->kategori, $request->tanggal_dari, $request->tanggal_sampai, 'docx');

        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'marginTop'    => 1000,
            'marginBottom' => 1000,
            'marginLeft'   => 1200,
            'marginRight'  => 1200,
        ]);

        // Judul
        $section->addText($judul, [
            'bold'     => true,
            'size'     => 12,
            'name'     => 'Arial',
        ], ['alignment' => 'center']);

        $section->addTextBreak(1);

        // Tabel
        $table = $section->addTable([
            'borderSize' => 6,
            'borderColor' => '000000',
            'cellMargin'  => 80,
            'width'       => 100 * 50,
            'unit'        => TblWidth::PERCENT,
        ]);

        // Header baris 1
        $headerStyle = ['bold' => true, 'size' => 10, 'name' => 'Arial'];
        $cellStyle   = ['bgColor' => 'D9D9D9'];
        $center      = ['alignment' => 'center'];

        $table->addRow();
        $table->addCell(500,  array_merge($cellStyle, ['vMerge' => 'restart']))->addText('No.', $headerStyle, $center);
        $table->addCell(2500, array_merge($cellStyle, ['vMerge' => 'restart']))->addText('Nama Barang', $headerStyle, $center);
        $table->addCell(1000, array_merge($cellStyle, ['vMerge' => 'restart']))->addText('Satuan', $headerStyle, $center);
        $table->addCell(3000, array_merge($cellStyle, ['gridSpan' => 3]))->addText('Banyaknya', $headerStyle, $center);
        $table->addCell(2500, array_merge($cellStyle, ['vMerge' => 'restart']))->addText('Keterangan', $headerStyle, $center);

        // Header baris 2
        $table->addRow();
        $table->addCell(500,  ['vMerge' => 'continue']);
        $table->addCell(2500, ['vMerge' => 'continue']);
        $table->addCell(1000, ['vMerge' => 'continue']);
        $table->addCell(1000, $cellStyle)->addText('Stok Sekarang', $headerStyle, $center);
        $table->addCell(1000, $cellStyle)->addText('Pemakaian', $headerStyle, $center);
        $table->addCell(1000, $cellStyle)->addText('Sisa Pemakaian', $headerStyle, $center);
        $table->addCell(2500, ['vMerge' => 'continue']);

        // Data
        $dataStyle = ['size' => 10, 'name' => 'Arial'];
        foreach ($laporanData as $index => $item) {
            $table->addRow();
            $table->addCell(500)->addText($index + 1, $dataStyle, $center);
            $table->addCell(2500)->addText($item['nama_barang'], $dataStyle);
            $table->addCell(1000)->addText($item['satuan'], $dataStyle, $center);
            $table->addCell(1000)->addText($item['stok_sekarang'], $dataStyle, $center);
            $table->addCell(1000)->addText($item['pemakaian'], $dataStyle, $center);
            $table->addCell(1000)->addText($item['sisa_pemakaian'], $dataStyle, $center);
            $table->addCell(2500)->addText($item['keterangan'], $dataStyle);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.wordprocessingml.document');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $objWriter = \PhpOffice\PhpWord\IOFactory::createWriter($phpWord, 'Word2007');
        $objWriter->save('php://output');
        exit;
    }

    public function downloadExcel(Request $request)
    {
        $laporanData = $this->getLaporanData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $judul       = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $filename    = $this->getFilename($request->kategori, $request->tanggal_dari, $request->tanggal_sampai, 'xlsx');

        $spreadsheet = new Spreadsheet();
        $sheet       = $spreadsheet->getActiveSheet();

        // Judul
        $sheet->mergeCells('A1:G1');
        $sheet->setCellValue('A1', $judul);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        // Header baris 1
        $sheet->mergeCells('A2:A3');
        $sheet->mergeCells('B2:B3');
        $sheet->mergeCells('C2:C3');
        $sheet->mergeCells('D2:F2');
        $sheet->mergeCells('G2:G3');

        $sheet->setCellValue('A2', 'No.');
        $sheet->setCellValue('B2', 'Nama Barang');
        $sheet->setCellValue('C2', 'Satuan');
        $sheet->setCellValue('D2', 'Banyaknya');
        $sheet->setCellValue('G2', 'Keterangan');

        // Header baris 2
        $sheet->setCellValue('D3', 'Stok Sekarang');
        $sheet->setCellValue('E3', 'Pemakaian');
        $sheet->setCellValue('F3', 'Sisa Pemakaian');

        // Style header
        $headerStyle = [
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9D9D9']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A2:G3')->applyFromArray($headerStyle);

        // Data
        $row = 4;
        foreach ($laporanData as $index => $item) {
            $sheet->setCellValue("A$row", $index + 1);
            $sheet->setCellValue("B$row", $item['nama_barang']);
            $sheet->setCellValue("C$row", $item['satuan']);
            $sheet->setCellValue("D$row", $item['stok_sekarang']);
            $sheet->setCellValue("E$row", $item['pemakaian']);
            $sheet->setCellValue("F$row", $item['sisa_pemakaian']);
            $sheet->setCellValue("G$row", $item['keterangan']);

            $sheet->getStyle("A$row:G$row")->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("C$row:F$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        // Auto width kolom
        foreach (range('A', 'G') as $col) {
            $sheet->getColumnDimension($col)->setAutoSize(true);
        }

        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header('Content-Disposition: attachment;filename="' . $filename . '"');
        header('Cache-Control: max-age=0');

        $writer = new Xlsx($spreadsheet);
        $writer->save('php://output');
        exit;
    }
}