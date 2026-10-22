<?php

namespace Modules\Hrms\Http\Controllers;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class HrmsController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        return view('hrms::index');
    }

    /**
     * Show the form for creating a new resource.
     */
    public function create()
    {
        return view('hrms::create');
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request) {}

    /**
     * Show the specified resource.
     */
    public function show($id)
    {
        if (strcasecmp($id, 'EmpCreation') === 0) {
            return redirect()->route('hrms.empcreation');
        }
        if (strcasecmp($id, 'EmpSearch') === 0) {
            return redirect()->route('hrms.empsearch');
        }
        if (strcasecmp($id, 'viewStdProfileDetailsEmp') === 0) {
            return redirect()->route('admissions.stdsearch');
        }

        return redirect()->route('hrms.empsearch');
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit($id)
    {
        return view('hrms::edit');
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, $id) {}

    /**
     * Remove the specified resource from storage.
     */
    public function destroy($id) {}
}
