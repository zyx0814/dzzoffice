var searchjson = {
    'after': 0,
    'before': 0,
    'owner': 0,
    'type': 0,
    'position': [],
    'keywords': 0,
    'uid': 0,
    'fid': [],
    'flag': [],
    'flagval':[]
};

//判断搜索条件是否为空
function ishascondition() {
    for (var o in searchjson) {
        if (searchjson[o] != false) {
            $('#emptysearchcondition').removeClass('hide');
            return true;
        }
    }
    return false;
}
jQuery(document).ready(function(e) {
    //特定的人开始
	//用户名分割问题
	$("#id_label_multiples").select2({
		placeholder: "点击或输入开始添加同事",
		separator: ",",
		multiple:true,
        width: '100%',
		ajax: {
            url: MOD_URL+'&op=search_condition&do=getuser',
            dataType: 'json',
            delay: 250,
            data: function(params) {
                return {
                    q: params.term,
                    page: params.page || 1
                };
            },
            processResults: function(data, page) {
                var more = (page * 30) < data.total_count;
                return {
                    results: data.items,
                    pagination: {
                        more: more
                    }
                };
            }
        }
	}).on('change', function (e) {
		$(this).val();
		if (typeof e.added != 'undefined') {
			var username = e.added.text;
			var userindex = $.inArray(username, usernamearr);
			if (userindex == -1) {
				usernamearr.push(username)
			}
		} else if (typeof e.removed != 'undefined') {
			var username = e.removed.text;
			var userindex = $.inArray(username, usernamearr);
			if (userindex != -1) {
				usernamearr.splice(userindex, 1);
			}
		}
		var val = $(this).val();
		
		ownerstr = usernamearr.join(',');
		searchjson['owner'] = ownerstr;
		searchjson['uid'] = val;
		searchConditionChange();
		return false;
	
	});
	//特定的人结束
	//特定的日期
    $('#selectStart').datepicker({
        autoclose:true,
        todayHighlight:true,
        clearBtn:true,
        language: 'zh_CN',
        todayBtn: "linked",
		calendarWeeks: true,
    });
});
jQuery('#searchval').on('keyup',function (event) {//回车搜索
	if (event.which !="") { e = event.which; }
	else if (event.charCode != "") { e = event.charCode; }
	else if (event.keyCode != "") { e = event.keyCode; }
	
	if(e==13){
		parseSearchInputVal(jQuery(this).val());
        execute_search();
	}
});
jQuery('#searchval').focus(function (e) {//头部搜索框变颜色
    var hascondition = ishascondition();
    var placeval=$(this).val();
    jQuery(this).parent().addClass('focus');
    if(!hascondition){
		jQuery('.dropdown-height').show();
	}
	dropdown_off();
});


jQuery('#searchval').blur(function (e) {//失去焦点时
	var hascondition = ishascondition();
    if (!hascondition) {
        $('#emptysearchcondition').addClass('hide');
    }
})

//清空搜索框
$(document).on('click', '#emptysearchcondition', function () {
    $(this).addClass('hide');
   // allowseracrinputwrite = true;
	resetting_condition();
	$('#searchval').val('').focus();
})

//搜索js，默认单条件搜索
$('.less_searchcondition li').click(function (e) {
    var type = $(this).find('a').data('val');
    if (typeof type == 'undefined') {
        $('.dropdown-height').hide();
        show_more_search_condition(e);
    } else {
        resetting_condition();
        $('#searchval').val('type:' + type + ' ');
        searchjson['type'] = type;
        $('.dropdown-height').hide();
        searchConditionChange();
        execute_search();
    }

});

