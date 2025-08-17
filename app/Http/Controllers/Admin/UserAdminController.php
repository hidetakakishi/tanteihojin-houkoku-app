<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;
use Illuminate\Support\Facades\Hash;

class UserAdminController extends Controller
{
    public function index(Request $request)
    {
        $q = trim($request->get('q', ''));
        $users = User::when($q, function ($query) use ($q) {
                    $query->where(function ($q2) use ($q) {
                        $q2->where('name', 'like', "%{$q}%")
                           ->orWhere('email', 'like', "%{$q}%");
                    });
                })
                ->orderByDesc('id')
                ->paginate(12)
                ->withQueryString();

        return view('admin.users.index', compact('users', 'q'));
    }

    public function create()
    {
        return view('admin.users.create');
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'name'     => ['required', 'string', 'max:255'],
            'email'    => ['required', 'string', 'email', 'max:255', 'unique:users,email'],
            'password' => ['required', 'string', 'min:8', 'confirmed'],
            'is_admin' => ['nullable', 'boolean'],
        ]);

        $user = User::create([
            'name'     => $data['name'],
            'email'    => $data['email'],
            'password' => Hash::make($data['password']),
            'is_admin' => (bool)($data['is_admin'] ?? false),
        ]);

        return redirect()->route('admin.users.index')->with('success', "ユーザー「{$user->name}」を作成しました。");
    }

    public function edit(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function update(Request $request, User $user)
    {
        $data = $request->validate([
            'name'     => ['required','string','max:255'],
            'email'    => ['required','string','email','max:255', Rule::unique('users','email')->ignore($user->id)],
            'is_admin' => ['nullable','boolean'],
            'change_password' => ['nullable','boolean'],
        ]);

        if ($request->boolean('change_password')) {
            $request->validate([
                'password' => ['required','string','min:8','confirmed'],
            ]);
            $user->password = Hash::make($request->input('password'));
        }

        $user->name     = $data['name'];
        $user->email    = $data['email'];
        $user->is_admin = $request->boolean('is_admin');
        $user->save();

        return redirect()->route('admin.users.index')->with('success', "ユーザー「{$user->name}」を更新しました。");
    }

    public function destroy(User $user)
    {
        // 自分自身の削除はブロック（お好みで）
        if (auth()->id() === $user->id) {
            return back()->withErrors(['error' => '自分自身は削除できません。']);
        }

        $name = $user->name;
        $user->delete();

        return redirect()->route('admin.users.index')->with('success', "ユーザー「{$name}」を削除しました。");
    }
}