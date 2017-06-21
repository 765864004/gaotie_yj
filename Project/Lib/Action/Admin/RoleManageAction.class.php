<?php
class RoleManageAction extends AdminAction
{
	private $_adminModel;
	private $_roleModel;
	private $_nodeModel;
	private $_roleAccessModel;
	
	public function __construct()
	{
		parent::__construct();
		
		$this->_adminModel = M('MAdmin');
		$this->_roleModel = M('MRole');
		$this->_nodeModel = M('MNode');
		$this->_roleAccessModel = M('MRoleAccess');
		
	}
	
	/**
	 * 角色列表
	 * */
	public function roleList()
	{
		$roleList = $this->_roleModel->select();
		
		$this->assign('list', $roleList);
		
		$this->display('RoleManager:roleList');
	}
	
	/**
	 * 添加角色
	 * */
	public function addRole()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				$data = array();
				$data['name'] = $_REQUEST['name'];
				$data['description'] = $_REQUEST['description'];
				$data['create_user'] = $_SESSION['manager']['trueName'];
				$data['status'] = $_REQUEST['status'];
				$data['create_time'] = date('Y-m-d H:i:s', time());
	
				$newId = $this->_roleModel->add($data);
				//echo $newId;exit;
				
				$parentNodeIdArr = array();
				$allParentsIds = array();
				
