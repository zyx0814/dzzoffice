/*!
 * 数字滚动增加显示
 * 主要通过class类内的scroll-numbers获取目标元素数字内容
 */
var easing = {
    quadratic: function (x){
      return Math.sqrt(x);
    }
};
//显示总数
function range(start, stop, step){
  var array = [];
  for(var i = start; i < stop; i += step) array.push(i);
  return array;
}

//跨度间隔并返回终值
function interpolation(fps, easing, finalValue){
  function scaleIt(value){return finalValue * value; }

  var x = range(0, 1, 1/fps),
      y = x.map(easing).map(scaleIt);

  return y;
}

//数字滚动
function animateEl(values, duration, onAnimate){
  var frameIndex = 0,
      fps = values.length,
      id = setInterval(anime, duration/fps );

  function anime(){
    var current = values[frameIndex],
        isLastFrame = (frameIndex === fps - 1);

    onAnimate(current, frameIndex, values);

    if(isLastFrame){
      clearInterval(id);
    }else{
      frameIndex++;
    }
  }
}

function round(value, decimals) {
  return Number(Math.round(value+'e'+decimals)+'e-'+decimals);
}

function unformat(content){
  var unlocalized = content.replace('.', '').replace(',', '.'),
      value = parseFloat(unlocalized);
  return value;
}

//格式化输出
function format(value){
  return value.toString().replace('.', ',');
}

//添加事件监听器
window.addEventListener("DOMContentLoaded", function(){
    var fps = 240,//滚动帧率
        els = [].slice.call(document.querySelectorAll('.scroll-numbers'));

    els.forEach(function(el){
        var content = (el.firstChild.textContent).trim(),
            decimalPlaces = content.split(',')[1] || '',
            value = unformat(content),
            values = interpolation(fps, easing.quadratic, value);
			//滚动持续时间：1秒
        animateEl(values, 1000, function (current, i, values){
          var isLast = (i === values.length - 1),
              value = round(current, decimalPlaces.length);
          el.firstChild.textContent = isLast? content : format(value);
        });
    });
});
