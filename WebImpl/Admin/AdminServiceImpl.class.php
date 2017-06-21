<?php
class AdminServiceImpl
{
	
	//系统管理员表
	private static function getAdminModel()
	{
		return M ('MAdmin');
	}
	
	//角色
	private static function getRoleModel()
	{
		return  M ('MRole');
	}
	
	//角色权限
	private static function getRoleAccessModel()
	{
		return  M ('MRoleAccess');
	}
	
	//node节点表
	private static function getNodeModel()
	{
		return  M ('MNode');
	}
	
	/*
	 * 后台管理员登陆，判断是否被禁用，返回管理员的角色id
	 */
	public function login($name, $password) 
	{
		$adminModel = self::getAdminModel();
		$roleModel = self::getRoleModel();
		
		$encodePwd = md5str($password);
		
		$filter = array();
		$filter['name'] = array('eq', $name);
		
		$isUserExists = $adminModel->where($filter)->find();
		if(count($isUserExists) > 0)
		{
			if($isUserExists["password"] == $encodePwd)
			{
				if($isUserExists["status"] == "N")
				{
					throw new Exception("该用户已被禁用，请与管理员联系");
				}
				else if($isUserExists["status"] == "Y")
				{
					//得到管理员的角色
					$RolePo = $roleModel->where(array('id'=>$isUserExists["role_id"]))->find();
					if($RolePo)
					{
						if($RolePo['status'] == "Y")
						{
							$_SESSION['manager']['roleName'] = $RolePo["name"];
							$_SESSION['manager']['roleId'] = $isUserExists["role_id"];
							$_SESSION['manager']['id'] = $isUserExists["id"];
							$_SESSION['manager']['trueName'] = $isUserExists["true_name"];
						}
						else
						{
							throw new Exception("该用户已角色已被禁用，请与管理员联系");
						}
					}
					else
					{
						throw new Exception("该用户已角色不存在，请与管理员联系");
					}
				}
				else
				{
					throw new Exception("该用户已被管理员删除，请与管理员联系");
				}
			}
			else
			{
				throw new Exception("您输入的密码错误，请重新输入");
			}
		}
		else
		{
			throw new Exception("该用户不存在，请重新输入");
		}
	}

	
	/**
	 * 返回顶部导航栏的列表,根据角色id得到该角色下面的导航栏列表
	 * */
	public function returnGroupList()
	{
		$roleId = $_SESSION['manager']['roleId'];//根据角色id显示角色下面对应的导航栏
		
		$nodeModel = self::getNodeModel();

		$join = array ("INNER JOIN t_m_role_access ra ON ra.node_id = t_m_node.id and ra.role_id=$roleId");
	
		$condition = array();
		$condition['level']  = array("eq",1);//节点必须是一级节点
		$condition['fid']    = array('eq',0);
	
		$order = "sort asc";
	
		$field  = array("id","name");
	
		$result = $nodeModel->join($join)->where($condition)->order($order)->select();
		//echo $nodeModel->getLastSql();

		return $result;
	}
	
	//根据组id得到组下面的所有的actionid
	public  function returnActionList($groupId)
	{
		$roleId = $_SESSION['manager']['roleId'];//根据角色id显示角色下面对应的导航栏
		
		$nodeModel = self::getNodeModel();
	
		$moduleList = $this->returnModuleList($groupId);
	
		$condition['level'] = array('eq',3);
	
		foreach($moduleList as $key=>$module){
	
			$moduleId = $module['id'];
	
			$condition['fid']  = array("eq", $moduleId);
			$condition['mid']  = array("eq", 0);
			$condition['status']  = array("eq", "Y");
			
			$order  = "sort asc";
	
			//1.表名设置
			$tableRoleAccess = 't_m_role_access';
			
			$join = array ("INNER JOIN $tableRoleAccess ra ON ra.node_id = t_m_node.id and ra.role_id=$roleId");
			
			$actionList = $nodeModel->join($join)->order($order)->where($condition)->select();
	
			if(count ($actionList) > 0){
				$moduleList[$key]['items'] = $actionList;
			}
		}
	
		return $moduleList;
	}
	
