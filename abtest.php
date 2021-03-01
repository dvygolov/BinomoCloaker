<?php
require_once __DIR__.'/bases/ipcountry.php';

function select_landing($save_user_flow,$landings,$isfolder=false){
    return select_item($landings,$save_user_flow,'landing',$isfolder);
}

function select_prelanding($save_user_flow,$prelandings,$isfolder=false){
    return select_item($prelandings,$save_user_flow,'prelanding',$isfolder);
}

function select_item($items,$save_user_flow=false,$itemtype='landing',$isfolder=true){
    $item='';
    if ($save_user_flow && isset($_COOKIE[$itemtype])) {
        $item = $_COOKIE[$itemtype];
        $t=array_search($item, $items);
        if ($t===false) $item='';
        if ($isfolder && !is_dir($item)) $item='';
    }
    if ($item===''){
        //A-B тестирование
        $t = rand(0, count($items) - 1);
        $item = $items[$t];
        //если у нас локальная прокла или ленд, то чекаем, есть ли папка под текущее ГЕО
        //если есть, то берём её
        if ($isfolder)
        {
            $country=getcountry();
            if (is_dir($item.$country))
                $item.=$country;
        }
        ywbsetcookie($itemtype,$item,'/');
    }
    return array($item,$t);
}

function select_item_by_index($items,$index){
    if ($index<count($items) && $index>=0)
        return $items[$index];
    else{
        $r = rand(0, count($items) - 1);
        return $items[$r];
    }
}
?>
