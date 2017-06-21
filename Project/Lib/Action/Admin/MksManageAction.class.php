<?php
class MksManageAction extends AdminAction{

	public function __construct(){

		parent::__construct();
	}

	/********************************成绩信息管理**********************************/

	public function mtaskResultList() {

		$currentPage = empty( $_REQUEST ['currentPage'] ) ? 1 : $_REQUEST ['currentPage'];
		$pageSize = empty( $_REQUEST ['pageSize'] ) ? 200 : $_REQUEST ['pageSize'];

		//1.试卷id，没有涉及到多表的查询，直接eq条件查询
		$pageid = trim ( $_REQUEST ['pageid'] );
		if ( !empty ( $pageid ) ) {
			$filter ['pageid'] = array ( 'eq', $pageid );
			//$this->assign ( "pageid", $pageid );
		}

		//2.部门、姓名 根据真实姓名和部门id在学生信息表里面查询该条件下面的学生id，然后根据查到的学生id在考试表里面做in的条件查询
		$departId = $_REQUEST ["departid"];
		$truename = $_REQUEST ['truename'];
			//查询学员id
		if ( $departId && $truename ) { //如果部门id和姓名 都存在
			$userIdArr = $this->_userInfoModel->where ( "departid = ".$departId." and truename like '%".$truename."%'" )->field ( "uid" )->select ();
		}
		else if ( $departId ) { //如果只有部门id
			$userIdArr = $this->_userInfoModel->where ( "departid = $departId." )->field("uid")->select();
		}
		else if ( $truename ) { //如果只有姓名
			$userIdArr = $this->_userInfoModel->where ( "truename like '%".$truename."%'" )->field ( "uid" )->select ();
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
			$order ['pageid'] = 'desc';
		}

		$totalCount = M()->table("m_score")->where ( $filter )->count ();
		$list = M()->table("m_score")->where ( $filter )->order ( $order )->page ( $currentPage )->limit ( $pageSize )->select ();
		$this->assign ( 'totalCount', $totalCount );

		//将成绩表里的学员id、试卷id 单独放到数组中 (保持唯一)
		if ( $list ) {
			$kaoshiPageArr = array ();
			$userArr = array ();
			foreach ( $list as $key => $value ) {
				$kaoshiPageArr [] = $value ['pageid'];//考试试卷id
				$userArr [] = $value ['userid'];//用户id
			}

			//试卷id 学员id
			$kaoshiPageArr = array_unique ( $kaoshiPageArr);//试卷id 去掉重复
			$userArr = array_unique ($userArr);

			//试卷名称
			$taskKaoshiArr = array ();
			$kaoshiList = M()->table("m_page")->where ( array ( "id" => array ( "in", $kaoshiPageArr ) ) )->select ();
			if ( $kaoshiList ) {
				foreach ( $kaoshiList as $kaoshiPo ) {
					$taskKaoshiArr [$kaoshiPo['id']] = $kaoshiPo;
				}
			}
			$this->assign ( "taskKaoshiArr", $taskKaoshiArr );
			//dump($taskKaoshiArr);
			//学员姓名
			$userList = $this->_userInfoModel->where ( array( 'uid' => array ( "in", $userArr ) ) )->select ();
			if ($userList) {
				foreach ( $userList as $userPo ) {
					$userKaoshiArr [$userPo['uid']] = $userPo;
				}
			}
			$this->assign ( "userKaoshiArr", $userKaoshiArr );
		}

		$this->assign ( 'list', $list );
		//dump($list);


		//搜索栏
		//得到所有的试卷(成绩表里的试卷id 排除重复)
		$pageKaoshiList = M()->table("m_score")->Distinct ( true )->field ( 'pageid' )->select ();

		$usePaperIdArr = array ();

		if ( $pageKaoshiList ) {
			foreach ( $pageKaoshiList as $kaosiPo ) {
				$usePaperIdArr [] = $kaosiPo ['pageid'];
			}
		}
		$filterkaoshi = array ();
		$filterkaoshi ['id'] = array("in", $usePaperIdArr);

		if ( $usePaperIdArr ) {
			$pageList = M()->table("m_page")->where ( $filterkaoshi )->order ( "id desc" )->select();
		}
		else
		{
			$pageList = array ();
		}
		$this->assign ( "pageList", $pageList );

		//部门列表
		$bumenArr = array ();
		$bumenList = $this->_departModelModel->select ();
		if ( $bumenList ) {
			foreach ( $bumenList as $bumenPo ) {
				$bumenArr [$bumenPo ['id']] = $bumenPo ['name'];
			}
		}

		//dump($bumenArr);
		//dump($bumenList);
		$this->assign ( "departList", $bumenArr );
		$this->assign ( "bumenList", $bumenList );

		$this->assign ( 'currentPage', $currentPage );
		$this->assign ( 'pageSize', $pageSize );

		$this->display ( 'MksManager:taskResultList' );
	}

