<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;
use App\Models\Skill;
use App\Models\ShiftPattern;

class StaffController extends Controller
{
    /**
     * スタッフ登録画面を表示
     */
    public function create()
    {
        $skills = Skill::all();
        return view('admin.shift_patterns.create', compact('skills'));
    }

    public function edit(ShiftPattern $shiftPattern)
    {
        $skills = Skill::all();
        return view('admin.shift_patterns.edit', compact('shiftPattern', 'skills'));
    }

    /**
     * スタッフを新規登録する
     */
    public function store(Request $request)
    {
        $request->validate([
            'staff_id' => ['required', 'string', 'max:255', 'unique:' . User::class], // 追加 
            'name' => ['required', 'string', 'max:255'],
            'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:' . User::class],
            'password' => ['required', 'confirmed', Rules\Password::defaults()],
        ]);

        User::create([
            'staff_id' => $request->staff_id,
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            // 必要に応じてスタッフ権限を付与するカラムがあればここに記述
            // 'role' => 'staff', 
        ]);

        return redirect()->route('dashboard')->with('success', 'スタッフを登録しました。');
    }


   
}
