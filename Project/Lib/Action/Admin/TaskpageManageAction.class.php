<?php
class TaskpageManageAction extends AdminAction
{
	public function __construct()
	{
		parent::__construct();
	}

	/**
	 * 试卷列表
	 * */
	public function taskPageList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 25 : $_REQUEST['pageSize'];
	
		//条件过滤
		$filter = array();
		if($_REQUEST['pindex'])
		{
			$filter['pindex'] = $_REQUEST['pindex'];
			$this->assign('pindex', $_REQUEST['pindex']); 
		}
	
		//条件排序
		$order = array();
		if(!empty($_REQUEST['state']))
		{
			$filter['state'] =  $_REQUEST['state'];
			$this->assign('state', $_REQUEST['state']);
		}
	
		//条件排序
		$order = array();
		if($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['autoid'] = ' desc';
		}
	
	
		$totalCount = $this->_taskPageModel->where($filter)->count();
		$list = $this->_taskPageModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();
	
		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);
	
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);

		$this->display('TaskpageManager:taskPageList');
	}

	/**
	 * 试卷添加
	 * */
	public function addTaskPage()
	{
		if($_POST)
		{
			$unitArr = $_REQUEST['unitA'];
			if(empty($unitArr))
			{
				$array = array('statusCode'=>'300','message'=>"没有选择部件");
				echo json_encode($array);
				exit();
			}
			
			$name = trim($_REQUEST['name']);
			$pindex = trim($_REQUEST['pindex']);
			$CRH = trim($_REQUEST['CRH']);
			$level = trim($_REQUEST['level']);
			$area = trim($_REQUEST['area']);
			
			//检测是否已经有同类型的试卷
			$isPageExists = $this->_taskPageModel->where("pindex = $pindex")->find();
			if($isPageExists)
			{
				$array = array('statusCode'=>'300','message'=>"该试卷已经存在");
				echo json_encode($array);
				exit();
			}
			
			
			$data = array();
			$data['name'] = $name;
			$data['pindex'] = $pindex;
			$data['CRH'] = $CRH;
			$data['level'] = $level;
			$data['area'] = $area;
			
			for($i = 0; $i<count($unitArr); $i++)
			{	
				$data['unit'] = $unitArr[$i];	
				$result = $this->_taskPageModel->add($data);
				
				if($result)
				{
					//插入试题结果到结果表
					$filter = array();
					$filter['CRH'] = $CRH;
					$filter['level'] = $level;
					$filter['area'] = $area;
					$filter['unit'] = $unitArr[$i];
					
					$taskInfoArr = $this->_taskbaseModel->where($filter)->order('childid asc')->select(); //1.先从task表中取得taskid和tool
					
					
					if($taskInfoArr)
					{
						foreach($taskInfoArr as $taskInfoArr)
						{
							$taskStuInfo = $this->_taskstuModel->where("taskid = $taskInfoArr[autoid]")->find(); //2.从t_task_stu表中取得ret	rettxt
							
							if($taskStuInfo)
							{
								$dataResult = array();
							
								$dataResult['pindex'] = $pindex;
								$dataResult['taskid'] = $taskInfoArr['autoid'];
								$dataResult['opttype'] = $taskInfoArr['opttype'];
								$dataResult['tool'] = $taskInfoArr['tool'];
								$dataResult['ret'] = $taskStuInfo['ret'];
								$dataResult['rettxt'] = $taskStuInfo['rettxt'];
							
								$result1 = $this->_taskPageResultModel->add($dataResult);
							}
						}
						
					}
				}
			}

			$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'taskpagelist','callbackType'=>'closeCurrent');
			
			echo json_encode($array);
			exit();
		}
		else
		{
			$taskBaseInfos = $this->_taskbaseModel->where("area & 1 = 1 and level & 1 = 1 and CRH & 1 = 1")->select();
			
			$unitVos = array();
			if($taskBaseInfos)
			{
				$unitIdArrs = array();
			
				$filter = array();
				foreach($taskBaseInfos as $vo)
				{
					if(in_array($vo['unit'], $unitIdArrs))
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
			
			$this->display('TaskpageManager:addTaskPage');
		}
	}

	/**
	 * 启用禁用删除试卷
	 * */
	public function taskPageProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['pindex'] = $id;

			if($type == "del")
			{	
				$this->_taskPageModel->where($filter)->delete();
				$this->_taskPageResultModel->where($filter)->delete();
				//同时删除每个试题对应的答案
			}

			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}
	
	/***
	 * 试卷学生列表
	* */
	public function pageUserList()
	{
		$SJTypeArr = C('SJTYPEARR');
		
		$userTypeArr = array();
		if(count($SJTypeArr) > 0)
		{
			foreach($SJTypeArr as $key=>$value)
			{
				$userTypeArr[$key] = $this->_userInfoModel->where("kspage = $key and state = 1")->select(); 
			}
		}
		
		$this->assign("userTypeArr", $userTypeArr);
		$this->display("TaskpageManager:pageUserList");
	}
	
	/***
	 * 分配试卷
	 * */
	public function fpKsPage()
	{
		if($_POST['submit'] == 1)
		{
			$ksPageType = $_REQUEST['pindex'];
			$userId = $_REQUEST['uid'];
			
			$data = array();
			$data['kspage'] = $ksPageType;
			$data['hold2'] = $_REQUEST['hold2'];
			
			//先删除已经分配的学生的试卷类型
			$this->_userInfoModel->where("kspage = $ksPageType")->setField("kspage",'0');
			if(count($userId)>0)
			{
				foreach($userId as $value)
				{
					$this->_userInfoModel->where("uid = $value")->save($data);
				}
			}
			$data = array("statusCode"=>"200","message"=>"操作成功",'navTabId'=>'pageUserList','callbackType'=>'closeCurrent');
			echo json_encode($data);
		}
		else
		{	
			$pIndex = $_REQUEST['id'];
			$this->assign('pindex', $pIndex);
			
			$stuList = $this->_userInfoModel->where("state = 1")->select();
			$this->assign('list', $stuList);
			
			$this->display("TaskpageManager:fpKsPage");
		}
	}
	
	/**
	 * 启用禁用删除试卷
	 * */
	public function fpKsProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];
	
		if($type && $id)
		{
			if($type == "del")
			{
				$userTypeArr = $this->_userInfoModel->where("kspage = $id")->select();
				if($userTypeArr)
				{
					foreach($userTypeArr as $value)
					{
						$data = array();
						$data['kspage'] = 0;
						$this->_userInfoModel->where("uid = $value[uid]")->save($data);
					}
				}
			}
	
			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}
}
?>