<?php

namespace App\Http\Controllers;

use App\Models\HearingSheet;
use App\Models\HearingPreinfo;
use App\Models\HearingItem;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class HearingSheetController extends Controller
{
    /**
     * ログインユーザーに紐づくヒアリングシート一覧を表示
     */
    public function list(Request $request)
    {
        $user = auth()->user();

        $query = HearingSheet::with([
            'report:id,hearing_sheet_id,report_key,access_key',
            'user:id,name',
        ]);

        // 管理者以外は自分の分だけ
        if (!$user?->is_admin) {
            $query->where('user_id', $user->id);
        }

        // ▼ キーワード検索（ID / 種類 / 依頼人 / 担当者 / 目的）
        if ($kw = trim((string) $request->get('q', ''))) {
            $query->where(function ($q) use ($kw) {
                $q->where('investigation_type', 'like', "%{$kw}%")
                ->orWhere('client_name', 'like', "%{$kw}%")
                ->orWhere('staff_name', 'like', "%{$kw}%")
                ->orWhere('purpose', 'like', "%{$kw}%");

                // 数値が入っていればID一致も見る
                if (ctype_digit($kw)) {
                    $q->orWhere('id', (int)$kw);
                }
            });
        }

        // ▼ 管理者のみ：担当者で絞り込み
        if ($user?->is_admin && ($staff = $request->get('staff'))) {
            if ($staff !== '') {
                $query->where('staff_name', $staff);
            }
        }

        // ▼ 並び替え（ホワイトリスト）
        $allowedSorts = ['interview_datetime', 'created_at', 'id'];
        $sort = $request->get('sort', 'interview_datetime');
        if (!in_array($sort, $allowedSorts, true)) {
            $sort = 'interview_datetime';
        }

        $dir = $request->get('dir', 'desc');
        if (!in_array($dir, ['asc', 'desc'], true)) {
            $dir = 'desc';
        }
        $query->orderBy($sort, $dir);

        // ページネーション（クエリ保持）
        $sheets = $query->paginate(10)->withQueryString();

        // 管理者用の担当者候補（distinct）
        $staffOptions = [];
        if ($user?->is_admin) {
            $staffOptions = HearingSheet::select('staff_name')
                ->distinct()
                ->orderBy('staff_name')
                ->pluck('staff_name')
                ->filter()
                ->values();
        }

        return view('hearingsheet.list', compact('sheets', 'staffOptions'));
    }
    
    /**
     * 個別ヒアリングシート詳細表示
     */
    public function show($id)
    {
        $sheet = HearingSheet::with(['preinfos', 'items'])->findOrFail($id);
        return view('hearingsheet.show', compact('sheet'));
    }

    public function edit($id)
    {
        $sheet = HearingSheet::with(['preinfos', 'items'])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        return view('hearingsheet.edit', compact('sheet'));
    }

    public function update(Request $request, $id)
    {
        $request->validate([
            'interview_datetime' => 'required|date',
            'investigation_type' => 'required|string',
            'client_name'        => 'required|string',
            'staff_name'         => 'required|string',
            'purpose'            => 'required|string',
        ]);

        $sheet = HearingSheet::where('user_id', auth()->id())->findOrFail($id);

        // 基本情報更新
        $sheet->update([
            'interview_datetime' => $request->input('interview_datetime'),
            'investigation_type' => $request->input('investigation_type'),
            'client_name'        => $request->input('client_name'),
            'staff_name'         => $request->input('staff_name'),
            'purpose'            => $request->input('purpose'),
        ]);

        // 事前情報を一旦全削除→再作成（シンプル運用）
        $sheet->preinfos()->delete();
        if ($request->has('preinfo')) {
            foreach ($request->input('preinfo') as $index => $info) {
                $value = $info['text'] ?? '';
                $label = $info['label'] ?? null;
                if (trim($value) === '' || trim($label) === '') continue;

                $imagePath = null;
                if ($request->hasFile("preinfo.$index.image")) {
                    $imagePath = $request->file("preinfo.$index.image")->store('preinfo_images', 'public');
                }

                $sheet->preinfos()->create([
                    'label'      => $label,
                    'value'      => $value,
                    'image_path' => $imagePath,
                ]);
            }
        }

        // 調査項目も全削除→再作成
        $sheet->items()->delete();
        if ($request->filled('survey_items')) {
            foreach ($request->input('survey_items') as $itemLabel) {
                if (trim($itemLabel) === '') continue;
                $sheet->items()->create(['item_label' => $itemLabel]);
            }
        }

        return redirect()
            ->route('hearingsheet.edit', $sheet->id)
            ->with('success', 'ヒアリングシートを更新しました。');
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
            'client_name'        => 'required|string',
            'staff_name'         => 'required|string',
            'purpose'            => 'required|string',
        ]);

        // メイン情報を保存（user_idを含む）
        $sheet = HearingSheet::create([
            'interview_datetime' => $request->input('interview_datetime'),
            'investigation_type' => $request->input('investigation_type'),
            'client_name'        => $request->input('client_name'),
            'staff_name'         => $request->input('staff_name'),
            'purpose'            => $request->input('purpose'),
            'user_id'            => Auth::id(),
        ]);

        // 事前情報（テキスト＋画像）
        if ($request->has('preinfo')) {
            foreach ($request->input('preinfo') as $index => $info) {
                $value = $info['text'] ?? '';
                $label = $info['label'] ?? null;

                if (trim($value) === '' || trim($label) === '') continue;

                $imagePath = null;
                if ($request->hasFile("preinfo.$index.image")) {
                    $imagePath = $request->file("preinfo.$index.image")->store('preinfo_images', 'public');
                }

                HearingPreinfo::create([
                    'hearing_sheet_id' => $sheet->id,
                    'label'            => $label,
                    'value'            => $value,
                    'image_path'       => $imagePath,
                ]);
            }
        }

        // 調査項目（チェックボックス）
        if ($request->filled('survey_items')) {
            foreach ($request->input('survey_items') as $itemLabel) {
                if (trim($itemLabel) === '') continue;

                HearingItem::create([
                    'hearing_sheet_id' => $sheet->id,
                    'item_label'       => $itemLabel,
                ]);
            }
        }

        // ✅ 保存後は編集画面へ
        return redirect()
            ->route('hearingsheet.edit', $sheet->id)
            ->with('success', 'ヒアリングシートを保存しました。');
    }

    public function destroy($id)
    {
        // ログインユーザーの所有物のみ削除
        $sheet = HearingSheet::with([
                'preinfos',
                'items',
                'report.contents.results',
                'report.videos',
            ])
            ->where('user_id', auth()->id())
            ->findOrFail($id);

        DB::beginTransaction();
        try {
            // 1) まず report 周りを削除（画像/動画の実ファイルも）
            if ($sheet->report) {
                // 1-1) 動画
                if (method_exists($sheet->report, 'videos')) {
                    foreach ($sheet->report->videos as $video) {
                        if (!empty($video->video_path)) {
                            Storage::disk('public')->delete($video->video_path);
                        }
                        $video->delete();
                    }
                }

                // 1-2) 調査結果（画像ファイル削除）
                foreach ($sheet->report->contents as $content) {
                    foreach ($content->results as $result) {
                        $paths = $result->image_paths;
                        if (is_array($paths)) {
                            foreach ($paths as $p) {
                                if (!empty($p)) {
                                    Storage::disk('public')->delete($p);
                                }
                            }
                        }
                        $result->delete();
                    }
                    $content->delete();
                }

                $sheet->report->delete();
            }

            // 2) 事前情報（画像ファイルも削除）
            foreach ($sheet->preinfos as $pi) {
                if (!empty($pi->image_path)) {
                    Storage::disk('public')->delete($pi->image_path);
                }
                $pi->delete();
            }

            // 3) 調査項目
            foreach ($sheet->items as $it) {
                $it->delete();
            }

            // 4) ヒアリングシート本体
            $sheet->delete();

            DB::commit();
            return back()->with('success', 'ヒアリングシートを削除しました。');

        } catch (\Throwable $e) {
            DB::rollBack();
            return back()->withErrors(['error' => '削除に失敗しました：' . $e->getMessage()]);
        }
    }
}