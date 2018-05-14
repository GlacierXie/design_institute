<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use think\Request;

class Bigscreen extends Controller
{
    function run()
    {
           include('GlobalHelp.php');
           return new GlobalHelp();
    }

    public  function  index()
    {	
        #$sql = "SELECT b.`name`, MIN(a.`start_time_plan`) AS stime, MAX(a.`end_time_plan`) as etime, ( COUNT( CASE WHEN a.`end_time_real` IS NOT NULL THEN a.`end_time_real` END) / COUNT(*) ) AS rate FROM `ipm_inst_subproject_task` a JOIN `ipm_inst_subproject` b ON a.`subproject_id` = b.`id` WHERE DATE_SUB(CURDATE(), INTERVAL 60 DAY) < date(a.`start_time_plan`) GROUP BY a.`subproject_id` ORDER BY `stime` DESC";
	#$sql = "SELECT b.`name`, MIN(a.`start_time_plan`) AS stime, MAX(a.`end_time_plan`) as etime, ( COUNT( CASE WHEN a.`end_time_real` IS NOT NULL THEN a.`end_time_real` END) / COUNT(*) ) AS rate FROM `ipm_inst_subproject_task` a JOIN `ipm_inst_subproject` b ON a.`subproject_id` = b.`id` GROUP BY a.`subproject_id` ORDER BY `stime` DESC";
        $sql = "SELECT b.`id`, b.`name` , a.`stime`, a.`etime`, a.`rate` FROM
(SELECT `subproject_id`, MIN(`start_time_plan`) AS stime, MAX(`end_time_plan`) as etime, ( COUNT( CASE WHEN `end_time_real` IS NOT NULL THEN `end_time_real` END) / COUNT(*) ) AS rate FROM `ipm_inst_subproject_task` WHERE `project_id` <> 38 GROUP BY `subproject_id` ORDER BY `stime` DESC) a JOIN `ipm_inst_subproject` b ON a.`subproject_id` = b.`id` WHERE DATE_SUB(CURDATE(), INTERVAL 100 DAY) < date(a.`stime`)";
	$result = Db::query($sql);
        return json($result); 
            
    }
}
