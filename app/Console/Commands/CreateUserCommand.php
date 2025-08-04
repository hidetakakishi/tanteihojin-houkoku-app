<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Models\User;
use Illuminate\Support\Facades\Hash;

class CreateUserCommand extends Command
{
    protected $signature = 'user:create 
                            {name : ユーザー名} 
                            {email : メールアドレス} 
                            {password : パスワード} 
                            {--role= : ロール（例：admin, user, investigator）}';

    protected $description = '新しいユーザーアカウントを作成します';

    public function handle()
    {
        $name = $this->argument('name');
        $email = $this->argument('email');
        $password = $this->argument('password');
        $role = $this->option('role') ?? 'user';

        // 重複チェック
        if (User::where('email', $email)->exists()) {
            $this->error("このメールアドレスは既に登録されています。");
            return 1;
        }

        // ユーザー作成
        $user = User::create([
            'name' => $name,
            'email' => $email,
            'password' => Hash::make($password),
            'role' => $role,
        ]);

        $this->info("ユーザー「{$user->name}」を作成しました（ID: {$user->id}）");

        return 0;
    }
}
