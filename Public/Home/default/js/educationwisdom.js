;(function($){
	$.suoku = $.suoku||{};
	$.suoku={
		inits:function(){
			var _this = this;
			$(".article-banner").articleBanner();
			$(".article-content").articleContent();
			$(".paging").paging();
			$(".new-article-list").newarticlelist();
			$(".search-page").searchpage();
			$(".comment").Comment();
			$(".ul").ul();
			$(".tab-btn").tabbtn();
			$(".resourcesHome-page").resourcesHomepage();
			$(".swiper").swiper();
		},
		articleBanner:function(){
			//interTime:毫秒；自动运行间隔。当effect为无缝滚动(topMarquee/leftMarquee)时，相当于运行速度。
			$(this).slide({titCell:".hd ul",mainCell:".bd ul",autoPage:true,effect:"fold",autoPlay:true,interTime:3000});
			//动态设定分页器尺寸，位置
			var lilength = $(this).find(".hd ul li").length;
			if($(this).find(".hd ul li").length<2){
				$(this).find(".hd ul").width(lilength*12+12);
			}else{
				$(this).find(".hd ul").width(lilength*12+12*lilength);
			}
			var bannerW = $(".article-banner").width();
			var ulW = $(this).find(".hd ul").width();
			$(this).find(".hd ul").css("margin-left",(bannerW-ulW)/2-10);
		},
		articleContent:function(){
			$(this).find(".tab-nav li").click(function(){
				$(this).addClass("on").siblings().removeClass("on");
			});
		},
		paging:function(){
			var numl = $(this).find(".num").length;
			var numW = $(this).find(".num").width();
			$(this).find("ul").width(numl*numW+96+6*(numl+3));
			$(".tabs-content").each(function(){
				var numl = $(this).find(".paging .num").length;
				var numW = $(this).find(".paging .num").width();
				$(this).find(".paging ul").width(numl*numW+96+6*(numl+3));
			});
			$(this).find(".num").click(function(){
				$(this).addClass("now-page").siblings().removeClass("now-page");
			});
		},
		newarticlelist:function(){
			var lilength = $(this).find("li").length;
			$(this).find("li").eq(lilength-1).css("border","none");
		},
		searchpage:function(){
			$(this).find(".page-content-right-content-lg .list p").click(function(){
				var hasclass = $(this).hasClass("on");
				if(!hasclass){
					$(this).addClass("on");
				}else{
					$(this).removeClass("on");
				}
			});
		},
		Comment:function(){
			$(this).find("textarea").val("我也来说两句");
			$(this).find("textarea").focus(function(){
				var textareaText = $(this).val();
				if(textareaText == "我也来说两句"){
					$(this).val("");
				}
			});
			$(this).find("textarea").blur(function(){
				var textareaText = $(this).val();
				if(textareaText == ""){
					$(this).val("我也来说两句");
				}
			});
		},
		ul:function(){
			$(this).each(function(){
				var lilength = $(this).find("li").length;
				var aa = 0;
				$(this).find("li").eq(lilength-1).css("border","none");
			});
		},
		tabbtn:function(){
			$(this).find("a").click(function(){
				var index = $(this).index();
				$(this).addClass("on").siblings().removeClass("on");
				if(index==0){
					$(".versions-ul,.page-content-header02").show();
					$(this).parent().css("margin-bottom","17px");
					$(".tab-content").css("height","770px");
				}else{
					$(".versions-ul,.page-content-header02").hide();
					$(".tab-content").css("height","775px");
					$(this).parent().css("margin-bottom","10px");
				}
			});
		},
		resourcesHomepage:function(){
			$(this).find(".new-article-list").each(function(){
				var lilength = $(this).find("li").length;
				$(this).find("li").eq(lilength-1).css("border","none");
			});
			$(this).find(".tabs li").click(function(){
				$(this).addClass("on").siblings().removeClass("on");
			});
			$(this).find(".course-ul li a").click(function(){
				$(this).addClass("on").parent().siblings().find("a").removeClass("on");
				$(this).parent().parent().siblings(".course-ul").find("a").removeClass("on");
			});
			$(this).find(".versions-ul li a").click(function(){
				$(this).addClass("on").parent().siblings().find("a").removeClass("on");
			});
			$(this).find(".accordion-content-list-header").click(function(){
				var hasclass = $(this).parent().find(".accordion-content-list-content").hasClass("on");
				if(hasclass){
					$(this).parent().find(".accordion-content-list-content").removeClass("on");
				}else{
					$(this).parent().find(".accordion-content-list-content").addClass("on");
					$(this).parent().siblings(".accordion-content-list").find(".accordion-content-list-content").removeClass("on");
				}
			});
			$(this).find(".accordion-content-list").each(function(){
				$(this).find("li").each(function(){
					var whereas_information = $(this).text();
					if(whereas_information.length>10){
						var encrypt = whereas_information.substring(0,9)+"...";
						$(this).text(encrypt);
					}
				});
			});
			$(this).find(".tabs-content").eq(0).show();
			$(this).find(".tabs li").click(function(){
		        var cid = $(this).attr('data-cid');                                
		        if(cid == 'undefinde'){
		            $('#cid_').val('');
		        }else{
		            $('#cid_').val(cid);
		        }
		        more_select();
			});
		},
		swiper:function(){
			$(this).slide({
				titCell:".hd ul",
				mainCell:".bd ul",
				autoPage:true,
				effect:"fold",
				autoPlay:true,
				interTime:3000,
				startFun:function(i,c,s){
					var imgdetails = $(".swiper .bd li").eq(i).data('details');
					$(".swiper .hd p").text(imgdetails);
				}
			});
		},                    
        page_s:function(){                       
            $(this).jPages({  
                containerID: 'itemContainer',         
                previous: '前一页',//false为不显示  
                next: '下一页',//false为不显示 自定义按钮 
                perPage: 10,//每页最多显示           
                callback: function(pages, items) {  
                    console.log(pages);  
                    console.log(items);  
                }   
            });   
            //跳转到某一页
        },                    
        page_s_mov:function(res_cid){       
            var list_mov_cid = 'list_mov_'+res_cid;                  
            $(this).jPages({  
                containerID: list_mov_cid,         
                previous: '前一页',//false为不显示  
                next: '下一页',//false为不显示 自定义按钮 
                perPage: 10,//每页最多显示           
                callback: function(pages, items) {  
                    //console.log(pages);  
                    //console.log(items);  
                }   
            });   
            //跳转到某一页
        }    
	};
    $.extend($.fn,$.suoku);
    $(function(){$.suoku.inits();})
})(jQuery);