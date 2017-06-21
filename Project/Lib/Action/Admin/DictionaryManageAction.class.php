<?php
/**
 *
 * 检修类别: 一级检修:1   二级检修:2   应急检修:4  巡    检:8
 * 部位：    车顶:1   车底:2   司机室:4   车内:8   车外:16
 * 工具用途：特殊工具:1   测量:8  照明:16   清洁:32
 * */


class DictionaryManageAction extends AdminAction
{
	public function __construct()
	{
		parent::__construct();
	}

	/*******************************************检修工具管理*********************************************************/
	/**
	 * 工具列表
	 * */
	public function toolList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];
		
		//条件过滤
		$filter = array();
		$tipname = trim($_REQUEST['tipname']);
		if(!empty($tipname))
		{
			$filter['tipname'] = array('like', '%'.$tipname.'%');
			$this->assign('tipname', $tipname);
		}

		//条件排序
		$order = array();
		if($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['id'] = ' desc';
		}


		$totalCount = $this->_toolModel->where($filter)->count();
		$list = $this->_toolModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();

		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);

		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);

		$this->display('DictionaryManager:toolList');
	}


	/**
	 * 添加工具
	 * */
	public function toolAdd()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				extract($_POST);

				$data = array();
				$data['id'] = trim($id);
				$data['picname'] = trim($picname);
				$data['mousename'] = trim($mousename);
				$data['tipname'] = trim($tipname);

				$usetypei	=	0;
				for($i=0;$i<count($uselev);$i++)
				{
					$usetypei	= $usetypei | $uselev[$i];
				}

				$data['uselev'] = $usetypei;

				$data['usetype'] = $usetype;

				$newId = $this->_toolModel->add($data);

				if($newId)
				{
					$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'toollist','callbackType'=>'closeCurrent');
				}
				else
				{
					$array = array('statusCode'=>'300','message'=>"添加失败");
				}
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


			$this->display('DictionaryManager:toolAdd');
		}
	}


	/**
	 * 修改工具
	 * */
	public function toolEdit()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				extract($_POST);

				$data = array();
				$data['picname'] = trim($picname);
				$data['mousename'] = trim($mousename);
				$data['tipname'] = trim($tipname);

				$usetypei	=	0;
				for($i=0;$i<count($uselev);$i++)
				{
					$usetypei	= $usetypei | $uselev[$i];
				}

				$data['uselev'] = $usetypei;

				$data['usetype'] = $usetype;

				$filter = array();
				$filter['id'] = $_REQUEST['id'];

				$result = $this->_toolModel->where($filter)->save($data);
				
				$array = array('statusCode'=>200,'message'=>"修改成功",'navTabId'=>'','callbackType'=>'closeCurrent');
			}
			else
			{
				$array = array('statusCode'=>'300','message'=>"验证失败", 'navTabId'=>'', 'callbackType'=>'closeCurrent');
			}
			echo json_encode($array);
			exit();
		}
		else
		{
			$id = $_REQUEST['id'];

			$filter = array();
			$filter['id'] = $id;
			$toolPo = $this->_toolModel->where($filter)->find();

			$this->assign('toolPo', $toolPo);

			$this->display('DictionaryManager:toolEdit');
		}
	}


	/**
	 * 启用禁用删除工具
	 * */
	public function toolProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['id'] = $id;

			if($type == "del")
			{
				$this->_toolModel->where($filter)->delete();
			}

			$data = array("statusCode"=>"200","message"=>"操作成功",'navTabId'=>'toolList','callbackType'=>'closeCurrent');
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
		exit;
	}

	/*******************************************检修部件管理*********************************************************/
	/**
	 * 部件列表
	 * */
	public function unitList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];
		
		//条件过滤
		$filter = array();
		$unitname = trim($_REQUEST['unitname']);
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
			$order['id'] = ' desc';
		}
		
		
		$totalCount = $this->_unitModel->where($filter)->count();
		$list = $this->_unitModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();
		
		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);
		
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);

		$this->display('DictionaryManager:unitList');
	}

	/**
	 * 添加部件
	 * */
	public function unitAdd()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				extract($_POST);

				$data = array();
				$data['id'] = trim($id);
				$data['unitname'] = trim($unitname);

				$leveli	=	0;
				for($i=0;$i<count($level);$i++)
				{
					$leveli	= $leveli | $level[$i];
				}
				$data['level'] = $leveli;

				$areai	=	0;
				for($i=0;$i<count($area);$i++)
				{
					$areai	= $areai | $area[$i];
				}

				$data['area'] = $areai;
				$data['CRH'] = trim($CRH);


				$newId = $this->_unitModel->add($data);

				if($newId)
				{
					$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'unitlist','callbackType'=>'closeCurrent');
				}
				else
				{
					$array = array('statusCode'=>'300','message'=>"添加失败");
				}
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
			$this->display('DictionaryManager:unitAdd');
		}
	}

	/**
	* 修改部件
	* */
	public function unitEdit()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				extract($_POST);

				$data = array();
				$data['id'] = trim($id);
				$data['unitname'] = trim($unitname);

				$leveli	=	0;
				for($i=0;$i<count($level);$i++)
				{
					$leveli	= $leveli | $level[$i];
				}
				$data['level'] = $leveli;

				$areai	=	0;
				for($i=0;$i<count($area);$i++)
				{
					$areai	= $areai | $area[$i];
				}

				$data['area'] = $areai;
				$data['CRH'] = trim($CRH);

				$filter = array();
				$filter['id'] = $_REQUEST['id'];


				$result = $this->_unitModel->where($filter)->save($data);
				
				$array = array('statusCode'=>200,'message'=>"修改成功",'navTabId'=>'','callbackType'=>'closeCurrent');
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
			$filter['id'] = $id;
			$unitPo = $this->_unitModel->where($filter)->find();

			$this->assign('unitPo', $unitPo);

			$this->display('DictionaryManager:unitEdit');
		}
	}

	/**
	* 启用禁用删除部件
	* */
	public function unitProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && isset($id))
		{
			$filter = array();
			$filter['id'] = $id;

			if($type == "del")
			{
				$this->_unitModel->where($filter)->delete();
			}
			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}

	/*******************************************检修热点管理*********************************************************/
	/**
	 * 热点列表
	 * */
	public function positionList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];
		
		//条件过滤
		$filter = array();
		$tipname = trim($_REQUEST['tipname']);
		if(!empty($tipname))
		{
			$filter['tipname'] = array('like', '%'.$tipname.'%');
			$this->assign('tipname', $tipname);
		}

		//条件排序
		$order = array();
		if($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['id'] = ' desc';
		}

		$totalCount = $this->_positionModel->where($filter)->count();
		$list = $this->_positionModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();

		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);
		
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);

		$this->display('DictionaryManager:positionList');
	}

	/**
	 * 添加热点
	 * */
	public function positionAdd()
	{
		if($_POST)
		{

			extract($_POST);

			$data = array();
			$data['modelname'] = trim($modelname);
			$data['tipname'] = trim($tipname);
			$data['randname'] = trim($randname);
// 			$data['toolnum'] = trim($toolnum);
			$data['CRH'] = trim($CRH);
			$data['area'] = trim($area);
			$data['unitid'] = trim($unit);

// 			for($i = 1; $i <= $toolnum; $i++)
// 			{
// 				$tooli = "tool".$i;
// 				$txti = "txt".$i;
// 				$data[$tooli] = $$tooli;
// 				$data[$txti] = $$txti;
// 			}


			$newId = $this->_positionModel->add($data);

			if($newId)
			{
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'positionlist','callbackType'=>'closeCurrent');
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
			//CRH1 车头部件列表
			$unitList = $this->_unitModel->where("CRH & 1 = 1 and area & 1 = 1")->order("id asc")->select();
			$this->assign('unitList', $unitList);

			$result = $this->_toolModel->select();
			$this->assign('toolList', $result);
			$this->display('DictionaryManager:positionAdd');
		}
	}

	/**
	 * 修改热点
	 * */
	public function positionEdit()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				extract($_POST);

				$data = array();
				$data['modelname'] = trim($modelname);
				$data['tipname'] = trim($tipname);
				$data['randname'] = trim($randname);