	//导出成绩到execl表中
	public function mtoExcel()
	{
		if ($_REQUEST['submit'] == 1)
		{
			$pageid = trim($_REQUEST['pageid']);//试卷id
			$filter['pageid'] = array('eq', $pageid);

			//1.查询学员id
			$departRid = $_REQUEST["departid"];
			if ($departRid)
			{
				$userIdArr = $this->_userInfoModel->where("departid = $departRid")->field("uid")->select();

				$userIds = array();
				if ($userIdArr)
				{
					foreach($userIdArr as $userPo)
					{
						$userIds[] = $userPo['uid'];
					}
				}
				if ($userIds)
				{
					$filter['uid'] = array('in', $userIds);
				}
			}

			//2.条件排序
			$order = array();
			$order['pageid'] = 'desc';

			//3.满足条件的考试成绩
			$list = M()->table("m_score")->where($filter)->order($order)->select();

			if ($list)
			{
				$kaoshiPageArr = array();
				$userArr = array();
				foreach ($list as $key=>$value)
				{
					$kaoshiPageArr[] = $value['pageid'];//考试试卷id
					$userArr[] = $value['userid'];//用户id
				}

				//试卷列表
				$kaoshiPageArr = array_unique($kaoshiPageArr);
				$userArr = array_unique($userArr);

				$taskKaoshiArr = array();
				$kaoshiList = M()->table("m_page")->where(array("id"=>array("in", $kaoshiPageArr)))->select();

				if ($kaoshiList)
				{
					foreach ($kaoshiList as $kaoshiPo)
					{
						$taskKaoshiArr[$kaoshiPo['id']] = $kaoshiPo;
					}
				}
				$this->assign("taskKaoshiArr", $taskKaoshiArr);

				//学员列表
				$userList = $this->_userInfoModel->where(array('uid'=>array("in", $userArr)))->select();
				if ($userList)
				{
					foreach ($userList as $userPo)
					{
						$userKaoshiArr[$userPo['uid']] = $userPo;
					}
				}
				$this->assign("userKaoshiArr", $userKaoshiArr);

				//部门列表
				$bumenArr = array();
				$bumenList = $this->_departModelModel->select();
				if ($bumenList)
				{
					foreach ($bumenList as $bumenPo)
					{
						$bumenArr[$bumenPo['id']] = $bumenPo['name'];//所有部门
					}
				}

				$dataResult = array();
				foreach ($list as $key=>$vo)
				{
					$dataResult[$key][] = $userKaoshiArr[$vo['userid']]['truename']; //姓名
					if ($userKaoshiArr[$vo['userid']]['usex'] == 0) //性别
					{
						$dataResult[$key][] = "男";
					}
					else
					{
						$dataResult[$key][] = "女";
					}
					$dataResult[$key][] = $bumenArr[$userKaoshiArr[$vo['userid']]['departid']]; //部门
					$dataResult[$key][] = $taskKaoshiArr[$vo['pageid']]['testname']; //试卷名称
					$dataResult[$key][] = date("Y-m-d H:i:s", $taskKaoshiArr[$vo['pageid']]['starttime']); //考试时间
					$dataResult[$key][] = $vo['score']; //考试得分
					switch($vo['state']){ //状态
						case "0": $str = "未开始";break;
						case "1": $str = "考试开始";break;
						case "2": $str = "考试结束";break;
						case "10": $str = "未补考";break;
						case "11": $str = "补考开始";break;
						case "12": $str = "补考结束";break;
						default: $str = "NULL";break;
					}
					$dataResult[$key][] = $str;
				}

				$fileName = "test_excel"; //文件名
				$headArr = array("学生姓名","性别","部门", "试卷名称", "考试时间", "考试得分", "状态"); //列名
				$this->getExcel($fileName,$headArr,$dataResult);
				//$data = array("statusCode"=>"300","message"=>"系统ok");
			}
			else
			{
				$data = array("statusCode"=>"300","message"=>"系统错误");
			}
			echo json_encode($data);

		}
		else
		{
			//得到所有的试卷 (已分配过的试卷)
			$pageKaoshiList = M()->table("m_score")->Distinct(true)->field('pageid')->select();
			//dump($pageKaoshiList);

			$usePaperIdArr = array();

			if ($pageKaoshiList)
			{
				foreach ($pageKaoshiList as $kaosiPo)
				{
					$usePaperIdArr[] = $kaosiPo['pageid'];
				}
			}
			$filterkaoshi = array();
			$filterkaoshi['id'] = array("in", $usePaperIdArr);

			if ($usePaperIdArr)
			{
				$pageList = M()->table("m_page")->where($filterkaoshi)->order("id desc")->select();
			}
			else
			{
				$pageList = array();
			}
			$this->assign("pageList", $pageList);

			//部门列表
			$bumenList = $this->_departModelModel->select();
			$this->assign("bumenList", $bumenList);

			$this->display("MksManager:toExcel");
		}
	}


