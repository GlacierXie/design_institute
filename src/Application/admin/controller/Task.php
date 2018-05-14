<?php
namespace app\admin\controller;
use app\home\model\Subprojects;
use app\home\Util\Wechat;
use think\Controller;
use app\home\model\Tasks;
use app\home\model\Taskgroups;
use app\home\model\Roles;
use app\admin\controller\Message;
use think\Db;
use think\Cache;
use app\home\model\Taskparters;
use think\Request;

class Task extends Controller
{
    //指派任务
    public function PushMessage($param){
        if(empty($param['changer_id']) && isset($param['parter'])){
            $subproject_name=$param['subproject_name'];
            $parter=$param['parter'];
            foreach($parter as $k=>$v){
                $parterid=$v;
                $creator_id=$param['creator_id'];
                $taskid=$param['taskid'];
                $task_name= Db::table('ipm_inst_subproject_task')->where('id',$taskid)->value('name');
                $parter_name= Db::table('ipm_user')->where('openid',$parterid)->value('nickname');
                $creator_id_name= Db::table('ipm_user')->where('openid',$creator_id)->value('nickname');
                $wechat = new Wechat();
                $access_token = $wechat->getAccessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
                $touser = $parterid;
                $template_id ='GWkuJaSjDORz28aPRnem6QK65_fC9vGAIUCczzsqjsU';
                $touser = $parterid;
                $template_id ='GWkuJaSjDORz28aPRnem6QK65_fC9vGAIUCczzsqjsU';
                $first['value'] = '您好，'.$creator_id_name.'给您分配了一个任务';
                $first['color'] = '#173177';
                $keyword1['value'] =$subproject_name;
                $keyword1['color'] = '#173177';
                $keyword2['value'] = $task_name;
                $keyword2['color'] = '#173177';
                $keyword3['value'] = $parter_name;
                $keyword3['color'] = '#173177';
                $keyword4['value'] = $param['end_time_plan'];
                $keyword4['color'] = '#173177';
                $remark['value'] = '请尽快落实任务！';
                $remark['color'] = '#173177';
                $data['first'] = $first;
                $data['keyword1'] = $keyword1;
                $data['keyword2'] = $keyword2;
                $data['keyword3'] = $keyword3;
                $data['keyword4'] = $keyword4;
                $data['remark'] = $remark;
                $msg['touser'] = $touser;
                $msg['template_id'] = $template_id;
                $msg['data'] = $data;
                $wechat->post($url, json_encode($msg));
            }
        }
        if(!empty($param['parter']) && !empty($param['changer_id'])){
            $subproject_name=$param['subproject_name'];
            $parter=$param['parter'];
            $changer_id=$param['changer_id'];
            array_push($parter, "$changer_id");
            foreach($parter as $k=>$v){
                $parterid=$v;
                $creator_id=$param['creator_id'];
                $taskid=$param['taskid'];
                $task_name= Db::table('ipm_inst_subproject_task')->where('id',$taskid)->value('name');
                $parter_name= Db::table('ipm_user')->where('openid',$parterid)->value('nickname');
                $creator_id_name= Db::table('ipm_user')->where('openid',$creator_id)->value('nickname');
                $wechat = new Wechat();
                $access_token = $wechat->getAccessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
                $touser = $parterid;
                $template_id ='GWkuJaSjDORz28aPRnem6QK65_fC9vGAIUCczzsqjsU';
                $first['value'] = '您好，'.$creator_id_name.'给您分配了一个任务';
                $first['color'] = '#173177';
                $keyword1['value'] =$subproject_name;
                $keyword1['color'] = '#173177';
                $keyword2['value'] = $task_name;
                $keyword2['color'] = '#173177';
                $keyword3['value'] = $parter_name;
                $keyword3['color'] = '#173177';
                $keyword4['value'] = $param['end_time_plan'];
                $keyword4['color'] = '#173177';
                $remark['value'] = '请尽快落实任务！';
                $remark['color'] = '#173177';
                $data['first'] = $first;
                $data['keyword1'] = $keyword1;
                $data['keyword2'] = $keyword2;
                $data['keyword3'] = $keyword3;
                $data['keyword4'] = $keyword4;
                $data['remark'] = $remark;
                $msg['touser'] = $touser;
                $msg['template_id'] = $template_id;
                $msg['data'] = $data;
                $wechat->post($url, json_encode($msg));
           }
        }


    }
    public function PushMessage1($param){
               // $param['changer_id'];
                $changer_id=$param['changer_id'];
                $creator_id=$param['creator_id'];
                $subproject_name=$param['subproject_name'];
                $taskid=$param['taskid'];
                $task_name= Db::table('ipm_inst_subproject_task')->where('id',$taskid)->value('name');
                $changer_name= Db::table('ipm_user')->where('openid',$changer_id)->value('nickname');
                $creator_id_name= Db::table('ipm_user')->where('openid',$creator_id)->value('nickname');
                $wechat = new Wechat();
                $access_token = $wechat->getAccessToken();
                $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
                $touser = $changer_id;
                $template_id ='GWkuJaSjDORz28aPRnem6QK65_fC9vGAIUCczzsqjsU';
                $first['value'] = '您好，'.$creator_id_name.'给您分配了一个任务';
                $first['color'] = '#173177';
                $keyword1['value'] =$subproject_name;
                $keyword1['color'] = '#173177';
                $keyword2['value'] = $task_name;
                $keyword2['color'] = '#173177';
                $keyword3['value'] = $changer_name;
                $keyword3['color'] = '#173177';
                $keyword4['value'] = $param['end_time_plan'];
                $keyword4['color'] = '#173177';
                $remark['value'] = '请尽快落实任务！';
                $remark['color'] = '#173177';
                $data['first'] = $first;
                $data['keyword1'] = $keyword1;
                $data['keyword2'] = $keyword2;
                $data['keyword3'] = $keyword3;
                $data['keyword4'] = $keyword4;
                $data['remark'] = $remark;
                $msg['touser'] = $touser;
                $msg['template_id'] = $template_id;
                $msg['data'] = $data;
                $wechat->post($url, json_encode($msg));

    }
    //底图审核通过，通知所有项目参与人员
    public function add_task(){
        $arr= $this->request->param();
        if (!isset($arr['creator_id']) || empty($arr['creator_id'])) {
            return json('creator_id empty');
        }
        if (!isset($arr['taskgroup_id']) || empty($arr['taskgroup_id']) ) {
            return json('taskgroup_id empty');
        }
        if (!isset($arr['end_time_plan']) || empty($arr['end_time_plan']) ) {
            return json('end_time_plan empty');
        }
        if (!isset($arr['start_time_plan']) || empty($arr['start_time_plan']) ) {
            return json('start_time_plan empty');
        }
        if (!isset($arr['subtask_name']) || empty($arr['subtask_name'])) {
            return json('subtask_name empty');
        }
        $role_id=$arr['role_id'];
        $subprj_id= Db::table('ipm_inst_subproject_taskgroup')->where('id',$arr['taskgroup_id'])->value('subproject_id');
        $project_id1= Db::table('ipm_inst_subproject')->where('id',$subprj_id)->value('project_id');
        $subproject_name= Db::table('ipm_inst_subproject')->where('id',$subprj_id)->value('name');
        $data['creator_id']=$arr['creator_id'];
        $data['taskgroup_id'] = $arr['taskgroup_id'];
        $data['subproject_id'] =$subprj_id;
        $data['project_id'] =$project_id1;
        $data['changer_id'] = $arr['changer_id'];
        $data['urgent'] = $arr['urgent'];
        $data['state'] =1;
        $data['remarks'] = $arr['remarks'];
        $data['end_time_plan'] = $arr['end_time_plan'];
        $data['start_time_plan'] = $arr['start_time_plan'];
        $data['name'] = $arr['subtask_name'];
        $taskTable= new Tasks();
        $taskpartersTable= new Taskparters();
        if(isset($arr['subtask_id']) && !empty($arr['subtask_id'])){
            $tateTable = DB::table('ipm_inst_subproject_task')->where('id',$arr['subtask_id'])->value('state');
            if($tateTable==1){
                    $subtask_id=$arr['subtask_id'];
                    $data['update_time']=date("Y-m-d H:i:s");
                    //修改
                    $tasklist=$taskTable->update_task($subtask_id,$data);
                    if($tasklist){
                        $data3=$taskpartersTable->del_taskparter($subtask_id);
                        if(isset($arr['parter'])){
                            $parter = $arr['parter'];
                            foreach($parter as $k=>$v) {
                                $data1['openid']=$v;
                                $data1['task_id']=$subtask_id;
                                $data1['update_time']= date("Y-m-d H:i:s");
                                $data1['create_time']= date("Y-m-d H:i:s");
                                $res1=$taskpartersTable->add_taskparter($data1);
                            }
                        }
                        $res['success'] = true;
                        $res['message'] = "update success";
                        echo  json_encode($res);
                        if(empty($arr['parter']) && !isset($arr['parter'])){
                            $param = array(
                                'taskid'=>$subtask_id,
                                'creator_id'=>$arr['creator_id'],
                                'changer_id'=>$arr['changer_id'],
                                'subproject_name'=>$subproject_name,
                                'end_time_plan'=>$arr['end_time_plan']
                            );

                            $this->PushMessage1($param);
                        }else{
                            $param = array(
                                'taskid'=>$subtask_id,
                                'creator_id'=>$arr['creator_id'],
                                'changer_id'=>$arr['changer_id'],
                                'subproject_name'=>$subproject_name,
                                'end_time_plan'=>$arr['end_time_plan'],
                                'parter'=>$arr['parter']
                            );

                            $this->PushMessage($param);
                        }
                    }else{
                        return json ();
                    }
            }else{
                $res['message'] = "项目已完成，不能修改";
                return json ($res);
            }
        }else {
            $data['create_time'] = date("Y-m-d H:i:s");
            $data['update_time'] = date("Y-m-d H:i:s");
            $task_id = $taskTable->add_task($data);
            if ($task_id) {
                $res['success'] = true;
                $res['message'] = "add success";
                echo  json_encode($res);
                if(empty($arr['parter']) && !isset($arr['parter'])){
                    $param = array(
                        'touser'=>$arr['changer_id'],
                        'taskid'=>$task_id,
                        'changer_id'=>$arr['changer_id'],
                        'creator_id'=>$arr['creator_id'],
                        'end_time_plan'=>$arr['end_time_plan']
                    );
                    $this->PushMessage1($param);
                }else{
                    $param = array(
                        'touser'=>$arr['changer_id'],
                        'taskid'=>$task_id,
                        'changer_id'=>$arr['changer_id'],
                        'creator_id'=>$arr['creator_id'],
                        'end_time_plan'=>$arr['end_time_plan'],
                        'parter'=>$arr['parter']
                    );
                    $this->PushMessage($param);
                }
            } else {
                return json();
            }
        }
    }


