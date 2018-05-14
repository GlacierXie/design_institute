<?php
namespace app\admin\controller;
use app\home\model\Defaulttasks;
use think\Controller;
use think\Db;
use think\Cache;
class Defaulttask extends Controller
{
    public function defaulttask_list(){
        if(request()->isGet()) {
            $task_group_id = input('task_group_id');
            $sql = "SELECT * from ipm_inst_default_task ";
            if (isset($task_group_id)) {
                $sql = $sql . "  where taskgroup_id = '$task_group_id'";
            }
            $list = Db::query($sql);
            return json ($list);

        }
    }
}