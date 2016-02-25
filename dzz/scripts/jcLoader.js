
    /*  
     * jcLoader()  一个简单的 js、css动态加载 api  
     * jcLoader().load(url,callback)  加载函数 支持链式操作  
     * -url 需要加载的 js/css 地址，支持同时加载多个 地址之间用 '，'隔开  
     * -callback 加载完成 url里面的文件之后触发的事件  
     * ---------------------------------------------------  
     * example:  
     
    完整版：  
    jcLoader().load({  
        type:"js",  
        url:"temp/demojs01.js,temp/demojs02.js,temp/demojs03.js,temp/demojs04.js,temp/demojs05.js,"  
    },function(){  
        alert("all file loaded");  
    }).load({  
        type:"css",  
        url:"temp/democss01.css"  
    },function(){  
        alert("all css file loaded");  
    })  
    简单版:  
    jcLoader().load({type:"js",url:"temp/demojs01.js"},function(){alert("all file loaded")});  
    jcLoader().load({type:"css",url:"temp/democss01.css"},function(){alert("all css file loaded")});  
     
     * ---------------------------------------------------  
     * power by jackNEss  
     * date:2011-11-10(光棍节前夕)  
     * ver 1.0  
     */  
   