var emptypreg = /^\s*$/i;
function show_more_search_condition(e) {

    var positionfill = jQuery('#positionsearch').data('fill');
    if (!positionfill) {
        jQuery.post(MOD_URL+'&op=search_condition', {'requestfile': true}, function (data) {
            if (data) {
                var html = '';
                for (var o in data) {
                    var typeinfo = '';
                    if(data[o]['type'])  typeinfo = '('+data[o]['type']+')';
                    html += '<div class="form-check form-check-inline"> ' +
                        '<input type="checkbox" class="form-check-input" id="position'+data[o]['pfid']+'" name="position[]" value="' + data[o]['pfid'] + '"> <label for="position'+data[o]['pfid']+'">' + data[o]['pname'] +typeinfo+ '</label> </div>';
                }
                jQuery('#header-seaech-checkbox').append(html);
                jQuery('#positionsearch').data('fill', true);
                if (searchjson['fid']) {
                    var fids = searchjson['fid'];
                    for (var f in fids) {
                        $('#header-seaech-checkbox').find('input[value="' + fids[f] + '"]').prop('checked', true);
                    }

                }
            }
        }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
            showmessage(__lang.do_failed, 'error', 3000, 1);
        });
    }
    if (positionfill && searchjson['fid'] != false && searchjson['fid'].length) {
        var fids = searchjson['fid'];
        for (var f in fids) {
            $('#header-seaech-checkbox').find('input[value="' + fids[f] + '"]').prop('checked', true);
        }

    }
    //类型设置
    if (searchjson['type']) {
        var text = $('.search_type li a[data-val="' + searchjson['type'] + '"]').text();
        jQuery('.search_type').closest('.dropdown-type').find('.anytime').text(text);
    }
    if (typeof searchjson['userselect'] != 'undefined') {
       $('#id_label_multiples').select2('data', searchjson['userselect']);
    }
   
    //特定用户设置
    if (searchjson['uid'].length >0) {
        var text = $('.searchowner li a[data-val="user"]').text();
        jQuery('.searchowner').closest('.dropdown-type').find('.anytime').text(text);
        jQuery('.searchowner').parents('.dropdown-type').find('.typeowner,.name_emile').show();
    }else{
		 //用户设置
		if (searchjson['owner']) {
			var text = $('.searchowner li a[data-val="' + searchjson['owner'] + '"]').text();
			jQuery('.searchowner').closest('.dropdown-type').find('.anytime').text(text);
			jQuery('.searchowner').parents('.dropdown-type').find('.typeowner').hide();
		}else if(searchjson['owner'] == false){
			var text = $('.searchowner li a[data-val="all"]').text();
			jQuery('.searchowner').closest('.dropdown-type').find('.anytime').text(text);
			jQuery('.searchowner').parents('.dropdown-type').find('.typeowner').hide();
		}
	}
    //收藏和回收站
    if (searchjson.flagval) {
        for(var o in searchjson.flagval){
            $('.header-seaech-checkbox').find('input[value="' + searchjson.flagval[o] + '"]').prop('checked', true);
        }

    }
    //文件位置
    if (searchjson['fid']) {
        var fids = searchjson['fid'];
        for (var f in fids) {
            $('#header-seaech-checkbox').find('input[value="' + fids[f] + '"]').prop('checked', true);
        }

    }
    //文件时间
    if(searchjson['after'] && !searchjson['before']){
        var day = getRecentNum(searchjson['after']);
        var dayarr = [1,-1,-7,-30,-90];
        if($.inArray(day,dayarr) > -1){
            var text =$('.searchdate').find('li a[data-val="' + day + '"]').text();
            jQuery('.searchdate').closest('.dropdown-type').find('.anytime').text(text);
        }else{ //自定义时间
            var text = $('.searchdate').find('li a[data-val="datetime"]').text();
            jQuery('.searchdate').closest('.dropdown-type').find('.anytime').text(text);
            jQuery('.searchdate').parents('.dropdown-type').find('.typexdate').show();
            $('#selectStart').datepicker('setDate',searchjson['after']);
            jQuery('#selectEnd').datepicker('setDate',searchjson['before']);
        }
	}else if(!searchjson['after'] && !searchjson['before']){
		  var text = $('.searchdate').find('li a[data-val="all"]').text();
			jQuery('.searchdate').closest('.dropdown-type').find('.anytime').text(text);
			jQuery('.searchdate').parents('.dropdown-type').find('.typexdate').hide();
		
    }else{
        var text = $('.searchdate').find('li a[data-val="datetime"]').text();
        jQuery('.searchdate').closest('.dropdown-type').find('.anytime').text(text);
        jQuery('.searchdate').parents('.dropdown-type').find('.typexdate').show();
        $('#selectStart').datepicker('setDate',searchjson['after']);
        jQuery('#selectEnd').datepicker('setDate',searchjson['before']);
    }

    if (searchjson['keywords']) {
        $('#resourcesname').val(searchjson['keywords']);
    }

    if (jQuery('.dropdown-width').is(":hidden")) {
        jQuery('.dropdown-width').show();
		dropdown_off();
    } else {
        jQuery('.dropdown-width').hide();
    }
    e.stopPropagation();
}

