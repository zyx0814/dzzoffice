/**
 * Created by zhangbo21 on 14-9-2.
 */
/*
 * getKfContent : 将image的src从base64替换为文件名
 * param : callback -- 回调函数 其参数为替换之后的内容
 * return : void
 * */

UE.Editor.prototype.getKfContent = function(callback){

    var me = this;
    var actionUrl = me.getActionUrl(me.getOpt('scrawlActionName')),
        params = UE.utils.serializeParam(me.queryCommandValue('serverparam')) || '',
        url = UE.utils.formatUrl(actionUrl + (actionUrl.indexOf('?') == -1 ? '?':'&') + params);

    // 找到所有的base64
    var count = 0;
    var imgs =me.body.getElementsByTagName('img');
    var base64Imgs = [];
    UE.utils.each(imgs, function(item){
        var imgType = item.getAttribute('src').match(/^[^;]+/)[0];
        if ( imgType === 'data:image/png') {
            base64Imgs.push(item);
        }    
    });

    if (base64Imgs.length == 0){
        execCallback();
    } else {
        UE.utils.each(base64Imgs, function(item){

            var opt ={};
            opt[me.getOpt('scrawlFieldName')]= item.getAttribute('src').replace(/^[^,]+,/, '');
            opt.onsuccess = function(xhr){
                var json = UE.utils.str2json(xhr.responseText),
                    url = me.options.scrawlUrlPrefix + json.url;

                item.setAttribute('src', url);
                item.setAttribute('_src', url);

                count++;

                execCallback();
            }
            opt.onerror = function(err){
                console.error(err);
                count++;

                execCallback();
            }


            UE.ajax.request(url, opt);

        });
    }

    function execCallback(){
        if (count >= base64Imgs.length) {
            ue.sync();
            callback(me.getContent());
        }
    }

};