function sysAlert()
{
	var msg    = arguments[0];
	var status = arguments[1]?arguments[1]:0;
	var url    = arguments[2]?arguments[2]:"";
	if(status == '0'){
		$.dialog({
			title: '错误',
			content: msg,
			ok: true,
			icon: 'error',
			top:'45%',
			lock: true,
			background: '#600', // 背景色
		    opacity: 0.27	// 透明度
		})
	}else{
		if(url == ""){
			$.dialog({
				title: 'OK',
				content: msg,
				ok:true,
				icon: 'succeed',
				top:'45%',
				lock: true,
				background: '#600', // 背景色
			    opacity: 0.37	// 透明度
			})
		}else{
			$.dialog({
				title: 'OK',
				content: msg,
				ok:function(){window.location=url;},
				icon: 'succeed',
				top:'45%',
				lock: true,
				background: '#600', // 背景色
			    opacity: 0.37	// 透明度
			})
		}
		
	}
}


//验证是否为日期类型
function isDate(str)
{
   var result=str.match(/^(\d{4})(-|\/)(\d{1,2})\2(\d{1,2})$/);
   if(result==null) return false;
   var d=new Date(result[1], result[3]-1, result[4]);
   return (d.getFullYear()==result[1] && d.getMonth()+1==result[3] && d.getDate()==result[4]);
}

//验证是否为数据类型
function isNumber(val)
{
    var reg = /[\d|\.|,]+/;
    return reg.test(val);
}

//验证是否为数字   
function istInt(val)
{ 
    return  val.search("^-?\\d+$") == 0 ;
}

//验证是否为数据和字母下划线类型
function isAlphaNumber(str)
{
	var result=str.match(/^[a-zA-z_0-9]+$/);
	if(result==null) return false;
	return true;
}
//验证是否为整形
function isInt(val)
{
    var reg = /\d+/;
    return reg.test(val);
}
//验证是否为email类型
function isEmail(email)
{
   var reg = /^[a-z0-9_-]+([-_\.][a-z0-9_-]+)*@([a-z0-9]+([-_][a-z0-9]+)*)+([\.][a-z]{1,4}){1,3}$/i ;
    return reg.test( email );
}

//验证是否为日期时间类型 ： 2010-04-03 05:32:56
function isDateTime(str)
{
	var result=str.match(/^(\d{4})(-|\/)(\d{1,2})\2(\d{1,2}) (\d{1,2}):(\d{1,2}):(\d{1,2})$/);
	if(result==null) return false;
	var d= new Date(result[1], result[3]-1, result[4], result[5], result[6], result[7]);
	return (d.getFullYear()==result[1]&&(d.getMonth()+1)==result[3]&&d.getDate()==result[4]&&d.getHours()==result[5]&&d.getMinutes()==result[6]&&d.getSeconds()==result[7]);
}

//验证密码：6-30个字符，允许为英文、数字、一般符合、特殊符号，不能输入空格，区分大小写(不为中文和空格)
function isPassword1(str)
{
	//var reg = /^[^\s^\u4e00-\u9fa5]{6,30}$/;
	//return reg.test(password);
	var length = str.length;
	if(length < 6 || length > 20){
		return false;
	}
	return true;
}

//验证html字符 
function isHtml(str)
{
	var reg = /<(S*?)[^>]*>.*?|<.*?\/>/;
	return reg.test(str);
}

//验证URL地址 
function isURL(str)
{
	var regExp = /^http(s)?:\/\/([\w-]+\.)+[\w-]+(\/[\w- .\/?%&=]*)?/;
	var objExp=new RegExp(regExp);
	return objExp.test(str);   
}

/*
 * 编码页面HTML代码
 * */
