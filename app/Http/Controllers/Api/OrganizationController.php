<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Department;
use App\Models\JobTitle;

class OrganizationController extends Controller
{
   public function departments(){
    $organizations = Department::all();
    return response(['message' => 'Department List',
'data' => $organizations]);
    }


    public function jobTitles() {

        $jobTitles = JobTitle::all();
        return response(['message' => 'Job Title List',
    'data' => $jobTitles]);


    }
}