//根据当前时间获取相差天数
function getRecentNum(date) {
    var now = new Date().getTime();
    var end = new Date(date).getTime();
    var chaTime = now - end;
    var days = Math.floor(chaTime / (24 * 3600 * 1000));
    if (days == 0) {
        return 1;
    }
    return parseInt('-' + days);
}

jQuery('.input-search-width').click(function (event) {//搜索框三角点击
    show_more_search_condition(event);
	 jQuery('.dropdown-height').hide();
});
function dropdown_off(){
	jQuery('.input-search').addClass('focus');
	jQuery(document).off('mousedown.headersearch').on('mousedown.headersearch',function(e) {//关闭搜索内容
		if(jQuery(event.target).closest('.input-search,.datepicker').length<1){
			jQuery('.dropdown-width').hide();
			jQuery('.dropdown-height').hide();
			jQuery('#searchval').trigger('blur');
			jQuery('.input-search').removeClass('focus');
			jQuery(document).off('mousedown.headersearch')
		}
	});
}

jQuery('.dropdown-width .close').click(function () {//关闭搜索内容
    jQuery('.dropdown-width').hide();
});


//头部搜索框中类型选择开始

//设置筛选框的值
function searchConditionChange() {
    ishascondition();
    var searcharr = [];
    for (var o in searchjson) {
        if (searchjson[o] != false && o != 'uid' && o != 'fid'  && o != 'userselect' && o != 'flagval') {
            searcharr.unshift(o + ':' + searchjson[o] + ' ');
        }
    }
    var searchval = searcharr.join(' ');
    $('#searchval').val(searchval);

}

var usernamearr = [];
//指定类型
jQuery('.dropdown-type .search_type li').click(function () {
    var val = jQuery(this).find('a').data('val');
    if (typeof val == 'undefined') {
        val = '';
    }
    searchjson['type'] = val;
    var text = jQuery(this).text();
    jQuery(this).closest('.dropdown-type').find('.anytime').text(text);
    searchConditionChange();
})

//指定用户
jQuery('.dropdown-type .searchowner li').click(function () {
    var val = jQuery(this).find('a').data('val');
    if (typeof val == 'undefined') {
        val = '';
    }
    if (val == 'user') {
        jQuery(this).parents('.dropdown-type').find('.typeowner,.name_emile').show();
        usernamearr = [];
        var text = jQuery(this).text();
        jQuery(this).closest('.dropdown-type').find('.anytime').text(text);
    } else {
        jQuery(this).parents('.dropdown-type').find('.typeowner').hide();
        $('#id_label_multiples').val(null).trigger('change');
        usernamearr = [];
        searchjson['owner'] = val;
        searchjson['uid'] = val;
        var text = jQuery(this).text();
        jQuery(this).closest('.dropdown-type').find('.anytime').text(text);
        searchConditionChange();
    }
})


