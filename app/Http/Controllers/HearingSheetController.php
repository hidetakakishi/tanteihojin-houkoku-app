<?php

namespace App\Http\Controllers;

use App\Models\HearingSheet;
use App\Models\HearingPreinfo;
use App\Models\HearingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;

class HearingSheetController extends Controller
{
     /**
     * ログインユーザーに紐づくヒアリングシート一覧を表示
     */
    public function list()
    {
        $user = Auth::user();

        $sheets = HearingSheet::where('user_id', $user->id)
                    ->latest()
                    ->paginate(10);

        return view('hearingsheet.list', compact('sheets'));
    }

   /**
     * 個別ヒアリングシート詳細表示
     */
    public function show($id)
    {
        $sheet = HearingSheet::with(['preinfos', 'items'])->findOrFail($id);
        return view('hearingsheet.show', compact('sheet'));
    }

    /**
     * ヒアリングシートの保存
     */
    public function store(Request $request)
    {
        // バリデーション（基本部分）
        $request->validate([
            'interview_datetime' => 'required|date',
            'investigation_type' => 'required|string',
            'client_name' => 'required|string',
            'staff_name' => 'required|string',
            'purpose' => 'required|string',
        ]);

        // メイン情報を保存（user_idを含む）
        $sheet = HearingSheet::create([
            'interview_datetime' => $request->input('interview_datetime'),
            'investigation_type' => $request->input('investigation_type'),
            'client_name' => $request->input('client_name'),
            'staff_name' => $request->input('staff_name'),
            'purpose' => $request->input('purpose'),
            'user_id' => Auth::id(), // ← ログインユーザーのIDを保存
        ]);

        // 事前情報（テキスト＋画像）
        if ($request->has('preinfo')) {
            foreach ($request->input('preinfo') as $index => $info) {
                $value = $info['text'] ?? '';
                $label = $info['label'] ?? null;

                if (trim($value) === '' || trim($label) === '') {
                    continue; // 値またはラベルが空ならスキップ
                }

                $imagePath = null;
                if ($request->hasFile("preinfo.$index.image")) {
                    $imagePath = $request->file("preinfo.$index.image")->store('preinfo_images', 'public');
                }

                HearingPreinfo::create([
                    'hearing_sheet_id' => $sheet->id,
                    'label' => $label,
                    'value' => $value,
                    'image_path' => $imagePath,
                ]);
            }
        }

        // 調査項目（チェックボックス）
        if ($request->filled('survey_items')) {
            foreach ($request->input('survey_items') as $itemLabel) {
                if (trim($itemLabel) === '') {
                    continue; // 空チェックボックス項目はスキップ
                }

                HearingItem::create([
                    'hearing_sheet_id' => $sheet->id,
                    'item_label' => $itemLabel,
                ]);
            }
        }

        return redirect()->route('hearingsheet.index')->with('success', 'ヒアリングシートを保存しました。');
    }
}