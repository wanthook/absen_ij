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
                $menu = $this->buildMenu();
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
    
    private function buildMenu()
    {
        $mod = Module::whereHas('users',function($q)
        {
            $q->where('users.id',Auth::user()->id);
        })
        ->orderBy('id','ASC')
        ->get()->toTree();
        
        $trans = function($params) use (&$trans)
        {
            $ret = '';
            foreach($params as $par)
            {
                $re = '';
                if(empty($par->parent))
                {
                    $ret .= '<li class="nav-item has-treeview">';
                    $ret .= '<a href="#" class="nav-link"><i class="nav-icon '.$par->icon.'"></i> <p>'.$par->nama.'<i class="right fas fa-angle-left"></i></p></a>';
                    $ret .= '<ul class="nav nav-treeview">';
                    $ret .= $trans($par->children);
                    $ret .= '</ul></li>';
                }
                else 
                {
                    $route = "";
                    if(!empty($par->param))
                    {
                        $route = route($par->route,explode('.',$par->param));
                    }
                    else
                    {
                        if($par->route != '#')
                        {
                            $route = route($par->route);
                        }
                        else
                        {
                            $route = "";                    
                        }
                    }
                    $ret .= '<li class="nav-item"><a href="'.$route.'" class="nav-link active"><i class="'.$par->icon.' nav-icon"></i><p>'.$par->nama.'</p></a></li>';
                }
            }
            return $ret;
            
        };
        return $trans($mod);
    }
}
