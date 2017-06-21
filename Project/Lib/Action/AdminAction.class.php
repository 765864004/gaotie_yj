<?php
import ('WebImpl.ServiceFactory', WEBSITE_DISK_PATH);
class AdminAction extends Action
{
	protected $_adminService;		//管理员服务接口

	protected $_positionModel;		//热点表
	protected $_toolModel;			//工具表
	protected $_unitModel;			//部件表
	protected $_materialModel;		//材料表

	protected $_taskbaseModel;		//检修任务表
	protected $_taskstuModel;		//检修答案表
	
	protected $_departModelModel;		//部门表
	protected $_userInfoModel;			//学员信息表
	protected $_kaoshiModel;			//学员考试表
	protected $_taskToolResultModel;	//学员工具结果
	protected $_taskResultModel; 	//学员考试结果
	
	protected $_taskPageListMoel;		//试卷表
	protected $_taskPageMoel;			//试卷试卷表
	protected $_taskPageResultMoel;	//考试答案表

	protected $_khGroupModel;	//考试答案表
	

	public function __construct()
	{
		parent::__construct();

		$this->_adminService = ServiceFactory::getAdminUserService();

		$this->_positionModel = M('PositionMod');
		$this->_toolModel = M("Tool");
		$this->_unitModel = M('Unit');
		$this->_materialModel = M("material");

		$this->_taskbaseModel = M('TaskBase');
		$this->_taskstuModel = M("TaskStu");

		$this->_departModelModel = M("departid_2013");
		$this->_userInfoModel = M("userInfo_2013");
		$this->_kaoshiModel = M("kaoshi_2013");
		$this->_taskToolResultModel = M("taskToolResult_2013");
		$this->_taskResultModel = M("taskPageList_2013");
		
		$this->_taskPageListMoel = M("taskPageList_2013");
		$this->_taskPageMoel = M("taskPage_2013");
		$this->_taskPageResultMoel = M("taskPageResult_2013");

		$this->_khGroupModel = M("y_kaohegroup");

		$crh = C("CRHTYPE");			//车型定义
		$this->assign('crh', $crh);		

		$JXTypeArr = C("JXTYPE");		//检修类别定义
		$this->assign('JXTypeArr', $JXTypeArr);

		$BWTypeArr = C("BWTYPE");		//部位定义
		$this->assign('BWTypeArr', $BWTypeArr);

		$TOOLUseTypeArr = C("TOOLUSETYPE");		//工具用途定义
		$this->assign('TOOLUseTypeArr', $TOOLUseTypeArr);

		$chageType = C("changeType");
		$this->assign('chageType', $chageType);

		//$this->checkPermission();
	}

	public function checkPermission()
	{
		if($_SESSION['manager']['id'] && $_SESSION['manager']['roleId'])
		{
			//检查用户的角色权限是否包含有当前的module和action，如果包含，则有权限访问，否则为非法访问
			$result = $this->_adminService->checkUserPermission(strtolower(GROUP_NAME),strtolower(MODULE_NAME),strtolower(ACTION_NAME));
			if($result == 1)
			{
				//不作处理
			}
			else
			{
				echo "没有权限";exit;
				$this->redirect("page/index/showAdminLogin");
			}
		}
		else
		{
			echo "没有登陆";exit;
			$this->redirect("page/index/showAdminLogin");
		}
	}

	/**
	 * 用户ajax上传图片验证
	 * */
	public function ajaxDouploadPic()
	{
		if(isset($_GET['width'])&&isset($_GET['height']))
		{
			$uploadRs = douploadCommon($_GET['width'],$_GET['height']);
		}
		else
		{
			$uploadRs=douploadCommon();
		}
		echo json_encode($uploadRs);
	}
}
?>