//指定时间
jQuery('.dropdown-type .searchdate li').click(function () {
    var val = jQuery(this).find('a').data('val');
    if (typeof val == 'undefined' || val == 'all') {
        val = '';
    }
    //日期选择器
    if (val == 'datetime') {
        jQuery(this).parents('.dropdown-type').find('.typexdate').show();
        var text = jQuery(this).text();
        jQuery(this).closest('.dropdown-type').find('.anytime').text(text);
    } else {
        jQuery(this).parents('.dropdown-type').find('.typexdate').hide();
        if (val != '') {
            val = getRecentDate(val);
        }
        searchjson['after'] = val;
        var text = jQuery(this).text();
        jQuery(this).closest('.dropdown-type').find('.anytime').text(text);
        searchConditionChange();
    }
})



$('#selectStart').change(function () {
    var start = $('#selectStart').val();
    var end = $('#selectEnd').val();
    if (satrtdate != '' && enddate != '') {
        var satrtdate = new Date(start);
        var enddate = new Date(end);
        if (enddate.getTime() < satrtdate.getTime()) {
            showmsg('开始时间不能大于结束时间');
            return false;
        }
    }
    searchjson['after'] = start;
    searchConditionChange();
})
jQuery('#selectEnd').change(function () {
    var start = $('#selectStart').val();
    var end = $('#selectEnd').val();
    if (satrtdate != '' && enddate != '') {
        var satrtdate = new Date(start);
        var enddate = new Date(end);
        if (enddate.getTime() < satrtdate.getTime()) {
            showmsg('开始时间不能大于结束时间');
            return false;
        }
    }
    searchjson['before'] = end;
    searchConditionChange();
});
//标记
jQuery(document).on('change', '.header-seaech-checkbox .checkbox-primary input[name="flag[]"]', function () {
    var obj = jQuery(this);//aaaa
    var pname = obj.next('label').text();
    var val = obj.val();
    if (obj.prop('checked')) {
        searchjson['flagval'].push(val);
        searchjson['flag'].push(pname);
    } else {
        if (jQuery.inArray(val, searchjson['flagval']) != -1) {
            searchjson['flagval'].splice(jQuery.inArray(val, searchjson['flagval']), 1);
        }

        if (jQuery.inArray(pname, searchjson['flag']) != -1) {
            searchjson['flag'].splice(jQuery.inArray(pname, searchjson['flag']), 1);
        }
    }
    searchConditionChange();
})
//位置
jQuery(document).on('change', '#header-seaech-checkbox .form-check input[name="position[]"]', function () {
    var obj = jQuery(this);
    var numpreg = /^\d+$/;
    var pname = obj.next('label').text();
    var val = obj.val();
    if (obj.prop('checked')) {
        searchjson['fid'].push(val);
        searchjson['position'].push(pname);
    } else {
        if (jQuery.inArray(val, searchjson['fid']) != -1) {
            searchjson['fid'].splice(jQuery.inArray(val, searchjson['fid']), 1);
        }
        if (jQuery.inArray(pname, searchjson['position']) != -1) {
            searchjson['position'].splice(jQuery.inArray(pname, searchjson['position']), 1);
        }
    }
    searchConditionChange();
})

//文件名
jQuery('#resourcesname').blur(function () {
    var val = jQuery(this).val();
    searchjson['keywords'] = val;
    searchConditionChange();
})

//根据前几天或后几天获取日期函数
function getRecentDate(num) {
    var now = new Date;
    if (num != 1) {
        now.setDate(now.getDate() + num);//获取num天后的日期
    }
    var y = now.getFullYear();
    var m = (now.getMonth() + 1) < 10 ? '0' + (now.getMonth() + 1) : (now.getMonth() + 1);
    var d = now.getDate() < 10 ? '0' + now.getDate() : now.getDate();
    return y + '-' + m + '-' + d;
}

