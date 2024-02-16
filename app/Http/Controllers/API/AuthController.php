<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\ForgotPasswordRequest;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Http\Requests\Auth\RegisterUser;
use App\Http\Requests\Auth\LoginUser;
use App\Models\User;

class AuthController extends Controller
{

    /**
     * Create User
     * @param Request $request
     * @return User
     */
    public function registerUser(RegisterUser $request)
    {
        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
        ]);

        return response()->json([
            'status' => true,
            'message' => 'User created successfully',
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ], 201);
    }

    /**
     * Login The User
     * @return User
     */
    public function loginUser(LoginUser $request)
    {

        $credentials = $request->only(['email', 'password']);
        $user = User::where('email', $credentials['email'])->first();

        if (!$user || !Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'errors' => [
                    'notExist' => ['Podany użytkownik nie istnieje'],
                ]
                // 'status' => false,
            ], 401);
        }

        return response()->json([
            'status' => true,
            'user_image' => $user->image,
            'message' => 'Logged in Successfully',
            'token' => $user->createToken("API TOKEN")->plainTextToken
        ], 200);
    }

    public function forgotPassword(ForgotPasswordRequest $request)
    {

        $email = $request->input('email');
        $newPassword = $request->input('newPassword');
        // $confirmPassword = $request->input('confirmPassword');
        $user = User::where('email', $email)->first();

        if ($user) {
            $user->password = Hash::make($newPassword);
            $user->save();
            return response()->json(['message' => 'Twoje hasło zostało zmienione'], 200);

        }
        return response()->json(['error' => "Użytkownik o tym adresie email nie istnieje"], 422);
    }

    public function updatePassword(Request $request)
    {
        // TODO:add new validate method
        // $request->validate([
        //     'oldPassword' => 'required',
        //     'newPassword' => [
        //         'required',
        //         'min:8',
        //         'regex:/^(?=.*[A-Z])(?=.*\d)(?=.*[@$!%*?&])[A-Za-z\d@$!%*?&]+$/',
        //     ],
        // ]);

        $user = User::find(auth()->user()->id);
        # Pobranie nowego i starego hasła z żądania
        $oldPassword = $request->input('oldPassword');
        $newPassword = $request->input('newPassword');
        $confirmPassword = $request->input('confirmPassword');


        # Match The Old Password
        if (!Hash::check($oldPassword, $user->password)) {
            return response()->json(['error' => "Błędne stare hasło"], 422);
        }
        if ($newPassword !== $confirmPassword) {
            return response()->json(['error' => "Hasła nie zgadzają się"], 422);
        }
        # Update the new Password
        $user->password = Hash::make($newPassword);
        $user->save();

        return response()->json(['message' => 'Hasło zostało pomyślnie zmienione!'], 200);
    }
}


// public function uploadUserPhoto(UploadUserAvatarRequest $request)
//     {
//         /** @var User $user */
//         $user = auth()->user();
//         // check if image has been received from form
//         $validated = $request->validated();

//         // check if user has an existing avatar
//         if($user->avatar_path != NULL){
//             // delete existing image file
//             Storage::disk('user_avatars')->delete($user->avatar_path);
//         }
//         // processing the uploaded image
//         $avatar_path = $validated['avatar']->store('','user_avatars');

//         // Update user's avatar column on 'users' table
//         $user->avatar_path = $avatar_path;

//         if($user->save()){
//             return response()->json([
//                 'status'    =>  'success',
//                 'message'   =>  'User avatar updated!',
//                 'avatar_url'=>  url('storage/user-avatar/'.$avatar_path)
//             ]);
//         }else{
//             return response()->json([
//                 'status'    => 'failure',
//                 'message'   => 'Failed to update user avatar!',
//                 'avatar_url'=> NULL
//             ]);
//         }
//     }
