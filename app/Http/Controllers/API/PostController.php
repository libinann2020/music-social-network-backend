<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Post;
use Illuminate\Http\Request;
use App\Services\ImageService;
use App\Http\Requests\Post\StorePostRequest;
use App\Http\Requests\Post\UpdatePostRequest;

class PostController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        try {
            $postsPerPage = 10;
            $post = Post::with('user')->orderBy('updated_at', 'DESC')->simplePaginate($postsPerPage);
            $pageCount = count(Post::all()) / $postsPerPage;

            return response()->json([
                'paginate'=> $post,
                'page_count' => ceil($pageCount)
            ], 200);
        } catch  (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong in PostController.index',
                'error'=>$e->getMessage()
            ], 400);
        }
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  App\Http\Requests\StorePostRequest  $request
     * @return \Illuminate\Http\Response
     */
    public function store(StorePostRequest $request)
    {
        try {
            if($request->hasFile('image') === false){
                return response()->json(['error' => 'There is no image to upload.'], 400);
            }
            $post = new Post;
            $post->title = $request->get('title'); // Set the title explicitly
            $post->description = $request->get('description');
            $post->location = $request->get('location');
            (new ImageService)->updateImage($post, $request, '/images/posts/', 'store');

            $post->save();

            return response()->json('New Post created!', 200);
        } catch  (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong in PostController.store',
                'error'=>$e->getMessage()
            ], 400);
        }
    }

    /**
     * Display the specified resource.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function show(int $id)
    {
        try {
            $post = Post::with('user')->findOrFail($id);

            return response()->json($post, 200);
        } catch  (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong in PostController.show',
                'error'=>$e->getMessage()
            ], 400);
        }
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  App\Http\Requests\UpdatePostRequest  $request
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function update(UpdatePostRequest $request, int $id)
    {
        try {
            $post = Post::findOrFail($id);
            if($request->hasFile('image')){
                (new ImageService)->updateImage($post, $request, '/images/posts/', 'update');
            }
            $post->title = $request->get('title');
            $post->location = $request->get('location');
            $post->description = $request->get('description');
            $post->save();

            return response()->json('Post with id ' . $id . ' was updated!', 200);
        } catch  (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong in PostController.store',
                'error'=>$e->getMessage()
            ], 400);
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  int $id
     * @return \Illuminate\Http\Response
     */
    public function destroy(int $id)
    {
        try {
            $post = Post::findOrFail($id);
            if(!empty($post->image)){
                $currentImage = public_path() . "/images/posts/" . $post->image;
                if(file_exists($currentImage)){
                    unlink($currentImage);
                }
            }
            
            $post->delete();

            return response()->json('Post deleted!', 200);
        } catch  (\Exception $e) {
            return response()->json([
                'message' => 'Something went wrong in PostController.destroy',
                'error'=>$e->getMessage()
            ], 400);
        }
    }
}