//多条件搜索提交
jQuery('#conditionSearchFile').click(function () {
    jQuery('.dropdown-width').hide();
    execute_search();
});
//点击搜索图标搜索
$(document).on('click', '.input-search-icon', function () {
    var val = $('#searchval').val();
    if (emptypreg.test(val)) {
        return false;
    }
    parseSearchInputVal(val);
	execute_search();
	
});
//输入框值发生改变
jQuery('#searchval').change(function(){
    var val = $(this).val();
    parseSearchInputVal(val);
});
//点击重置搜索条件
$(document).on('click', '.resetting', function () {
    resetting_condition();
});
//处理输入框值
function parseSearchInputVal(val){
    var emptyprge = /\s+/;
    var questryjson = {'after': 0, 'before': 0, 'owner': 0, 'type': 0, 'position': 0, 'keywords': ''};
    val = val.split(emptyprge);
    var splitstr = /\s*:\s*/;
    for(var o in val){
        if(splitstr.test(val[o])){
            var arr =  val[o].split(splitstr);
            if(typeof questryjson[arr[0]] != 'undefined'){
                questryjson[arr[0]] = arr[1];
            }
        }else{
            questryjson['keywords'] += val[o]+' ';
        }

    }
    createQueryStr(questryjson);
}
//根据输入框值生成搜索条件执行搜索
function createQueryStr(json){
    var usernoparse = ['self','noself'];
    var flagnoparse = ['已收藏','在回收站'];
    var username = '';
    var foldername = '';
    if(json['owner']){
        if($.inArray(json['owner'],usernoparse) == -1 && json['owner'] != 'all'){
            username = json['owner'];
        }else if(json['owner'] == 'all'){
            json.uid = 0;
        }else{
            json.uid = json['owner'];
        }
    }
    if(json['position']){
        var positions = json['position'].split(',');
        var foldername = '';
        json.flag = '';
        for(var o in positions){
            if(positions[o] == '已收藏'){
                json.flag += 'isstarred,';
            }else if(positions[o] == '在回收站'){
                json.flag += 'isdelete,';
            }else{
                foldername += positions[o]+',';
            }

        }
        if(json.flag != false && json.flag.charAt(json.flag.length - 1) == ','){
            json.flag = json.flag.substr(0,json.flag.length - 1);
        }
        if(foldername.charAt(foldername.length - 1) == ','){
            foldername = foldername.substr(0,foldername.length - 1);
        }
    }
    if(foldername || username){
        $.post(MOD_URL+'&op=searchFile&do=parseinputcondition',{'foldername':foldername,'username':username},function(data){
            if(data['fids']){
                json.fid = '';
                for(var o in data['fids']){
                    json.fid += data['fids'][o]+',';
                }
                if(json.fid && json.fid.charAt(json.fid.length - 1) == ','){
                    json.fid = json.fid.substr(0,json.fid.length - 1);
                }
            }
            if(data['uids']){
                json.uid = '';
                for(var o in data['uids']){
                    json.uid += data['uids'][o]+',';
                }
                if(json.uid && json.uid.charAt(json.uid.length - 1) == ','){
                    json.uid= json.uid.substr(0,json.uid.length - 1);
                }
            }
            searchjson = json;
            searchConditionChange();
            /*execute_search();*/
        },'json').fail(function (jqXHR, textStatus, errorThrown) {
            showmessage(__lang.do_failed, 'error', 3000, 1);
        });
    }else{
        searchjson = json;
        searchConditionChange();
        /*execute_search();*/
    }
    return false;

}
//生成搜索条件值
function searchvalbuild() {
    var searchSubmitCondition = {"after": 0, "before": 0, "type": 0, "keywords": 0, "uid": 0, "fid": 0, "flagval": 0};
    for (var o in searchSubmitCondition) {
        if (searchjson[o]) {
            searchSubmitCondition[o] = searchjson[o];
        }
    }
    return searchSubmitCondition;
}

//执行搜索
function execute_search() {
    var searchSubmitCondition = searchvalbuild();
    var querystr = '';
    for (var o in searchSubmitCondition) {
        if (!searchSubmitCondition[o] || searchSubmitCondition[o].length == 0) {
            continue;
        }
        querystr += o + '=' + searchSubmitCondition[o] + '&';
    }
    querystr = querystr.substr(0, querystr.length - 1);
    var requeststr = encodeURIComponent(querystr);
    location.hash = '#searchFile&sid=search&searchtype=' + requeststr;
}

