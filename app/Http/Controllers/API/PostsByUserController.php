<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;

class PostsByUserController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(int $user_id)
    {
        try {
            $posts = Post::where('user_id',$user_id)->get();

            return response()->json($posts, 200);
        } catch  (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong in PostsByUserController.index',
                'error'=>$e->getMessage()
            ], 400);
        }
    }

}