// 				$data['toolnum'] = trim($toolnum);

// 				for($i = 1; $i <= $toolnum; $i++)
// 				{
// 					$tooli = "tool".$i;
// 					$txti = "txt".$i;
// 					$data[$tooli] = $$tooli;
// 					$data[$txti] = $$txti;
// 				}

				//dump($data);exit;

				$filter = array();
				$filter['id'] = $_REQUEST['id'];
				$result = $this->_positionModel->where($filter)->save($data);

				$array = array('statusCode'=>200,'message'=>"修改成功",'navTabId'=>'','callbackType'=>'closeCurrent');
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
			$filter['id'] = $id;
			$positionPo = $this->_positionModel->where($filter)->find();
			$this->assign('positionPo', $positionPo);

			$result = $this->_toolModel->select();
			$this->assign('toolList', $result);

			$this->display('DictionaryManager:positionEdit');
		}
	}

	/**
	 * 启用禁用删除热点
	 * */
	public function positionProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];

		if($type && $id)
		{
			$filter = array();
			$filter['id'] = $id;

			if($type == "del")
			{
				$this->_positionModel->where($filter)->delete();
			}

			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}
	
	
	/*****************************************************************************应急故障名称*********************************************************/
	/**
	 * 应急故障名称列表
	 * */
	public function yjTaskList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];
		
		if($_REQUEST['submit'] == 1)
		{
			//条件过滤
			$filter = array();
			$cname = trim($_REQUEST['cname']);
			if(!empty($cname))
			{
				$filter['cname'] = array('like', '%'.$cname.'%');
				$this->assign('cname', $cname);
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
		
		
			$totalCount = $this->_yjTaskModel->where($filter)->count();
			$list = $this->_yjTaskModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();
		
			$this->assign('totalCount', $totalCount);
			$this->assign('list', $list);
		}
	
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);
	
		$this->display('DictionaryManager:yjTaskList');
	}
	
	
	/**
	 * 添加应急故障名称
	 * */
	public function yjTaskAdd()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				extract($_POST);
				$data = array();
				$data['CRH'] = trim($CRH);
				$data['cname'] = trim($cname);
				$data['collid'] = trim($collid);
				$data['hold1'] = trim($hold1);
	
				$newId = $this->_yjTaskModel->add($data);
	
				if($newId)
				{
					$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'yjtasklist','callbackType'=>'closeCurrent');
				}
				else
				{
					$array = array('statusCode'=>'300','message'=>"添加失败");
				}
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
			$collisePos = $this->_yjCollideModel->order('autoid asc')->select();
			$this->assign("collisePos", $collisePos);
	
			$this->display('DictionaryManager:yjTaskAdd');
		}
	}
	
	
	/**
	 * 修改应急故障名称
	 * */
	public function yjTaskEdit()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				extract($_POST);
	
				$data = array();
				$data['CRH'] = trim($CRH);
				$data['cname'] = trim($cname);
				$data['collid'] = trim($collid);
				$data['hold1'] = trim($hold1);
	
				$filter = array();
				$filter['autoid'] = $_REQUEST['id'];
	
	
				$result = $this->_yjTaskModel->where($filter)->save($data);
	
				$array = array('statusCode'=>200,'message'=>"修改成功",'navTabId'=>'yjtasklist','callbackType'=>'closeCurrent');
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
			$Po = $this->_yjTaskModel->where($filter)->find();
			$this->assign('Po', $Po);
	
			$collisePos = $this->_yjCollideModel->order('autoid asc')->select();
			$this->assign("collisePos", $collisePos);
	
			$this->display('DictionaryManager:yjTaskEdit');
		}
	}
	
	
	/**
	 * 启用禁用删除应急故障名称
	 * */
	public function yjTaskProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];
	
		if($type && $id)
		{
			$filter = array();
			$filter['autoid'] = $id;
	
			if($type == "del")
			{
				$this->_yjTaskModel->where($filter)->delete();
			}
	
			$data = array("statusCode"=>"200","message"=>"操作成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}
	
	/***************************************************************材料处理****************************************************************/
	
	/**
	 * 材料列表
	 * */
	public function materialList()
	{
		$currentPage = empty($_REQUEST['currentPage']) ? 1 : $_REQUEST['currentPage'];
		$pageSize = empty($_REQUEST['pageSize']) ? 200 : $_REQUEST['pageSize'];
		
		//条件过滤
		$filter = array();
		$tipname = trim($_REQUEST['tipname']);
		if(!empty($tipname))
		{
			$filter['tipname'] = array('like', '%'.$tipname.'%');
			$this->assign('tipname', $tipname);
		}
		
		//条件排序
		$order = array();
		if($_REQUEST['_order'] && $_REQUEST['_sort'])
		{
			$order[$_REQUEST['_order']] =  $_REQUEST['_sort'];
		}
		else
		{
			$order['id'] = ' desc';
		}
		
		
		$totalCount = $this->_materialModel->where($filter)->count();
		$list = $this->_materialModel->where($filter)->order($order)->page($currentPage)->limit($pageSize)->select();
		
		$this->assign('totalCount', $totalCount);
		$this->assign('list', $list);
		
		$this->assign('currentPage', $currentPage);
		$this->assign('pageSize', $pageSize);
		
		$this->display('DictionaryManager:materialList');
	}
	
	/**
	 * 添加材料
	 * */
	public function materialAdd()
	{
		if($_POST)
		{
			extract($_POST);
	
			$data = array();
			$data['id'] = trim($id);
			$data['picname'] = trim($picname);
			$data['tipname'] = trim($tipname);
	
			$newId = $this->_materialModel->add($data);
	
			if($newId)
			{
				$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'materiallist','callbackType'=>'closeCurrent');
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
			$this->display('DictionaryManager:materialAdd');
		}
	}
	
	/**
	 * 编辑材料
	 * */
	public function materialEdit()
	{
		if($_POST)
		{
			extract($_POST);
	
			$data = array();
			$data['picname'] = trim($picname);
			$data['tipname'] = trim($tipname);
			
			$filter = array();
			$filter['id'] = $_REQUEST['id'];
			$result = $this->_materialModel->where($filter)->save($data);
	
			$array = array('statusCode'=>'200','message'=>"编辑成功",'navTabId'=>'','callbackType'=>'');
			
			echo json_encode($array);
			exit();
		}
		else
		{
			$id = $_REQUEST['id'];
			
			$filter = array();
			$filter['id'] = $id;
			$Po = $this->_materialModel->where($filter)->find();
			
			$this->assign('po', $Po);
			
			$this->display('DictionaryManager:materialEdit');
		}
	}
	
	/**
	 * 材料处理
	 * */
	public function materialProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];
	
		if($type && $id)
		{
			$filter = array();
			$filter['id'] = $id;
	
			if($type == "del")
			{
				$this->_materialModel->where($filter)->delete();
			}
	
			$data = array('statusCode'=>'200','message'=>"处理成功");
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
		exit;
	}
}
?>