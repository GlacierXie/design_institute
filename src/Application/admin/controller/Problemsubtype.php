<?php
namespace app\admin\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\home\model\Problemsubtypes;
use think\Request;

class Problemsubtype extends Controller
{
    //查看小问题列表
    public function subtype_list(){
        if(request()->isGet()) {
            $type_id = input('type_id');
            if (!isset($type_id) || empty($type_id)) {
                return json('111');
            }
            $userTbale = new Problemsubtypes();
            $res = $userTbale->subtype_Liset($type_id);
            return json($res);
        }
    }
}