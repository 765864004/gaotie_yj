<?php
class YksManageAction extends AdminAction
{

    public function __construct()
    {
        parent::__construct();

        //$this->_taskbaseModel = M('TaskBase');
    }

    /********************************成绩信息管理**********************************/


    //查询所有的考试记录
    public function ytaskResultList()
    {
//        //分页
//        $currentPage = empty( $_REQUEST ['currentPage'] ) ? 1 : $_REQUEST ['currentPage'];
//        $pageSize = empty( $_REQUEST ['pageSize'] ) ? 200 : $_REQUEST ['pageSize'];
//
//        //查询所有试卷
//        $pageList = M()->table("y_kaohegroup")->select();
//        $this->assign("pageList",$pageList);
//        //dump($pageList);
//
//        $khid = trim ( $_REQUEST ['khid'] ); //试卷id
//        if ( !empty ( $khid ) ) {
//            //$filter ['$khid'] = array ( 'eq', $khid );
//            $this->assign ( "khid", $khid );
//
//            $totalCount =  M()->table('y_kaohe_userscore')->where("khid = $khid")->count ();
//            $kebases = M()->table('y_kaohe_userscore')
//                ->where("khid = $khid")
//                ->join("t_user_info_2013 ON y_kaohe_userscore.userid = t_user_info_2013.uid")
//                ->join("y_kaohegroup ON y_kaohe_userscore.errornum = y_kaohegroup.errornum")
//                ->field("y_kaohe_userscore.*, t_user_info_2013.truename, y_kaohegroup.unitname")
//                ->page ( $currentPage )->limit ( $pageSize )
//                ->select();
//        }else{
//            $totalCount =  M()->table('y_kaohe_userscore')->count ();
//            $kebases = M()->table('y_kaohe_userscore')
//                ->join("t_user_info_2013 ON y_kaohe_userscore.userid = t_user_info_2013.uid")
//                ->join("y_kaohegroup ON y_kaohe_userscore.errornum = y_kaohegroup.errornum")
//                ->field("y_kaohe_userscore.*, t_user_info_2013.truename, y_kaohegroup.unitname")
//                ->page ( $currentPage )->limit ( $pageSize )
//                ->select();
//        }

        $currentPage = empty( $_REQUEST ['currentPage'] ) ? 1 : $_REQUEST ['currentPage'];//
        $pageSize = empty( $_REQUEST ['pageSize'] ) ? 200 : $_REQUEST ['pageSize'];

        //1.试卷id，没有涉及到多表的查询，直接eq条件查询
        $khid = trim ( $_REQUEST ['khid'] );
        if ( !empty ( $khid ) ) {
            $filter ['khid'] = array ( 'eq', $khid );
            $this->assign ( "khid", $khid );
        }

        //2.部门、姓名 根据真实姓名和部门id在学生信息表里面查询该条件下面的学生id，然后根据查到的学生id在考试表里面做in的条件查询
        $departId = $_REQUEST ["departid"]; //部门id
        $truename = $_REQUEST ['truename']; //真实姓名
        //查询学员id
        if ( $departId && $truename ) { //如果部门id和姓名 都存在
            $userIdArr = M()->table("t_user_info_2013")->where ( "departid = ".$departId." and truename like '%".$truename."%'" )->field ( "uid" )->select ();
        }
        else if ( $departId ) { //如果只有部门id
            $userIdArr = M()->table("t_user_info_2013")->where ( "departid = $departId" )->field("uid")->select();
        }
        else if ( $truename ) { //如果只有姓名
            $userIdArr = M()->table("t_user_info_2013")->where ( "truename like '%".$truename."%'" )->field ( "uid" )->select ();
        }
        $this->assign ( "departId", $departId );
        $this->assign ( "truename", $truename );

        //将学员id 放在一个数组里面 $userIds
        $userIds = array ();
        if ( $userIdArr ) { //如果存在学员id
            foreach ( $userIdArr as $userPo ) {
                $userIds [] = $userPo ['uid'];
            }
        }
        if ( $userIds ) {
            $filter ['userid'] = array ( 'in', $userIds );
        }
        else {
            //如果选中了部门或者真实姓名但是又没有查询到学生的话，设置查询的学生id为0，即查不到。
            if ( $departId || $truename ) {
                $filter ['userid'] = array ( 'eq', 0 );
            }
        }

        //条件排序
        $order = array ();
        if ( $_REQUEST ['_order'] && $_REQUEST ['_sort'] ) {
            $order [$_REQUEST ['_order']] =  $_REQUEST ['_sort'];
        }
        else {
            $order ['khid'] = 'desc';
        }

        $totalCount = M()->table('y_kaohe_userscore')->where ( $filter )->count ();
        $kebases = M()->table('y_kaohe_userscore')
            ->where($filter)
            ->join("t_user_info_2013 ON y_kaohe_userscore.userid = t_user_info_2013.uid")
            ->join("y_kaohegroup ON y_kaohe_userscore.khid = y_kaohegroup.id")
            ->field("y_kaohe_userscore.*, t_user_info_2013.truename, t_user_info_2013.usex, t_user_info_2013.departid, y_kaohegroup.unitname, y_kaohegroup.uname")
            ->order ( $order )
            ->page ( $currentPage )->limit ( $pageSize )
            ->select();
        //dump($kebases);

        //部门放入成绩信息
        foreach($kebases as $k=>&$v){
            $departid = $v["departid"];
            $depart = $this->_departModelModel->where("id =$departid")->find();
            $v["departname"] = $depart["name"];
        }

        //部门列表
        $bumenArr = array ();
        $bumenList = $this->_departModelModel->select ();
        if ( $bumenList ) {
            foreach ( $bumenList as $bumenPo ) {
                $bumenArr [$bumenPo ['id']] = $bumenPo ['name'];
            }
        }

        $this->assign ( "departList", $bumenArr );
        $this->assign ( "bumenList", $bumenList );

        //查询所有试卷
        $pageList = M()->table("y_kaohegroup")->select();
        $this->assign("pageList",$pageList);

        //分页
        $this->assign('totalCount', $totalCount);
        $this->assign('currentPage', $currentPage);
        $this->assign('pageSize', $pageSize);

        $this->assign("khbases",$kebases);
        //dump($kebases);
        $this->display ( 'YksManager:taskResultList' );
    }