//重置函数
function resetting_condition() {
    $('.dropdown-type').each(function () {
        var obj = $(this);
        var text = obj.find("a[data-val='all']").text();
        obj.find('.anytime').text(text);
        obj.find('.typexdate').hide();
        obj.find('.typeowner').hide();
    });
  	$('#emptysearchcondition').addClass('hide');
    $('#id_label_multiples').val(null).trigger('change');
    $('#selectStart').val('');
    $('#selectEnd').val('');
    $('#resourcesname').val('');
    $('.header-seaech-checkbox').find('input').prop('checked', false);
    searchjson = {
        'after': 0,
        'before': 0,
        'owner': 0,
        'type': 0,
        'position': [],
        'keywords': 0,
        'uid': 0,
        'fid': [],
        'flag': [],
        'flagval':[]
    };
	$('#searchval').val('');
}

//设置搜索框的值
function setSearchCondition() {
    ishascondition();
    var arr = [];
    for (var o in searchjson) {
        if (searchjson[o] != false && o != 'uid' && o != 'fid' && o != 'flagval' && o != 'userselect') {
            arr.unshift(o + ':' + searchjson[o] + ' ');
        }
    }
    var searchval = arr.join(' ');
    $('#searchval').val(searchval);

}
//设置搜索框的值
function setSearchval(searchval) {
    var empty = /^\s*$/;
    if(empty.test(searchval)){
		$('#emptysearchcondition').trigger('click');
        return false;
    }
    //分割请求字符串
    var searcharr = searchval.split('&');

    //遍历请求数组
    for (var o in searcharr) {

        var searchval = searcharr[o].split('=');
        if ($.inArray(searchval[0], searchjsonarr) != -1) {

            searchjson[searchval[0]] = unique(searchval[1].split(','));

        } else {

            searchjson[searchval[0]] = searchval[1];

        }
    }
    //如果flag有值转换flag值
    for (var o in searchjson['flagval']) {
        if (searchjson['flagval'][o] == 'isdelete') {
            searchjson['flag'].push('在回收站');
        }
        if (searchjson['flagval'][o] == 'isstarred') {
            searchjson['flag'].push('已收藏');
        }
    }
    //匹配uid值，如果为数字需要请求得到用户名
    var numpre = /\d+/;
    for (var o in searchjson['uid']) {

        if (!numpre.test(searchjson['uid'])) {

            searchjson['owner'] += searchjson['uid'][o] + ',';

            searchjson['uid'].splice(o, 1);
        }
    }
    var searchSubmitCondition = {'uid': 0, 'fid': 0};

    for (var o in searchSubmitCondition) {
        if (searchjson[o]) {
            searchSubmitCondition[o] = searchjson[o];
        }
    }
    //获取用户和文件夹名
    $.post(_explorer.appUrl + '&op=searchFile&do=getsearchval', searchSubmitCondition, function (data) {
        if (data['folder']) {
            for (var o in data['folder']) {
                searchjson['position'].push(data['folder'][o]);
            }
        }
        if (data['user']) {
            searchjson.userselect = [];
            for (var o in data['user']) {
                searchjson['owner'] += data['user'][o] + ',';
                searchjson.userselect.push({'id':o,'text':data['user'][o]});
            }
            searchjson['owner'] = searchjson['owner'].substr(0, searchjson['owner'].length - 1);
        }
        //设置搜索框值
        setSearchCondition();
    }, 'json').fail(function (jqXHR, textStatus, errorThrown) {
        showmessage(__lang.do_failed, 'error', 3000, 1);
    });

}
function unique(arr) {
    var res = [];
    var json = {};
    for (var i = 0; i < arr.length; i++) {
        if (!json[arr[i]]) {
            res.push(arr[i]);
            json[arr[i]] = 1;
        }
    }
    return res;
}
//搜索页面js结束