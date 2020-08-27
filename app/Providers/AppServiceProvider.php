<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Support\Facades\Schema;

use App\Module;
use Auth;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Bootstrap any application services.
     *
     * @return void
     */
    public function boot()
    {
        //
        Schema::defaultStringLength(191);
        view()->composer('*', function ($view) 
        {
            $menu = "";
            if(Auth::user())
            {
                $menu = $this->parentMenu();
            }

            //...with this variable
            $view->with('menu', $menu );    
        });  
    }

    /**
     * Register any application services.
     *
     * @return void
     */
    public function register()
    {
        //
    }
    
    private function parentMenu($moduleParent=0)
    {
        $moduls = Module::whereHas('users',function($q)
        {
            $q->where('users.id',Auth::user()->id);
        })
        ->where('module.parent',$moduleParent)
        ->orderBy('order','ASC')
        ->get();
        
        $ret = '';
        
        foreach($moduls as $modul)
        {
            $child  = $this->parentMenu($modul->id);
            
            $actFlag = "";
            
//            if($modul->selected == $selected)
//            {
//                $actFlag = " active";
//            }
            
            if($child!="")
            {
                $ret .= '<li class="nav-item has-treeview">';
                $ret .= '<a href="#" class="nav-link'.$actFlag.'"><i class="nav-icon '.$modul->icon.'"></i> <p>'.$modul->nama.'<i class="right fas fa-angle-left"></i></p></a>';
                $ret .= '<ul class="nav nav-treeview">';
                $ret .= $child;
                $ret .= '</ul></li>';
            }
            else
            {
                $route = "";
                if(!empty($modul->param))
                {
                    $route = route($modul->route,explode('.',$modul->param));
//                    $route ="";
                }
                else
                {
                    if($modul->route != '#')
                    {
                        $route = route($modul->route);
                    }
                    else
                    {
                        $route = "";                    
                    }
                }
                $ret .= '<li class="nav-item"><a href="'.$route.'" class="nav-link active'.$actFlag.'"><i class="'.$modul->icon.' nav-icon"></i><p>'.$modul->nama.'</p></a></li>';
            }
            
        }
        return $ret;
    }
}
