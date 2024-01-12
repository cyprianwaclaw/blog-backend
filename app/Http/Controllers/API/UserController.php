<?php

namespace App\Http\Controllers\API;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Models\Image;
use Illuminate\Support\Facades\Validator;

class UserController extends Controller
{
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
