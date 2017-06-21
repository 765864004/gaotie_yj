<?php
class IdNumber{

	
	
	public function GetHometownByIdNumber($IdNumber){
	
	
		return '';
	}//function GetHometownByIdNumber() end
	
	
	public function GetBirthdayByIdNumber($IdNumber){
	
		$birthday = substr($IdNumber,6,8);
	
		$time = strtotime($birthday);
	
		$birthday = date("Y-m-d",$time);
	
		return $birthday;
	}//function GetBirthdayByIdNumber();
	
	public function GetGenderByIdNumber($IdNumber){
	
		$GenderNumber = substr($IdNumber,16,1);
	    
	    if($GenderNumber%2==1){
			$Gender = 'M';
		}
		else if($GenderNumber%2==0){
			$Gender = 'F';
		}
	
		return $Gender;
	
	}//function GetGenderByIdNumber end
  
}//class IdNumber end
?>