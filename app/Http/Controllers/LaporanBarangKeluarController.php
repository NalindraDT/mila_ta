<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\BarangKeluar;
use PhpOffice\PhpWord\PhpWord;
use PhpOffice\PhpWord\SimpleType\TblWidth;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Style\Fill;

class LaporanBarangKeluarController extends Controller
{
    public function index()
    {
        return view('laporan-barang-keluar.index');
    }

    private function getData($kategori, $tanggal_dari, $tanggal_sampai)
    {
        $query = BarangKeluar::with(['barang.satuan'])
                    ->whereBetween('tanggal_keluar', [$tanggal_dari, $tanggal_sampai]);

        if ($kategori != 'semua') {
            $query->whereHas('barang', function ($q) use ($kategori) {
                $q->where('kategori', $kategori);
            });
        }

        return $query->orderBy('tanggal_keluar')->get();
    }

    private function getJudul($kategori, $tanggal_dari, $tanggal_sampai)
    {
        $tglAwal  = \Carbon\Carbon::parse($tanggal_dari)->locale('id')->isoFormat('D MMMM YYYY');
        $tglAkhir = \Carbon\Carbon::parse($tanggal_sampai)->locale('id')->isoFormat('D MMMM YYYY');
        $periode  = $tglAwal == $tglAkhir ? $tglAwal : "$tglAwal s/d $tglAkhir";
        $jenis    = $kategori == 'semua' ? 'SEMUA JENIS' : strtoupper($kategori);

        return "BARANG KELUAR KN PRAJAPATI TANGGAL $periode JENIS $jenis";
    }

    private function getFilename($kategori, $tanggal_dari, $tanggal_sampai, $ext)
    {
        $tglAwal  = \Carbon\Carbon::parse($tanggal_dari)->format('d-m-Y');
        $tglAkhir = \Carbon\Carbon::parse($tanggal_sampai)->format('d-m-Y');
        $periode  = $tglAwal == $tglAkhir ? $tglAwal : "{$tglAwal}_sd_{$tglAkhir}";
        $kategoriLabel = $kategori == 'semua' ? 'Semua' : str_replace(' ', '_', $kategori);

        return "Laporan_Barang_Keluar_{$kategoriLabel}_{$periode}.{$ext}";
    }

    public function generate(Request $request)
    {
        $request->validate([
            'kategori'       => 'required',
            'tanggal_dari'   => 'required|date|before_or_equal:today',
            'tanggal_sampai' => 'required|date|after_or_equal:tanggal_dari|before_or_equal:today',
        ], [
            'kategori.required'              => 'Jenis wajib dipilih.',
            'tanggal_dari.required'          => 'Tanggal awal wajib diisi.',
            'tanggal_dari.before_or_equal'   => 'Tanggal awal tidak boleh melebihi tanggal hari ini.',
            'tanggal_sampai.required'        => 'Tanggal akhir wajib diisi.',
            'tanggal_sampai.after_or_equal'  => 'Tanggal akhir harus sama atau setelah tanggal awal.',
            'tanggal_sampai.before_or_equal' => 'Tanggal akhir tidak boleh melebihi tanggal hari ini.',
        ]);

        $items = $this->getData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $judul = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);

