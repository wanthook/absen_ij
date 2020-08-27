<?php

/* 
 * To change this license header, choose License Headers in Project Properties.
 * To change this template file, choose Tools | Templates
 * and open the template in the editor.
 */

function set_active($uri, $output = 'active')
{
    if( is_array($uri) ) 
    {
        foreach ($uri as $u) 
        {
            if (Route::is($u)) 
            {
              return $output;
            }
        }
    } 
    else 
    {
        if (Route::is($uri))
        {
            return $output;
        }
    }
}
