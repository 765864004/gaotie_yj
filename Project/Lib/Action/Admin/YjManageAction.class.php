<?php
class YjManageAction extends AdminAction
{
	public function __construct()
	{
		parent::__construct();
	}

	
	
	/*******************************************应急故障碰撞点*********************************************************/
	/**
	 * 应急故障碰撞点列表
	 * */
	public function yjCollideList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 25 : $_REQUEST['pageSize'];
	
		//条件过滤
		$filter = array();
	
	
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
	
	
		$totalCount = $this->_yjCollideModel->where($filter)->count();
		$list = $this->_yjCollideModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();
	
		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);
	
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);
	
		$this->display('YjManager:yjCollideList');
	}
	
	/**
	 * 添加应急故障处理流程
	 * */
	public function yjCollideAdd()
	{
		if($_POST)
		{
			extract($_POST);
	
			$data = array();
			$data['collname'] = trim($collname);
			$data['mapid'] = trim($mapid);
			$data['distance'] = trim($distance);
			$newId = $this->_yjCollideModel->add($data);
	
			if($newId)
			{
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'yjcollidelist','callbackType'=>'closeCurrent');
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
			$this->display('YjManager:yjCollideAdd');
		}
	}
	
	/**
	 * 添加应急故障处理流程
	 * */
	public function yjCollideEdit()
	{
		if($_POST)
		{
			extract($_POST);
	
			$data = array();
			$data['collname'] = trim($collname);
			$data['mapid'] = trim($mapid);
			$data['distance'] = trim($distance);
			
			$newId = $this->_yjCollideModel->where("autoid = $id")->save($data);
	
			if($newId)
			{
				$array = array('statusCode'=>'200','message'=>"编辑成功",'navTabId'=>'yjcollidelist','callbackType'=>'closeCurrent');
			}
			else
			{
				$array = array('statusCode'=>'300','message'=>"编辑失败");
			}
	
			echo json_encode($array);
			exit();
		}
		else
		{
			$id = $_REQUEST['id'];
			$po = $this->_yjCollideModel->where("autoid = $id")->find();
			$this->assign("po", $po);
			
			$this->display('YjManager:yjCollideEdit');
		}
	}
	
	/**
	 * 启用禁用删除应急故障处理流程
	 * */
	public function yjCollideProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];
	
		if($type && $id)
		{
			$filter = array();
			$filter['autoid'] = $id;
	
			if($type == "del")
			{
				$this->_yjCollideModel->where($filter)->delete();
			}
			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}
	
	/*******************************************应急故障处理流程*********************************************************/
	/**
	 * 应急故障处理流程列表
	 * */
	public function yjTipList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 25 : $_REQUEST['pageSize'];
	
		//条件过滤
		$filter = "";
		if(!empty($_REQUEST['id']))
		{
			$id = $_REQUEST['id'];
			$filter = " and tip.id = $id ";
			$this->assign('id', $id);
		}
		
		
		//条件排序
		//$order = array('id asc and childid asc');
	
		$totalCount = $this->_yjTipModel->count();
	//	$list = $this->_yjTipModel->where($filter)->page($currentPage)->limit($pageSize)->select();
		
		//echo $this->_yjTipModel->getLastSql();
		$start = ($currentPage-1)*$pageSize;
		//echo "select top $pageSize * from t_yj_tip as tip inner join t_yj_task as task on task.autoid = tip.id where tip.autoid not in 
		//		(select top $start tip.autoid from t_yj_tip as tip inner join t_yj_task as task on task.autoid = tip.id $filter  order by tip.id asc,tip.childid asc) $filter";
		//exit;
		
		$list = M()->query("select top $pageSize tip.*,task.cname from t_yj_tip as tip inner join t_yj_task as task on task.autoid = tip.id where tip.autoid not in 
				(select top $start tip.autoid from t_yj_tip as tip inner join t_yj_task as task on task.autoid = tip.id $filter  order by tip.id asc,tip.childid asc) $filter");

		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);
	
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);
		
		$gzlist = $this->_yjTaskModel->select();
		$this->assign('gzlist', $gzlist);
	
		$this->display('YjManager:yjTipList');
	}
	
	/**
	 * 添加应急故障处理流程
	 * */
	public function yjTipAdd()
	{
		if($_POST)
		{
			extract($_POST);
			
			$data = array();
			$data['id'] = trim($id);
			$data['childid'] = trim($childid);
			$data['txt'] = $_REQUEST['txt'];
			$data['hold1'] = $_REQUEST["hold1"];
			
			$newId = $this->_yjTipModel->add($data);

			if($newId)
			{
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'yjtiplist','callbackType'=>'closeCurrent');
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
			
			$list = $this->_yjTaskModel->select();
			$this->assign('list', $list);
			$this->display('YjManager:yjTipAdd');
		}
	}
	
	/**
	* 修改应急故障处理流程
	* */
	public function yjTipEdit()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				extract($_POST);
		
				$data = array();
				$data['id'] = trim($tid);
				$data['childid'] = trim($childid);
				$data['txt'] = trim($txt);
				$data['hold1'] = trim($hold1);
		
				$filter = array();
				$filter['autoid'] = $_REQUEST['id'];
		
				$result = $this->_yjTipModel->where($filter)->save($data);
		
				$array = array('statusCode'=>200,'message'=>"修改成功",'navTabId'=>'yjtiplist','callbackType'=>'closeCurrent');
			}
			else
			{
				$array = array('statusCode'=>'300','message'=>"验证失败");
			}
			echo json_encode($array);
			exit();
		}
		else
		{
			$id = $_REQUEST['id'];
	
			$filter = array();
			$filter['autoid'] = $id;
			$Po = $this->_yjTipModel->where($filter)->find();
			
			$list = $this->_yjTaskModel->select();
			$this->assign('list', $list);
	
			$this->assign('Po', $Po);
	
			$this->display('YjManager:yjTipEdit');
		}
	}
	
	/**
	* 启用禁用删除应急故障处理流程
	* */
	public function yjTipProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];
	
		if($type && $id)
		{
			$filter = array();
			$filter['autoid'] = $id;
	
			if($type == "del")
			{
				$this->_yjTipModel->where($filter)->delete();
			}
			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}
	
	/*******************************************应急故障状态初始化*********************************************************/
	/**
	 * 列表
	 * */
	public function yjInitList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 25 : $_REQUEST['pageSize'];
		
		//条件过滤
		$filter = array();
	
		
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
	
		
		$totalCount = $this->_yjInitModel->where($filter)->count();
		$list = $this->_yjInitModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();
		
		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);
		
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);

		$this->display('YjManager:yjInitList');
	}

	/**
	 * 添加应急故障处理流程
	 * */
	public function yjInitAdd()
	{
		if($_POST)
		{
			
			extract($_POST);
			
			$data = array();
			$data['id'] = trim($id);
			$data['pid'] = trim($unitid);
			$data['pstate'] = trim($pstate);

			$newId = $this->_yjInitModel->add($data);

			if($newId)
			{
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'yjinitlist','callbackType'=>'closeCurrent');
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
			$list = $this->_yjTaskModel->select();
			$this->assign('list', $list);
			
			//CRH1车头部件列表
			$unitList = $this->_unitModel->where("CRH & 1 = 1 and area & 1 = 1")->order("id asc")->select();
			$this->assign('unitList', $unitList);
			
			$this->display('YjManager:yjInitAdd');
		}
	}

	/**
	 * 修改应急故障处理流程
	 * */
	public function positionEdit()
	{
// 		if($_POST)
// 		{
// 			if(M()->autoCheckToken($_POST))
// 			{
// 				extract($_POST);
				
// 				$data = array();
// 				$data['modelname'] = trim($modelname);
// 				$data['tipname'] = trim($tipname);
// 				$data['randname'] = trim($randname);
				
// 				$data['toolnum'] = trim($toolnum);
			
				
// 				//dump($data);exit;
				
// 				$filter = array();
// 				$filter['id'] = $_REQUEST['id'];
// 				$result = $this->_yjInitModel->where($filter)->save($data);

// 				$array = array('statusCode'=>200,'message'=>"修改成功",'navTabId'=>'positionlist','callbackType'=>'closeCurrent');
// 			}
// 			else
// 			{
// 				$array = array('statusCode'=>'300','message'=>"验证失败");
// 			}
// 			echo json_encode($array);
// 			exit();
// 		}
// 		else
// 		{
// 			$id = $_REQUEST['id'];

// 			$filter = array();
// 			$filter['id'] = $id;
// 			$positionPo = $this->_positionModel->where($filter)->find();
// 			$this->assign('positionPo', $positionPo);
			
// 			$result = $this->_toolModel->select();
// 			$this->assign('toolList', $result);

// 			$this->display('YjManager:yjInitEdit');
// 		}
	}

	/**
	 * 启用禁用删除应急故障处理流程
	 * */
	public function yjInitProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['autoid'] = $id;

			if($type == "del")
			{
				$this->_yjInitModel->where($filter)->delete();
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
	 * 应急故障考试列表
	* */
	public function yjKsList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 25 : $_REQUEST['pageSize'];
	
		//条件过滤
		$filter = array();
	
	
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
	
	
		$totalCount = $this->_yjKsModel->where($filter)->count();
		$list = $this->_yjKsModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();
		if($list)
		{
			foreach($list as $key=>$value)
			{
				$stuPo = $this->_userInfoModel->where("uid = {$value['stuid']}")->find();
				$list[$key]['stuname'] = $stuPo['uname'];
			}
		}
		
	
		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);
	
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);
	
		$this->display('YjManager:yjKsList');
	}
	
	/***
	 * 应急故障考试添加
	* */
	public function yjKsAdd()
	{
		if($_POST)
		{
			extract($_POST);
	
			if(empty($stuid))
			{
				$array = array('statusCode'=>'300','message'=>"没有选择座位");
				echo json_encode($array);
				exit();
			}
			
			$data = array();
			$data['name'] = trim($name);
			$data['id'] = $taskId;
			
			$yjTaskPo = $this->_yjTaskModel->where("autoid = $taskId")->find();
			$data['CRH'] = $yjTaskPo['CRH'];
			
			for($i=0;$i<count($stuid); $i++)
			{
				$data['stuid'] = trim($stuid[$i]);
				$data['macip'] = -1062731511+$stuid[$i];
				$newId = $this->_yjKsModel->add($data);
				
				$dataStu = array();
				$dataStu['hold1'] = $taskId;
				$stuUp = $this->_userInfoModel->where("uid = {$stuid[$i]}")->save($dataStu);
			}
	
			//dump($data);exit;
			
	
			if($newId)
			{
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'yjkslist','callbackType'=>'closeCurrent');
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
			$yjTaskPos = $this->_yjTaskModel->select();
			$this->assign('yjTaskPos', $yjTaskPos);
			
			$stuList = $this->_userInfoModel->where("state = 1")->select();
			$this->assign('list', $stuList);
			
			$this->display('YjManager:yjKsAdd');
		}
	}
	
	/***
	 * 应急故障考试编辑
	* */
	public function yjKsEdit()
	{
		$this->display('TaskManager:taskKsEdit');
	}
	
	/***
	 * 应急故障考试处理
	* */
	public function yjKsProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];
	
		if($type && $id)
		{
			$filter = array();
			$filter['autoid'] = $id;
	
			if($type == "del")
			{
				$yjKsPo = $this->_yjKsModel->where($filter)->find();
			//	$yjUserId = 
				
				$this->_yjKsModel->where($filter)->delete();
				
				// 然后重置用户表的taskid信息
				
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