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
			$(".my-resources").myresources();
			$(".test-page").testpage();
			$(".examination-page").examinationpage();
			$(".my-pass-test-page").mypasstestpage();
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
				var index = $(this).index();
				$(".tabs-content").eq(index).show().siblings(".tabs-content").hide();
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
		myresources:function(){
			$(this).find("a.del").click(function(){
				$(this).parent().parent().parent().parent().parent().parent().remove();
			});
		},
		testpage:function(){
			$(this).find(".subject-type-content ul li").each(function(){
				var index = $(this).index()+1;
				if(index%2==0){
					$(this).find("i").css("margin-right","12px");
					$(this).css("background-position","32px center");
					$(this).find("p").css("margin-left","57px");
				}
			});
			$(this).find(".my-school-paper-content ul li").eq(3).css("border","none");
			$(this).find(".test-swiper-banner").slide({
														titCell:".hd ul",
														mainCell:".bd ul",
														autoPage:true,
														effect:"fold",
														autoPlay:true,
														interTime:3000,
														startFun:function(i,c,s){
															var imgdetails = $(".test-swiper-banner .bd li").eq(i).data('details');
															$(".test-swiper-banner p").text(imgdetails);
														}
						});
			$(this).find(".grade-page-content").eq(0).show();
			$(this).find(".grade-page-header ul li").click(function(){
				var index = $(this).index();
				$(this).addClass("on").siblings().removeClass("on");
				$(this).parent().parent().parent().find(".grade-page-content").eq(index).show().siblings(".grade-page-content").hide();
			});
		},
		examinationpage:function(){
			$(this).find(".pass-test-list").each(function(){
				$(this).find(".mp-content:last-child").css("border","none");
			});
			$(this).find(".my-pass-test").eq($(this).find(".my-pass-test").length-1).find(".my-pass-content").show();
			$(this).find(".my-pass-test-header").click(function(){
				var hasclass = $(this).hasClass("on");
				if(hasclass){
					$(this).removeClass("on");
					$(this).parent().find(".my-pass-content").hide();
				}else{
					$(this).addClass("on");
					$(this).parent().find(".my-pass-content").show();
					$(this).parent().siblings(".my-pass-test").find(".my-pass-content").hide();
					$(this).parent().siblings(".my-pass-test").find(".my-pass-test-header").removeClass("on");
				}
			});
		},
		mypasstestpage:function(){
			$(this).find(".my-pass-keyword input").val("");
			$(this).find(".my-pass-keyword input").keyup(function(){
				var val = $(this).val();
				if(val!=""){
					$(this).parent().find("i").show();
				}else{
					$(this).parent().find("i").hide();
				}
			});
			$(this).find(".my-pass-keyword i").click(function(){
				$(this).hide();
				$(".my-pass-keyword input").val("");
			});
		}
	};
    $.extend($.fn,$.suoku);
    $(function(){$.suoku.inits();})
})(jQuery);