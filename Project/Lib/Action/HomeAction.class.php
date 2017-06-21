<?php
class HomeAction extends Action
{
	
	
	protected $_departModelModel;		//部门表
	protected $_userInfoModel;			//学员信息表
	protected $_kaoshiModel;			//学员考试表
	protected $_taskToolResultModel;	//学员工具结果
	protected $_taskResultModel; 		//学员考试结果
	
	protected $_taskPageListMoel;		//试卷表
	protected $_taskPageMoel;			//试卷试卷表
	protected $_taskPageResultMoel;	//考试答案表
	

	public function __construct()
	{
		parent::__construct();

		$this->_departModelModel = M("departid_2013");
		$this->_userInfoModel = M("userInfo_2013");
		$this->_kaoshiModel = M("kaoshi_2013");
		$this->_taskToolResultModel = M("taskToolResult_2013");
		$this->_taskResultModel = M("taskPageList_2013");
		
		$this->_taskPageListMoel = M("taskPageList_2013");
		$this->_taskPageMoel = M("taskPage_2013");
		$this->_taskPageResultMoel = M("taskPageResult_2013");
	}
}
?>