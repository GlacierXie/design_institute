<?php
namespace app\home\controller;
use think\Controller;
use think\Db;
use think\Cache;
use app\home\controller\GlobalHelp;
use app\home\model\Companys;
use think\Request;
use think\Loader;

class Excel extends Controller
{
    public function export($xlsData){
        Loader::import('PHPExcel.Classes.PHPExcel');
        Loader::import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        Loader::import('PHPExcel.Classes.PHPExcel.Writer.Excel5');
        Loader::import('PHPExcel.Classes.PHPExcel.Writer.Excel2007');
        Loader::import('PHPExcel.Classes.PHPExcel.Worksheet.Drawing');
        $objExcel = new \PHPExcel();
        // 垂直居中
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');

        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I,J,K");
        $arrHeader1 = array('项目问题记录清单（问题分级）');
        $arrHeader = array('序号','问题阶段','负责人','问题简述','问题详情','面积','问题部位','审核人','提出日期','问题级别','图片');

        //填充表头信息
        $lenth =  count($arrHeader);
        $lenth1 =  count($arrHeader1);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]3","$arrHeader[$i]");
        };
        for($i = 0;$i < $lenth1;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader1[$i]");
            $objActSheet->mergeCells('A1:K1');
            $objActSheet->getRowDimension('1')->setRowHeight(30);
            $objActSheet->getStyle('A1:K1')->applyFromArray(
                array(
                    'font' => array (
                        'size'=>16,
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'vertical'=> \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
             );
        };
        $subprj_name=$xlsData[0]['subprj_name'];
        $time= date("Y-m-d H:i:s");
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]2","项目名称：".$subprj_name);
            $objActSheet->mergeCells('A2:E2');
            $objActSheet->getRowDimension('1')->setRowHeight(30);
            $objActSheet->setCellValue("$letter[$i]2","导出时间：".$time);
            $objActSheet->mergeCells('F2:K2');
            $objActSheet->getRowDimension('2')->setRowHeight(30);
            $objActSheet->getStyle('A2:K2')->applyFromArray(
                array(
                    'font' => array (
                        'size'=>12,
                        'bold' => true
                    ),
                    'alignment' => array(
                        'horizontal' => \PHPExcel_Style_Alignment::VERTICAL_CENTER,
                        'vertical'=> \PHPExcel_Style_Alignment::VERTICAL_CENTER
                    )
                )
            );
        }
        //填充表格信息
        $b=0;
       foreach($xlsData as $k=>$v){
            $k +=4;
            $b+=1;
            $objActSheet->setCellValue('A'.$k,$b);
            $objActSheet->setCellValue('B'.$k, $v['prjState']);
            $objActSheet->setCellValue('C'.$k, $v['creator_nickname']);
            $objActSheet->setCellValue('D'.$k, $v['title']);
            $objActSheet->setCellValue('E'.$k, $v['description']);
            $objActSheet->setCellValue('F'.$k, $v['errorArea']);
            $objActSheet->setCellValue('G'.$k, $v['subtype_name']);
            $objActSheet->setCellValue('H'.$k, $v['changer_name']);
            $objActSheet->setCellValue('I'.$k, $v['create_time']);
            $objActSheet->setCellValue('J'.$k, $v['problemGrade']);
           $file_list=$v['file_list'];
           foreach($file_list as $kk=>$vv){
                // 图片生成
            $aa = new \PHPExcel_Worksheet_Drawing();
            $aa->setPath($vv);
            // 设置宽度高度
            $aa->setHeight(80);//照片高度
           //   $aa->setWidth(60); //照片宽度
            /*设置图片要插入的单元格*/
            $aa->setCoordinates('K'.$k);
            $aa->setOffsetX(5);
           // $aa->setOffsetY(10);
            $aa->setWorksheet($objExcel->getActiveSheet());
            // 表格内容

           }
           // 表格高度
           $objActSheet->getRowDimension($k)->setRowHeight(60);
        }
        $width = array(5,20,20,15,10,10,30,10,15,40);
        //设置表格的宽度
        $objActSheet->getColumnDimension('A')->setWidth($width[0]);
        $objActSheet->getColumnDimension('B')->setWidth($width[4]);
        $objActSheet->getColumnDimension('C')->setWidth($width[4]);
        $objActSheet->getColumnDimension('D')->setWidth($width[1]);
        $objActSheet->getColumnDimension('E')->setWidth($width[9]);
        $objActSheet->getColumnDimension('F')->setWidth($width[4]);
        $objActSheet->getColumnDimension('G')->setWidth($width[4]);
        $objActSheet->getColumnDimension('H')->setWidth($width[4]);
        $objActSheet->getColumnDimension('I')->setWidth($width[1]);
        $objActSheet->getColumnDimension('k')->setWidth($width[9]);
        $outfile = "问题列表.xls";
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
    public  function  ExportExcel()
    {

        if(request()->isGet())
        {

            $company_id = input('company_id');
            $project_id = input('project_id');
            $subproject_id = input('subproject_id');
            $prjState = input('prjState');
            $problemGrade  = input('problemGrade');
            $title  = input('title');
            $state  = input('state');
            $creator_id  = input('creator_id');
            $changer_id  = input('changer_id');
            $type_id  = input('type_id');
            $subtype_id  = input('subtype_id');
            $keyword  = input('keyword');
            if(!isset($company_id))
                return json();
            if(!isset($subproject_id))
                return json();
            $help=new GlobalHelp();
            $sql = "SELECT DISTINCT a.id,b.id as subprj_id,b.name as subprj_name,f.name as prj_name, f.id as prj_id,
                a.creator_id,c.real_name as creator_nickname,a.type_id,d.name as type_name,a.subtype_id,e.name as subtype_name,
                c.headimgurl as creator_headimgurl,
                  (CASE
                        WHEN a.prjState ='1' THEN  '底图深化'
                        WHEN a.prjState='2' THEN '配模阶段'
                        WHEN a.prjState='3' THEN '制图阶段'
                        WHEN a.prjState='4' THEN '预拼装阶段'
                        WHEN a.prjState='5' THEN '现场施工阶段'
                        	END
                        ) AS 'prjState',
			        a.problemGrade,a.errorArea,a.title,a.description,
			         (CASE
                        WHEN a.state ='1' THEN  '待解决'
                        WHEN a.state='2' THEN '待审核'
                        WHEN a.state='3' THEN '已解决'
                        	END
                        ) AS 'state',
			        a.changer_id,a.update_time,a.create_time
			        from ipm_inst_problem a
					left join ipm_user c on a.creator_id=c.openid
					left join ipm_inst_subproject b on a.subproject_id=b.id
					left join ipm_inst_project f on f.id = b.project_id
					left join ipm_inst_problem_type d on a.type_id=d.id
					left join ipm_inst_problem_subtype e on a.subtype_id=e.id
					where  a.company_id=$company_id ";

            if(isset($project_id))
            {
                $sql = $sql." and a.project_id =$project_id ";
            }
            if(isset($subproject_id))
            {
                $sql = $sql." and  a.subproject_id = '$subproject_id'";
            }
            if(isset($prjState))
            {
                $sql = $sql." and  a.prjState = '$prjState'";
            }
            if(isset($problemGrade))
            {
                $sql = $sql." and  a.problemGrade = '$problemGrade'";
            }
            if(isset($title))
            {
                $sql = $sql." and  a.title = '$title'";
            }
            if(isset($state))
            {
                $sql = $sql." and  a.state = '$state'";
            }
            if(isset($creator_id))
            {
                $sql = $sql." and  a.creator_id = '$creator_id'";
            }
            if(isset($changer_id))
            {
                $sql = $sql." and  a.changer_id = '$changer_id'";
            }
            if(isset($type_id))
            {
                $sql = $sql." and  a.type_id = '$type_id'";
            }
            if(isset($subtype_id))
            {
                $sql = $sql." and  a.subtype_id = '$subtype_id'";
            }
            if(isset($keyword))
            {
                $sql = $sql." and  (a.title LIKE '%$keyword%' OR a.description LIKE '%$keyword%' OR a.create_time LIKE '%$keyword%')";
            }

            $problemdatalist = Db::query($sql);

            if(empty($problemdatalist))
                return json();
            foreach ($problemdatalist as $k => $v)
            {

                $tmpproblemid = $problemdatalist[$k]['id'];
                $problemdatalist[$k]['changer_name'] = $help->getUserName($problemdatalist[$k]['changer_id']);
                $problemdatalist[$k]['changer_headimgurl'] = $help->getUserheadimgurl($problemdatalist[$k]['changer_id']);
                $sql = "SELECT DISTINCT id from ipm_inst_problem_files where problem_id = $tmpproblemid";
                $problemfilelist = Db::query($sql);
                foreach ($problemfilelist as $kk => $vv)
                {
                    $str = md5($problemfilelist[$kk]['id']."jpg");
                    $project_idTmp = $problemdatalist[$k]['prj_id'];
                    $subproject_idTmp = $problemdatalist[$k]['subprj_id'];
                    $problemfilelist[$kk] = ROOT_PATH. 'public' . DS . 'PjrFiles'. DS .$company_id.DS.$project_idTmp.DS.$subproject_idTmp.DS.$str.".jpg";
                }
                $problemdatalist[$k]['file_list'] = $problemfilelist;
            }

            $outputList = array();
            foreach($problemdatalist as $k=>$v)
                $outputList[] = $v;
            $aa=$this->export($outputList);
//            echo '<pre>';
//            print_r($outputList);die();
           // return json($outputList);
        }
        else
        {
            return json();
        }
    }
    public function set_excel($xlsData){
        Loader::import('PHPExcel.Classes.PHPExcel');
        Loader::import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        Loader::import('PHPExcel.Classes.PHPExcel.Writer.Excel5');
        Loader::import('PHPExcel.Classes.PHPExcel.Writer.Excel2007');
        Loader::import('PHPExcel.Classes.PHPExcel.Worksheet.Drawing');
        $objExcel = new \PHPExcel();
        // 垂直居中
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');

        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E");
        $arrHeader = array('厂家','项目名称','项目时间','项目状态','问题总数');

        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        };

        foreach($xlsData as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['project_name']);
          //  $objActSheet->mergeCells('A'. $k .':A' .($k+1));
            $objActSheet->setCellValue('B'.$k, $v['subprj_name']);

            $objActSheet->setCellValue('C'.$k, $v['start_time_plan']);

            $objActSheet->setCellValue('D'.$k, $v['prjState']);

            $objActSheet->setCellValue('E'.$k, $v['sum']);

            $aa=$v['data_person'];
           $bb=$v['data_type'];
            foreach($aa as $kk=>$vv){
                $objActSheet->setCellValue('F'.$k.$kk, $vv['real_name']);
                $objActSheet->setCellValue('G'.$k.$kk, $vv['count']);
            }
            foreach($bb as $kk1=>$vv1){
                $objActSheet->setCellValue('H'.$k.$kk1, $vv1['name']);
                $objActSheet->setCellValue('I'.$k.$kk1, $vv1['num']);
            }
           // $objActSheet->getRowDimension($k)->setRowHeight(20);
        }

        $outfile = "问题列表.xls";
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }
    public function a1(){
        $data_project= Db::query("  select prj.name as project_name,subprj.id as subprj_id,subprj.name as subprj_name,subprj.start_time_plan,
                                   (CASE
                                            WHEN problem.prjState ='1' THEN  '底图深化'
                                            WHEN problem.prjState='2' THEN '配模阶段'
                                            WHEN problem.prjState='3' THEN '制图阶段'
                                            WHEN problem.prjState='4' THEN '预拼装阶段'
                                            WHEN problem.prjState='5' THEN '现场施工阶段'
                                                END
                                            ) AS 'prjState',
                                  count(problem.id) as sum
                                  from ipm_inst_problem as problem
                                    left join ipm_inst_subproject as subprj on subprj.id=problem.subproject_id
                                    left join ipm_inst_project as prj on prj.id=problem.project_id
                                   where subprj.start_time_plan>=2018-01-01 group by problem.subproject_id
                         ");
        //遍历每个项目
       foreach($data_project as $k=>$v){
           $sub_id=$v['subprj_id'];

           //人数组
           $data_project[$k]['data_person']= Db::query("  select id,user.real_name,user.nickname,count(changer_id) as count from ipm_inst_problem   as a
                               left join ipm_user as user on user.openid=a.changer_id
                               where subproject_id=$sub_id group by  changer_id
                         ");

           //类型数组
           $data_project[$k]['data_type']= Db::query("      select a.id,subtype.name,count(a.subtype_id) as num from ipm_inst_problem  as a
                           left join ipm_inst_problem_subtype as subtype on subtype.id=a.subtype_id
                            where subproject_id=$sub_id group by  subtype_id
                         ");

       }
//        echo '<pre>';
//        print_r($data_project);die();

      $aa=$this->set_excel($data_project);

    }
    public function inexport(){
        $data=Db::query(" SELECT a.openid,b.computer_id,a.nickname,a.real_name,a.remark,
                  from_unixtime(b.trail_time) as trail_time,b.trail_days,(TO_DAYS(now()) - TO_DAYS(from_unixtime(b.trail_time))) as useTime,b.trail_days - TO_DAYS(now()) + TO_DAYS(from_unixtime(b.trail_time)) as RemainingTime FROM
                  ipm_user a left join ipm_trail b on a.openid = b.openid where TO_DAYS(now()) - TO_DAYS(from_unixtime(b.trail_time)) <= b.trail_days ORDER BY trail_days DESC");

        $this->get_excel($data);
    }

    public function get_excel($xlsData){
        Loader::import('PHPExcel.Classes.PHPExcel');
        Loader::import('PHPExcel.Classes.PHPExcel.IOFactory.PHPExcel_IOFactory');
        Loader::import('PHPExcel.Classes.PHPExcel.Writer.Excel5');
        Loader::import('PHPExcel.Classes.PHPExcel.Writer.Excel2007');
        Loader::import('PHPExcel.Classes.PHPExcel.Worksheet.Drawing');
        $objExcel = new \PHPExcel();
        // 垂直居中
        $objWriter = \PHPExcel_IOFactory::createWriter($objExcel, 'Excel5');

        $objActSheet = $objExcel->getActiveSheet();
        $key = ord("A");
        $letter =explode(',',"A,B,C,D,E,F,G,H,I");
        $arrHeader = array('openid','电脑Id','微信昵称','真实姓名','所属公司','绑定时间','试用天数','使用天数','剩余天数');
        //填充表头信息
        $lenth =  count($arrHeader);
        for($i = 0;$i < $lenth;$i++) {
            $objActSheet->setCellValue("$letter[$i]1","$arrHeader[$i]");
        };

        foreach($xlsData as $k=>$v){
            $k +=2;
            $objActSheet->setCellValue('A'.$k,$v['openid']);
            $objActSheet->setCellValue('B'.$k, $v['computer_id']);
            $objActSheet->setCellValue('C'.$k, $v['nickname']);
            $objActSheet->setCellValue('D'.$k, $v['real_name']);
            $objActSheet->setCellValue('E'.$k, $v['remark']);
            $objActSheet->setCellValue('F'.$k, $v['trail_time']);
            $objActSheet->setCellValue('G'.$k, $v['trail_days']);
            $objActSheet->setCellValue('H'.$k, $v['useTime']);
            $objActSheet->setCellValue('I'.$k, $v['RemainingTime']);
        }

        $outfile = "现有用户数据.xls";
        ob_end_clean();
        header("Content-Type: application/force-download");
        header("Content-Type: application/octet-stream");
        header("Content-Type: application/download");
        header('Content-Disposition:inline;filename="'.$outfile.'"');
        header("Content-Transfer-Encoding: binary");
        header("Cache-Control: must-revalidate, post-check=0, pre-check=0");
        header("Pragma: no-cache");
        $objWriter->save('php://output');
    }


}