<?php

namespace App\Http\Controllers\API;

use App\Models\User;
use App\Models\UserDetail;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class UserDetailController extends Controller
{

    public function updateAbout(Request $request)
    {
        $newText = $request->input('about_user');
        $user = User::find(auth()->user()->id);

        if (!$user->detail) {
            $user->detail()->create(['about_user' => $newText]);
        } else {
            $user->detail->update(['about_user' => $newText]);
        }
        $updatedDetail = $user->detail()->first();

        return response()->json([
            'message' => "Zmieniono opis urzytkownika",
            "about_user" => $updatedDetail->about_user
        ], 200);
    }
}
