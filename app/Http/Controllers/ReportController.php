<?php

namespace App\Http\Controllers;

use App\Models\HearingSheet;
use Illuminate\Http\Request;
use App\Models\Report;
use App\Models\ReportContent;
use App\Models\ReportResult;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Storage;
use Barryvdh\DomPDF\Facade\Pdf;
use Spatie\Browsershot\Browsershot;

class ReportController extends Controller
{
    public function create($id)
    {
        $hearing = HearingSheet::with(['preinfos', 'items'])->findOrFail($id);

        $report = Report::with(['contents.results'])
            ->where('hearing_sheet_id', $id)
            ->first();

        return view('report.create', compact('hearing', 'report'));
    }

    public function preview($id)
    {
        $report = Report::with([
            'hearingSheet',
            'hearingSheet.preinfos',
            'hearingSheet.items',
            'contents.results',
        ])->findOrFail($id);

        return view('report.preview', compact('report'));
    }

    public function store(Request $request)
    {
        DB::beginTransaction();

        try {
            // 1. 報告書を作成（UUIDは report_key に）
            $report = Report::firstOrNew([
                'hearing_sheet_id' => $request->input('hearing_sheet_id'),
            ]);

            $report->staff_comment = $request->input('staff_comment');
            $report->report_key = $report->report_key ?? (string) Str::uuid(); // 初回のみuuidを発行
            $report->save();

            $test = $report->id;
            // 2. 調査内容をループで作成
            foreach ($request->input('content_summary', []) as $i => $summary) {
                $contentId = $request->input('content_ids')[$i] ?? null;

                $reportContent = $contentId
                    ? ReportContent::find($contentId) ?? new ReportContent()
                    : new ReportContent();

                $reportContent->report_id = $report->id;
                $reportContent->summary = $summary;
                $reportContent->save();

                foreach ($request->input("content_descriptions.$i", []) as $j => $description) {
                    $resultId = $request->input("result_ids.$i")[$j] ?? null;

                    $result = $resultId
                        ? ReportResult::find($resultId) ?? new ReportResult()
                        : new ReportResult();

                    $result->report_content_id = $reportContent->id;
                    $result->description = $description;

                    // ✅ 調査日追加
                    $dateInput = $request->input("content_dates.$i")[$j] ?? null;
                    $result->date = $dateInput ? \Carbon\Carbon::parse($dateInput)->format('Y-m-d') : null;

                    // 画像保存
                    $images = $request->file("content_images.$i.$j", []);
                    $paths = [];
                    foreach ($images as $img) {
                        $paths[] = $img->store("report_images", "public");
                    }
                    if (!empty($paths)) {
                        $result->image_paths = $paths;
                    }

                    $result->save();
                }
            }

            // 4. hearing_items の追加
            $hearing = HearingSheet::findOrFail($request->input('hearing_sheet_id'));

            if ($request->has('new_investigation_items')) {
                foreach ($request->input('new_investigation_items') as $label) {
                    if (trim($label)) {
                        $hearing->items()->create(['item_label' => $label]);
                    }
                }
            }

            // 5. hearing_preinfos の追加
            $existingIds = $request->input('preinfo_ids', []);

            if ($request->has('preinfo_labels')) {
                foreach ($request->input('preinfo_labels') as $i => $label) {
                    // すでにIDがある場合はスキップ（＝既存）
                    if (!empty($existingIds[$i])) {
                        continue;
                    }

                    $value = $request->input('preinfo_values')[$i] ?? null;
                    $image = $request->file('preinfo_images')[$i] ?? null;
                    $imagePath = $image ? $image->store('preinfo_images', 'public') : null;

                    $hearing->preinfos()->create([
                        'label' => $label,
                        'value' => $value,
                        'image_path' => $imagePath,
                    ]);
                }
            }

            DB::commit();
            return redirect()->route('report.create', ['id' => $request->input('hearing_sheet_id')])->with('success', '報告書を保存しました。');

        } catch (\Exception $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '保存に失敗しました: ' . $e->getMessage()]);
        }
    }

    public function downloadPdf($id)
    {
        $report = Report::with([
            'hearingSheet.preinfos',
            'hearingSheet.items',
            'contents.results'
        ])->findOrFail($id);

        $pdf = Pdf::loadView('report.preview', compact('report'))->setPaper('a4', 'portrait');
        return $pdf->download("report_{$id}.pdf");

        // $url = route('report.preview', ['id' => $id]);

        // $pdfPath = storage_path("app/public/reports/report_{$id}.pdf");

        // Browsershot::url($url)
        //     ->noSandbox() // VPSなどで必要
        //     ->waitUntilNetworkIdle()
        //     ->format('A4')
        //     ->savePdf($pdfPath);

        // return response()->download($pdfPath);
    }
}
