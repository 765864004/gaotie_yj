<?php
class KsManageAction extends AdminAction{

	public function __construct(){

		parent::__construct();
	}

	/********************************成绩信息管理**********************************/

	public function taskResultList() {

		$currentPage = empty( $_REQUEST ['currentPage'] ) ? 1 : $_REQUEST ['currentPage'];
		$pageSize = empty( $_REQUEST ['pageSize'] ) ? 200 : $_REQUEST ['pageSize'];

		//1.试卷id，没有涉及到多表的查询，直接eq条件查询
		$paperid = trim ( $_REQUEST ['paperid'] );
		if ( !empty ( $paperid ) ) {
			$filter ['paperid'] = array ( 'eq', $paperid );
			$this->assign ( "paperid", $paperid );
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
			$filter ['uid'] = array ( 'in', $userIds );
		}
		else {
			//如果选中了部门或者真实姓名但是又没有查询到学生的话，设置查询的学生id为0，即查不到。
			if ( $departId || $truename ) {
				$filter ['uid'] = array ( 'eq', 0 );
			}
		}

		//条件排序
		$order = array ();
		if ( $_REQUEST ['_order'] && $_REQUEST ['_sort'] ) {
			$order [$_REQUEST ['_order']] =  $_REQUEST ['_sort'];
		}
		else {
			$order ['paperid'] = 'desc';
		}

		$totalCount = $this->_kaoshiModel->where ( $filter )->count ();
		$list = $this->_kaoshiModel->where ( $filter )->order ( $order )->page ( $currentPage )->limit ( $pageSize )->select ();
		$this->assign ( 'totalCount', $totalCount );

		//将成绩表里的学员id、试卷id 单独放到数组中 (保持唯一)
		if ( $list ) {
			$kaoshiPageArr = array ();
			$userArr = array ();
			foreach ( $list as $key => $value ) {
				$kaoshiPageArr [] = $value ['paperid'];//考试试卷id
				$userArr [] = $value ['uid'];//用户id
			}

			//试卷名称
			$kaoshiPageArr = array_unique ( $kaoshiPageArr);//试卷id 去掉重复
			$userArr = array_unique ($userArr);

			$taskKaoshiArr = array ();
			$kaoshiList = $this->_taskPageListMoel->where ( array ( "autoid" => array ( "in", $kaoshiPageArr ) ) )->select ();
			if ( $kaoshiList ) {
				foreach ( $kaoshiList as $kaoshiPo ) {
					$taskKaoshiArr [$kaoshiPo['autoid']] = $kaoshiPo;
				}
			}
			//dump($kaoshiPageArr);
			$this->assign ( "taskKaoshiArr", $taskKaoshiArr );

			$userList = $this->_userInfoModel->where ( array( 'uid' => array ( "in", $userArr ) ) )->select ();
			if ($userList) {
				foreach ( $userList as $userPo ) {
					$userKaoshiArr [$userPo['uid']] = $userPo;
				}
			}
			$this->assign ( "userKaoshiArr", $userKaoshiArr );
		}

		$this->assign ( 'list', $list );

		//得到所有的试卷(成绩表里的试卷id 排除重复)
		$pageKaoshiList = $this->_kaoshiModel->Distinct ( true )->field ( 'paperid' )->select ();

		$usePaperIdArr = array ();

		if ( $pageKaoshiList ) {
			foreach ( $pageKaoshiList as $kaosiPo ) {
				$usePaperIdArr [] = $kaosiPo ['paperid'];
			}
		}
		$filterkaoshi = array ();
		$filterkaoshi ['autoid'] = array("in", $usePaperIdArr);

		if ( $usePaperIdArr ) {
			$pageList = $this->_taskPageListMoel->where ( $filterkaoshi )->order ( "autoid desc" )->select();
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

		$this->display ( 'KsManager:taskResultList' );
	}

	//导出成绩到execl表中
	public function toExcel()
	{
		if ($_REQUEST['submit'] == 1)
		{
			$paperid = trim($_REQUEST['paperid']);//试卷id
			$filter['paperid'] = array('eq', $paperid);

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

			//条件排序
			$order = array();
			$order['paperid'] = 'desc';

			//满足条件的考试成绩
			$list = $this->_kaoshiModel->where($filter)->order($order)->select();

			if ($list)
			{
				$kaoshiPageArr = array();
				$userArr = array();
				foreach ($list as $key=>$value)
				{
					$kaoshiPageArr[] = $value['paperid'];//考试试卷id
					$userArr[] = $value['uid'];//用户id
				}

				//试卷列表
				$kaoshiPageArr = array_unique($kaoshiPageArr);
				$userArr = array_unique($userArr);

				$taskKaoshiArr = array();
				$kaoshiList = $this->_taskPageListMoel->where(array("autoid"=>array("in", $kaoshiPageArr)))->select();
				if ($kaoshiList)
				{
					foreach ($kaoshiList as $kaoshiPo)
					{
						$taskKaoshiArr[$kaoshiPo['autoid']] = $kaoshiPo;
					}
				}
				$this->assign("taskKaoshiArr", $taskKaoshiArr);

				//考试成绩封装
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
					$dataResult[$key][] = $userKaoshiArr[$vo['uid']]['truename'];
					if ($userKaoshiArr[$vo['uid']]['usex'] == 0)
					{
						$dataResult[$key][] = "男";
					}
					else
					{
						$dataResult[$key][] = "女";
					}
					$dataResult[$key][] = $bumenArr[$userKaoshiArr[$vo['uid']]['departid']];
					$dataResult[$key][] = $taskKaoshiArr[$vo['paperid']]['name'];
					$dataResult[$key][] = date("Y-m-d H:i:s", $vo['kstime']);
					$dataResult[$key][] = $vo['defen'];
					switch($vo['state']){
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


				$fileName = "test_excel";
				$headArr = array("学生姓名","性别","部门", "试卷名称", "考试时间", "考试得分", "状态");
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

			//得到所有的试卷
			$pageKaoshiList = $this->_kaoshiModel->Distinct(true)->field('paperid')->select();

			$usePaperIdArr = array();

			if ($pageKaoshiList)
			{
				foreach ($pageKaoshiList as $kaosiPo)
				{
					$usePaperIdArr[] = $kaosiPo['paperid'];
				}
			}
			$filterkaoshi = array();
			$filterkaoshi['autoid'] = array("in", $usePaperIdArr);

			if ($usePaperIdArr)
			{
				$pageList = $this->_taskPageListMoel->where($filterkaoshi)->order("autoid desc")->select();
			}
			else
			{
				$pageList = array();
			}
			$this->assign("pageList", $pageList);

			//部门列表
			$bumenList = $this->_departModelModel->select();
			$this->assign("bumenList", $bumenList);

			$this->display("KsManager:toExcel");
		}
	}


	//重考设置
	public function reks()
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
				$this->_kaoshiModel->where($filter)->save($data);
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
	public function taskpageList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];


		//条件过滤
		$filter = array();
		$name = trim($_REQUEST['name']);//试卷名称
		if(!empty($name))
		{
			$filter['name'] = array('like', '%'.$name.'%');
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
			$order['autoid'] = 'desc';
		}

		$totalCount = $this->_taskPageListMoel->where($filter)->count();
		$list = $this->_taskPageListMoel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();
		//dump($list);

		if ($list)
		{
			$araeArr = array();
			foreach($list as $taskPage)
			{
				$shiJuanId = $taskPage['autoid'];
				$areaPo = $this->_taskPageMoel->where("pindex = $shiJuanId")->find();
				$araeArr[$taskPage['autoid']] =  $areaPo;
			}
		}

		//dump($araeArr);
		$this->assign("araeArr", $araeArr);

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

		$this->display('KsManager:taskpageList');
	}

	//添加考试试卷
	public function taskpageAdd()
	{
		if ($_REQUEST['submit'] == 1)
		{
			$unitArr = $_REQUEST['unitA'];	 	  //选中的热点部位
			$guzhangNum = $_REQUEST['guzhangNum'];//故障的个数
			if (empty($unitArr))
			{
				$array = array('statusCode'=>'300','message'=>"没有选择热点部位");
				echo json_encode($array);
				exit();
			}
			if (count($unitArr) < $guzhangNum)
			{
				$array = array('statusCode'=>'300','message'=>"设置的故障的个数不能大于热点部位的个数");
				echo json_encode($array);
				exit();
			}

			//将试卷的基本信息写到基本信息表中
			$date1['name'] = trim($_REQUEST['name']);
			$date1['tid']  = $_SESSION['manager']['id'];
			$date1['zongfen'] = trim($_REQUEST['zongfen']);
			$date1['kstime0'] = trim($_REQUEST['kstime0'])*60;
			$date1['kstime1'] = strtotime(trim($_REQUEST['kstime1']));
			$date1['kstime2'] = strtotime(trim($_REQUEST['kstime2']));

			$newTaskPageId = $this->_taskPageListMoel->add($date1);

			$isRandom = $_REQUEST["isRandom"];//答案是否随机 1:是 0:否  貌似没什么用

			if ($newTaskPageId)
			{
				//将试卷中的所有的部件信息写入到考试试卷表中 task_page_2013

				$data = array();
				$data["pindex"] = $newTaskPageId;//试卷编号
				$data['CRH'] = $_REQUEST['CRH'];
				$data['level'] = $_REQUEST['level'];
				$data['area'] = $_REQUEST['area'];

				//如果设置的故障的个数大于0，在热点中随机故障个数的数设置故障的值
				$guzhangUnitArr = array ();
				if ( $guzhangNum > 0 )
				{
					$guzhangUnitArrTmp = array_rand ( $unitArr, $guzhangNum );
					foreach ( $guzhangUnitArrTmp as $value )
					{
						$guzhangUnitArr[] = $unitArr[$value];
					}
				}

				for ( $i = 0; $i<count($unitArr); $i++ )//循环每一个部件信息
				{
					$data['unit'] = $unitArr[$i];
					$result = $this->_taskPageMoel->add($data);

					if ($result)
					{
						//插入试题结果到结果表
						$filter = array ();
						$filter['CRH'] 		= $_REQUEST['CRH'];
						$filter['level'] 	= $_REQUEST['level'];
						$filter['area'] 	= $_REQUEST['area'];
						$filter['unit'] 	= $unitArr[$i];

						$taskInfoArr = $this->_taskbaseModel->where ( $filter )->order ( 'childid asc' )->select (); //1.先从task表中取得taskid和tool

						if ( $taskInfoArr )
						{
							//取任务表里面的选择题，然后随机其中的一个任务
							$taskXztArr = array ();
							for ( $j = 0; $j < count ( $taskInfoArr ); $j++ )
							{
								if ( $taskInfoArr[$j]['opttype'] == 1)
								{
									$taskXztArr [] = $taskInfoArr[$j];
								}
							}
							if ( $taskXztArr )
							{
								$taskRandArr = $taskXztArr [array_rand($taskXztArr, 1)];//首先随机一个任务表以备随机答案使用。。
							}

							for ( $j = 0; $j < count ( $taskInfoArr ); $j++ )
							{
								$taskStuInfo = $this->_taskstuModel->where ("taskid = ".$taskInfoArr[$j]['autoid'])->find (); //2.从t_task_stu表中取得ret	rettxt
								//dump(1);exit;
								if ( $taskStuInfo )
								{
									$dataResult = array ();

									$dataResult['pindex'] = $newTaskPageId;
									$dataResult['taskid'] = $taskInfoArr [$j]['autoid'];
									$dataResult['opttype'] = $taskInfoArr [$j]['opttype'];
									$dataResult['tool'] = $taskInfoArr [$j]['tool'];

									if ( $taskInfoArr[$j]['opttype'] == 1)//1：选择题 3：对话题
									{
										//如果设置的故障的个数为0, 在正常的答案里面随机一个retstate的索引保存在ret中
										if ( $guzhangNum == 0 )
										{
											$tmpRetArr = array ();
											for ( $k = 1; $k <= $taskInfoArr[$j]['tnum']; $k++ )
											{
												if ( $taskInfoArr[$j]['retstate'.$k] == 0 )
												{
													$tmpRetArr[] = $k;
												}
											}
											$dataResult['ret'] = $tmpRetArr [array_rand ( $tmpRetArr, 1 )];
										}
										else
										{
											//dump($guzhangUnitArr);
											//dump(in_array( $unitArr[$i], $guzhangUnitArr));
											//dump($taskRandArr['autoid'] == $taskInfoArr [$j]['autoid']);
											//echo "<br/>";echo "<br/>";echo "<br/>";
											//如果这个热点部件在随机到的部件id里面，那么这个热点部件里面有一个试题需要选择错误的答案
											if ( in_array( $unitArr[$i], $guzhangUnitArr) && ($taskRandArr['autoid'] == $taskInfoArr [$j]['autoid']))
											{

												$tmpRetArr = array ();//保存错误答案
												$tmpRetErrArr = array ();
												for ( $k = 1; $k <= $taskInfoArr[$j]['tnum']; $k++ )
												{
													if ( $taskInfoArr[$j]['retstate'.$k] == 1 )
													{
														$tmpRetArr[] = $k;
													}
													if ( $taskInfoArr[$j]['retstate'.$k] == 0 )
													{
														$tmpRetErrArr[] = $k;
													}
												}

												if ( count( $tmpRetArr ) > 0 )
												{
													$dataResult['ret'] = $tmpRetArr [array_rand ( $tmpRetArr, 1 )];
												}
												else
												{
													$dataResult['ret'] = $tmpRetErrArr [array_rand ( $tmpRetErrArr, 1 )];
												}

											}
											else //否则随机取一个正确的答案
											{
												$tmpRetArr = array ();//保存正确答案
												$tmpRetErrArr = array ();
												for ( $k = 1; $k <= $taskInfoArr[$j]['tnum']; $k++ )
												{
													if ( $taskInfoArr[$j]['retstate'.$k] == 0 )
													{
														$tmpRetArr[] = $k;
													}

													if ( $taskInfoArr[$j]['retstate'.$k] == 1 )
													{
														$tmpRetErrArr[] = $k;
													}
												}
												if ( count( $tmpRetArr ) > 0 )
												{
													$dataResult['ret'] = $tmpRetArr [array_rand ( $tmpRetArr, 1 )];
												}
												else
												{
													$dataResult['ret'] = $tmpRetErrArr [array_rand ( $tmpRetErrArr, 1 )];
												}
											}
										}
									}
									else
									{
										$dataResult['ret'] = $taskStuInfo ['ret'];
									}

									$dataResult['rettxt'] = $taskStuInfo ['rettxt'];
									$dataResult['material'] = $taskInfoArr [$j] ['material'];

									$result1 = $this->_taskPageResultMoel->add($dataResult);
								}
							}
						}
					}
				}

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
		}

		$this->display('KsManager:taskpageAdd');
	}

	//删除考试试卷
	public function taskpageDel()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if ($type && $id)
		{
			$filter = array();
			$filter['pindex'] = $id;

			if ($type == "del")
			{
				$this->_taskPageListMoel->where("autoid = $id")->delete();
				$this->_taskPageMoel->where($filter)->delete();
				$this->_taskPageResultMoel->where($filter)->delete();
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
	public function taskpagefpList()
	{
		//条件过滤
		$filter = array();
		$filter['state'] = array('eq', 0);

		$name = trim($_REQUEST['name']);//试卷名称  有试卷名称的话，得到试卷的id
		$taskPageIdArr = array();
		if($name)
		{
			$taskPageList = $this->_taskPageListMoel->where("name like '%".$name."%'")->select();
			if($taskPageList)
			{
				foreach($taskPageList as $taskListPo)
				{
					$taskPageIdArr[] = $taskListPo['autoid'];
				}
			}

			if(count($taskPageIdArr) > 0)
			{
				$filter['paperid'] = array('in', $taskPageIdArr);
			}
			else
			{
				$filter['paperid'] = array('eq', 0);
			}

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
			$order['paperid'] = 'desc';
		}

		$list = $this->_kaoshiModel->where($filter)->order($order)->select();//dump($list);exit;
		if($list)
		{
			$kaoshiPageArr = array();
			$userArr = array();
			foreach($list as $key=>$value)
			{
				$kaoshiPageArr[] = $value['paperid'];
				$userArr[$value['paperid']][] = $value['uid'];
			}

			//试卷名称
			$kaoshiPageArr = array_unique($kaoshiPageArr);
			$kaoshiList = $this->_taskPageListMoel->where(array("autoid"=>array("in", $kaoshiPageArr)))->select();
			$this->assign("kaoshiList", $kaoshiList);
		//	dump($kaoshiList);
			//试卷分配给的用户
			$userKaoshiArr = array();
			foreach($userArr as $key=>$value)
			{
				$userList = $this->_userInfoModel->where(array('uid'=>array("in", $value)))->select();
				if($userList)
				{
					foreach($userList as $userPo)
					{
						$userKaoshiArr[$key][] = $userPo["truename"];
					}
				}
			}
		}
		//dump($kaoshiList);

		$this->assign("userKaoshiArr", $userKaoshiArr);


		$this->assign('list', $list);


		$this->display('KsManager:taskpagefpList');
	}

	//查看试卷分配
	public function taskpageFpuser()
	{
		$taskPageId = $_REQUEST['id'];

		$taskPagelist = $this->_kaoshiModel->where("paperid = $taskPageId")->select();
		if($taskPagelist)
		{
			$userArr = array();
			foreach($taskPagelist as $value)
			{
				$userArr[] = $value['uid'];
			}

			$userList = $this->_userInfoModel->where(array('uid'=>array("in", $userArr)))->select();
			$this->assign("userList", $userList);
		}

		$this->display('KsManager:taskpageFpuser');
	}

	//试卷分配
	public function taskpagefpAdd()
	{
		if($_REQUEST['submit'] == 1)
		{
			$paperid = $_REQUEST["paperid"];
			$userIdArr = $_REQUEST["userId"];//用户id数组

			if(count($userIdArr) > 0)
			{
				foreach($userIdArr as $userid)
				{
					$data = array();
					$data['paperid'] = $paperid;
					$data['uid'] = $userid;
					$data['state'] = 0;

					$this->_kaoshiModel->add($data);
				}
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'taskpagefpList','callbackType'=>'closeCurrent');
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
			//试卷列表
			$pageKaoshiList = $this->_kaoshiModel->Distinct(true)->field('paperid')->select();

			$usePaperIdArr = array();

			if($pageKaoshiList)
			{
				foreach($pageKaoshiList as $kaosiPo)
				{
					$usePaperIdArr[] = $kaosiPo['paperid'];
				}
			}
			$filterkaoshi = array();
			$filterkaoshi['autoid'] = array("not in", $usePaperIdArr);

			if($usePaperIdArr)
			{
				$pageList = $this->_taskPageListMoel->where($filterkaoshi)->order("autoid desc")->select();
			}
			else
			{
				$pageList = $this->_taskPageListMoel->order("autoid desc")->select();
			}

			$this->assign("pageList", $pageList);

			//部门列表
			$bumenList = $this->_departModelModel->select();
			$this->assign("departList", $bumenList);

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

		$this->display("KsManager:taskpagefpAdd");
	}

	//试卷分配修改
	public function taskpagefpEdit()
	{
		if($_REQUEST['submit'] == 1)
		{
			$paperid = $_REQUEST['id'];//试卷id
			$userIdArr = $_REQUEST["userId"];//用户id数组

			if(count($userIdArr) > 0)
			{
				//首先删除已报名的改试卷的所有信息，然后和添加一个步骤
				$this->_kaoshiModel->where("paperid = $paperid")->delete();

				foreach($userIdArr as $userid)
				{
					$data = array();
					$data['paperid'] = $paperid;
					$data['uid'] = $userid;
					$data['state'] = 0;

					$this->_kaoshiModel->add($data);
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
			$id = $_REQUEST["id"];

			$ksFenpei = $this->_kaoshiModel->where("paperid = $id")->select();
			if($ksFenpei)
			{
				$userIdFpArr = array();
				foreach($ksFenpei as $fenpeiPo)
				{
					$userIdFpArr[] = $fenpeiPo['uid'];
				}
			}
			$pageInfo = $this->_taskPageListMoel->where("autoid = $id")->find();
			$this->assign("pageInfo", $pageInfo);

			//部门列表
			$bumenList = $this->_departModelModel->select();
			$this->assign("departList", $bumenList);

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

		$this->display("KsManager:taskpagefpEdit");
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
	public function taskpageFpDel()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['paperid'] = $id;

			if($type == "del")
			{
				$this->_kaoshiModel->where($filter)->delete();
			}

			$data = array("statusCode"=>"200","message"=>"操作成功",'navTabId'=>'taskpagefpList','callbackType'=>'closeCurrent');
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
}
?>