    public function Count_Task1($subprj_id)
    {

        $TaskgroupsTable = new Taskgroups();
        $taskTable = new Tasks();
        $result['sum_1'] = 0;
        $res = $TaskgroupsTable->taskparter_list($subprj_id);
        foreach ($res as $k => $v) {
            $taskgroup_id = $res[$k]['id'];
            $arr1 = $taskTable->CountTasklist1($taskgroup_id);
            $result['sum_1']  = intval( $result['sum_1'] ) + intval($arr1[0]['sum_1']);
        }
        return ($result);

    }
    public function Count_Task($subprj_id)
    {

        $TaskgroupsTable = new Taskgroups();
        $taskTable = new Tasks();
        $result['finish'] = 0;
        $res = $TaskgroupsTable->taskparter_list($subprj_id);
        foreach ($res as $k => $v) {
            $taskgroup_id = $res[$k]['id'];
            $arr = $taskTable->CountTasklist($taskgroup_id);
            $result['finish']  = intval($result['finish'] ) + intval($arr[0]['sum']);

        }
        return ($result);

    }
    public function month_task_list($subprj_id){
        //获取当月的第一天
        $start_time=date('Y-m-01', strtotime(date("Y-m-d")));
        // $start_time='2017-08-01';
        $str='%Y-%m-%d';
        $ye=  explode('-', $start_time)[0];
        $me=  explode('-', $start_time)[1];
        //获取当月的最后一天
        $var = date("t",strtotime($start_time));
        $TaskgroupsTable=new Taskgroups();
        $taskTable= new Tasks();
        $res=$TaskgroupsTable->taskparter_list($subprj_id);
        for($d=0;$d<=$var;$d++){
            $time = $ye.'-'.$me.'-'.$d;
            $result[$d]['time'] = $time;
            $result[$d]['sum'] = 0;
            $result[$d]['sum_1'] = 0;
            foreach($res as $k=>$v){
                $taskgroup_id=$res[$k]['id'];
                $rel= $taskTable->select_TaskList($taskgroup_id,$time,$str);
                $rel_1= $taskTable->select_TaskList_1($taskgroup_id,$time,$str);
                $result[$d]['sum'] = intval($result[$d]['sum']) + intval($rel[0]['sum']);
                $result[$d]['sum_1'] = intval($result[$d]['sum_1']) + intval($rel_1[0]['sum_1']);
            }


        }
        $arr1=$this->Count_Task1($subprj_id);
        $arr=$this->Count_Task($subprj_id);
        $result[0]['sum_1']=$arr1['sum_1'];
        $result[0]['sum']=$arr['finish'];

        return json ($result);
    }
    public function edit_taks_state(){
        $arr= $this->request->param();
        $state=$arr['state'];
        $subprj_id=$arr['subprj_id'];
        $grouptaksTable=new Taskgroups();
        $taskTable=new Tasks();
        $res=$grouptaksTable->taskgroup_id($subprj_id);
        if($res){
            $data['state']=$state;
            $data['update_time'] = date("Y-m-d H:i:s");
            foreach($res as $key=>$val){
                $taskgroup_id=$res[$key]['id'];
                $arr=$taskTable->update_state($taskgroup_id,$data);
                if($arr){
                    $res['success'] = true;
                    $res['message'] = "update success";
                }
            }
        }else{
            return json ();
        }

        return json ($res);
    }
    // 删除任务
    public function del_task(){
        $arr= $this->request->param();
        $task_id=$arr['task_id'];
        $taskTable=new Tasks();
        $taskstate=$taskTable->task_state($task_id);
       if($taskstate==2 or $taskstate==3){
           $res['success'] = false;
           $res['message'] = 'state==2 or state==3';
           return json ($res);
       }else{
           $creator_id= Db::table('ipm_inst_subproject_task')->where('id',$task_id)->value('creator_id');
           $changer_id= Db::table('ipm_inst_subproject_task')->where('id',$task_id)->value('changer_id');
           $res=$taskTable->del_task($task_id);
           if($res){
               $res1['success'] = true;
               $res1['message'] = "delete success";
               echo  json_encode($res1);
               $date=array(
                   'creator_id'=>$creator_id,
                   'changer_id'=>$changer_id,
                   'task_id'=>$task_id,
               );
               $this->PushMessage3($date);
           }else{
               $res1['success'] = false;
               $res1['message'] = 'error';
               return json ($res1);
           }
       }

    }
    public function PushMessage3($date){
        $task_id=$date['task_id'];
        $creator_id[]=$date['creator_id'];
        $creator=$date['creator_id'];
        $changer_id=$date['changer_id'];
        array_push($creator_id, "$changer_id");
        foreach($creator_id as $k=>$v) {
            $parterid = $v;
            $task_name = Db::table('ipm_inst_subproject_task')->where('id', $task_id)->value('name');
            $changer_name = Db::table('ipm_user')->where('openid', $changer_id)->value('nickname');
            $creator_id_name = Db::table('ipm_user')->where('openid', $creator)->value('nickname');
            $wechat = new Wechat();
            $access_token = $wechat->getAccessToken();
            $url = "https://api.weixin.qq.com/cgi-bin/message/template/send?access_token=$access_token";
            $template_id = 'VwytmOOTU4AtzN7LpIISxkv2MQ3RuGI6anFYtq3elE0';
            $first['value'] = '您好，您有收到新的任务';
            $first['color'] = '#173177';
            $keyword1['value'] = $task_id;
            $keyword1['color'] = '#173177';
            $keyword2['value'] = $task_name;
            $keyword2['color'] = '#173177';
            $keyword3['value'] = $changer_name;
            $keyword3['color'] = '#173177';
            $keyword4['value'] = $creator_id_name;
            $keyword4['color'] = '#173177';
            $keyword5['value'] = date("Y-m-d H:i:s");
            $keyword5['color'] = '#173177';
            $remark['value'] = '任务删除成功';
            $remark['color'] = '#173177';
            $data['first'] = $first;
            $data['keyword1'] = $keyword1;
            $data['keyword2'] = $keyword2;
            $data['keyword3'] = $keyword3;
            $data['keyword4'] = $keyword4;
            $data['keyword5'] = $keyword5;
            $data['remark'] = $remark;
            $msg['touser'] = $parterid;
            $msg['template_id'] = $template_id;
            $msg['data'] = $data;
            $wechat->post($url, json_encode($msg));
        }

    }
    public function select_check_charger(){
        $arr= $this->request->param();
        if (!isset($arr['subproject_id']) || empty($arr['subproject_id'])) {
            return json('subproject_id empty');
        }
        $taskTable=new Tasks();
        $res=$taskTable->check_chargerlist($arr['subproject_id']);
        if($res){
            return json ($res);
        }else{

            return json ();
        }
    }
    public function TaskCount(){
        $arr= $this->request->param();
        if (!isset($arr['subproject_id']) || empty($arr['subproject_id'])){
            return json('subproject_id  不能为空');
        }
        $TaskgroupsTable = new Taskgroups();
        $taskTable = new Tasks();
        $roleTable= new Roles();
        $result['TaskCount'] = 0;
        $result['TaskFinish'] = 0;
        $result['TodayCountTasklist'] = 0;
        $result['WeekCountTasklist'] = 0;
        $result['MonthCountTasklist'] = 0;
        $result['addTodayCountTasklist'] = 0;
        $res = $TaskgroupsTable->taskparter_list($arr['subproject_id']);
        $subproject_list=$roleTable->role_user_list($arr['subproject_id']);
        $count=count($subproject_list);
        foreach ($res as $k => $v) {
            $taskgroup_id = $res[$k]['id'];
            $arr1 = $taskTable->CountTasklist1($taskgroup_id);
            $arr = $taskTable->CountTasklist($taskgroup_id);
            $Today = $taskTable->TodayCountTasklist($taskgroup_id);
            $Week = $taskTable->WeekCountTasklist($taskgroup_id);
            $Month = $taskTable->MonthCountTasklist($taskgroup_id);
            $addToday = $taskTable->addTodayCountTasklist($taskgroup_id);
            $result['TaskCount']  = intval( $result['TaskCount'] ) + intval($arr1[0]['sum_1']);
            $result['TaskFinish']  = intval($result['TaskFinish'] ) + intval($arr[0]['sum']);
            $result['TodayCountTasklist']  = intval($result['TodayCountTasklist'] ) + intval($Today[0]['sum']);
            $result['WeekCountTasklist']  = intval($result['WeekCountTasklist'] ) + intval($Week[0]['sum']);
            $result['MonthCountTasklist']  = intval($result['MonthCountTasklist'] ) + intval($Month[0]['sum']);
            $result['addTodayCountTasklist']  = intval($result['addTodayCountTasklist'] ) + intval($addToday[0]['sum']);
            $result['addTodayCountTasklist']  = intval($result['addTodayCountTasklist'] ) + intval($addToday[0]['sum']);
            $result['ParticipantsCount']  = $count;
        }
        return json($result);

    }
	//20180106Ryan 自己点完成任务
	public function updateTaskStatus_self()
	{
		$arr= $this->request->param();
		$update_time = date("Y-m-d H:i:s");
		$creat_time = date("Y-m-d H:i:s");
		$task_id=$arr['task_id'];
		$openid=$arr['openid'];
		$set_sjbqs = $arr['set_sjbqs'];
		$subprj_id = $arr['subprj_id'];
		//先判断前置任务设计部签收的状态
			Db::query("UPDATE ipm_inst_subproject_task SET state = 3 ,end_time_real='$update_time' ,update_time = '$update_time' WHERE id = '$task_id'");
			$dataInsert = [
                            'id' => -1,
                            'openid' => $openid,
                            'task_id' => $task_id,
							'change_type' => 8,
                            'update_time' => $update_time,
                            'create_time' => $creat_time
                          ];
            Db::table('ipm_inst_subproject_task_change')->insertGetId($dataInsert);
			//暂写死，底图签收同步完成设计部签收
             $message=new Message();
			if($set_sjbqs)
			{
                //设计部签收id
				$ts_qianzhi = $task_id-1;
				$state_qz = Db::query("select state from ipm_inst_subproject_task where id='$ts_qianzhi'");
				if($state_qz[0]['state']==2){	
				//同步完成设计部签收
				$task_id=$task_id-1;
				Db::query("UPDATE ipm_inst_subproject_task SET state = 3 ,end_time_real='$update_time' ,update_time = '$update_time' WHERE id = '$task_id'");
				//设计部签收任务记录
				//$dataInsert = ['id' => -1, 'openid' => $openid,'task_id' => $task_id,
				//			'change_type' => 8,'update_time' => $update_time,'create_time' => $creat_time];
				//			Db::table('ipm_inst_subproject_task_change')->insertGetId($dataInsert);
				//将项目状态改成底图深化已审核			
				Db::query("UPDATE ipm_inst_subproject SET state = 3 ,update_time = '$update_time' WHERE id = '$subprj_id'");
				//记录项目状态变化
				Db::query("INSERT INTO ipm_inst_subproject_state_change (id,openid,subproject_id,changed_state,prev_state,update_time,create_time)
								VALUES(-1,'$openid','$subprj_id',3,2,'$update_time', '$creat_time')");
				    $result['success'] = true;
			        echo json_encode($result);
                    $task_name=Db::table('ipm_inst_subproject_task')->where('id',$task_id)->value('name');
                    $subprj_name=Db::table('ipm_inst_subproject')->where('id',$subprj_id)->value('name');
                    $changer_ids=Db::table('ipm_inst_subproject_task')->where('id',$task_id)->value('changer_id');
                    if($changer_ids){
                        $arr=array(
                            'subprj_id'=>$subprj_id,
                            'task_name'=>$task_name,
                            'subprj_name'=>$subprj_name,
                            'openid'=>$changer_ids
                        );
                        $message->PushMessageTaskStatus($arr);
                        return;
                    }else{
                        return ;
                    }
				}else if($state_qz[0]['state']==1){
					$result['success']=false;
					$result['errormsg']='设计部还未提交底图，无法签收';
					return json($result);
				}
				else if($state_qz[0]['state']==3){
					$result['success']=false;
					$result['errormsg']='任务已完成，无需重复签收';
					return json($result);
				}
			}else{
                $result['success'] = true;
                return  json($result);
            }

		
		  
	}
	//20180109Ryan 网页点重做任务
	public function redoTaskStatus_self(){
		$arr= $this->request->param();
		$update_time = date("Y-m-d H:i:s");
		$creat_time = date("Y-m-d H:i:s");
		$task_id=$arr['task_id'];
		$openid=$arr['openid'];
		$set_sjbqs = $arr['set_sjbqs'];
		$subprj_id = $arr['subprj_id'];
		Db::query("UPDATE ipm_inst_subproject_task SET state = 1 ,end_time_real=null ,update_time = '$update_time' WHERE id = '$task_id'");//end_time_real一定要置null，不然会影响项目看板
		
		$dataInsert = ['id' => -1, 'openid' => $openid,'task_id' => $task_id,
						'change_type' => 7,'update_time' => $update_time,'create_time' => $creat_time];
						Db::table('ipm_inst_subproject_task_change')->insertGetId($dataInsert);
		//暂写死，底图签收任务重做同步重做设计部签收任务
		if($set_sjbqs)
		{
			$ts_qianzhi = $task_id-1;
			Db::query("UPDATE ipm_inst_subproject_task SET state = 1 ,end_time_real=null ,update_time = '$update_time' WHERE id = '$ts_qianzhi'");//end_time_real一定要置null，不然会影响项目看板
			Db::query("delete from ipm_inst_file where type = '$ts_qianzhi'");
			//修改项目状态，打回
			Db::query("UPDATE ipm_inst_subproject SET state = 2 ,update_time = '$update_time' WHERE id = '$subprj_id'");
			Db::query("INSERT INTO ipm_inst_subproject_state_change (id,openid,subproject_id,changed_state,prev_state,update_time,create_time)
								VALUES(-1,'$openid','$subprj_id',2,3,'$update_time', '$creat_time')");
		}
		$result['success'] = true;
        return json($result);  
	}
}