    //重考设置
    public function yreks()
    {
        $type = $_REQUEST['actType'];
        $id   = $_REQUEST['id'];

        if ($type && isset($id))
        {
            $filter = array();
            $filter['id'] = $id;

            if ($type == "del")
            {
                $data['status'] = 10;
                M()->table('y_kaohe_userscore')->where($filter)->save($data);
            }
            $data = array("statusCode"=>"200","message"=>"操作成功",'navTabId'=>'ytaskResultList');
        }
        else
        {
            $data = array("statusCode"=>"300","message"=>"系统错误");
        }
        echo json_encode($data);
    }


    //导出成绩到execl表中
    public function ytoExcel()
    {


        //得到所有的试卷
        $pageKaoshiList = M()->table("y_kaohe_userscore")->Distinct(true)->field('khid')->select(); //学员考过的试卷id 去掉重复
        //dump($pageKaoshiList);

        $usePaperIdArr = array(); //试卷id 放在一个数组

        if ($pageKaoshiList)
        {
            foreach ($pageKaoshiList as $kaosiPo)
            {
                $usePaperIdArr[] = $kaosiPo['khid'];
            }
        }
        $filterkaoshi = array();
        $filterkaoshi['id'] = array("in", $usePaperIdArr);

        if ($usePaperIdArr)
        {
            $pageList =M()->table("y_kaohegroup")->where($filterkaoshi)->order("id desc")->select();
        }
        else
        {
            $pageList = array();
        }
        $this->assign("pageList", $pageList);
        //dump($pageList);

        //部门列表
        $bumenList = $this->_departModelModel->select();
        $this->assign("bumenList", $bumenList);

        $this->display("YksManager:toExcel");

    }

