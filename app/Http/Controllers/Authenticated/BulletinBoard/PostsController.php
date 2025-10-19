<?php

namespace App\Http\Controllers\Authenticated\BulletinBoard;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Categories\MainCategory;
use App\Models\Categories\SubCategory;
use App\Models\Posts\Post;
use App\Models\Posts\PostComment;
use App\Models\Posts\Like;
use App\Models\Users\User;
use App\Http\Requests\BulletinBoard\PostFormRequest;
use Auth;
use Illuminate\Support\Facades\DB;

class PostsController extends Controller
{
    public function show(Request $request)
    {
        $posts = Post::with('user', 'postComments')->get();
        //$categories = MainCategory::get();
        $categories = MainCategory::with('subCategories')->get();
        $like = new Like;
        $post_comment = new Post;
        if (!empty($request->keyword)) {
            $matchedSubCategory = subCategory::where('sub_category',$request->keyword)->first();
            if ($matchedSubCategory) {
                # code...
                $postIds = DB::table('post_sub_categories')->where('sub_category_id',$matchedSubCategory->id)->pluck('post_id');
                $posts = Post::with('user','postComments')->whereIn('id',$postIds)->get();
            } else {
                # code...
                $posts = Post::with('user', 'postComments')
                ->where('post_title', 'like', '%' . $request->keyword . '%')
                ->orWhere('post', 'like', '%' . $request->keyword . '%')->get();
            }
        } else if ($request->category_word) {
            //$sub_category = $request->category_word;
            $sub_category = SubCategory::where('sub_category',$request->category_word)->first();
            $postIds = DB::table('post_sub_categories')->where('sub_category_id',$sub_category->id)->pluck('post_id');
            $posts = Post::with('user', 'postComments')->whereIn('id',$postIds)->get();
        } else if ($request->like_posts) {
            $likes = Auth::user()->likePostId()->get('like_post_id');
            $posts = Post::with('user', 'postComments')
                ->whereIn('id', $likes)->get();
        } else if ($request->my_posts) {
            $posts = Post::with('user', 'postComments')
                ->where('user_id', Auth::id())->get();
        }
        return view('authenticated.bulletinboard.posts', compact('posts', 'categories', 'like', 'post_comment'));
    }

    public function postDetail($post_id)
    {
        $post = Post::with('user', 'postComments')->findOrFail($post_id);
        return view('authenticated.bulletinboard.post_detail', compact('post'));
    }

    public function postInput()
    {
        $main_categories = MainCategory::get();
        return view('authenticated.bulletinboard.post_create', compact('main_categories'));
    }

    public function postCreate(PostFormRequest $request)
    {
        $post = Post::create([
            'user_id' => Auth::id(),
            'post_title' => $request->post_title,
            'post' => $request->post_body
        ]);

        DB::table('post_sub_categories')->insert([
            'post_id'=>$post->id,
            'sub_category_id'=>$request->post_category_id,
            'created_at'=>now()
        ]);
        return redirect()->route('post.show');
    }

    public function postEdit(Request $request)
    {
        $request->validate([
            'post_title' => ['required', 'string', 'max:100'],
            'post_body' => ['required', 'string', 'max:2000']
        ], [
            'post_title.required' => 'タイトルは必ず入力してください',
            'post_title.string' => 'タイトルは文字列である必要があります',
            'post_title.max' => 'タイトルは100文字以内で記入してください',
            'post_body.required' => '投稿内容は必ず入力してください',
            'post_body.string' => '投稿内容は文字列である必要があります',
            'post_body.max' => '投稿内容は2000文字以内で記入してください。',
        ]);
        Post::where('id', $request->post_id)->update([
            'post_title' => $request->post_title,
            'post' => $request->post_body,
        ]);
        return redirect()->route('post.detail', ['id' => $request->post_id]);
    }

    public function postDelete($id)
    {
        Post::findOrFail($id)->delete();
        return redirect()->route('post.show');
    }
    public function mainCategoryCreate(Request $request)
    {
        $request->validate([
            'main_category_name'=>['required','max:100','string','unique:main_categories,main_category'],
        ],[
            'main_category_name.required'=>'メインカテゴリーは必ず入力してください',
            'main_category_name.max'=>'メインカテゴリーは100文字以内で入力してください',
            'main_category_name.string'=>'メインカテゴリーは文字列で入力してください',
            'main_category_name.unique'=>'既に登録されているメインカテゴリーです'
        ]);
        MainCategory::create(['main_category' => $request->main_category_name]);
        return redirect()->route('post.input');
    }

    public function subCategoryCreate(Request $request)
    {
        $request->validate([
            'main_category_id'=>['required','exists:main_categories,id'],
            'sub_category_name'=>['required','max:100','string','unique:sub_categories,sub_category']
        ],[
            'main_category_id.required'=>'メインカテゴリーは必ず選択してください',
            'main_category_id.exists'=>'登録されていないメインカテゴリーが選択されています',
            'sub_category_name.required'=>'サブカテゴリーは必ず入力してください',
            'sub_category_name.max'=>'サブカテゴリーは100文字以内で入力してください',
            'sub_category_name.string'=>'サブカテゴリーは文字列で入力してください',
            'sub_category_name.unique'=>'既に登録されているサブカテゴリーです'
        ]);

        SubCategory::create(['sub_category' => $request->sub_category_name,
        'main_category_id'=>$request->main_category_id]);
        return redirect()->route('post.input');
    }

    public function commentCreate(Request $request)
    {
        $request->validate([
            'comment'=>['required','max:250', 'string']
        ],[
            'comment.required' => 'コメントは必ず入力してください',
            'comment.max' => 'コメントは250文字以内で記入してください。',
            'comment.string' => 'コメントは文字列である必要があります',
        ]);
        PostComment::create([
            'post_id' => $request->post_id,
            'user_id' => Auth::id(),
            'comment' => $request->comment
        ]);
        return redirect()->route('post.detail', ['id' => $request->post_id]);
    }

    public function myBulletinBoard()
    {
        $posts = Auth::user()->posts()->get();
        $like = new Like;
        return view('authenticated.bulletinboard.post_myself', compact('posts', 'like'));
    }

    public function likeBulletinBoard()
    {
        $like_post_id = Like::with('users')->where('like_user_id', Auth::id())->get('like_post_id')->toArray();
        $posts = Post::with('user')->whereIn('id', $like_post_id)->get();
        $like = new Like;
        return view('authenticated.bulletinboard.post_like', compact('posts', 'like'));
    }

    public function postLike(Request $request)
    {
        $user_id = Auth::id();
        $post_id = $request->post_id;

        $like = new Like;

        $like->like_user_id = $user_id;
        $like->like_post_id = $post_id;
        $like->save();

        return response()->json();
    }

    public function postUnLike(Request $request)
    {
        $user_id = Auth::id();
        $post_id = $request->post_id;

        $like = new Like;

        $like->where('like_user_id', $user_id)
            ->where('like_post_id', $post_id)
            ->delete();

        return response()->json();
    }
}
