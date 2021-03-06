<?php

namespace App\Http\Controllers;

use App\Category;
use Illuminate\Http\Request;

class CategoryController extends Controller
{
    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
        $categories = Category::paginate(3);
        return view('admin.categories.index' , compact('categories'));
    }
    /**
     * Display a listing of the trashed resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function trash()
    {
        $categories = Category::onlyTrashed()->paginate(6);
        return view('admin.categories.index' , compact('categories'));
    }

    /**
     * Show the form for creating a new resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function create()
    {
        $categories = Category::all();
        return view('admin.categories.create' , compact('categories'));
    }

    /**
     * Store a newly created resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @return \Illuminate\Http\Response
     */
    public function store(Request $request)
    {
        $request->validate([
            'title'=> 'required|min:5',
            'slug'=>'required|min:5|unique:categories',
        ]);

        $categories = Category::create($request->only('title' , 'description','slug'));
        $categories->childrens()->attach($request->parent_id);
        return back()->with('message' , 'Category Added Successfully!');
    }

    /**
     * Display the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function show(Category $category)
    {
        //
    }

    /**
     * Show the form for editing the specified resource.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function edit(Category $category)
    {
        $categories = Category::where('id' , '!=' , $category->id)->get();
        return view('admin.categories.create' , ['categories' => $categories , 'category' => $category]);
       
    }

    /**
     * Update the specified resource in storage.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function update(Request $request, Category $category)
    {

        $request->validate([
            'title'=> 'required|min:5',
            'slug'=>'required|min:5|unique:categories',
        ]);

        $category->title = $request->title;
        $category->description = $request->description;
        $category->slug = $request->slug;
        // detach all parent categories
        $category->childrens()->detach();
        // attach selected parent catgegories
        $category->childrens()->attach($request->parent_id);
        // save crueent record into database
        $saved = $category->save();
        return back()->with('message' , 'Record Successfully Updated');
    }

    /**
     * Recover the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function recover($id)
    {
        $category = Category::withTrashed()->findOrFail($id);
        if($category->restore()){   
            return back()->with('message' , 'Category Sussessfully Recovered');
        } else {
        return back()->with('message' , 'Error Recovering Record');
        }
    }
    /**
     * Delete the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function destroy(Category $category)
    {
        if($category->childrens()->detach() && $category->forceDelete()){   
            return back()->with('message' , 'Record Sussessfully Deleted');
        } else {
        return back()->with('message' , 'Error Deleting Record');
        }
    }

    /**
     * Remove the specified resource from storage.
     *
     * @param  \App\Category  $category
     * @return \Illuminate\Http\Response
     */
    public function remove(Category $category)
    {
        if($category->delete()){
            return back()->with('message' , 'Record Sussessfully Trashed');
        } else {
        return back()->with('message' , 'Error Deleting Record');
        }
    }
}
