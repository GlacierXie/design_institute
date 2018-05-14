<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\home\model\Problemtypes;
use think\Request;

class Problemtype extends Controller
{
    //大问题列表
     public function type_list(){
        if(request()->isGet()) {
            $ProblemtypesTbale = new Problemtypes();
            $res = $ProblemtypesTbale->type_Liset();
            return json($res);
        }
    }
}