	//重考设置
	public function mreks()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];


		if ($type && isset($id))
		{
			$filter = array();
			$filter['id'] = $id;

			if ($type == "del")
			{
				$data['state'] = 10;
				M()->table("m_score")->where($filter)->save($data);
			}
			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}

	/********************************试卷信息管理**********************************/
	public function mtaskpageList()
	{

		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];


		//条件过滤
		$filter = array();
		$name = trim($_REQUEST['testname']);//试卷名称
		if(!empty($name))
		{
			$filter['testname'] = array('like', '%'.$name.'%');
			$this->assign('name', $name);
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

		$totalCount = M()->table("m_page")->where($filter)->count();
		$list = M()->table("m_page")->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();

		if ($list)
		{
			foreach($list as &$vo)
			{
				$shiJuanId = $vo['id'];
				$taskPagelist = M()->table("m_task_page_list")->where("pageid = $shiJuanId")->find();
				$vo["taskname"] =  $taskPagelist["taskname"];
			}
		}


		//得到所有的管理员信息
		$adminList = M()->table("t_m_admin")->select();

		if($adminList){
			$adminArr = array();
			foreach($adminList as $adminPo)
			{
				$adminArr[$adminPo['id']] = $adminPo['true_name'];
			}
		}

		$this->assign("adminArr", $adminArr);

		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);

		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);
		$this->display('MksManager:taskpageList');
	}

	//添加考试试卷
	public function mtaskpageAdd()
	{
		if ($_REQUEST['submit'] == 1)
		{
			$taskid = $_REQUEST['taskid'];	 	  //选中的热点部位
			if (empty($taskid))
			{
				$array = array('statusCode'=>'300','message'=>"没有选择任务");
				echo json_encode($array);
				exit();
			}

			//将试卷的基本信息写到基本信息表中
			$date1['testname'] = trim($_REQUEST['testname']); //试卷名称
			//$date1['tid']  = $_SESSION['manager']['id'];
			$date1['time'] = trim($_REQUEST['time']); //考试时长
			$date1['starttime'] = strtotime(trim($_REQUEST['starttime'])); //开始时间
			$date1['endtime'] = strtotime(trim($_REQUEST['endtime'])); //结束时间
			$taskid = $_REQUEST['taskid']; //任务id

			$newTaskPageId = M()->table("m_page")->add($date1);

			if ($newTaskPageId)
			{
				$date2['pageid'] = $newTaskPageId; //试卷id
				$date2['taskid'] = $taskid; //任务编号
				//任务名称
				$task = M()->table("m_task_step")->where("taskid = $taskid")->limit(0,1)->find();
				$date2['taskname'] = $task['taskname'];

				$page = M()->table("m_task_page_list")->add($date2);
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'taskpageList','callbackType'=>'closeCurrent');
			}
			else
			{
				$array = array('statusCode'=>'300','message'=>"添加失败");
			}

			echo json_encode($array);
			exit();
		}
		else
		{
			$taskBaseInfos = M()->table("m_task_step as task")
				->field("task.taskid ,task.taskname")
				->group("task.taskid ,task.taskname")
				->select();

			//dump($taskBaseInfos);
			$this->assign('unitList', $taskBaseInfos);
		}

		$this->display('MksManager:taskpageAdd');
	}

	//删除考试试卷
	public function mtaskpageDel()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if ($type && $id)
		{
			$filter = array();
			$filter['pageid'] = $id;

			if ($type == "del")
			{
				M()->table("m_page")->where("id = $id")->delete();
				M()->table("m_task_page_list")->where($filter)->delete();
			}

			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}


	/********************************试卷分配管理**********************************/
	//试卷分配列表 （已分配的试卷信息）
	public function mtaskpagefpList()
	{
		//1.条件过滤（搜索）
		$filter = array();
		//$filter['state'] = array('eq', 0);

		$name = trim($_REQUEST['testname']);//试卷名称  有试卷名称的话，得到试卷的id
		$taskPageIdArr = array();
		if($name)
		{
			$taskPageList = M()->table("m_page")->where("testname like '%".$name."%'")->select();
			//试卷id
			if($taskPageList)
			{
				foreach($taskPageList as $taskListPo)
				{
					$taskPageIdArr[] = $taskListPo['id'];
				}
			}

			if(count($taskPageIdArr) > 0)
			{
				$filter['pageid'] = array('in', $taskPageIdArr);
			}
			else
			{
				$filter['pageid'] = array('eq', 0);
			}

			$this->assign('name', $name);
		}


		//2.条件排序
		$order = array();
		if($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['pageid'] = 'desc';
		}

		$list = M()->table("m_score")->where($filter)->order($order)->select();//dump($list);exit;
		//dump($list);
		if($list)
		{
			$kaoshiPageArr = array();
			$userArr = array();
			foreach($list as $key=>$value)
			{
				$kaoshiPageArr[] = $value['pageid']; //试卷id
				$userArr[$value['userid']][] = $value['userid']; //用户id
			}

			//试卷 (已分配的试卷信息)
			$kaoshiPageArr = array_unique($kaoshiPageArr);
			$kaoshiList = M()->table("m_page")->where(array("id"=>array("in", $kaoshiPageArr)))->select();
			$this->assign("kaoshiList", $kaoshiList);
		}
		$this->assign('list', $list);
		$this->display('MksManager:taskpagefpList');
	}

	//查看试卷分配人员
	public function mtaskpageFpuser()
	{
		$taskPageId = $_REQUEST['id'];

		$taskPagelist = M()->table("m_score")->where("pageid = $taskPageId")->select();
		//dump($taskPagelist);
		if($taskPagelist)
		{
			$userArr = array();
			foreach($taskPagelist as $value)
			{
				$userArr[] = $value['userid'];
			}

			$userList = $this->_userInfoModel->where(array('uid'=>array("in", $userArr)))->select();
			$this->assign("userList", $userList);
		}

		$this->display('MksManager:taskpageFpuser');
	}

	//试卷分配
	public function mtaskpagefpAdd()
	{
		if($_REQUEST['submit'] == 1)
		{
			$pageid = $_REQUEST["pageid"]; //试卷id
			$userIdArr = $_REQUEST["userId"];//用户id数组

			if(count($userIdArr) > 0)
			{
				foreach($userIdArr as $userid)
				{
					$data = array();
					$data['pageid'] = $pageid; //试卷id
					$data['userid'] = $userid; //用户id
					$data['state'] = 0; //状态
					$task = M()->table("m_task_page_list")->where("pageid = $pageid")->find();
					$data['taskid'] = $task["taskid"];
					M()->table("m_score")->add($data);
				}
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'mtaskpagefpList','callbackType'=>'closeCurrent');
			}
			else
			{
				$array = array('statusCode'=>'300','message'=>"没有选择学生");
			}

			echo json_encode($array);
			exit();
		}
		else
		{
			//1.试卷列表
			$pageKaoshiList = M()->table("m_score")->Distinct(true)->field('pageid')->select();
			//dump($pageKaoshiList);

			$usePaperIdArr = array();

			if($pageKaoshiList)
			{
				foreach($pageKaoshiList as $kaosiPo)
				{
					$usePaperIdArr[] = $kaosiPo['pageid'];
				}
			}
			//dump($usePaperIdArr);
			$filterkaoshi = array();
			$filterkaoshi['id'] = array("not in", $usePaperIdArr);

			if($usePaperIdArr)
			{
				$pageList = M()->table("m_page")->where($filterkaoshi)->order("id desc")->select();
			}
			else
			{
				$pageList = M()->table("m_page")->order("id desc")->select();
			}

			//dump($pageList);
			$this->assign("pageList", $pageList);

			//2.部门列表
			$bumenList = $this->_departModelModel->select();
			$this->assign("departList", $bumenList);

			//3.学员列表
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
		}

		$this->display("MksManager:taskpagefpAdd");
	}

	//试卷分配修改
	public function mtaskpagefpEdit()
	{
		if($_REQUEST['submit'] == 1)
		{
			$pageid = $_REQUEST['id'];//试卷id
			$userIdArr = $_REQUEST["userid"];//用户id数组

			if(count($userIdArr) > 0)
			{
				//首先删除已报名的改试卷的所有信息，然后和添加一个步骤
				M()->table("m_score")->where("pageid = $pageid")->delete();

				foreach($userIdArr as $userid)
				{
					$data = array();
					$data['pageid'] = $pageid;
					$data['userid'] = $userid;
					$data['state'] = 0;
					$task = M()->table("m_task_page_list")->where("pageid = $pageid")->find();
					$data['taskid'] = $task["taskid"];

					M()->table("m_score")->add($data);
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
		else
		{
			//1.试卷信息
			$id = $_REQUEST["id"];

			$ksFenpei = M()->table("m_score")->where("pageid = $id")->select();
			//学员id
			if($ksFenpei)
			{
				$userIdFpArr = array();
				foreach($ksFenpei as $fenpeiPo)
				{
					$userIdFpArr[] = $fenpeiPo['userid'];
				}
			}
			$pageInfo = M()->table("m_page")->where("id = $id")->find();
			$this->assign("pageInfo", $pageInfo);

			//2.部门列表
			$bumenList = $this->_departModelModel->select();
			$this->assign("departList", $bumenList);

			//3.学员
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
						$str .= " selected";
					}

					$str .= ">".$userPo['truename'];

					$departName = $userPo['departname'];
				}
			}
			$this->assign("userOption", $str);
		}

		$this->display("MksManager:taskpagefpEdit");
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

	//试卷分配删除
	public function mtaskpageFpDel()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['pageid'] = $id;

			if($type == "del")
			{
				M()->table("m_score")->where($filter)->delete();
			}

			$data = array("statusCode"=>"200","message"=>"操作成功",'navTabId'=>'mtaskpagefpList','callbackType'=>'forward');
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
		exit;
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



	function impUser(){
		if (!empty($_FILES)) {
			import("@.ORG.UploadFile");
			$config=array(
				'allowExts'=>array('xlsx','xls'),
				'savePath'=>'./Public/upload/',
				'saveRule'=>'time',
			);
			$upload = new UploadFile($config);
			if (!$upload->upload()) {
				$this->error($upload->getErrorMsg());
			} else {
				$info = $upload->getUploadFileInfo();

			}

			vendor("PHPExcel.PHPExcel");
			$file_name=$info[0]['savepath'].$info[0]['savename'];
			$objReader = PHPExcel_IOFactory::createReader('Excel5');
			$objPHPExcel = $objReader->load($file_name,$encode='utf-8');
			$sheet = $objPHPExcel->getSheet(0);
			$highestRow = $sheet->getHighestRow(); // 取得总行数
			$highestColumn = $sheet->getHighestColumn(); // 取得总列数
			for($i=3;$i<=$highestRow;$i++)
			{
				$data['account']= $data['truename'] = $objPHPExcel->getActiveSheet()->getCell("B".$i)->getValue();
				$sex = $objPHPExcel->getActiveSheet()->getCell("C".$i)->getValue();
				// $data['res_id']  = $objPHPExcel->getActiveSheet()->getCell("D".$i)->getValue();
				$data['class'] = $objPHPExcel->getActiveSheet()->getCell("E".$i)->getValue();
				$data['year'] = $objPHPExcel->getActiveSheet()->getCell("F".$i)->getValue();
				$data['city']= $objPHPExcel->getActiveSheet()->getCell("G".$i)->getValue();
				$data['company']= $objPHPExcel->getActiveSheet()->getCell("H".$i)->getValue();
				$data['zhicheng']= $objPHPExcel->getActiveSheet()->getCell("I".$i)->getValue();
				$data['zhiwu']= $objPHPExcel->getActiveSheet()->getCell("J".$i)->getValue();
				$data['jibie']= $objPHPExcel->getActiveSheet()->getCell("K".$i)->getValue();
				$data['honor']= $objPHPExcel->getActiveSheet()->getCell("L".$i)->getValue();
				$data['tel']= $objPHPExcel->getActiveSheet()->getCell("M".$i)->getValue();
				$data['qq']= $objPHPExcel->getActiveSheet()->getCell("N".$i)->getValue();
				$data['email']= $objPHPExcel->getActiveSheet()->getCell("O".$i)->getValue();
				$data['remark']= $objPHPExcel->getActiveSheet()->getCell("P".$i)->getValue();
				$data['sex']=$sex=='男'?1:0;
				$data['res_id'] =1;

				$data['last_login_time']=0;
				$data['create_time']=$data['last_login_ip']=$_SERVER['REMOTE_ADDR'];
				$data['login_count']=0;
				$data['join']=0;
				$data['avatar']='';
				$data['password']=md5('123456');
				M('Member')->add($data);

			}
			$this->success('导入成功！');
		}else
		{
			$this->error("请选择上传的文件");
		}


	}

}
?>