function HtmlEncode(html)
{
    var s = "";
    if (html.length == 0) return "";
    s = html.replace(/&/g,"&amp;");
    s = s.replace(/</g, "&lt;");
    s = s.replace(/>/g, "&gt;");
    s = s.replace(/ /g, "&nbsp;");
    s = s.replace(/\'/g, "&#39;");
    s = s.replace(/\"/g, "&quot;");
    return s;   
}
/*
 *  解码页面HTML代码
 * */
function HtmlDecode(html)
{
    var s = "";
    if (html.length == 0) return "";
    s = html.replace(/&amp;/g,"&");
    s = s.replace(/&lt;/g,"<");
    s = s.replace(/&gt;/g,">");
    s = s.replace(/&nbsp;/g," ");
    s = s.replace(/&#39;/g,"'");
    s = s.replace(/&quot;/g,"\"/");
    return s;   
}

//身份证函数
function checkIdentityCard(cardId)	
{
	var city = {11:"",12:"",13:"",14:"",15:"",21:"",22:"",23:"",31:"",32:"",33:"",34:"",35:"",36:"",37:"",41:"",42:"",
				43:"",44:"",45:"",46:"",50:"",51:"",52:"",53:"",54:"",61:"",62:"",63:"",64:"",65:"",71:"",81:"",82:"",91:""}
	if((cardId == "") || (cardId == null))
	{
		return 1;
	}
	else
	{
		var iSum = 0;
		var re = /^\d{17}(\d|X)$/i;
		if(!re.test(cardId))
		{
			return 2;
		}
		cardId = cardId.replace(/X$/i,"a");
		//验证地址是否合法
		if(city[parseInt(cardId.substr(0,2))] == null)
		{
			return 3;
		}
		//验证出生日期是否合法
		var sBirthday = cardId.substr(6,4)+"-"+Number(cardId.substr(10,2))+"-"+Number(cardId.substr(12,2));
		var date = new Date(sBirthday.replace(/-/g,"/"));

		if(sBirthday != (date.getFullYear()+"-"+(date.getMonth()+1))+"-"+date.getDate())
		{
			return 4;
		}

		for(var i=17;i>=0;i-- )
		{
			iSum += (Math.pow(2,i)%11)*parseInt(cardId.charAt(17-i),11);
		}
		if(iSum%11 != 1)
		{
			return 5;
		}
		else
		{
			return 0;
		}
	}
}

//检测是否有空格,true有空格
function checkBlank(str)
{
	var re = /\s+/;
	if(re.test(str))
	{
		return true;
	}else{
		return false;		
	}
}

//是否以数字开头，true以数字开头
function checkNumStart(str){
	var re = /^\d+/;
	if(re.test(str)){
		return true;
	}else{
		return false;
	}
}

//检测密码
function isPassword(str)
{
	var length = str.length;
	if(length < 6 || length > 20){
		return false;
	}
	else if(str == "123456")
	{
		return false;
	}
	return true;
}

function countCharacters(str){
   var totalCount = 0;
   for (var i=0; i<str.length; i++) {
	   var c = str.charCodeAt(i);
	   if ((c >= 0x0001 && c <= 0x007e) || (0xff60<=c && c<=0xff9f)) {
		   totalCount++;
	   }else {    
		  totalCount+=3;
	   }
 }
 return totalCount;
}

function checkTelphone(tel)
{
	var regExp =/^1[34589]\d{9}$/;
	if(regExp.test(tel))
	{
		return true;
	}
	else
	{
		return false;
	}
}

function checkQQ(tel)
{
	var regExp =/^[1-9][0-9]{5,13}$/;
	if(regExp.test(tel))
	{
		return true;
	}
	else
	{
		return false;
	}
}

/**
 * interval ：D表示查询精确到天数的之差
 * H表示查询精确到小时之差
 * M表示查询精确到分钟之差
 * iS表示查询精确到秒之差
 * T表示查询精确到毫秒之差
 * */
function dateDiff(interval, date1, date2)
{
   var objInterval = {'D':1000 * 60 * 60 * 24,'H':1000 * 60 * 60,'M':1000 * 60,'S':1000,'T':1};
   interval = interval.toUpperCase();
   var dt1 = new Date(Date.parse(date1.replace(/-/g, '/')));
   var dt2 = new Date(Date.parse(date2.replace(/-/g, '/')));
   try
   {
      return Math.round((dt2.getTime() - dt1.getTime()) / eval_r('objInterval.'+interval));
   }
   catch (e)
   {
      return e.message;
   }
}