        return view('laporan-barang-keluar.index', compact('items', 'judul', 'request'));
    }

    public function downloadWord(Request $request)
    {
        $items    = $this->getData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $judul    = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $filename = $this->getFilename($request->kategori, $request->tanggal_dari, $request->tanggal_sampai, 'docx');

        $phpWord = new PhpWord();
        $section = $phpWord->addSection([
            'marginTop' => 1000, 'marginBottom' => 1000, 'marginLeft' => 1200, 'marginRight' => 1200,
        ]);

        $section->addText($judul, ['bold' => true, 'size' => 12, 'name' => 'Arial'], ['alignment' => 'center']);
        $section->addTextBreak(1);

        $table = $section->addTable([
            'borderSize' => 6, 'borderColor' => '000000', 'cellMargin' => 80,
            'width' => 100 * 50, 'unit' => TblWidth::PERCENT,
        ]);

        $headerStyle = ['bold' => true, 'size' => 10, 'name' => 'Arial'];
        $cellStyle   = ['bgColor' => 'D9D9D9'];
        $center      = ['alignment' => 'center'];

        $table->addRow();
        $table->addCell(500, $cellStyle)->addText('No.', $headerStyle, $center);
        $table->addCell(1100, $cellStyle)->addText('Tanggal', $headerStyle, $center);
        $table->addCell(2500, $cellStyle)->addText('Nama Barang', $headerStyle, $center);
        $table->addCell(1100, $cellStyle)->addText('Satuan', $headerStyle, $center);
        $table->addCell(1200, $cellStyle)->addText('Jumlah Keluar', $headerStyle, $center);
        $table->addCell(2100, $cellStyle)->addText('Keterangan', $headerStyle, $center);

        $dataStyle = ['size' => 10, 'name' => 'Arial'];
        foreach ($items as $index => $item) {
            $table->addRow();
            $table->addCell(500)->addText($index + 1, $dataStyle, $center);
            $table->addCell(1100)->addText(\Carbon\Carbon::parse($item->tanggal_keluar)->format('d/m/Y'), $dataStyle, $center);
            $table->addCell(2500)->addText($item->barang->nama_barang ?? '-', $dataStyle);
            $table->addCell(1100)->addText($item->barang->satuan->nama_satuan ?? '-', $dataStyle, $center);
            $table->addCell(1200)->addText($item->jumlah_keluar, $dataStyle, $center);
            $table->addCell(2100)->addText($item->keterangan ?: '-', $dataStyle);
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
        $items    = $this->getData($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $judul    = $this->getJudul($request->kategori, $request->tanggal_dari, $request->tanggal_sampai);
        $filename = $this->getFilename($request->kategori, $request->tanggal_dari, $request->tanggal_sampai, 'xlsx');

        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();

        $sheet->mergeCells('A1:F1');
        $sheet->setCellValue('A1', $judul);
        $sheet->getStyle('A1')->applyFromArray([
            'font'      => ['bold' => true, 'size' => 12],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER],
        ]);

        $sheet->setCellValue('A2', 'No.');
        $sheet->setCellValue('B2', 'Tanggal');
        $sheet->setCellValue('C2', 'Nama Barang');
        $sheet->setCellValue('D2', 'Satuan');
        $sheet->setCellValue('E2', 'Jumlah Keluar');
        $sheet->setCellValue('F2', 'Keterangan');

        $headerStyle = [
            'font'      => ['bold' => true],
            'alignment' => ['horizontal' => Alignment::HORIZONTAL_CENTER, 'vertical' => Alignment::VERTICAL_CENTER],
            'fill'      => ['fillType' => Fill::FILL_SOLID, 'startColor' => ['argb' => 'FFD9D9D9']],
            'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
        ];
        $sheet->getStyle('A2:F2')->applyFromArray($headerStyle);

        $row = 3;
        foreach ($items as $index => $item) {
            $sheet->setCellValue("A$row", $index + 1);
            $sheet->setCellValue("B$row", \Carbon\Carbon::parse($item->tanggal_keluar)->format('d/m/Y'));
            $sheet->setCellValue("C$row", $item->barang->nama_barang ?? '-');
            $sheet->setCellValue("D$row", $item->barang->satuan->nama_satuan ?? '-');
            $sheet->setCellValue("E$row", $item->jumlah_keluar);
            $sheet->setCellValue("F$row", $item->keterangan ?: '-');

            $sheet->getStyle("A$row:F$row")->applyFromArray([
                'borders'   => ['allBorders' => ['borderStyle' => Border::BORDER_THIN]],
                'alignment' => ['vertical' => Alignment::VERTICAL_CENTER],
            ]);
            $sheet->getStyle("A$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("B$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $sheet->getStyle("D$row:E$row")->getAlignment()->setHorizontal(Alignment::HORIZONTAL_CENTER);
            $row++;
        }

        foreach (range('A', 'F') as $col) {
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