    //执行导出成绩到execl表中
    public function ytoExcelzx()
    {
        if ($_REQUEST['submit'] == 1) {
            //1.获取试卷id
            $paperid = trim($_REQUEST['khid']);//试卷id
            $filter['khid'] = array('eq', $paperid);

            //2.获取学员id 根据部门查询学员id
            $departRid = $_REQUEST["departid"];
            if ($departRid) {
                $userIdArr = M()->table("t_user_info_2013")->where("departid = $departRid")->field("uid")->select();

                //获取所有的学员id
                $userIds = array();
                if ($userIdArr) {
                    foreach ($userIdArr as $userPo) {
                        $userIds[] = $userPo['uid'];
                    }
                }
                if ($userIds) {
                    $filter['userid'] = array('in', $userIds);
                }
            }

            //条件排序
            $order = array();
            $order['khid'] = 'desc';

            //3..满足条件的考试成绩(通过试卷id +学员id 查询成绩)
            $list = M()->table("y_kaohe_userscore")->where($filter)->order($order)->select();

            //4.试卷列表（学员考过的试卷)
            if ($list) {
                //得到试卷id和学员id
                $kaoshiPageArr = array();
                $userArr = array();
                foreach ($list as $key => $value) {
                    $kaoshiPageArr[] = $value['khid'];//考试试卷id
                    $userArr[] = $value['userid'];//用户id
                }

                //试卷列表
                $kaoshiPageArr = array_unique($kaoshiPageArr); //试卷id 去掉重复
                $userArr = array_unique($userArr); //用户id 去掉重复
                //a.试卷
                $taskKaoshiArr = array();
                $kaoshiList =M()->table("y_kaohegroup")->where(array("id" => array("in", $kaoshiPageArr)))->select();
                //dump($kaoshiList);
                if ($kaoshiList) {
                    foreach ($kaoshiList as $kaoshiPo) {
                        $taskKaoshiArr[$kaoshiPo['id']] = $kaoshiPo; //试卷数组序号 = 试卷id
                    }
                }
                //dump($taskKaoshiArr);
                $this->assign("taskKaoshiArr", $taskKaoshiArr); //试卷列表

                //考试成绩封装
                // b.学员
                $userList = $this->_userInfoModel->where(array('uid' => array("in", $userArr)))->select(); //学员信息
                //dump($userList);
                if ($userList) {
                    foreach ($userList as $userPo) {
                        $userKaoshiArr[$userPo['uid']] = $userPo; //学员数组序号  = 学员id
                    }
                }
                //dump($userKaoshiArr);
                $this->assign("userKaoshiArr", $userKaoshiArr);

                //c.部门列表
                $bumenArr = array();
                $bumenList = $this->_departModelModel->select();
                //dump($bumenList);
                if ($bumenList) {
                    foreach ($bumenList as $bumenPo) {
                        $bumenArr[$bumenPo['id']] = $bumenPo['name'];//所有部门
                    }
                }
                //dump($bumenArr);

                $dataResult = array();
                foreach ($list as $key => $vo) {
                    $dataResult[$key][] = $userKaoshiArr[$vo['userid']]['truename'];
                    if ($userKaoshiArr[$vo['userid']]['usex'] == 0) {
                        $dataResult[$key][] = "男";
                    } else {
                        $dataResult[$key][] = "女";
                    }
                    $dataResult[$key][] = $bumenArr[$userKaoshiArr[$vo['userid']]['departid']];
                    $dataResult[$key][] = $taskKaoshiArr[$vo['khid']]['unitname'];
                    $dataResult[$key][] = $vo['time'];
                    $dataResult[$key][] = $vo['score'];
                    switch ($vo['status']) {
                        case "0":
                            $str = "未开始";
                            break;
                        case "1":
                            $str = "考试开始";
                            break;
                        case "2":
                            $str = "考试结束";
                            break;
                        case "10":
                            $str = "未补考";
                            break;
                        case "11":
                            $str = "补考开始";
                            break;
                        case "12":
                            $str = "补考结束";
                            break;
                        default:
                            $str = "NULL";
                            break;
                    }
                    $dataResult[$key][] = $str;
                }
                //dump($dataResult);
                //exit;


                $fileName = "test_excel"; //文件名
                $headArr = array("学生姓名", "性别", "部门", "试卷名称", "考试时间", "考试得分", "状态");
                $this->getExcel($fileName, $headArr, $dataResult);
                //$data = array("statusCode"=>"300","message"=>"系统ok");
            } else {
                $data = array("statusCode" => "300", "message" => "系统错误");
            }
            echo json_encode($data);

        }
    }


    /********************************试卷信息管理**********************************/
    //试卷信息管理
    public function ytaskpageList(){

        $currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
        $pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];


