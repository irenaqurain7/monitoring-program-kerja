<?php

namespace App\Http\Controllers;

use App\Models\Campus;
use App\Models\Unit;
use Illuminate\View\View;

class OrganizationController extends Controller
{
    public function campus(Campus $campus): View
    {
        return view('organization.campus', compact('campus'));
    }

    public function unit(Unit $unit): View
    {
        return view('organization.unit', compact('unit'));
    }

    public function manageUnit(Unit $unit): View
    {
        return view('organization.manage-unit', compact('unit'));
    }
}