	//得到登录用户的模块权限
	public function checkUserPermission($groupName, $moduleName, $actionName)
	{
		$nodeModel = self::getNodeModel();
		
		$flag = 0;
		
		$roleId = $_SESSION['manager']['roleId'];
		if($roleId)
		{
			//1.检查用户的组权限
			$join = array ("INNER JOIN t_m_role_access ra ON ra.node_id = t_m_node.id and ra.role_id=$roleId and t_m_node.level=1 and t_m_node.fid=0");
			
			$groupPermisstion = $nodeModel->join($join)->select();
			//dump($groupPermisstion);
			if(count($groupPermisstion) > 0)
			{
				foreach($groupPermisstion as $value)
				{
					$groupPermisstionName[] = strtolower(str_replace("_", "", $value['name']));
					if($groupName == strtolower(str_replace("_", "", $value['name'])))
					{
						$groupId = $value['id'];
					}
				}
			
				if(in_array($groupName, $groupPermisstionName))
				{
					//检查用户的模块权限
					$join = array ("INNER JOIN t_m_role_access ra ON ra.node_id = t_m_node.id and ra.role_id=$roleId and t_m_node.level=2 and t_m_node.fid={$groupId}");
					$modulePermisstion = $nodeModel->join($join)->select();
					
					if(count($modulePermisstion) > 0)
					{
						if(count($modulePermisstion) > 0)
						{
							foreach($modulePermisstion as $value1)
							{
								$modulePermisstionName[] = strtolower(str_replace("_", "", $value1['name']));
								if($moduleName == strtolower(str_replace("_", "", $value1['name'])))
								{
									$moduleId = $value1['id'];
								}
							}
							if(in_array($moduleName, $modulePermisstionName))
							{
								//检查用户的action权限
								$join = array ("INNER JOIN t_m_role_access ra ON ra.node_id = t_m_node.id and ra.role_id=$roleId and t_m_node.level=3 and t_m_node.fid='{$moduleId}'");
								$actionPermisstion = $nodeModel->join($join)->select();
								if($actionPermisstion)
								{
									foreach($actionPermisstion as $value2)
									{
										$actionPermisstionName[] = strtolower(str_replace("_", "", $value2['name']));
									}
									if(in_array($actionName, $actionPermisstionName))
									{
										$flag = 1;
									}
								}
							}
						}
					}
				}
			}
		}
		return $flag;
	}
	
	/*******************************************私有方法**************************************************/
	/**
	 * 根据组名得到模块列表
	 * */
	private function returnModuleList($groupId=null)
	{
		$roleId = $_SESSION['manager']['roleId'];//根据角色id显示角色下面对应的导航栏
	
		$nodeModel = self::getNodeModel();
	
		//返回业务类具体的名称	，就是有那些业务类是需要显示出来的
		$groupList = $this->returnGroupList();
	
		$groupIdList = array();
		foreach($groupList as $value){
			array_push($groupIdList,$value['id']);
		}
	
		$defaultGroupId = $groupIdList[0];//默认的分组就是第一个分组
	
		//如果传进来的分组ID不是数字或者没有传分组ID的话
		if(!is_numeric($groupId))
		{
			$groupId = $defaultGroupId;
			$condition['fid'] = array("eq",$defaultGroupId);
		}
		else
		{
			if(in_array($groupId,$groupIdList))
			{
				$condition['fid'] = array("eq",$groupId);
			}
			else
			{
				$groupId = $defaultGroupId;
				$condition['fid'] = array("eq",$defaultGroupId);
			}
	
		}
	
		//1.表名设置
		$tableRoleAccess = 't_m_role_access';
	
		$join = array ("INNER JOIN $tableRoleAccess ra ON ra.node_id = t_m_node.id and ra.role_id=$roleId");
	
		$condition['level']      = array("eq",2);
		$condition['status']    = array("eq",'Y');
	
		$order = "sort asc";
	
		$groupName = $this->getNodeNameByNodeId($groupId);
	
		$moduleList = $nodeModel->join($join)->order($order)->where($condition)->select();
		
		//echo $nodeModel->getLastSql();
	
		foreach($moduleList as $key=>$module)
		{
			$moduleList[$key]['groupName'] = $groupName;
		}
	
		return $moduleList;
	}
	
	/**
	 * 根据节点id得到节点名称
	 * */
	private function getNodeNameByNodeId($id)
	{
		$nodeModel = self::getNodeModel();
	
		$filter['id'] = array("eq", $id);
	
		$result = $nodeModel->where($filter)->find();
	
		$nodeName = $result['name'];
	
		return $nodeName;
	
	}

	
}
?>