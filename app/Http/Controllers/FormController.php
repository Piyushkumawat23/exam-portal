<?php
namespace App\Http\Controllers;

use App\Models\Form;
use Illuminate\Http\Request;

class FormController extends Controller
{
    public function index()
    {
        return Form::all();
    }

    public function show($id)
    {
        return Form::findOrFail($id);
    }

    public function store(Request $request)
    {
        $form = Form::create($request->all());
        return response()->json($form);
    }

    public function update(Request $request, $id)
    {
        $form = Form::findOrFail($id);
        $form->update($request->all());
        return response()->json($form);
    }

    public function destroy($id)
    {
        Form::findOrFail($id)->delete();
        return response()->json(['message'=>'Form deleted']);
    }


    public function getApplicants($id)
{
    // Form ke sath Submissions aur unke Users ka data layenge
    // 'submissions.user' ka matlab hai submission ke sath student ka naam/email bhi aayega
    $form = Form::with('submissions.user' ,'submissions.payment')->findOrFail($id);
    return response()->json($form->submissions);
}
}

