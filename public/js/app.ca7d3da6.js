(function(t){function e(e){for(var i,a,l=e[0],c=e[1],r=e[2],d=0,p=[];d<l.length;d++)a=l[d],Object.prototype.hasOwnProperty.call(s,a)&&s[a]&&p.push(s[a][0]),s[a]=0;for(i in c)Object.prototype.hasOwnProperty.call(c,i)&&(t[i]=c[i]);u&&u(e);while(p.length)p.shift()();return o.push.apply(o,r||[]),n()}function n(){for(var t,e=0;e<o.length;e++){for(var n=o[e],i=!0,l=1;l<n.length;l++){var c=n[l];0!==s[c]&&(i=!1)}i&&(o.splice(e--,1),t=a(a.s=n[0]))}return t}var i={},s={app:0},o=[];function a(e){if(i[e])return i[e].exports;var n=i[e]={i:e,l:!1,exports:{}};return t[e].call(n.exports,n,n.exports,a),n.l=!0,n.exports}a.m=t,a.c=i,a.d=function(t,e,n){a.o(t,e)||Object.defineProperty(t,e,{enumerable:!0,get:n})},a.r=function(t){"undefined"!==typeof Symbol&&Symbol.toStringTag&&Object.defineProperty(t,Symbol.toStringTag,{value:"Module"}),Object.defineProperty(t,"__esModule",{value:!0})},a.t=function(t,e){if(1&e&&(t=a(t)),8&e)return t;if(4&e&&"object"===typeof t&&t&&t.__esModule)return t;var n=Object.create(null);if(a.r(n),Object.defineProperty(n,"default",{enumerable:!0,value:t}),2&e&&"string"!=typeof t)for(var i in t)a.d(n,i,function(e){return t[e]}.bind(null,i));return n},a.n=function(t){var e=t&&t.__esModule?function(){return t["default"]}:function(){return t};return a.d(e,"a",e),e},a.o=function(t,e){return Object.prototype.hasOwnProperty.call(t,e)},a.p="/";var l=window["webpackJsonp"]=window["webpackJsonp"]||[],c=l.push.bind(l);l.push=e,l=l.slice();for(var r=0;r<l.length;r++)e(l[r]);var u=c;o.push([0,"chunk-vendors"]),n()})({0:function(t,e,n){t.exports=n("56d7")},"0e0d":function(t,e,n){},"197e":function(t,e,n){},"1f27":function(t,e,n){"use strict";n("c5be")},3139:function(t,e,n){"use strict";n("a309")},3732:function(t,e,n){"use strict";n("197e")},"3a10":function(t,e,n){},"4dcb":function(t,e,n){},"4ee2":function(t,e,n){},"56d7":function(t,e,n){"use strict";n.r(e);n("0fae");var i=n("9e2f"),s=n.n(i),o=(n("e260"),n("e6cf"),n("cca6"),n("a79d"),n("2b0e")),a=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{attrs:{id:"app"},on:{click:t.onAppClick}},[n("router-view")],1)},l=[],c=n("5530"),r=n("1157"),u=n.n(r),d=n("2f62"),p={name:"App",data:function(){return{appClick:Date.now()}},methods:Object(c["a"])(Object(c["a"])({},Object(d["b"])(["setAppClick","setAppKeyF2","setAppKeyCtrlC","setAppKeyCtrlV"])),{},{onAppClick:function(){this.setAppClick(Date.now())}}),mounted:function(){var t=this;u()(document).on("keyup",(function(e){var n=e.keyCode,i=e.ctrlKey;113===n&&t.setAppKeyF2(Date.now()),i&&(67===n&&t.setAppKeyCtrlC(Date.now()),86===n&&t.setAppKeyCtrlV(Date.now()))}))}},f=p,h=(n("3139"),n("2877")),m=Object(h["a"])(f,a,l,!1,null,"75a5614e",null),v=m.exports,g=(n("a434"),{uploadFileList:[],totalProgress:0,inProgressNum:0,allUploadComplete:0}),C={uploadFile:function(t,e){t.uploadFileList.push(e)},uploader:function(t,e){t.uploader=e},totalProgress:function(t,e){t.totalProgress=e},changeProgress:function(t,e){for(var n=e[0],i=e[1],s=-1,o=0,a=t.uploadFileList.length;o<a;o++)if(t.uploadFileList[o].uuid==n){s=o;break}var l=t.uploadFileList[s];l.progress=i,t.uploadFileList.splice(s,1,l)},inProgressNum:function(t,e){t.inProgressNum=e},allUploadComplete:function(t,e){t.allUploadComplete=e}},y={uploadFile:function(t,e){var n=t.commit;n("uploadFile",e)},uploader:function(t,e){var n=t.commit;n("uploader",e)},setTotalProgress:function(t,e){var n=t.commit;n("totalProgress",e)},changeProgress:function(t,e){var n=t.commit;n("changeProgress",e)},setInProgressNum:function(t,e){var n=t.commit;n("inProgressNum",e)},setAllUploadComplete:function(t,e){var n=t.commit;n("allUploadComplete",e)}},b={namespaced:!0,state:g,mutations:C,actions:y},L=b;o["default"].use(d["a"]);var P=new d["a"].Store({modules:{index:L},state:{appClick:0,appKeyF2:0,appKeyCtrlC:0,appKeyCtrlV:0},mutations:{onAppClick:function(t,e){t.appClick=e},appKeyF2:function(t,e){t.appKeyF2=e},appKeyCtrlC:function(t,e){t.appKeyCtrlC=e},setAppKeyCtrlV:function(t,e){t.appKeyCtrlV=e}},actions:{setAppClick:function(t,e){var n=t.commit;n("onAppClick",e)},setAppKeyF2:function(t,e){var n=t.commit;n("appKeyF2",e)},setAppKeyCtrlC:function(t,e){var n=t.commit;n("appKeyCtrlC",e)},setAppKeyCtrlV:function(t,e){var n=t.commit;n("setAppKeyCtrlV",e)}}}),w=(n("4ee2"),n("3a10"),n("4dcb"),n("2b27")),k=n.n(w),x=n("e5d9"),_=n("8c4f"),F=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"wrap"},[n("div",{staticClass:"header"},[n("div",{staticClass:"back",on:{click:t.goBack}},[n("span",{staticClass:"icon iconfont iconnext-copy"})]),n("div",{staticClass:"title"},[t._v(t._s(t.title))]),n("el-dropdown",{staticClass:"downList"},[n("span",{staticClass:"el-dropdown-link"},[t._v(" 用户3"),n("i",{staticClass:"icon el-icon-arrow-down el-icon--right"})]),n("el-dropdown-menu",{attrs:{slot:"dropdown"},slot:"dropdown"},[n("el-dropdown-item",{staticClass:"downItem"},[n("span",{staticClass:"icon iconfont iconexit01-copy",style:t.downItemIcon}),t._v(" 退出 ")])],1)],1)],1),n("div",{staticClass:"mainWrap"},[n("div",{staticClass:"main"},[n("el-menu",{staticClass:"el-menu-vertical-demo",attrs:{collapse:t.isCollapse}},[n("el-menu-item",{attrs:{index:"0"},on:{click:t.onCollapse}},[n("i",{staticClass:"el-icon-s-fold"}),n("span",{attrs:{slot:"title"},slot:"title"},[t._v("展开收起")])]),n("el-menu-item",{attrs:{index:"1"},on:{click:t.onSideAll}},[n("i",{staticClass:"el-icon-menu"}),n("span",{attrs:{slot:"title"},slot:"title"},[t._v("全部文件")])]),n("el-menu-item",{attrs:{index:"2"},on:{click:t.onSideUpload}},[n("i",{staticClass:"el-icon-upload2"}),n("el-badge",{staticClass:"menuItem2Badge",attrs:{slot:"title",value:t.inProgressNum,max:99},slot:"title"},[n("span",[t._v("上传列表")])])],1)],1),n("div",{staticClass:"mainBody"},[n("router-view",{on:{upload:t.onUpload}})],1)],1)]),n("div",{staticClass:"footer"})])},B=[],A=(n("b0c0"),n("d3b7"),n("40c8")),O=n.n(A),S=n("53ca");n("7db0");function I(t){if("object"===Object(S["a"])(t))return t;var e={status:-1,msg:"服务错误，请重试"};try{e=JSON.parse(t)}catch(n){e={status:-1,msg:t}}return e}function q(t){return{succ:function(e){t.$notify.success({title:"成功",message:e,duration:1e3})},info:function(e){t.$notify({title:"提示",message:e,duration:1e3})},error:function(e){t.$notify.error({title:"警告",message:e,duration:1e3})}}}function E(t,e){var n=t.container,i=t.item,s=t.selectedClassName,o=void 0===s?"selected":s,a=e.mouseUpFn,l=void 0===a?function(){}:a,c=e.mouseDownFn,r=void 0===c?function(){}:c,d=e.mouseMoveFn,p=void 0===d?function(){}:d,f=e.selectedFn,h=void 0===f?function(){}:f,m=$(j,200),v=$(p,200),g=u()("".concat(n)),C=g.find("".concat(i)),y=g.parent(),b=y.find(".selectBox"),L=y.offset().left,P=y.offset().top,w=0,k=0;g.css("user-select","none"),y.css("user-select","none"),u()(document).on("mousedown",(function(t){0===b.length&&(y.append('<div class="selectBox"></div>'),b=y.find(".selectBox")),r(),w=t.clientX-L,k=t.clientY-P+y.scrollTop(),C=g.find("".concat(i)),C.css("user-select","none"),C.removeClass(o),C.each((function(t,e){return u()(e).data("index",t)})),b.css({top:k,left:w,width:0,height:0,display:"block"}),u()(document).on("mousemove",(function(t){var e=t.clientX-L,n=t.clientY-P;e=e<=0?0:e,n=n<=0?0:n,e=e>=y.innerWidth()-2?y.innerWidth()-2:e,n=n>=y.height()-2?y.height()-2:n;var i=e-w,s=n-k+y.scrollTop(),a=i<0?w+i:w,l=s<0?k+s:k;b.css({width:Math.abs(i),height:Math.abs(s),left:a,top:l}),v(),m({$elList:C,$selectBox:b,$parent:y},(function(t){C.removeClass(o),u()(t).addClass(o),h(t)}))}))})),u()(document).mouseup((function(){b.css({display:"none",width:0,height:0}),u()(document).off("mousemove"),l()}))}function j(t){var e=arguments.length>1&&void 0!==arguments[1]?arguments[1]:function(){},n=t.$elList,i=t.$selectBox,s=t.$parent,o=[];n.each((function(t,e){var n=u()(e),a=n.position().left,l=n.position().top,c=a+n.outerWidth(),r=l+n.outerHeight(),d=i.position().left,p=d+i.outerWidth(),f=i.position().top+s.scrollTop(),h=f+i.outerHeight();d<c&&f<r&&h>l&&p>a&&o.push(n[0])})),e(o)}function $(t,e){var n=!0;return function(){var i=arguments;if(!n)return!1;n=!1,setTimeout((function(){t.apply(null,i),n=!0}),e)}}var D="http://localhost:89",U="".concat(D,"/api/clodedisk/folder"),T="".concat(D,"/api/clodedisk/list"),K="".concat(D,"/api/clodedisk"),N="".concat(D,"/api/clodedisk/folder/name"),R="".concat(D,"/api/clodedisk/file/name"),M="".concat(D,"/api/clodedisk/resource/copy"),z="".concat(D,"/api/clodedisk/resource/cut"),G="".concat(D,"/api/clodedisk/upload"),V={NEW_FOLDER:U,LIST_BY_PATH:T,DEL:K,RENAME_FOLDER:N,RENAME_FILE:R,PASET:M,PASET_CUT:z,PREFIX:D,UPLOAD:G},W=new O.a.FineUploaderBasic({autoUpload:!0,maxConnections:3,request:{endpoint:V.UPLOAD,methods:"POST",customHeaders:{"X-CSRF-TOKEN":u()('meta[name="csrf-token"]').attr("content")}},chunking:{enabled:!0,mandatory:!0,partSize:5242880},callbacks:{},title:"首页"}),H={name:"Index",props:["appClick"],data:function(){return{downItemIcon:{position:"relative",top:"3px",fontSize:"22px"},isCollapse:!1,collapseLabel:"收起",curInUploadNum:0}},methods:Object(c["a"])(Object(c["a"])({},Object(d["b"])("index",["uploadFile","setTotalProgress","changeProgress","setInProgressNum","setAllUploadComplete"])),{},{autoLogin:function(){this.$cookies.set("seller_id",1)},onCollapse:function(){this.isCollapse=!this.isCollapse,this.collapseLabel=this.isCollapse?"展开":"收起"},onSideAll:function(){this.$router.push("/")},onSideUpload:function(){this.$router.push("/progress")},onUpload:function(t,e){W.addFiles(t,{fid:e})},qqUpload:function(t){var e=W.getFile(t);return this.uploadFile({name:e.name,progress:0,uuid:W.getUuid(t),file:e}),this.setInProgressNum(W.getInProgress()),Promise.resolve()},qqUploadChunk:function(){},qqProgress:function(t,e,n,i){var s=Math.ceil(n/i*100),o=W.getUuid(t);this.changeProgress([o,s])},qqComplete:function(){this.setInProgressNum(W.getInProgress())},qqTotalProgress:function(t,e){if(0!=t&&0!=e){var n=Math.ceil(t/e*100);this.setTotalProgress(n)}},qqError:function(t,e,n,i){var s=I(i.responseText),o=s.msg;this.$message.warning(o)},qqAllComplete:function(){this.setAllUploadComplete(Date.now())},initUploaderCallback:function(){W._options.callbacks.onUploadChunk=this.qqUploadChunk,W._options.callbacks.onUpload=this.qqUpload,W._options.callbacks.onProgress=this.qqProgress,W._options.callbacks.onComplete=this.qqComplete,W._options.callbacks.onTotalProgress=this.qqTotalProgress,W._options.callbacks.onError=this.qqError,W._options.callbacks.onAllComplete=this.qqAllComplete},goBack:function(){this.$router.back()}}),computed:Object(c["a"])(Object(c["a"])({},Object(d["c"])("index",["inProgressNum"])),{},{title:function(){return this.$route.meta.title}}),watch:{},mounted:function(){window.vm=this,window.$=u.a,this.autoLogin(),this.initUploaderCallback()}},X=H,J=X,Y=(n("1f27"),Object(h["a"])(J,F,B,!1,null,"b9b119d0",null)),Z=Y.exports,Q=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"wrap"},[n("div",{staticClass:"mainBodyHeader"},[n("el-button",{staticClass:"btn",attrs:{size:"mini"},on:{click:t.onNewFolder}},[t._v("新建文件夹")]),n("el-button",{staticClass:"btn",attrs:{size:"mini",disabled:t.selectedList.length>1},on:{click:t.onRenameBtn}},[t._v("重命名")]),n("el-button",{staticClass:"btn",attrs:{size:"mini",disabled:0==t.selectedList.length},on:{click:t.onDelBtn}},[t._v("删除")]),n("el-button",{staticClass:"btn",attrs:{size:"mini"},on:{click:t.onUploadBtn}},[t._v("上传文件")]),n("el-button",{staticClass:"btn",attrs:{size:"mini",disabled:0==t.selectedList.length},on:{click:t.onCopyBtn}},[t._v("复制")]),n("el-button",{staticClass:"btn",attrs:{size:"mini",disabled:0==t.selectedList.length},on:{click:t.onCutBtn}},[t._v("剪切")]),n("el-button",{staticClass:"btn",attrs:{size:"mini",disabled:0==t.copyList.length},on:{click:t.onPasetBtn}},[t._v("粘贴")]),n("div",{staticStyle:{display:"none"}},[n("input",{ref:"qqfile",attrs:{type:"file",id:"qqfile",multiple:""},on:{change:t.onUpload}})])],1),n("el-breadcrumb",{staticClass:"mainBodyCrumb",attrs:{"separator-class":"el-icon-arrow-right"}},t._l(t.crumb,(function(e){return n("el-breadcrumb-item",{key:e.id,staticClass:"crumbItem",attrs:{to:e.path}},[t._v(t._s(e.name))])})),1),n("div",{staticClass:"checkAllBar"},[n("el-checkbox",{attrs:{value:t.isSelectAll},on:{change:t.onSelectAll}},[t._v("全选")]),n("div",{staticClass:"tip"},[t._v("已选择 "),n("span",[t._v(t._s(t.selectedList.length))]),t._v(" 项")])],1),n("div",{staticClass:"mainBodyListWrap",on:{contextmenu:function(e){return e.preventDefault(),t.onContextmenu(e)}}},[n("div",{ref:"mainBodyListScroll",staticClass:"mainBodyListScroll"},[n("div",{ref:"mainBodyList",staticClass:"mainBodyList clear"},[t._l(t.curFolderData,(function(e){return n("el-popover",{key:e.id,attrs:{width:"100",trigger:"manual",placement:"right-start"},model:{value:e.isShowContextmenu,callback:function(n){t.$set(e,"isShowContextmenu",n)},expression:"item.isShowContextmenu"}},[n("div",{ref:"mainBodyListItem",refInFor:!0,staticClass:"item mainBodyListItem",class:{itemImg:t.isImg(e),itemActive:e.selected},attrs:{slot:"reference",title:e.name},on:{dblclick:function(n){return n.stopPropagation(),t.onOpenFolder(e)},click:function(n){return n.stopPropagation(),t.onResourceClick(e,n)},contextmenu:function(n){return n.preventDefault(),n.stopPropagation(),t.onRigClick(e)},mousedown:function(t){t.stopPropagation()}},slot:"reference"},[n("div",{staticClass:"itemSelectIcon"},[n("span",{staticClass:"itemSelectIconIcon iconfont iconweibiaoti521"})]),t.isImg(e)?[n("div",{staticClass:"imgPreviewBtn",on:{click:function(n){return n.stopPropagation(),t.onPreviewImg(e)}}},[n("span",{staticClass:"imgPreviewBtnIcon iconfont iconsousuo-"})]),n("el-image",{attrs:{src:e.img_path_sm,fid:"cover"}})]:["folder"==e.type?n("i",{staticClass:"icon el-icon-folder-opened"}):n("i",{staticClass:"icon el-icon-tickets"}),n("span",{staticClass:"text"},[t._v(t._s(e.name))])]],2),n("div",{staticClass:"fileContextmenu"},[n("div",{directives:[{name:"show",rawName:"v-show",value:"folder"===e.type,expression:"item.type === 'folder'"}],class:t.selectedList.length>2?"disabled":"",on:{click:function(n){return t.onOpenFolder(e)}}},[t._v("打开")]),n("div",{directives:[{name:"show",rawName:"v-show",value:t.isImg(e),expression:"isImg(item)"}],class:t.selectedList.length>2?"disabled":"",on:{click:function(n){return t.onPreviewImg(e)}}},[t._v("查看大图")]),n("div",{on:{click:function(n){return t.onCopy(e)}}},[t._v("复制")]),n("div",{on:{click:function(n){return t.onCut(e)}}},[t._v("剪切")]),n("div",{class:t.selectedList.length>2?"disabled":"",on:{click:function(n){return t.onRename(e)}}},[t._v("重命名")]),n("div",{on:{click:function(n){return t.onDel(e)}}},[t._v("删除")])])])})),n("el-dialog",{attrs:{title:t.imgPreview.alias,visible:t.imgPreview.visible,width:t.imgPreview.width,center:"",modal:"","modal-append-to-body":"","append-to-body":""},on:{"update:visible":function(e){return t.$set(t.imgPreview,"visible",e)}}},[n("el-image",{attrs:{src:t.imgPreview.path,fit:"cover"}})],1)],2)])])],1)},tt=[],et=n("2909"),nt=(n("d81d"),n("1276"),n("ac1f"),n("99af"),n("159b"),n("a15b"),n("a9e3"),n("aece"),{name:"IndexIndex",data:function(){return{collapseLabel:"收起",curFolderData:[],curFid:0,crumb:[],imgExtList:["jpg","jpeg","png"],imgPreview:{path:"",visible:!1,title:"",width:"0",alias:""},curPath:"",selectedList:[],selfRouteUrl:"/folder",copyList:[],isOnDragSelect:!1,isCut:!1}},methods:{onNewFolder:function(){var t=this;this.$prompt("请输入文件夹名","新建文件夹",{confirmButtonText:"确定",cancelButtonText:"取消",inputPattern:/^[\u4e00-\u9fa5_a-zA-Z0-9/-]{2,16}$/,inputErrorMessage:"文件夹名只能包含中英文下划线，短斜线，反斜线，字数在1-16字"}).then((function(e){var n=e.value;u.a.post(V.NEW_FOLDER,{fid:t.curFid,folderName:n}).then((function(e){var n=I(e),i=n.status,s=n.msg;-1!=i?t.onGetListAllByPath(t.curPath):t.notify.error(s)}))}))},onGetListAllByPath:function(){var t=this,e=arguments.length>0&&void 0!==arguments[0]?arguments[0]:"/";u.a.get(V.LIST_BY_PATH,{page:1,pageSize:100,path:e}).then((function(n){var i=I(n),s=i.msg,o=i.data,a=i.status;if(-1==a)return t.notify.error(s),!1;t.curFolderData=o.data.map((function(t){t.isShowContextmenu=!1;var e=t.name.split("."),n=e.length>1?e.pop():"";return t.ext=n,t.selected=!1,t})),t.crumb=[];for(var l=o.crumbData,c=0,r=l.length;c<r;c++){var u=l[c].path,d={path:c+1!=r&&"".concat(t.selfRouteUrl,"?path=").concat(u),name:l[c].name};t.crumb.push(d)}return t.curFid=o.fid,t.curPath=e,t.selectedList=[],t.$nextTick((function(){return t.initDragSelect()})),!0}))},onOpenFolder:function(t){if(!(this.selectedList.length>2)){var e=t.path;this.$router.push("".concat(this.selfRouteUrl,"?path=").concat(e))}},onResourceClick:function(t,e){var n=e.ctrlKey;if(n)if(t.selected){for(var i=-1,s=this.selectedList,o=0,a=s.length;o<a;o++)if(s[o].id===t.id){i=o;break}t.selected=!1,this.selectedList.splice(i,1)}else t.selected=!0,this.selectedList.push(t);else this.selectedList.forEach((function(t){return t.selected=!1})),this.selectedList=[],this.selectedList.push(t),t.selected=!0;this.curFolderData.forEach((function(t){return t.isShowContextmenu=!1}))},onRigClick:function(t){t.isShowContextmenu=!0},delByIdList:function(t){var e=this;return u.a.post(V.DEL,{_method:"delete",idList:t}).then((function(t){var n=JSON.parse(t),i=n.msg,s=n.status;return-1==s&&e.notify.error(i),t}))},onDel:function(t,e){var n=this,i=[];if(null==t)i=e;else{var s=t.id,o=t.type;t.isShowContextmenu=!1,i=this.selectedList.length>1?Object(et["a"])(this.selectedList).map((function(t){return{id:t.id,type:t.type}})):[{id:s,type:o}]}this.$confirm("此操作将永久删除该文件, 是否继续?","确定删除",{confirmButtonText:"确定",cancelButtonText:"取消",type:"warning"}).then((function(){n.delByIdList(i).then((function(t){var e=I(t),i=e.status;1==i&&n.onGetListAllByPath(n.curPath)}))}))},onRename:function(t){var e=this;if(!(this.selectedList.length>2)){t.isShowContextmenu=!1;var n=t.type,i=this.curFid,s="folder"===n?t.name.split("."):t.alias.split(".");s.length>1&&s.pop(),s=s.join(""),this.$prompt("请输入新的文件名","提示",{confirmButtonText:"确定",cancelButtonText:"取消",inputPattern:/^[^\\\\/:\\*\\?"<>\\|]{1,}$/,inputErrorMessage:"文件名不能为空，不能出现 \\/:*?<>|",inputValue:s}).then((function(s){var o=s.value.split(".").pop(),a={_method:"put",id:t.id,name:o,fid:i},l="folder"===n?u.a.post(V.RENAME_FOLDER,a):u.a.post(V.RENAME_FILE,a);l.then((function(t){var n=I(t),i=n.status,s=n.msg;-1==i?e.notify.error(s):e.onGetListAllByPath(e.curPath)}))}))}},isImg:function(t){return-1!=this.imgExtList.indexOf(t.ext)},onRenameBtn:function(){if(!(this.selectedList.length>1)){var t=this.selectedList[0];this.onRename(t)}},onDelBtn:function(){if(0!=this.selectedList.length){var t=this.selectedList.map((function(t){return{type:t.type,id:t.id}}));this.onDel(null,t)}},onPreviewImg:function(t){var e=this;if(!(this.selectedList.length>2)){var n=t.img_path,i=t.alias;this.imgPreview.path=n,this.imgPreview.alias=i;var s=u()("body").width(),o=new Image;o.src=n,o.onload=function(){var t=Number(o.width),n=0;n=t>s?s:t,e.imgPreview.width=n+"px",e.imgPreview.visible=!0},t.isShowContextmenu=!1}},onUploadBtn:function(){var t=this.$refs.qqfile;t.click()},onUpload:function(){var t=this.$refs.qqfile,e=t.files;this.$emit("upload",e,this.curFid)},onCopyBtn:function(){0!=this.selectedList.length&&(this.isCut=!1,this.copyList=this.selectedList.map((function(t){return{id:t.id,type:t.type}})))},onCopy:function(t){this.isCut=!1,this.selectedList.length>1?this.copyList=Object(et["a"])(this.selectedList).map((function(t){return{id:t.id,type:t.type}})):this.copyList=[{id:t.id,type:t.type}],t.isShowContextmenu=!1},onCutBtn:function(){0!=this.selectedList.length&&(this.copyList=this.selectedList,this.isCut=!0)},onCut:function(t){this.onCopy(t),this.isCut=!0},onPasetBtn:function(){var t=this.copyList,e=this.curFid;this.isCut?this.doPasetOnCut(e,t):this.doPaset(e,t)},doPaset:function(t,e){var n=this;u.a.post(V.PASET,{_method:"put",idList:e,distId:t}).then((function(t){var e=I(t),i=e.msg,s=e.status;-1!=s?(n.notify.succ("粘贴成功"),n.onGetListAllByPath(n.curPath)):n.notify.error(i)}))},doPasetOnCut:function(t,e){var n=this;u.a.post(V.PASET_CUT,{_method:"put",distId:t,idList:e}).then((function(t){var e=I(t),i=e.msg,s=e.status;-1==s&&n.notify.error(i),n.isCut=!1,n.onGetListAllByPath(n.curPath)}))},onContextmenu:function(t){var e=this;return this.$contextmenu({items:[{label:"刷新",onClick:function(){e.onGetListAllByPath(e.curPath).then((function(){e.notify.info("已更新")}))}},{label:"新建文件夹",onClick:function(){e.onNewFolder()}},{label:"粘贴",disabled:!e.copyList.length,onClick:function(){e.isCut?e.doPasetOnCut(e.curFid,e.copyList):e.doPaset(e.curFid,e.copyList)}}],event:t}),!1},initDragSelect:function(){var t=this;E({container:".mainBodyList",item:".mainBodyListItem"},{selectedFn:function(e){t.curFolderData.forEach((function(t){return t.selected=!1}));var n=[];u()(e).each((function(e,i){var s=u()(i).data("index");t.curFolderData[s].selected=!0,n.push(t.curFolderData[s])})),t.selectedList=n}})},onSelectAll:function(){this.isSelectAll?(this.selectedList.forEach((function(t){return t.selected=!1})),this.selectedList=[]):(this.selectedList=Object(et["a"])(this.curFolderData),this.selectedList.forEach((function(t){return t.selected=!0})))}},watch:{"$route.fullPath":function(){var t=this.$route.query.path;this.onGetListAllByPath(t)},allUploadComplete:function(){this.onGetListAllByPath(this.curPath),this.$refs.qqfile.value=""},appKeyF2:function(){if(1===this.selectedList.length){var t=this.selectedList[0];this.onRename(t)}},appKeyCtrlC:function(){this.selectedList.length>0&&(this.onCopyBtn(),this.notify.info("已复制"))},appKeyCtrlV:function(){this.copyList.length?this.onPasetBtn():this.notify.info("请先复制东西")}},computed:Object(c["a"])(Object(c["a"])(Object(c["a"])({},Object(d["c"])("index",["allUploadComplete"])),Object(d["c"])(["appClick","appKeyF2","appKeyCtrlC","appKeyCtrlV"])),{},{notify:function(){return q(this)},isSelectAll:function(){return 0!==this.curFolderData.length&&this.selectedList.length===this.curFolderData.length}}),mounted:function(){var t=this.$route.query.path;t?this.onGetListAllByPath(t):this.$router.push({name:"indexIndex",query:{path:"/"}})}}),it=nt,st=it,ot=(n("e610"),Object(h["a"])(st,Q,tt,!1,null,"156b8e32",null)),at=ot.exports,lt=function(){var t=this,e=t.$createElement,n=t._self._c||e;return n("div",{staticClass:"wrap"},[n("div",{staticClass:"header"},[n("div",{staticClass:"progressBar"},[n("span",{staticClass:"text"},[t._v("总进度：")]),n("div",{staticClass:"bar"},[n("el-progress",{staticClass:"elBar",attrs:{percentage:t.totalProgress}})],1)])]),n("div",{staticClass:"bodyWrap"},[n("div",{staticClass:"body"},[n("div",{staticClass:"list"},[n("el-table",{staticStyle:{width:"100%"},attrs:{data:t.tableData,stripe:""}},[n("el-table-column",{attrs:{type:"index",label:"序号",align:"center",width:"50"}}),n("el-table-column",{attrs:{prop:"name",label:"文件名",width:"180"}}),n("el-table-column",{attrs:{prop:"progress",label:"上传进度"},scopedSlots:t._u([{key:"default",fn:function(t){return[n("el-progress",{attrs:{percentage:t.row.progress}})]}}])})],1)],1)])])])},ct=[],rt={name:"IndexProgress",data:function(){return{}},computed:Object(c["a"])(Object(c["a"])({},Object(d["c"])("index",{uploader:function(t){return t.uploader},uploadFileList:function(t){return t.uploadFileList},totalProgress:function(t){return t.totalProgress}})),{},{tableData:function(){var t=this.uploadFileList.map((function(t){return{name:t.name,progress:t.progress}}));return t}}),mounted:function(){}},ut=rt,dt=(n("3732"),Object(h["a"])(ut,lt,ct,!1,null,"9c81ee22",null)),pt=dt.exports;o["default"].use(_["a"]);var ft=new _["a"]({routes:[{name:"index",path:"/",component:Z,redirect:"/folder",children:[{name:"indexIndex",path:"/folder",component:at,meta:{title:"全部文件"}},{name:"progress",path:"/progress",component:pt,meta:{title:"上传列表"}}]}]});o["default"].config.productionTip=!1,o["default"].use(s.a),o["default"].use(k.a),o["default"].use(x["a"]),o["default"].config.keyCodes.f1=112,o["default"].config.keyCodes.f1=113,u.a.ajaxSetup({headers:{"X-CSRF-TOKEN":u()('meta[name="csrf-token"]').attr("content")}}),new o["default"]({render:function(t){return t(v)},router:ft,store:P}).$mount("#app")},a309:function(t,e,n){},aece:function(t,e,n){},c5be:function(t,e,n){},e610:function(t,e,n){"use strict";n("0e0d")}});
//# sourceMappingURL=app.ca7d3da6.js.map