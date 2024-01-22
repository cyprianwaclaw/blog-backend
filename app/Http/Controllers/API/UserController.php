<?php

namespace App\Http\Controllers\API;

use App\Models\Image;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{

    public function getAuthenticatedUser()
    {
        $user = Auth::user();

        if ($user) {
            return response()->json(['data' => $user], 200);
        } else {
            return response()->json(['data' => null], 200);
        }
    }

    public function getAuthenticatedUser1()
    {
        if (Auth::check()) {
            // Użytkownik jest zalogowany
            return response()->json('zalogowany');
        } else {
            // Użytkownik nie jest zalogowany
            return response()->json('nie zalogowany');
        }
    }
    // public function upload(Request $request)
    // {
    //     // $validator = Validator::make($request->all(), [
    //     //     'image' => 'required|mimes:png,jpg'
    //     // ]);
    //     // if ($validator->fails()) {
    //     //     return response()->json([
    //     //         'status' => false,
    //     //         'message' => 'Please fix the errors',
    //     //         'errors' => $validator->errors()
    //     //     ]);
    //     // };
    //     // $img = $request->image;
    //     // $ext = $img->getClientOrininalExtension();
    //     // $imageName = time().'/'.$ext;
    //     //     $img ->move(public_path().'/storage', $imageName );
    //     // // $image = Image::create
    //     // $image = new Image;
    //     // $image -> name=$imageName;
    //     // $image->save();


    // }
}
