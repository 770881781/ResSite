$(function(){
	$(".customSelectStyle").each(function(){
		var obj = $(this);
		obj.height(32);
		initStyle(obj);
	});
	$(".customSelectStyle .customSelectValue,.customSelectStyle .customSelectBtn").click(function(){
		var display = $(this).parent().find(".customSelectValueList").css("display");
		if(display=="none"){
			$(this).parent().find(".customSelectValueList").show();
		}else{
			$(this).parent().find(".customSelectValueList").hide();
		}
	});
	$(".listValue").click(function(){
		var listValue = $(this).text();
		$(this).parent().parent().find(".customSelectValue").text(listValue);
		$(this).parent().hide();
	});
	/*控制显示个数，超出部分显示滚动条 */
	$(".customSelectStyle").each(function(){
		var showNum = $(this).data("show");
		if(showNum != "undefined"){
			var listValueH = $(this).find(".listValue").height();
			$(this).find(".customSelectValueList").height(listValueH*showNum);
		}
	});
	/*div失去焦点关闭下拉框*/
	$(document).bind("click",function(e){ 
		var target = $(e.target); 
		$(".customSelectValueList").each(function(){
			var display = $(this).css('display');
			if(display=="block"){
				var index = $(this).parent().index();
				if(target.closest($(this).parent()).length == 0){ 
					$(this).hide();
				}
			}
		});
	});
	//初始化样式
	function initStyle(obj){
		var selectStyleW = obj.width(),
		    selectStyleH = obj.height();
		/*obj.find(".listValue").hover(
			function(){
				$(this).css("background-color","#7cd0ff");
			},
			function(){
				$(this).css("background-color","#fff");
			}
		);*/
		obj.css({
					"position":"relative",
					"paddingLeft":"10px",
					"cursor":"pointer",
					"float":"left",
					"border":"1px solid #d9d9d9"
				});
		obj.find(".customSelectValue").css({
												"position":"absolute",
												"width":selectStyleW,
												"height":selectStyleH,
												"lineHeight":selectStyleH+"px",
												"paddingLeft":"10px",
												"top":"0",
												"left":"0",
												"fontSize":"15px",
												"color":"#7d7d7d",
												"overflow":"hidden"
											});
		obj.find(".customSelectBtn").css({
											"position":"absolute",
											"height":selectStyleH,
											"fontSize":"18px",
											"top":"0px",
											"right":"0",
											"width":"22px",
											"background":"url('../images/customSelectBtn.png')",
											"backgroundPosition":"center center",
											"backgroundRepeat":"no-repeat",
											"borderLeft":"1px solid #d9d9d9"
										});
		obj.find(".customSelectValueList").css({
													"position":"absolute",
													"width":"auto",
													"top":selectStyleH,
													"left":"-1px",
													"display":"none",
													"border":"1px solid #d9d9d9",
													"overflowX":"hidden",
													"overflowY":"auto",
													"zIndex":"10",
													"background":"#fff"
											});
		obj.find(".listValue").css({
										"paddingLeft":"10px",
										"paddingRight":"20px",
										"minWidth":selectStyleW-20,
										"height":obj.height(),
										"lineHeight":obj.height()+"px",
										"fontSize":"15px",
										"color":"#7d7d7d"
								});
	}
})