        //条件过滤（搜索）
        $filter = array();
        $unitname = trim($_REQUEST['unitname']);//试卷名称
        if(!empty($unitname))
        {
            $filter['unitname'] = array('like', '%'.$unitname.'%');
            $this->assign('unitname', $unitname);
        }

        //条件排序
        $order = array();
        if($_REQUEST['_order'] && $_REQUEST['_sort'])
        {
            $order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
        }
        else
        {
            $order['id'] = 'desc';
        }

        $totalCount = M()->table('y_kaohegroup')->where($filter)->count();
        $taskpages = M()->table('y_kaohegroup')->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();

        //dump($taskpages);
        //分页
        $this->assign('totalCount', $totalCount);
        $this->assign('currentPage', $currentPage);
        $this->assign('pageSize', $pageSize);

        //全部试卷信息
        $this->assign("taskpages",$taskpages);

        $this->display('YksManager:taskpageList');
    }

    //添加考试试卷
    public function ytaskpageAdd(){

        //查询全部课程任务
        $unitList= M()->table('y_task_base')->select();
        $this->assign("unitList", $unitList);
        //dump($unitList);
        $this->display('YksManager:taskpageAdd');
    }

    //执行添加试卷
    public function ytaskpageAddzx(){

        if ($_REQUEST['submit'] == 1)
        {
            $unitArr = $_REQUEST['unitA']; //课程任务id

            $taskbase = M()->table('y_task_base')->where("id = $unitArr")->find();
            //dump($taskbase);

            if (empty($unitArr))
            {
                $array = array('statusCode'=>'300','message'=>"没有选择热点部位");
                echo json_encode($array);
                exit();
            }

            $data = array();
            $data['uname']=$taskbase["errorname"]; //任务名
            $data['errornum']=$taskbase["errornum"]; //任务编号
            $data['taskid'] = $taskbase["id"]; //任务id

            $data['unitname']= $_REQUEST['unitname']; //试卷名称
            $data['score']=$_REQUEST['score']; //总分
            $data['starttime']=strtotime($_REQUEST['starttime']); //开始时间
            $data['endtime']=strtotime($_REQUEST['endtime']); //结束时间

            $pageid = M()->table('y_kaohegroup')->add($data);
            if($pageid){
                $array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'ytaskpageList','callbackType'=>'closeCurrent');
            }
            else
            {
                $array = array('statusCode'=>'300','message'=>"添加失败");
            }
            echo json_encode($array);
            exit();
        }

    }

    public function ytaskpageAddzx2()
    {

        if ($_REQUEST['submit'] == 1)
        {
            $unitArr = $_REQUEST['unitA'];	 	  //选中的热点部位

            if (empty($unitArr))
            {
                $array = array('statusCode'=>'300','message'=>"没有选择热点部位");
                echo json_encode($array);
                exit();
            }

            //将试卷的基本信息写到基本信息表中
            //$data['id'] = 11111;
            $date['uname'] = '444464';
            //$newTaskPageId = $this->_taskPageListMoel->add($date1);
            $newTaskPageId = M()->table("y_kaohegroup")->add($date);

            if ($newTaskPageId) {
                $array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'taskpageList','callbackType'=>'closeCurrent');
            }
            else {
                $array = array('statusCode'=>'300','message'=>"添加失败");
            }

            //header('Content-Type: application/json');
            echo json_encode($array);
            exit();
        }
    }

    public function ytaskpageAdd2(){

            $taskBaseInfos = $this->_taskbaseModel->where("area & 1 = 1 and level & 1 = 1 and CRH & 1 = 1")->select();

            $unitVos = array();
            if ($taskBaseInfos)
            {
                $unitIdArrs = array();

                $filter = array();
                foreach ($taskBaseInfos as $vo)
                {
                    if (in_array($vo['unit'], $unitIdArrs))
                    {
                        continue;
                    }
                    else
                    {
                        $unitIdArrs[] = $vo['unit'];
                    }
                }

                $filter['id'] = array('in', $unitIdArrs);
                $unitVos = $this->_unitModel->where($filter)->select();
            }

            $this->assign('unitList', $unitVos);


        $this->display('YksManager:taskpageAdd2');
    }

    //查看试卷步骤
    public function ytaskpageStep()
    {
        $errornum = $_REQUEST['errornum'];
        $steps = M()->table("y_step_base_new")
            ->where("errornum = $errornum")
            ->select();

        $this->assign("steps", $steps);
        $this->display('YksManager:taskpageStep');
    }


    //删除考试试卷
    public function ytaskpageDel()
    {
        $type = $_REQUEST['actType'];
        $id   = $_REQUEST['id'];

        if ($type && $id)
        {
            $filter = array();
            $filter['pindex'] = $id;

            if ($type == "del")
            {
//                $this->_taskPageListMoel->where("autoid = $id")->delete();
//                $this->_taskPageMoel->where($filter)->delete();
//                $this->_taskPageResultMoel->where($filter)->delete();
                M()->table('y_kaohegroup')->where("id = $id")->delete();
            }

            $data = array("statusCode"=>"200","message"=>"操作成功",'navTabId'=>'ytaskpageList');
        }
        else
        {
            $data = array("statusCode"=>"300","message"=>"系统错误");
        }
        echo json_encode($data);
    }

    /********************************试卷分配**********************************/
    //查看试卷分配
    public function ytaskpagefpList(){

        //(去掉重复 根据试卷编号分组)
//        $fplists = M()->table('y_kaohe_userscore')
//            ->join("y_KaoHeGroup ON y_kaohe_userscore.khid = y_kaohegroup.id")
//            ->field("y_kaohe_userscore.khid, y_kaohe_userscore.errornum, y_kaohe_userscore.time, y_KaoHeGroup.unitname, y_KaoHeGroup.starttime, y_KaoHeGroup.endtime")
//            ->group('y_kaohe_userscore.khid')
//            ->select();
//        $sql="select tsum.stuid, tsum.zf, tstu.stuname from
//              (select stuid, sum(maxstar) as zf from
//              (select stuid, passid, max(star) as maxstar from t_star group by stuid, passid) tmax
//        	  group by stuid) tsum
//              left join t_student tstu ON tsum.stuid=tstu.stuid";
//        $tests=$this->star->query($sql);

        //条件过滤
        $filter = array();
        $name = trim($_REQUEST['name']);//试卷名称  有试卷名称的话，得到试卷的id
        if($name){
            $khgroup = M()->table("y_kaohegroup")->where("unitname like '%".$name."%'")->find();
            $khid = $khgroup["id"];
            $sql = "select y_kaohe_userscore.khid, y_kaohe_userscore.time from y_kaohe_userscore where khid = '$khid'  group by khid, time";
        }else{
            $sql = "select y_kaohe_userscore.khid, y_kaohe_userscore.time from y_kaohe_userscore group by khid, time";
        }

        //dump($filter);

//        $u = M()->table("y_kaohe_userscore")
//            ->where("khid = $khid")
//            ->group('khid,time')
//            ->field('khid,time')
//            ->select();
//        dump($u);

        $userscore = M()->table('y_kaohe_userscore')->query($sql);
        foreach($userscore as &$v){
            $khid = $v["khid"];
            $v["shijuan"] = M()->table("y_kaohegroup")->where("id = $khid")->find();
        }
        //dump($userscore);

        $this->assign("fplists",$userscore);

        $this->display('YksManager:taskpagefpList');
    }

    //查看试卷分配人员(一份试卷的所有考试人员)
    public function ytaskpageFpuser()
    {
        $khid   = $_REQUEST['khid'];
        $stus = M()->table("y_kaohe_userscore")
            ->where("khid='$khid'")
            ->join("t_user_info_2013 ON y_kaohe_userscore.userid = t_user_info_2013.uid")
            ->field("t_user_info_2013.truename")
            ->select();

        $this->assign("stus",$stus);
        //dump($stus);
        $this->display('YksManager:taskpageFpuser');
    }

    //添加试卷分配
    public function ytaskpagefpAdd(){

        //查询所有的试卷
        $shijuans = M()->table("y_kaohegroup")->select();
        //dump($shijuans);
        $this->assign("shijuans",$shijuans);

        //查询所有的部门
        $bumenList = $this->_departModelModel->select();
        $this->assign("departList", $bumenList);

        //查询所有学员
        $sql = "select user1.*, depart.name as departname from t_user_info_2013 as user1,t_departid_2013 as depart where user1.departid=depart.id order by user1.departid desc";
        //	echo $sql;
        $userList = M()->query($sql);//dump($userList);
        if($userList)
        {
            $departName = "";
            foreach($userList as $userPo)
            {
                if($userPo['departname'] != $departName)
                {
                    $str .= "<optgroup label='".$userPo['departname']."'>";
                }
                $str .= "<option value='".$userPo['uid']."'>".$userPo['truename'];

                $departName = $userPo['departname'];
            }
        }
        $this->assign("userOption", $str);

        $this->display("YksManager:taskpagefpAdd");
    }

    //执行添加
    public function ytaskpagefpAddzx(){

        if($_REQUEST['submit'] == 1)
        {
            $khid = $_REQUEST["khid"]; //试卷id
            $userIdArr = $_REQUEST["userId"];//用户id数组

            //通过试卷id 查询试卷信息
            $shijuan =M()->table('y_kaohegroup')->where("id = $khid")->find();
            $errornum = $shijuan["errornum"]; //课程任务编号
            //dump($shijuan);

            if(count($userIdArr) > 0)
            {
                foreach($userIdArr as $userid)
                {
                    $data = array();
                    $data['khid'] = $khid;
                    $data['userid'] = $userid;
                    $data["errornum"] = $errornum;
                    $data['status'] = 0;

                    //dump($data);

                    M()->table('y_kaohe_userscore')->add($data);
                }
                $array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'ytaskpagefpList','callbackType'=>'closeCurrent');
            }
            else
            {
                $array = array('statusCode'=>'300','message'=>"没有选择学生");
            }

            echo json_encode($array);
            exit();
        }
    }

    //试卷分配修改
    public function ytaskpagefpEdit(){

        $id = $_REQUEST["id"]; //试卷id

        //一张试卷的所有学员id 放在数组里$userIdFpArr
        $ksFenpei = M()->table('y_kaohe_userscore')->where("khid = $id")->select();
        //dump($ksFenpei);
        if($ksFenpei)
        {
            $userIdFpArr = array();
            foreach($ksFenpei as $fenpeiPo)
            {
                $userIdFpArr[] = $fenpeiPo['userid'];
            }
        }
        //dump($userIdFpArr);

        //查询试卷信息
        $pageInfo = M()->table('y_kaohegroup')->where("id = $id")->find();
        $this->assign("pageInfo", $pageInfo);

        //部门列表
        $bumenList = $this->_departModelModel->select();
        $this->assign("departList", $bumenList);

        //学员列表 (考试学员在学员表中 则selected)
        $sql = "select user1.*, depart.name as departname from t_user_info_2013 as user1,t_departid_2013 as depart where user1.departid=depart.id order by user1.departid desc";
        //	echo $sql;
        $userList = M()->query($sql);//dump($userList);
        if($userList)
        {
            $departName = "";
            foreach($userList as $userPo)
            {
                if($userPo['departname'] != $departName)
                {
                    $str .= "<optgroup label='".$userPo['departname']."'>";
                }
                $str .= "<option value='".$userPo['uid']."'";
                if(in_array($userPo['uid'], $userIdFpArr))
                {
                    $str .= "selected";
                }

                $str .= ">".$userPo['truename'];

                $departName = $userPo['departname'];
            }
        }
        $this->assign("userOption", $str);


        $this->display("YksManager:taskpagefpEdit");
    }

    //执行试卷分配修改
    public function ytaskpagefpEditzx()
    {
        if($_REQUEST['submit'] == 1)
        {
            $paperid = $_REQUEST['id'];//试卷id
            $userIdArr = $_REQUEST["userId"];//用户id数组

            //查询试卷信息 （插入成绩表）
            $shijuan=M()->table('y_kaohegroup')->where("id = $paperid")->find();
            $errornum = $shijuan["errornum"];


            if(count($userIdArr) > 0)
            {
                //首先删除已报名的改试卷的所有信息，然后和添加一个步骤
                M()->table('y_kaohe_userscore')->where("khid = $paperid")->delete();

                foreach($userIdArr as $userid)
                {
                    $data = array();
                    $data['khid'] = $paperid; //试卷id
                    $data['userid'] = $userid; //学员id
                    $data["errornum"] = $errornum;
                    $data['status'] = 0;

                    M()->table('y_kaohe_userscore')->add($data);
                }
                $array = array('statusCode'=>'200','message'=>"试卷重新分配成功",'navTabId'=>'','callbackType'=>'closeCurrent');
            }
            else
            {
                $array = array('statusCode'=>'300','message'=>"没有选择学生");
            }

            echo json_encode($array);
            exit();
        }
    }

    //试卷分配删除
    public function ytaskpageFpDel()
    {
        $type = $_REQUEST['actType'];
        $id   = $_REQUEST['id']; //试卷id

        if($type && $id)
        {
            $filter = array();
            $filter['khid'] = $id;

            if($type == "del")
            {
                M()->table('y_kaohe_userscore')->where($filter)->delete();
            }

            $data = array("statusCode"=>"200","message"=>"操作成功",'navTabId'=>'ytaskpagefpList','callbackType'=>'forward');
        }
        else
        {
            $data = array("statusCode"=>"300","message"=>"系统错误");
        }
        echo json_encode($data);
        exit;
    }


    //根据部门得到该部门下面的所有学员
    public function ajaxGetUserByDepartId()
    {
        $id = $_REQUEST['id'];

        $str = "";
        if($id)
        {
            $userList = $this->_userInfoModel->where("departid = $id")->select();
            if($userList)
            {
                foreach($userList as $userPo)
                {
                    $str .= "<option value='".$userPo['uid']."'>".$userPo['truename'];
                }
            }
        }
        else
        {
            //$userList = M()->table("t_user_info_2013 as tuser")->join("t_departid_2013 as depart on tuser.departid=depart.id")->select();

            $sql = "select user1.*, depart.name as departname from t_user_info_2013 as user1,t_departid_2013 as depart where user1.departid=depart.id order by user1.departid desc";
            //	echo $sql;
            $userList = M()->query($sql);//dump($userList);
            if($userList)
            {
                $departName = "";
                foreach($userList as $userPo)
                {
                    if($userPo['departname'] != $departName)
                    {
                        $str .= "<optgroup label='".$userPo['departname']."'>";
                    }
                    $str .= "<option value='".$userPo['uid']."'>".$userPo['truename'];

                    $departName = $userPo['departname'];
                }
            }
        }

        $this->ajaxReturn($str,1,1);
    }



    private function getExcel($fileName,$headArr,$data)
    {
        //require_once 'library/PHPExcel.php';
        //require_once 'library/PHPExcel/Writer/Excel2007.php';
        //require_once 'library/PHPExcel/Writer/Excel5.php';
        //include_once 'library/PHPExcel/IOFactory.php';
        vendor("PHPExcel.PHPExcel");

        if(empty($data) || !is_array($data)){
            die("data must be a array");
        }
        if(empty($fileName)){
            exit;
        }
        $date = date("Y_m_d",time());
        $fileName .= "_{$date}.xlsx";

        //创建新的PHPExcel对象
        $objPHPExcel = new PHPExcel();
        $objProps = $objPHPExcel->getProperties();

        //设置表头
        $key = ord("A");
        foreach($headArr as $v){
            $colum = chr($key);
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($colum.'1', $v);
            $key += 1;
        }
        $objPHPExcel->getActiveSheet()->freezePane('A2');
        $column = 2;
        $objActSheet = $objPHPExcel->getActiveSheet();
        foreach($data as $key => $rows){ //行写入
            $span = ord("A");
            foreach($rows as $keyName=>$value){// 列写入
                $j = chr($span);
                $objActSheet->setCellValue($j.$column, $value);
                $span++;
            }
            $column++;
        }

        $fileName = iconv("utf-8", "gb2312", $fileName);
        //重命名表
        $objPHPExcel->getActiveSheet()->setTitle('Simple');
        //设置活动单指数到第一个表,所以Excel打开这是第一个表
        $objPHPExcel->setActiveSheetIndex(0);//dump($objPHPExcel);exit;
        //将输出重定向到一个客户端web浏览器(Excel2007)
        header('Content-Type: application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        header("Content-Disposition: attachment; filename=\"$fileName\"");
        header('Cache-Control: max-age=0');
        $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
        $objWriter->save('php://output'); //文件通过浏览器下载
        exit;
    }


}