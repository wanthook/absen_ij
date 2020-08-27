<?php

namespace App\Http\Controllers;

//use Illuminate\Http\Request;

class DashboardController extends Controller
{    
    
    /**
     * Show the application dashboard.
     *
     * @return \Illuminate\Http\Response
     */
    public function index()
    {
//        $menu = $this->parentMenu('dashboard');
        return view('dashboard');
    }
}
