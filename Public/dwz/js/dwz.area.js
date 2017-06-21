(function($){
	
	  $.fn.extend({
		    //AjaxGetData:function(data,callback,method,url){
		   
		   Area:function(){
		    	$("#CityFilter").hide();
			    $("#CityFilter").attr("disabled","disabled");
		    	$("#ProvinceFilter").die('change').live('change',function(){
		    		
		    		var data = "province_id="+$(this).attr("value");
		    		var post_url = $(this).attr("post_url")
		    		
		    		$.fn.AjaxGetData(data,ChangeCitySelectOption,post_url,'AjaxReturnCityArray');
		    		
		    	});
		    	
		    	function ChangeCitySelectOption(msg){
		    		//改变城市的下拉列表，就是选择省份的时候,城市的下拉列表对应的发生变化
		    		//选择湖北省，只出现湖北省的城市
		    			var status   = msg.status;
		    			if(!status){
		    				$("#CityFilter").attr("disabled","disabled");
		    				$("#CityFilter").hide();
		    				//这个地方可以用dwz的对话框弹一下
		    				return;
		    			}
		    			
		    			var CityList = msg.data;
		    			if(CityList.length>0){
		    				var string ="<option value='NotSelected'>该省份全部城市</option>";
			    			
			    			$.each(CityList,function(key,item){
			    				string+="<option value='"+item.city_id+"'>"+item.city+"</option>\r\n";
			    			});
			    			string+="</select>";
			    			$("#CityFilter").removeAttr("disabled");
		    				$("#CityFilter").show();
			    			$("#CityFilter").html(string);
		    			}//if end
		    			else{
		    				$("#CityFilter").attr("disabled","disabled");
		    				$("#CityFilter").hide();
		    			}
		    			
		    	}//function ChangeCitySelectOption() end
		    	
		    
		    }//function Area() end
		   
	 });//$.fn.extend  function end
	
	
	
})(jQuery)