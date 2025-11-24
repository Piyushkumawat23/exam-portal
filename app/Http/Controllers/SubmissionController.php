<?php
namespace App\Http\Controllers;

use App\Models\Submission;
use Illuminate\Http\Request;

class SubmissionController extends Controller
{
    public function store(Request $request)
    {
        $sub = Submission::create([
            'user_id'=>auth()->id(),
            'form_id'=>$request->form_id,
            'data'=>$request->data,
            'status'=>'submitted'
        ]);

        return response()->json($sub);
    }

    public function show($id)
    {
        return Submission::with('form')->findOrFail($id);
    }


public function userSubmissions()
{
    return Submission::with(['form', 'payment']) // 'payment' relation add kiya
                ->where('user_id', auth()->id())
                ->get();
}
}