				if($newId)
				{
					$nodeIdArr = $_REQUEST['nodeId'];
					if(count($nodeIdArr) > 0)
					{
						foreach($nodeIdArr as $nodeId)
						{
							$nodePo = $this->_nodeModel->where(array('id'=>$nodeId))->find();
							$parentNodeIdArr[] = $nodePo['fid'];
							
							$dataRoleAccess = array();
							$dataRoleAccess['role_id'] = $newId;
							$dataRoleAccess['node_id'] = $nodeId;
							
							$this->_roleAccessModel->add($dataRoleAccess);
						}
						//dump($parentNodeIdArr);
						
						foreach($parentNodeIdArr as $fid)
						{
							$nodePo = $this->_nodeModel->where(array('id'=>$fid))->find();
							$allParentsIds[] = $nodePo['fid'];
							$allParentsIds[] = $fid;
						}
						
						$allParentsIds = array_unique($allParentsIds);
						
						//dump($allParentsIds);exit;
						foreach($allParentsIds as $nodeId)
						{
							$dataRoleAccess = array();
							$dataRoleAccess['role_id'] = $newId;
							$dataRoleAccess['node_id'] = $nodeId;
							
							$this->_roleAccessModel->add($dataRoleAccess);
							//echo $this->_roleAccessModel->getLastSql();echo "<br/>";
						}
						//exit;
					}
					
					$array = array('statusCode'=>'200','message'=>"添加成功",'navTabId'=>'rolelist','callbackType'=>'closeCurrent');
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
			$moduleList = $this->_nodeModel->where(array('level'=>2))->order('sort asc')->select();
			
			if(count($moduleList) > 0)
			{
				foreach($moduleList as $key=>$po)
				{
					$actionList = $this->_nodeModel->where(array('level'=>3, 'fid'=>$po['id']))->order('sort asc')->select();
					$moduleList[$key]['actionList'] = $actionList;
				}
			}
			$this->assign("moduleList", $moduleList);
			
			$this->display('RoleManager:addRole');
		}
	}
	
	/**
	 * 修改角色
	 * */
	public function editRole()
	{
		if($_POST)
		{
			if(M()->autoCheckToken($_POST))
			{
				$data = array();
				$id = $_REQUEST['id'];
				
				$data['id'] = $id;
				$data['name'] = $_REQUEST['name'];
				$data['description'] = $_REQUEST['description'];
				$data['status'] = $_REQUEST['status'];
	
				$newId = $this->_roleModel->save($data);
				
				$selfRoleAccess = $this->_roleAccessModel->where(array('role_id'=>$id))->field("node_id")->select();
				$selfNodeIdArr = array();
				if(count($selfRoleAccess) > 0)
				{
					foreach($selfRoleAccess as $po)
					{
						$selfNodeIdArr[] = $po['node_id'];
					}
				}
				
				$parentNodeIdArr = array();
				$allParentsIds = array();
				$nodeIdArr = $_REQUEST['nodeId'];
				if(count($nodeIdArr) > 0)
				{
					foreach($nodeIdArr as $nodeId)
					{
						$nodePo = $this->_nodeModel->where(array('id'=>$nodeId))->find();
						$parentNodeIdArr[] = $nodePo['fid'];
					}
					//dump($parentNodeIdArr);
				
					foreach($parentNodeIdArr as $fid)
					{
						$nodePo = $this->_nodeModel->where(array('id'=>$fid))->find();
						$allParentsIds[] = $nodePo['fid'];
						$allParentsIds[] = $fid;
					}
				
					$allParentsIds = array_unique($allParentsIds);
				}
				
				$nodeIdArr = array_merge($nodeIdArr, $allParentsIds);
				
				//得到新增加的nodeid
				$newIdArr = array_diff($nodeIdArr, $selfNodeIdArr);
				//删掉的nodeid
				$delIdArr = array_diff($selfNodeIdArr, $nodeIdArr);
				
				//dump($newIdArr);dump($delIdArr);exit;
				
				if(count($newIdArr) > 0)
				{
					foreach($newIdArr as $nodeId)
					{
						$dataRoleAccess = array();
						$dataRoleAccess['role_id'] = $id;
						$dataRoleAccess['node_id'] = $nodeId;
						
						$this->_roleAccessModel->add($dataRoleAccess);
					}
				}
				
				if(count($delIdArr) > 0)
				{
					$this->_roleAccessModel->where(array('role_id'=>$id, 'node_id'=>array('in', $delIdArr)))->delete();
					//echo $this->_roleAccessModel->getLastSql();exit;
				}
				
				$array = array('statusCode'=>'200','message'=>"修改成功",'navTabId'=>'rolelist','callbackType'=>'closeCurrent');
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
			$moduleList = $this->_nodeModel->where(array('level'=>2))->order('sort asc')->select();
			
			if(count($moduleList) > 0)
			{
				foreach($moduleList as $key=>$po)
				{
					$actionList = $this->_nodeModel->where(array('level'=>3, 'fid'=>$po['id']))->order('sort asc')->select();
					$moduleList[$key]['actionList'] = $actionList;
				}
			}
			
			//得到角色信息
			$id = $_REQUEST['id'];
			$rolePo = $this->_roleModel->where(array('id'=>$id))->find();
			
			//得到角色的权限信息
			$roleAccessNodeIdArr = $this->_roleAccessModel->where(array('role_id'=>$id))->field('node_id as nid')->select();
			$roleNodeIdArr = array();
			if(count($roleAccessNodeIdArr) > 0)
			{
				foreach($roleAccessNodeIdArr as $v)
				{
					$roleNodeIdArr[] = $v['nid'];
				}
			} 
			
			$this->assign('rolePo', $rolePo);
			$this->assign('roleNodeIdArr', $roleNodeIdArr);
			$this->assign("moduleList", $moduleList);
			
			$this->display('RoleManager:editRole');
		}
	}
	
	/**
	 * 启用禁用删除角色
	 * */
	public function roleProcess()
	{
		$type = $_REQUEST['actType'];
		$id   = $_REQUEST['id'];
		
		if($type && $id)
		{
			$filter = array();
			$filter['id'] = $id;
			
			if($type == "show")
			{
				$filter['status'] = "Y";
				$this->_roleModel->save($filter);
				
			}
			else if($type == 'hidden')
			{
				$filter['status'] = "N";
				$this->_roleModel->save($filter);
			}
			else if($type == "del")
			{
				$isAdminUseRole = $this->_adminModel->where(array('role_id'=>$id))->select();
				if(count($isAdminUseRole) > 0)
				{
					$data = array("statusCode"=>"300","message"=>"有管理员使用该角色，先删除管理员或者解除绑定后在删除该角色");
					echo json_encode($data);
					exit;
				}
				else
				{
					$this->_roleAccessModel->where(array('role_id'=>$id))->delete();
					$this->_roleModel->where(array('id'=>$id))->delete();
				}
			}
			$data = array("statusCode"=>"200","message"=>"操作成功",'navTabId'=>'rolelist');
		}
		else
		{
			$data = array("statusCode"=>"300","message"=>"系统错误");
		}
		echo json_encode($data);
	}
	
	/**
	 * 查看角色
	 * */
	public function showRole()
	{
		//todo
	}

}
?>